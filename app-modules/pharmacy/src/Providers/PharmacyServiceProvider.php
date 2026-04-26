<?php

namespace Stella\Pharmacy\Providers;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Stella\Pharmacy\PharmacyPlugin;

class PharmacyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            $panel->plugin(PharmacyPlugin::make());
        });
    }

    public function boot(): void {}
}
