<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Services\PayPal\PayPalPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalPlansTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api-m.sandbox.paypal.com';

    protected function setUp(): void
    {
        parent::setUp();
        config(['paypal.client_id' => 'client', 'paypal.secret' => 'secret', 'paypal.mode' => 'sandbox']);
    }

    private function token(): array
    {
        return [self::API.'/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 32400])];
    }

    private function remotePlan(string $id, string $value, string $unit, int $trialDays = 0, string $status = 'ACTIVE'): array
    {
        $cycles = [];
        if ($trialDays) {
            $cycles[] = ['tenure_type' => 'TRIAL', 'frequency' => ['interval_unit' => 'DAY', 'interval_count' => $trialDays]];
        }
        $cycles[] = ['tenure_type' => 'REGULAR', 'frequency' => ['interval_unit' => $unit, 'interval_count' => 1],
            'pricing_scheme' => ['fixed_price' => ['value' => $value, 'currency_code' => 'EUR']]];

        return ['id' => $id, 'product_id' => 'PROD-1', 'name' => 'Devocional mensual', 'description' => 'Devocional mensual', 'status' => $status, 'billing_cycles' => $cycles];
    }

    public function test_saving_a_recurring_plan_creates_the_product_and_plan_on_paypal(): void
    {
        Http::fake($this->token() + [
            self::API.'/v1/catalogs/products' => Http::response(['id' => 'PROD-1']),
            self::API.'/v1/billing/plans' => Http::response(['id' => 'P-NEW', 'status' => 'ACTIVE']),
        ]);
        $plan = Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Devocional mensual'], 'interval' => 'month', 'price_cents' => 499, 'currency' => 'EUR', 'trial_days' => 7]);

        $this->assertSame('created', app(PayPalPlans::class)->sync($plan));

        $plan->refresh();
        $this->assertSame('PROD-1', $plan->paypal_product_id);
        $this->assertSame('P-NEW', $plan->paypal_plan_id);

        Http::assertSent(fn ($r) => $r->url() === self::API.'/v1/catalogs/products' && $r['type'] === 'DIGITAL' && $r['name'] === 'Devocional mensual');
        Http::assertSent(function ($r) {
            if ($r->url() !== self::API.'/v1/billing/plans') {
                return false;
            }
            $cycles = $r['billing_cycles'];

            return $r['product_id'] === 'PROD-1'
                && $cycles[0]['tenure_type'] === 'TRIAL' && $cycles[0]['frequency'] === ['interval_unit' => 'DAY', 'interval_count' => 7]
                && $cycles[1]['tenure_type'] === 'REGULAR' && $cycles[1]['frequency'] === ['interval_unit' => 'MONTH', 'interval_count' => 1]
                && $cycles[1]['pricing_scheme']['fixed_price'] === ['value' => '4.99', 'currency_code' => 'EUR']
                && $cycles[1]['total_cycles'] === 0
                && $r['payment_preferences']['auto_bill_outstanding'] === true;
        });
    }

    public function test_changing_the_price_creates_a_new_plan_and_deactivates_the_old_one(): void
    {
        Http::fake($this->token() + [
            self::API.'/v1/billing/plans/P-OLD' => Http::response($this->remotePlan('P-OLD', '4.99', 'MONTH')),
            self::API.'/v1/billing/plans/P-OLD/deactivate' => Http::response('', 204),
            self::API.'/v1/billing/plans' => Http::response(['id' => 'P-NEW', 'status' => 'ACTIVE']),
        ]);
        $plan = Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Devocional mensual'], 'interval' => 'month', 'price_cents' => 599, 'currency' => 'EUR',
            'paypal_product_id' => 'PROD-1', 'paypal_plan_id' => 'P-OLD']);

        $this->assertSame('replaced', app(PayPalPlans::class)->sync($plan));
        $this->assertSame('P-NEW', $plan->fresh()->paypal_plan_id);

        Http::assertSent(fn ($r) => $r->url() === self::API.'/v1/billing/plans/P-OLD/deactivate');
        Http::assertSent(fn ($r) => $r->url() === self::API.'/v1/billing/plans' && $r['billing_cycles'][0]['pricing_scheme']['fixed_price']['value'] === '5.99');
        Http::assertNotSent(fn ($r) => $r->url() === self::API.'/v1/catalogs/products');
    }

    public function test_an_unchanged_plan_only_patches_the_name_when_it_differs(): void
    {
        Http::fake($this->token() + [
            self::API.'/v1/billing/plans/P-OLD' => Http::response($this->remotePlan('P-OLD', '4.99', 'MONTH')),
            self::API.'/v1/billing/plans/P-OLD/deactivate' => Http::response('', 204),
            self::API.'/v1/billing/plans' => Http::response(['id' => 'P-NEW']),
        ]);
        $plan = Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Devocional mensual'], 'interval' => 'month', 'price_cents' => 499, 'currency' => 'EUR',
            'paypal_product_id' => 'PROD-1', 'paypal_plan_id' => 'P-OLD']);

        $this->assertSame('unchanged', app(PayPalPlans::class)->sync($plan));
        Http::assertNotSent(fn ($r) => $r->url() === self::API.'/v1/billing/plans');

        $plan->setTranslation('name', 'es', 'Devocional cada mes')->save();
        Http::fake($this->token() + [
            self::API.'/v1/billing/plans/P-OLD' => Http::response($this->remotePlan('P-OLD', '4.99', 'MONTH')),
        ]);
        $this->assertSame('updated', app(PayPalPlans::class)->sync($plan));
        Http::assertSent(fn ($r) => $r->method() === 'PATCH' && $r->url() === self::API.'/v1/billing/plans/P-OLD' && $r[0]['path'] === '/name' && $r[0]['value'] === 'Devocional cada mes');
        $this->assertSame('P-OLD', $plan->fresh()->paypal_plan_id);
    }

    public function test_deactivating_a_plan_in_the_panel_deactivates_it_on_paypal(): void
    {
        Http::fake($this->token() + [
            self::API.'/v1/billing/plans/P-OLD' => Http::response($this->remotePlan('P-OLD', '4.99', 'MONTH')),
            self::API.'/v1/billing/plans/P-OLD/deactivate' => Http::response('', 204),
        ]);
        $plan = Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Devocional mensual'], 'interval' => 'month', 'price_cents' => 499, 'currency' => 'EUR',
            'paypal_product_id' => 'PROD-1', 'paypal_plan_id' => 'P-OLD', 'active' => false]);

        $this->assertSame('updated', app(PayPalPlans::class)->sync($plan));
        Http::assertSent(fn ($r) => $r->url() === self::API.'/v1/billing/plans/P-OLD/deactivate');
    }

    public function test_one_off_plans_and_missing_credentials_are_skipped(): void
    {
        Http::fake();
        $once = Plan::create(['key' => 'test-once', 'name' => ['es' => 'Test'], 'interval' => 'once', 'price_cents' => 1999]);
        $this->assertSame('skipped', app(PayPalPlans::class)->sync($once));

        config(['paypal.client_id' => null]);
        $monthly = Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Mensual'], 'interval' => 'month', 'price_cents' => 499]);
        $this->assertSame('skipped', app(PayPalPlans::class)->sync($monthly));
        Http::assertNothingSent();
    }

    public function test_sync_command_reports_each_recurring_plan(): void
    {
        Http::fake($this->token() + [
            self::API.'/v1/catalogs/products' => Http::response(['id' => 'PROD-1']),
            self::API.'/v1/billing/plans' => Http::response(['id' => 'P-NEW', 'status' => 'ACTIVE']),
        ]);
        Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Mensual'], 'interval' => 'month', 'price_cents' => 499]);
        Plan::create(['key' => 'test-once', 'name' => ['es' => 'Test'], 'interval' => 'once', 'price_cents' => 1999]);

        $this->artisan('paypal:sync-plans')
            ->expectsOutputToContain('mdp-month')
            ->doesntExpectOutputToContain('test-once')
            ->assertSuccessful();

        $this->assertSame('P-NEW', Plan::firstWhere('key', 'mdp-month')->paypal_plan_id);
    }
}
