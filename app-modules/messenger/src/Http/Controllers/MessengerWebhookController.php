<?php

namespace Stella\Messenger\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stella\Messenger\Services\MessengerService;

class MessengerWebhookController extends Controller
{
    public function __construct(
        private readonly MessengerService $messengerService,
    ) {}

    public function verify(Request $request): Response
    {
        if ($request->query('hub_verify_token') !== config('facebook.verify_token')) {
            abort(403);
        }

        return response($request->query('hub_challenge'));
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (($payload['object'] ?? null) !== 'page') {
            return response()->json(['status' => 'ignored']);
        }

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $event) {
                $senderId = $event['sender']['id'] ?? null;

                if (! $senderId || isset($event['message']['is_echo'])) {
                    continue;
                }

                if (isset($event['message'])) {
                    $this->messengerService->sendTextMessage($senderId, 'Hello World');
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
