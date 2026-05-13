<?php

namespace Stella\Messenger\Providers;

use Illuminate\Support\ServiceProvider;
use Stella\Messenger\Services\MessengerService;

class MessengerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MessengerService::class);
    }

    public function boot(): void {}
}
