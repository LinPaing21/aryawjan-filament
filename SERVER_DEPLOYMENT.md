# Server Deployment Guide — Aryawjan (Demo)

Hosting on a single **DigitalOcean Droplet** with Nginx, PHP 8.5, and PostgreSQL 18 + pgvector all on the same server.

**Recommended Droplet:** Basic · 2 GB RAM · 2 vCPUs · Ubuntu 24.04 LTS

---

## 1. Initial Server Setup

```bash
# SSH into your droplet
ssh root@your-server-ip

# Update system
apt update && apt upgrade -y

# Create a non-root user (optional but good practice)
adduser deploy
usermod -aG sudo deploy
```

---

## 2. Install PHP 8.5

```bash
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y php8.5 php8.5-fpm php8.5-cli \
  php8.5-pgsql php8.5-mbstring php8.5-xml \
  php8.5-bcmath php8.5-curl php8.5-zip \
  php8.5-intl php8.5-readline php8.5-tokenizer

# Verify
php -v
```

---

## 3. Install PostgreSQL 18 + pgvector

```bash
# Add the official PostgreSQL apt repository (PGDG)
# Ubuntu's default repos don't carry all PG versions
apt install -y curl ca-certificates
install -d /usr/share/postgresql-common/pgdg
curl -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc --fail \
  https://www.postgresql.org/media/keys/ACCC4CF8.asc
sh -c 'echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] \
  https://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" \
  > /etc/apt/sources.list.d/pgdg.list'
apt update

# Install PostgreSQL 18
apt install -y postgresql-18 postgresql-contrib-18

# Install pgvector build dependencies
apt install -y build-essential postgresql-server-dev-18 git

# Build and install pgvector (latest — do not pin a version, older releases break on PG18)
git clone https://github.com/pgvector/pgvector.git /tmp/pgvector
cd /tmp/pgvector
make && make install
cd ~ && rm -rf /tmp/pgvector

# Start PostgreSQL
systemctl enable postgresql
systemctl start postgresql
```

### Create Database & User

```bash
sudo -u postgres psql <<'SQL'
CREATE USER aryawjan WITH PASSWORD 'your-strong-password';
CREATE DATABASE aryawjan OWNER aryawjan;
\c aryawjan
CREATE EXTENSION IF NOT EXISTS vector;
\q
SQL
```

Verify the extension:

```bash
sudo -u postgres psql -d aryawjan -c "SELECT extname, extversion FROM pg_extension WHERE extname = 'vector';"
```

---

## 4. Install Nginx

```bash
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

---

## 5. Install Composer & Node.js

```bash
# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Node.js 24 (LTS)
curl -fsSL https://deb.nodesource.com/setup_24.x | bash -
apt install -y nodejs
```

---

## 6. Deploy the Application

```bash
# Create web directory
mkdir -p /var/www/aryawjan
chown -R www-data:www-data /var/www/aryawjan

# Clone or upload your project
cd /var/www
git clone your-repo-url aryawjan
# -- OR -- upload via scp/rsync:
# rsync -avz ./ deploy@your-server-ip:/var/www/aryawjan/

cd /var/www/aryawjan

# Install PHP dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build frontend
npm ci && npm run build
```

---

## 7. Configure Environment

```bash
cp .env.example .env
nano .env
```

Set these values (everything else can stay as the example default):

```dotenv
APP_NAME="Aryawjan Clinic"
APP_ENV=production
APP_DEBUG=false
APP_KEY=                          # fill after key:generate
APP_URL=http://your-server-ip     # or https://yourdomain.com

APP_TIMEZONE=Asia/Yangon

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=aryawjan
DB_USERNAME=aryawjan
DB_PASSWORD=your-strong-password
DB_SSLMODE=disable                # same-server, no SSL needed

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local

MAIL_MAILER=log                   # demo: emails go to log file

GEMINI_API_KEY=your-gemini-api-key
```

Then generate the app key:

```bash
php artisan key:generate
```

---

## 8. Run Migrations & Seed

```bash
php artisan migrate --force
php artisan db:seed --force        # only if you have demo seeders
```

---

## 9. Set File Permissions

```bash
chown -R www-data:www-data /var/www/aryawjan
chmod -R 755 /var/www/aryawjan
chmod -R 775 /var/www/aryawjan/storage
chmod -R 775 /var/www/aryawjan/bootstrap/cache

# Storage symlink for public uploads
php artisan storage:link
```

---

## 10. Nginx Virtual Host

```bash
nano /etc/nginx/sites-available/aryawjan
```

Paste:

```nginx
server {
    listen 80;
    server_name your-server-ip;   # or yourdomain.com

    root /var/www/aryawjan/public;
    index index.php;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public";
    }
}
```

Enable and reload:

```bash
ln -s /etc/nginx/sites-available/aryawjan /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

---

## 11. Queue Worker (Systemd)

Since this is a demo, a simple systemd service is enough — no Supervisor or Horizon needed.

```bash
nano /etc/systemd/system/aryawjan-worker.service
```

Paste:

```ini
[Unit]
Description=Aryawjan Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/aryawjan
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
systemctl daemon-reload
systemctl enable aryawjan-worker
systemctl start aryawjan-worker
systemctl status aryawjan-worker
```

---

## 12. Cache Config & Routes

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 13. Verify

| Check | Command |
|---|---|
| App loads | `curl -I http://your-server-ip` |
| Health endpoint | `curl http://your-server-ip/up` |
| Queue worker | `systemctl status aryawjan-worker` |
| PHP-FPM | `systemctl status php8.5-fpm` |
| PostgreSQL | `systemctl status postgresql` |
| pgvector | `sudo -u postgres psql -d aryawjan -c "SELECT extversion FROM pg_extension WHERE extname='vector';"` |
| Logs | `tail -f /var/www/aryawjan/storage/logs/laravel.log` |

---

## Updating the App

```bash
cd /var/www/aryawjan

git pull                                       # or re-upload files

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

systemctl restart aryawjan-worker
systemctl reload nginx
```

---

## Notes

- **No Redis required** — sessions, cache, and queues all use the database driver, which is fine for a demo load.
- **No S3** — uploaded files are stored in `storage/app/private`. They will persist as long as the droplet storage persists.
- **Logs** — written to `storage/logs/laravel.log` (daily rotation, 14-day retention). For demo this is sufficient.
- **HTTPS** — not covered here. If you point a domain at this droplet and want HTTPS, run `apt install certbot python3-certbot-nginx && certbot --nginx -d yourdomain.com`.
