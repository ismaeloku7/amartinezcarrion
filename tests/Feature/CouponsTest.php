<?php

namespace Tests\Feature;

use App\Livewire\Portal\Paywall;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Plan;
use App\Models\PlanPaypalVariant;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AccessService;
use App\Services\Coupons\CouponException;
use App\Services\Coupons\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class CouponsTest extends TestCase
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

    private function plan(string $key, string $interval, int $price, ?string $paypalPlanId = null): Plan
    {
        $plan = Plan::create(['key' => $key, 'name' => ['es' => 'Plan '.$key], 'interval' => $interval, 'price_cents' => $price, 'currency' => 'EUR', 'paypal_plan_id' => $paypalPlanId, 'paypal_product_id' => $paypalPlanId ? 'PROD-1' : null]);
        $plan->products()->sync([$this->product->id]);

        return $plan;
    }

    private function token(): array
    {
        return [self::API.'/v1/oauth2/token' => Http::response(['access_token' => 'tok', 'expires_in' => 32400])];
    }

    public function test_percent_and_fixed_coupons_compute_prices_and_respect_their_rules(): void
    {
        $service = app(CouponService::class);
        $monthly = $this->plan('mdp-month', 'month', 499, 'P-M');
        $once = $this->plan('test-once', 'once', 1999);

        $percent = Coupon::create(['code' => 'verano20', 'type' => 'percent', 'value' => 20, 'duration' => 'once']);
        $discount = $service->apply(' Verano20 ', $monthly, $this->user);
        $this->assertSame([499, 399, 499], [$discount->originalCents, $discount->firstCents, $discount->recurringCents]);
        $this->assertTrue($discount->changesRecurringPlan());

        $percent->update(['duration' => 'forever']);
        $discount = $service->apply('VERANO20', $monthly, $this->user);
        $this->assertSame([399, 399], [$discount->firstCents, $discount->recurringCents]);
        $discount = $service->apply('VERANO20', $once, $this->user);
        $this->assertSame([1599, 1599], [$discount->firstCents, $discount->recurringCents]);
        $this->assertFalse($discount->changesRecurringPlan());

        Coupon::create(['code' => 'MENOS5', 'type' => 'fixed', 'value' => 500]);
        $this->assertSame(1499, $service->apply('MENOS5', $once, $this->user)->firstCents);

        // Fixed discount bigger than the price: free for one-off, refused for subscriptions.
        Coupon::create(['code' => 'GRATIS', 'type' => 'fixed', 'value' => 5000, 'duration' => 'forever']);
        $this->assertSame(0, $service->apply('GRATIS', $once, $this->user)->firstCents);
        $this->expectException(CouponException::class);
        $service->apply('GRATIS', $monthly, $this->user);
    }

    public function test_coupon_restrictions_are_enforced(): void
    {
        $service = app(CouponService::class);
        $monthly = $this->plan('mdp-month', 'month', 499, 'P-M');
        $yearly = $this->plan('mdp-year', 'year', 3999, 'P-Y');

        $this->assertThrowsMessage(fn () => $service->apply('NOPE', $monthly, $this->user), 'Este cupón no existe.');

        $coupon = Coupon::create(['code' => 'ANUAL10', 'type' => 'percent', 'value' => 10]);
        $coupon->plans()->sync([$yearly->id]);
        $this->assertThrowsMessage(fn () => $service->apply('ANUAL10', $monthly, $this->user), 'Este cupón no es válido para este plan.');
        $this->assertSame(3599, $service->apply('ANUAL10', $yearly, $this->user)->firstCents);

        $coupon->update(['expires_at' => now()->subDay()]);
        $this->assertThrowsMessage(fn () => $service->apply('ANUAL10', $yearly, $this->user), 'Este cupón ha caducado.');
        $coupon->update(['expires_at' => null, 'starts_at' => now()->addDay()]);
        $this->assertThrowsMessage(fn () => $service->apply('ANUAL10', $yearly, $this->user), 'Este cupón aún no está activo.');
        $coupon->update(['starts_at' => null, 'active' => false]);
        $this->assertThrowsMessage(fn () => $service->apply('ANUAL10', $yearly, $this->user), 'Este cupón ya no está activo.');

        $coupon->update(['active' => true, 'max_redemptions' => 1]);
        $pending = $service->reserve($service->apply('ANUAL10', $yearly, $this->user), $this->user, paypalOrderId: 'O-1');
        $this->assertSame(3599, $service->apply('ANUAL10', $yearly, $this->user)->firstCents, 'pending uses do not count');
        $service->confirmOrder('O-1');
        $this->assertNotNull($pending->fresh()->confirmed_at);
        $this->assertThrowsMessage(fn () => $service->apply('ANUAL10', $yearly, User::factory()->create()), 'Este cupón ya se ha agotado.');

        $coupon->update(['max_redemptions' => null]);
        $this->assertThrowsMessage(fn () => $service->apply('ANUAL10', $yearly, $this->user), 'Ya has usado este cupón.');
        $coupon->update(['once_per_user' => false]);
        $this->assertSame(3599, $service->apply('ANUAL10', $yearly, $this->user)->firstCents);
    }

    public function test_single_payment_with_coupon_sends_the_discounted_amount_and_confirms_the_use_on_capture(): void
    {
        $plan = $this->plan('test-once', 'once', 1999);
        Coupon::create(['code' => 'MENOS5', 'type' => 'fixed', 'value' => 500]);
        Http::fake($this->token() + [
            self::API.'/v2/checkout/orders' => Http::response(['id' => 'ORDER1', 'links' => [['rel' => 'payer-action', 'href' => 'https://paypal.test/approve']]]),
            self::API.'/v2/checkout/orders/ORDER1/capture' => Http::response(['id' => 'ORDER1', 'status' => 'COMPLETED',
                'purchase_units' => [['reference_id' => (string) $plan->id, 'custom_id' => (string) $this->user->id,
                    'payments' => ['captures' => [['id' => 'CAP1', 'status' => 'COMPLETED', 'amount' => ['currency_code' => 'EUR', 'value' => '14.99']]]]]]]),
        ]);

        $this->actingAs($this->user)->get('/checkout/'.$plan->id.'?coupon=menos5')->assertRedirect('https://paypal.test/approve');

        Http::assertSent(function ($r) {
            if ($r->url() !== self::API.'/v2/checkout/orders') {
                return false;
            }
            $unit = $r['purchase_units'][0];

            return $unit['amount']['value'] === '14.99'
                && $unit['amount']['breakdown']['item_total']['value'] === '19.99'
                && $unit['amount']['breakdown']['discount']['value'] === '5.00'
                && $unit['items'][0]['unit_amount']['value'] === '19.99';
        });
        $redemption = CouponRedemption::first();
        $this->assertSame(['ORDER1', 1999, 1499, null], [$redemption->paypal_order_id, $redemption->original_cents, $redemption->first_cents, $redemption->confirmed_at]);

        $this->actingAs($this->user)->get('/checkout/success?plan='.$plan->id.'&token=ORDER1')->assertRedirect('/');

        $redemption->refresh();
        $this->assertNotNull($redemption->confirmed_at);
        $this->assertSame(Purchase::first()->id, $redemption->purchase_id);
        $this->assertSame(1499, Purchase::first()->amount_cents);
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp'));
    }

    public function test_invalid_coupon_on_checkout_returns_to_the_paywall_with_the_reason(): void
    {
        $plan = $this->plan('test-once', 'once', 1999);
        Http::fake();

        $this->actingAs($this->user)->get('/checkout/'.$plan->id.'?coupon=NOPE')
            ->assertRedirect('/planes/mdp?cupon=NOPE')
            ->assertSessionHas('coupon_error', 'Este cupón no existe.');
        Http::assertNothingSent();
        $this->assertSame(0, CouponRedemption::count());
    }

    public function test_full_discount_on_a_single_payment_grants_access_without_paypal(): void
    {
        $plan = $this->plan('test-once', 'once', 1999);
        Coupon::create(['code' => 'REGALO', 'type' => 'percent', 'value' => 100, 'max_redemptions' => 1]);
        Http::fake();

        $this->actingAs($this->user)->get('/checkout/'.$plan->id.'?coupon=REGALO')
            ->assertRedirect('/')
            ->assertSessionHas('status', 'Cupón aplicado. Ya tienes acceso.');

        Http::assertNothingSent();
        $this->assertSame(0, Purchase::first()->amount_cents);
        $this->assertNotNull(CouponRedemption::first()->confirmed_at);
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp'));

        // The single use is spent.
        $this->actingAs(User::factory()->create())->get('/checkout/'.$plan->id.'?coupon=REGALO')
            ->assertSessionHas('coupon_error', 'Este cupón ya se ha agotado.');
    }

    public function test_subscription_with_a_first_period_coupon_uses_a_derived_paypal_plan(): void
    {
        $plan = $this->plan('mdp-month', 'month', 499, 'P-M');
        Coupon::create(['code' => 'PRIMERMES', 'type' => 'percent', 'value' => 50, 'duration' => 'once']);
        Http::fake($this->token() + [
            self::API.'/v1/billing/plans' => Http::response(['id' => 'P-VARIANT', 'status' => 'ACTIVE']),
            self::API.'/v1/billing/subscriptions' => Http::sequence()
                ->push(['id' => 'I-SUB1', 'status' => 'APPROVAL_PENDING', 'links' => [['rel' => 'approve', 'href' => 'https://paypal.test/approve']]])
                ->push(['id' => 'I-SUB2', 'status' => 'APPROVAL_PENDING', 'links' => [['rel' => 'approve', 'href' => 'https://paypal.test/approve2']]]),
            self::API.'/v1/billing/subscriptions/I-SUB1' => Http::response(['id' => 'I-SUB1', 'status' => 'ACTIVE', 'plan_id' => 'P-VARIANT', 'custom_id' => (string) $this->user->id,
                'billing_info' => ['next_billing_time' => now()->addMonth()->toIso8601ZuluString()]]),
        ]);

        $this->actingAs($this->user)->get('/checkout/'.$plan->id.'?coupon=PRIMERMES')->assertRedirect('https://paypal.test/approve');

        Http::assertSent(function ($r) {
            if ($r->url() !== self::API.'/v1/billing/plans') {
                return false;
            }
            $cycles = $r['billing_cycles'];

            return $r['product_id'] === 'PROD-1'
                && $cycles[0]['tenure_type'] === 'TRIAL' && $cycles[0]['total_cycles'] === 1
                && $cycles[0]['frequency'] === ['interval_unit' => 'MONTH', 'interval_count' => 1]
                && $cycles[0]['pricing_scheme']['fixed_price']['value'] === '2.50'
                && $cycles[1]['tenure_type'] === 'REGULAR' && $cycles[1]['pricing_scheme']['fixed_price']['value'] === '4.99';
        });
        Http::assertSent(fn ($r) => $r->url() === self::API.'/v1/billing/subscriptions' && $r['plan_id'] === 'P-VARIANT');
        $this->assertDatabaseHas('plan_paypal_variants', ['plan_id' => $plan->id, 'first_cents' => 250, 'recurring_cents' => 499, 'paypal_plan_id' => 'P-VARIANT']);
        $this->assertSame('P-M', $plan->fresh()->paypal_plan_id, 'the base plan is untouched');

        // Coming back: the webhook/return carries the variant's plan id and still maps to our plan.
        $this->actingAs($this->user)->get('/checkout/success?plan='.$plan->id.'&subscription_id=I-SUB1')
            ->assertSessionHas('status', 'Pago completado. Ya tienes acceso.');
        $this->assertTrue(app(AccessService::class)->hasAccess($this->user, 'mdp'));
        $this->assertNotNull(CouponRedemption::firstWhere('paypal_subscription_id', 'I-SUB1')->confirmed_at);

        // A second user with the same coupon reuses the variant instead of creating another PayPal plan.
        $this->actingAs(User::factory()->create())->get('/checkout/'.$plan->id.'?coupon=PRIMERMES')->assertRedirect('https://paypal.test/approve2');
        $this->assertCount(1, Http::recorded(fn ($r) => $r->url() === self::API.'/v1/billing/plans'));
        $this->assertSame(1, PlanPaypalVariant::count());
    }

    public function test_forever_coupon_derives_a_plan_with_only_the_discounted_regular_price(): void
    {
        $plan = $this->plan('mdp-year', 'year', 3999, 'P-Y');
        Coupon::create(['code' => 'SIEMPRE10', 'type' => 'percent', 'value' => 10, 'duration' => 'forever']);
        Http::fake($this->token() + [
            self::API.'/v1/billing/plans' => Http::response(['id' => 'P-VARIANT']),
            self::API.'/v1/billing/subscriptions' => Http::response(['id' => 'I-SUB1', 'links' => [['rel' => 'approve', 'href' => 'https://paypal.test/approve']]]),
        ]);

        $this->actingAs($this->user)->get('/checkout/'.$plan->id.'?coupon=SIEMPRE10')->assertRedirect('https://paypal.test/approve');

        Http::assertSent(fn ($r) => $r->url() === self::API.'/v1/billing/plans'
            && count($r['billing_cycles']) === 1
            && $r['billing_cycles'][0]['tenure_type'] === 'REGULAR'
            && $r['billing_cycles'][0]['pricing_scheme']['fixed_price']['value'] === '35.99');
    }

    public function test_paywall_shows_discounted_prices_and_links_with_the_coupon(): void
    {
        $monthly = $this->plan('mdp-month', 'month', 499, 'P-M');
        $yearly = $this->plan('mdp-year', 'year', 3999, 'P-Y');
        $coupon = Coupon::create(['code' => 'ANUAL10', 'type' => 'percent', 'value' => 10]);
        $coupon->plans()->sync([$yearly->id]);

        Livewire::actingAs($this->user)->test(Paywall::class, ['product' => 'mdp'])
            ->assertSee('¿Tienes un cupón?')
            ->set('couponCode', 'anual10')
            ->call('applyCoupon')
            ->assertSet('appliedCoupon', 'ANUAL10')
            ->assertSee('35,99 EUR')
            ->assertSee('/checkout/'.$yearly->id.'?coupon=ANUAL10')
            ->assertDontSee('/checkout/'.$monthly->id.'?coupon=ANUAL10')
            ->call('removeCoupon')
            ->assertDontSee('35,99 EUR');

        Livewire::actingAs($this->user)->test(Paywall::class, ['product' => 'mdp'])
            ->set('couponCode', 'NOPE')
            ->call('applyCoupon')
            ->assertSet('appliedCoupon', null)
            ->assertSee('Este cupón no existe.');

        $this->actingAs($this->user)->get('/planes/mdp?cupon=ANUAL10')->assertOk()->assertSee('35,99 EUR');
    }

    public function test_admin_can_open_the_coupons_panel(): void
    {
        $coupon = Coupon::create(['code' => 'ANUAL10', 'type' => 'percent', 'value' => 10, 'max_redemptions' => 5]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/coupons')->assertOk()->assertSee('ANUAL10')->assertSee('10 %');
        $this->actingAs($admin)->get('/admin/coupons/'.$coupon->id.'/edit')->assertOk();
        $this->actingAs($admin)->get('/admin/coupons/create')->assertOk();
    }

    private function assertThrowsMessage(callable $fn, string $message): void
    {
        try {
            $fn();
        } catch (CouponException $e) {
            $this->assertSame($message, $e->getMessage());

            return;
        }
        $this->fail('Expected CouponException: '.$message);
    }
}
