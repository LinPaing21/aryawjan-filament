<?php

namespace Stella\Messenger\Services;

use Illuminate\Support\Facades\Log;
use JoelButcher\Facebook\Facades\Facebook;

class MessengerService
{
    /**
     * Send a text message to a Messenger user.
     */
    public function sendTextMessage(string $recipientId, string $text): void
    {
        try {
            Facebook::post('/me/messages', [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $text],
                'access_token' => config('facebook.page_access_token'),
            ]);
        } catch (\Throwable $e) {
            Log::error('MessengerService: failed to send message', [
                'recipient' => $recipientId,
                'error' => $e->getMessage(),
            ]);
        }
    }

}
