<?php

namespace Stella\Clinic\Providers;

use Illuminate\Support\ServiceProvider;

class ClinicServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		\Filament\Panel::configureUsing(function (\Filament\Panel $panel): void {
			$panel->plugin(\Stella\Clinic\ClinicPlugin::make());
		});
	}
	
	public function boot(): void
	{
	}
}
