<?php

namespace Stella\Ai;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AiPlugin implements Plugin
{
    public function getId(): string
    {
        return 'ai';
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
                for: 'Stella\\Ai\\Filament\\Resources',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Stella\\Ai\\Filament\\Widgets',
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => Blade::render('@livewire(\'Stella\\\\Ai\\\\Livewire\\\\AdminAssistantFloating\')'),
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
