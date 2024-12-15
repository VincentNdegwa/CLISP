<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Laravel\Paddle\Events\WebhookReceived;

class PaddleEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        $eventType = $event->payload['event_type'];
        Log::info("Listened the paddle event " . $eventType);

        if ($eventType === 'subscription.activated') {
            redirect(route(('subscription.activated')));
        }

        if ($eventType === 'subscription.cancelled') {
            redirect(route(('subscription.cancelled')));
        }
    }
}
