<?php

namespace App\Providers;

use App\Listeners\SyncEntitlementsFromStripe;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(WebhookReceived::class, SyncEntitlementsFromStripe::class);
    }
}
