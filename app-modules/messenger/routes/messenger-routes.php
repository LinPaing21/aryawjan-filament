<?php

use Illuminate\Support\Facades\Route;
use Stella\Messenger\Http\Controllers\MessengerWebhookController;

Route::middleware('web')->prefix('messenger')->name('messenger.')->group(function (): void {
    Route::get('/webhook', [MessengerWebhookController::class, 'verify'])->name('webhook.verify');
    Route::post('/webhook', [MessengerWebhookController::class, 'handle'])->name('webhook.handle');
});
