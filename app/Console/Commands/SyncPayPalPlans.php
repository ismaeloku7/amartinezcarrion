<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Services\PayPal\PayPalClient;
use App\Services\PayPal\PayPalPlans;
use Illuminate\Console\Command;

class SyncPayPalPlans extends Command
{
    protected $signature = 'paypal:sync-plans {--plan= : Only this plan key}';

    protected $description = 'Create or update the PayPal billing plans (P-…) for the recurring plans of the catalog.';

    public function handle(PayPalPlans $plans): int
    {
        if (! PayPalClient::enabled()) {
            $this->error('PAYPAL_CLIENT_ID / PAYPAL_SECRET are not configured.');

            return self::FAILURE;
        }

        $query = Plan::query()->where('interval', '!=', Plan::INTERVAL_ONCE)->orderBy('position');
        if ($key = $this->option('plan')) {
            $query->where('key', $key);
        }

        $failed = 0;
        foreach ($query->get() as $plan) {
            $result = $plans->syncQuietly($plan);
            $line = sprintf('%-14s %-10s %s', $plan->key, $result['status'], $plan->fresh()->paypal_plan_id ?? '-');
            $result['status'] === 'failed' ? $this->error($line.'  '.$result['error']) : $this->line($line);
            $failed += $result['status'] === 'failed' ? 1 : 0;
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
