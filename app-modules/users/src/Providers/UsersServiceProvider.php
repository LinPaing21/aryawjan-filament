<?php

namespace Stella\Users\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Stella\Users\UsersPlugin;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(UsersPlugin::make());
        });
    }

    public function boot(): void {}
}
