# Aryawjan Clinic AI

A full-stack clinic management system integrating a **Viber chatbot** for patient interaction with an **AI-powered dashboard** for doctors and pharmacists.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Architecture | Modular — [InterNachi/Modular](https://github.com/InterNachi/modular) |
| Admin UI | Filament v5 |
| Reactive UI | Livewire v4 |
| Styling | Tailwind CSS v4 |
| Database | PostgreSQL 18 with pgvector |
| Cache | Redis |
| Queue | Database / Redis |
| AI Provider | Google Gemini (via Laravel AI SDK) |
| Patient Channel | Viber Bot API |
| Access Control | Spatie Laravel Permission v7 |
| Local Dev | Laravel Sail (Docker) |

---

## Modules

The application is split into four independent modules under `app-modules/`, each with its own models, migrations, Filament resources, service providers, and tests.

### `stella/users`
Manages all user accounts and access control.

- Roles: **Admin**, **Doctor**, **Pharmacist**, **Patient**
- Doctor profiles (specialisation, linked schedule)
- Patient profiles
- Permission-based Filament resource visibility

### `stella/clinic`
Core clinic operations.

- **DoctorSchedule** — available days (stored as JSON array e.g. `["Mon","Wed","Fri"]`), time slots, and max patients per day
- **Appointment** — token-based booking system with statuses
- **MedicalRecord** — doctor's clinical notes, AI-suggested diagnoses, prescriptions
- **Invoice** — linked to medical records

### `stella/pharmacy`
Pharmacy inventory and dispensing workflow.

- **Medicine** — stock tracking
- **PharmacyRequest** — AI-suggested medications submitted for pharmacist review and doctor approval before dispensing

### `stella/ai`
AI integration and knowledge base.

- **RAG (Retrieval-Augmented Generation)** — medical books ingested as vector embeddings stored in `medical_knowledge` (pgvector)
- **Triage Service** — classifies patient-reported symptoms into a care path
- **AI Tools** — `CreateMedicalRecord`, `RetrievePatientMedicalRecord` used by the doctor assistant chat

---

## Business Logic

### Triage Flow (Viber → AI → Clinic/Pharmacy)

```
Patient sends symptoms via Viber
        │
        ▼
  AI Triage Service
  (Gemini + RAG on medical knowledge base)
        │
        ├── Serious symptoms ──► Book Appointment
        │                              │
        │                        Doctor reviews
        │                        Medical Record created
        │                        Invoice generated
        │
        └── Minor symptoms ──► PharmacyRequest created
                                       │
                               Pharmacist reviews
                                       │
                               Doctor approves
                                       │
                               Medication dispensed
```

### Doctor Dashboard
- Sees only **own** appointments, medical records, and schedule (row-level scoping via `doctor_id`)
- AI assistant chat (Gemini) with access to RAG medical knowledge base
- Creates and signs off medical records and prescriptions

### Pharmacist Dashboard
- Reviews incoming `PharmacyRequest` items flagged as minor by triage
- Confirms stock availability; routes to doctor for final approval
- AI assistant chat for drug interaction and dosage guidance

### Admin Dashboard
- Full access to all resources across modules
- User and role management
- System logs via Filament Log Viewer

---

## Local Development

**Requirements:** Docker

```bash
# 1. Copy environment file
cp .env.example .env

# 2. Install dependencies
composer install

# 3. Start containers
./vendor/bin/sail up -d

# 4. Generate app key and run migrations
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# 5. Build frontend assets
npm install && npm run build

# 6. Start all dev processes (server, queue, logs, Vite)
composer run dev
```

The app will be available at `http://localhost`.

---

## Testing

```bash
# Run all tests
./vendor/bin/sail artisan test --compact

# Run a specific test file
./vendor/bin/sail artisan test --compact tests/Feature/ExampleTest.php

# Run tests matching a name
./vendor/bin/sail artisan test --compact --filter=testName
```

Test suites: **Unit**, **Feature**, and per-module tests in `app-modules/*/tests/`.

---

## Environment Variables

Key variables to configure (see `.env.example` for the full list):

```dotenv
APP_NAME=Aryawjan
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=aryawjan

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

GEMINI_API_KEY=your-key-here
```

See [`LARAVEL_CLOUD_REVIEW.md`](./LARAVEL_CLOUD_REVIEW.md) for the full production deployment checklist.

---

## License

Private — all rights reserved.
