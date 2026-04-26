<?php

namespace Stella\Clinic;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Stella\Clinic\Filament\Resources\DoctorScheduleResource;

class ClinicPlugin implements Plugin
{
    public function getId(): string
    {
        return 'clinic';
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
                for: 'Stella\\Clinic\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Stella\\Clinic\\Filament\\Pages',
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
