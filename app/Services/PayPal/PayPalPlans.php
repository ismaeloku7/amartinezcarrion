<?php

namespace App\Services\PayPal;

use App\Models\Language;
use App\Models\Plan;

/**
 * Keeps the PayPal side of a recurring plan in step with the panel: a catalog
 * product per portal plan and a billing plan (P-…) with the same price, period and
 * trial. PayPal plans cannot change price, so a price change creates a new plan and
 * deactivates the previous one; existing subscribers keep paying the old price.
 */
class PayPalPlans
{
    public function __construct(private PayPalClient $client) {}

    /** Create or update the plan on PayPal. Returns what was done: created|replaced|updated|unchanged|skipped. */
    public function sync(Plan $plan): string
    {
        if (! PayPalClient::enabled() || ! $plan->isRecurring() || $plan->price_cents <= 0) {
            return 'skipped';
        }

        $plan->refresh(); // work from the saved row, including database defaults such as `active`
        $this->ensureProduct($plan);

        $remote = $plan->paypal_plan_id ? $this->fetch($plan->paypal_plan_id) : null;

        if ($remote && $this->matches($remote, $plan)) {
            $changed = $this->updateDetails($remote, $plan);
            $changed = $this->updateStatus($remote, $plan) || $changed;

            return $changed ? 'updated' : 'unchanged';
        }

        if ($remote && ($remote['status'] ?? null) === 'ACTIVE') {
            $this->client->post('/v1/billing/plans/'.$remote['id'].'/deactivate');
        }

        $created = $this->client->post('/v1/billing/plans', $this->payload($plan), PayPalClient::requestId('plan-'.$plan->id));
        $plan->forceFill(['paypal_plan_id' => $created['id']])->save();

        if (! $plan->active) {
            $this->client->post('/v1/billing/plans/'.$created['id'].'/deactivate');
        }

        return $remote ? 'replaced' : 'created';
    }

    /** Swallow PayPal errors (report them) so saving in the panel never fails because of PayPal. */
    public function syncQuietly(Plan $plan): array
    {
        try {
            return ['status' => $this->sync($plan), 'error' => null];
        } catch (\Throwable $e) {
            report($e);

            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    public function payload(Plan $plan): array
    {
        $cycles = [];
        $sequence = 1;

        if ($plan->trial_days > 0) {
            $cycles[] = [
                'frequency' => ['interval_unit' => 'DAY', 'interval_count' => (int) $plan->trial_days],
                'tenure_type' => 'TRIAL',
                'sequence' => $sequence++,
                'total_cycles' => 1,
                'pricing_scheme' => ['fixed_price' => ['value' => '0', 'currency_code' => $plan->currency]],
            ];
        }

        $cycles[] = [
            'frequency' => ['interval_unit' => $plan->interval === Plan::INTERVAL_YEAR ? 'YEAR' : 'MONTH', 'interval_count' => 1],
            'tenure_type' => 'REGULAR',
            'sequence' => $sequence,
            'total_cycles' => 0,
            'pricing_scheme' => ['fixed_price' => ['value' => PayPalBilling::money($plan->price_cents), 'currency_code' => $plan->currency]],
        ];

        return [
            'product_id' => $plan->paypal_product_id,
            'name' => self::name($plan),
            'description' => self::description($plan),
            'status' => 'ACTIVE',
            'billing_cycles' => $cycles,
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 2,
            ],
        ];
    }

    /** Same price, currency, period and trial as the panel? Otherwise a new plan is needed. */
    public function matches(array $remote, Plan $plan): bool
    {
        $cycles = collect($remote['billing_cycles'] ?? []);
        $regular = $cycles->firstWhere('tenure_type', 'REGULAR');
        $trial = $cycles->firstWhere('tenure_type', 'TRIAL');

        if (! $regular) {
            return false;
        }

        $price = $regular['pricing_scheme']['fixed_price'] ?? [];
        $unit = $plan->interval === Plan::INTERVAL_YEAR ? 'YEAR' : 'MONTH';
        $trialDays = $trial ? ($trial['frequency']['interval_count'] ?? 0) * match ($trial['frequency']['interval_unit'] ?? 'DAY') {
            'DAY' => 1, 'WEEK' => 7, 'MONTH' => 30, 'YEAR' => 365, default => 1,
        } : 0;

        return PayPalBilling::cents($price['value'] ?? null) === (int) $plan->price_cents
            && strtoupper($price['currency_code'] ?? '') === strtoupper($plan->currency)
            && ($regular['frequency']['interval_unit'] ?? null) === $unit
            && (int) ($regular['frequency']['interval_count'] ?? 0) === 1
            && $trialDays === (int) $plan->trial_days
            && ($remote['product_id'] ?? $plan->paypal_product_id) === $plan->paypal_product_id;
    }

    private function ensureProduct(Plan $plan): void
    {
        if ($plan->paypal_product_id) {
            return;
        }

        $product = $this->client->post('/v1/catalogs/products', [
            'name' => self::name($plan),
            'description' => self::description($plan),
            'type' => 'DIGITAL',
            'category' => 'SOFTWARE',
        ], PayPalClient::requestId('product-'.$plan->id));

        $plan->forceFill(['paypal_product_id' => $product['id']])->save();
    }

    private function fetch(string $paypalPlanId): ?array
    {
        try {
            return $this->client->get('/v1/billing/plans/'.$paypalPlanId);
        } catch (PayPalException $e) {
            if ($e->response->status() === 404) {
                return null; // created in another PayPal account/mode: start again
            }
            throw $e;
        }
    }

    private function updateDetails(array $remote, Plan $plan): bool
    {
        $ops = [];
        if (($remote['name'] ?? null) !== self::name($plan)) {
            $ops[] = ['op' => 'replace', 'path' => '/name', 'value' => self::name($plan)];
        }
        if (($remote['description'] ?? null) !== self::description($plan)) {
            $ops[] = ['op' => 'replace', 'path' => '/description', 'value' => self::description($plan)];
        }
        if ($ops) {
            $this->client->patch('/v1/billing/plans/'.$remote['id'], $ops);
        }

        return $ops !== [];
    }

    private function updateStatus(array $remote, Plan $plan): bool
    {
        $status = $remote['status'] ?? null;
        if ($plan->active && $status === 'INACTIVE') {
            $this->client->post('/v1/billing/plans/'.$remote['id'].'/activate');

            return true;
        }
        if (! $plan->active && $status === 'ACTIVE') {
            $this->client->post('/v1/billing/plans/'.$remote['id'].'/deactivate');

            return true;
        }

        return false;
    }

    private static function name(Plan $plan): string
    {
        return mb_substr($plan->getTranslation('name', Language::defaultCode()) ?: $plan->key, 0, 127);
    }

    private static function description(Plan $plan): string
    {
        $description = $plan->getTranslation('description', Language::defaultCode()) ?: self::name($plan);

        return mb_substr($description, 0, 127);
    }
}
