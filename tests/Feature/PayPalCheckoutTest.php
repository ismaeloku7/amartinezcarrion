<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\PaypalSubscription;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private const API = 'https://api-m.sandbox.paypal.com';

    private Product $product;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        config(['paypal.client_id' => 'client', 'paypal.secret' => 'secret', 'paypal.mode' => 'sandbox', 'paypal.webhook_id' => 'WH-1']);
        $this->product = Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP']]);
        $this->user = User::factory()->create();
    }

    private function plan(string $interval, ?string $paypalPlanId = null): Plan
    {
        $plan = Plan::create(['key' => 'mdp-'.$interval, 'name' => ['es' => 'MDP '.$interval], 'interval' => $interval,
            'price_cents' => 499, 'currency' => 'EUR', 'paypal_plan_id' => $paypalPlanId]);
        $plan->products()->sync([$this->product->id]);

        return $plan;
    }

    private function token(): array
    {
        return [self::API.'/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 32400])];
    }

    public function test_single_payment_redirects_to_paypal_and_grants_access_when_captured(): void
    {
        $plan = $this->plan('once');
        Http::fake($this->token() + [
            self::API.'/v2/checkout/orders' => Http::response(['id' => 'ORDER1', 'status' => 'PAYER_ACTION_REQUIRED',
                'links' => [['rel' => 'payer-action', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER1']]]),
            self::API.'/v2/checkout/orders/ORDER1/capture' => Http::response(['id' => 'ORDER1', 'status' => 'COMPLETED',
                'purchase_units' => [['reference_id' => (string) $plan->id, 'custom_id' => (string) $this->user->id,
                    'payments' => ['captures' => [['id' => 'CAP1', 'status' => 'COMPLETED', 'amount' => ['currency_code' => 'EUR', 'value' => '4.99']]]]]]]),
        ]);

        $this->actingAs($this->user)->get('/checkout/'.$plan->id)
            ->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=ORDER1');

        Http::assertSent(fn ($request) => $request->url() === self::API.'/v2/checkout/orders'
            && $request['intent'] === 'CAPTURE'
            && $request['purchase_units'][0]['amount'] === ['currency_code' => 'EUR', 'value' => '4.99']
            && $request['purchase_units'][0]['custom_id'] === (string) $this->user->id);

        $this->actingAs($this->user)->get('/checkout/success?plan='.$plan->id.'&token=ORDER1&PayerID=X')
            ->assertRedirect('/')
            ->assertSessionHas('status', 'Pago completado. Ya tienes acceso.');

        $purchase = Purchase::firstWhere('paypal_order_id', 'ORDER1');
        $this->assertNotNull($purchase);
        $this->assertSame('CAP1', $purchase->paypal_capture_id);
        $this->assertSame(499, $purchase->amount_cents);
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp'));

        // Coming back twice (or the webhook arriving later) does not duplicate the purchase.
        $this->actingAs($this->user)->get('/checkout/success?plan='.$plan->id.'&token=ORDER1');
        $this->assertSame(1, Purchase::count());
    }

    public function test_subscription_redirects_to_paypal_and_grants_access_when_active(): void
    {
        $plan = $this->plan('month', 'P-MONTH');
        Http::fake($this->token() + [
            self::API.'/v1/billing/subscriptions' => Http::response(['id' => 'I-SUB1', 'status' => 'APPROVAL_PENDING',
                'links' => [['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/webapps/billing/subscriptions?ba_token=BA-1']]]),
            self::API.'/v1/billing/subscriptions/I-SUB1' => Http::response(['id' => 'I-SUB1', 'status' => 'ACTIVE', 'plan_id' => 'P-MONTH',
                'custom_id' => (string) $this->user->id, 'billing_info' => ['next_billing_time' => now()->addMonth()->toIso8601ZuluString()]]),
        ]);

        $this->actingAs($this->user)->get('/checkout/'.$plan->id)
            ->assertRedirect('https://www.sandbox.paypal.com/webapps/billing/subscriptions?ba_token=BA-1');

        Http::assertSent(fn ($request) => $request->url() === self::API.'/v1/billing/subscriptions'
            && $request['plan_id'] === 'P-MONTH' && $request['custom_id'] === (string) $this->user->id);

        $pending = PaypalSubscription::firstWhere('paypal_id', 'I-SUB1');
        $this->assertSame('APPROVAL_PENDING', $pending->status);
        $this->assertFalse(app(AccessService::class)->hasAccess($this->user, 'mdp'));

        $this->actingAs($this->user)->get('/checkout/success?plan='.$plan->id.'&subscription_id=I-SUB1&ba_token=BA-1')
            ->assertRedirect('/')
            ->assertSessionHas('status', 'Pago completado. Ya tienes acceso.');

        $this->assertSame('ACTIVE', $pending->fresh()->status);
        $this->assertNotNull($pending->fresh()->paid_through_at);
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp'));
    }

    public function test_webhook_cancellation_keeps_access_until_the_paid_period_ends(): void
    {
        $plan = $this->plan('month', 'P-MONTH');
        $paidThrough = now()->addDays(10)->startOfSecond();
        PaypalSubscription::create(['user_id' => $this->user->id, 'plan_id' => $plan->id, 'paypal_id' => 'I-SUB1',
            'paypal_plan_id' => 'P-MONTH', 'status' => 'ACTIVE', 'paid_through_at' => $paidThrough]);
        app(AccessService::class)->grantPlan($this->user, $plan, Entitlement::SOURCE_SUBSCRIPTION, 'I-SUB1');

        Http::fake($this->token() + [
            self::API.'/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS']),
        ]);

        $this->postJson('/paypal/webhook', [
            'id' => 'WH-EVT-1', 'event_type' => 'BILLING.SUBSCRIPTION.CANCELLED',
            'resource' => ['id' => 'I-SUB1', 'status' => 'CANCELLED', 'plan_id' => 'P-MONTH', 'custom_id' => (string) $this->user->id],
        ], $this->signatureHeaders())->assertOk();

        Http::assertSent(fn ($request) => $request->url() === self::API.'/v1/notifications/verify-webhook-signature'
            && $request['webhook_id'] === 'WH-1' && $request['webhook_event']['event_type'] === 'BILLING.SUBSCRIPTION.CANCELLED');

        $subscription = PaypalSubscription::firstWhere('paypal_id', 'I-SUB1');
        $this->assertSame('CANCELLED', $subscription->status);
        $this->assertNotNull($subscription->cancelled_at);

        $entitlement = $this->user->entitlements()->first();
        $this->assertTrue($entitlement->ends_at->equalTo($paidThrough));
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp'));

        $this->travelTo($paidThrough->copy()->addMinute());
        $this->assertFalse(app(AccessService::class)->hasAccess($this->user->fresh(), 'mdp'));
    }

    public function test_webhook_activation_grants_access_to_the_user_in_custom_id(): void
    {
        $plan = $this->plan('year', 'P-YEAR');
        Http::fake($this->token() + [
            self::API.'/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'SUCCESS']),
        ]);

        $this->postJson('/paypal/webhook', [
            'id' => 'WH-EVT-2', 'event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED',
            'resource' => ['id' => 'I-NEW', 'status' => 'ACTIVE', 'plan_id' => 'P-YEAR', 'custom_id' => (string) $this->user->id,
                'billing_info' => ['next_billing_time' => now()->addYear()->toIso8601ZuluString()]],
        ], $this->signatureHeaders())->assertOk();

        $this->assertDatabaseHas('paypal_subscriptions', ['paypal_id' => 'I-NEW', 'user_id' => $this->user->id, 'plan_id' => $plan->id, 'status' => 'ACTIVE']);
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp'));
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        Http::fake($this->token() + [
            self::API.'/v1/notifications/verify-webhook-signature' => Http::response(['verification_status' => 'FAILURE']),
        ]);

        $this->postJson('/paypal/webhook', ['event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED', 'resource' => ['id' => 'I-X']], $this->signatureHeaders())
            ->assertStatus(400);

        $this->postJson('/paypal/webhook', ['event_type' => 'BILLING.SUBSCRIPTION.ACTIVATED', 'resource' => ['id' => 'I-X']])
            ->assertStatus(400);

        $this->assertDatabaseCount('paypal_subscriptions', 0);
    }

    public function test_user_can_cancel_their_own_subscription(): void
    {
        $plan = $this->plan('month', 'P-MONTH');
        $subscription = PaypalSubscription::create(['user_id' => $this->user->id, 'plan_id' => $plan->id, 'paypal_id' => 'I-SUB1',
            'paypal_plan_id' => 'P-MONTH', 'status' => 'ACTIVE', 'paid_through_at' => now()->addDays(5)]);
        app(AccessService::class)->grantPlan($this->user, $plan, Entitlement::SOURCE_SUBSCRIPTION, 'I-SUB1');

        Http::fake($this->token() + [
            self::API.'/v1/billing/subscriptions/I-SUB1/cancel' => Http::response('', 204),
            self::API.'/v1/billing/subscriptions/I-SUB1' => Http::response(['id' => 'I-SUB1', 'status' => 'CANCELLED', 'plan_id' => 'P-MONTH']),
        ]);

        $other = User::factory()->create();
        $this->actingAs($other)->post('/billing/subscriptions/'.$subscription->id.'/cancel')->assertForbidden();

        $this->actingAs($this->user)->post('/billing/subscriptions/'.$subscription->id.'/cancel')->assertRedirect('/cuenta');

        Http::assertSent(fn ($request) => $request->url() === self::API.'/v1/billing/subscriptions/I-SUB1/cancel');
        $this->assertSame('CANCELLED', $subscription->fresh()->status);
        $this->assertNotNull($this->user->entitlements()->first()->ends_at);
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp')); // until paid_through_at
    }

    public function test_account_page_lists_subscriptions_with_a_cancel_button(): void
    {
        $plan = $this->plan('month', 'P-MONTH');
        $subscription = PaypalSubscription::create(['user_id' => $this->user->id, 'plan_id' => $plan->id, 'paypal_id' => 'I-SUB1',
            'paypal_plan_id' => 'P-MONTH', 'status' => 'ACTIVE', 'paid_through_at' => now()->addDays(5)]);

        $this->actingAs($this->user)->get('/cuenta')
            ->assertOk()
            ->assertSee('MDP month')
            ->assertSee('Próximo cobro')
            ->assertSee('/billing/subscriptions/'.$subscription->id.'/cancel');

        $subscription->update(['status' => 'CANCELLED']);
        $this->actingAs($this->user)->get('/cuenta')
            ->assertOk()
            ->assertSee('Acceso hasta')
            ->assertDontSee('/billing/subscriptions/'.$subscription->id.'/cancel');
    }

    private function signatureHeaders(): array
    {
        return [
            'PAYPAL-AUTH-ALGO' => 'SHA256withRSA',
            'PAYPAL-CERT-URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-1',
            'PAYPAL-TRANSMISSION-ID' => 'tx-1',
            'PAYPAL-TRANSMISSION-SIG' => 'sig',
            'PAYPAL-TRANSMISSION-TIME' => '2026-11-02T10:00:00Z',
        ];
    }
}
