<?php

namespace Stella\Ai\Providers;

use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
	public function register(): void
	{
        \Filament\Panel::configureUsing(function (\Filament\Panel $panel): void {
			$panel->plugin(\Stella\Ai\AiPlugin::make());
		});
	}

	public function boot(): void
	{
	}
}
