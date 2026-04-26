<?php

namespace Stella\Pharmacy;

use Filament\Contracts\Plugin;
use Filament\Panel;

class PharmacyPlugin implements Plugin
{
    public function getId(): string
    {
        return 'pharmacy';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'Stella\\Pharmacy\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Stella\\Pharmacy\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Stella\\Pharmacy\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
