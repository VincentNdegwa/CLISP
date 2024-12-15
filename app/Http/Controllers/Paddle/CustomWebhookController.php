<?php

namespace App\Http\Controllers\Paddle;

use Laravel\Paddle\Http\Controllers\WebhookController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomWebhookController extends BaseController
{

    /**
     * Handle the SubscriptionCanceled event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleSubscriptionCanceled(array $payload)
    {
        Log::info('Subscription canceled', $payload);

        // Add your logic for handling subscription cancellations
    }

    /**
     * Handle the SubscriptionCreated event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleSubscriptionCreated(array $payload)
    {
        Log::info('Subscription created', $payload);

        // Add your logic for handling subscription creation
    }

    /**
     * Handle the SubscriptionPaused event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleSubscriptionPaused(array $payload)
    {
        Log::info('Subscription paused', $payload);

        // Add your logic for handling subscription pauses
    }

    /**
     * Handle the SubscriptionUpdated event.
     *
     * @param  array  $payload
     * @return void
     */
    protected function handleSubscriptionUpdated(array $payload)
    {
        Log::info('Subscription updated', $payload);

        // Add your logic for handling subscription updates
    }
}
