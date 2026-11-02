<?php

namespace Tests\Feature;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\AccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_grants_all_its_products_and_ending_the_source_revokes_them(): void
    {
        $mdp = Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP']]);
        $cards = Product::create(['key' => 'cards', 'type' => 'practice', 'name' => ['es' => 'Fichas']]);
        $plan = Plan::create(['key' => 'both', 'name' => ['es' => 'Ambos'], 'interval' => 'year']);
        $plan->products()->sync([$mdp->id, $cards->id]);
        $user = User::factory()->create();
        $access = app(AccessService::class);

        $this->assertFalse($access->hasAccess($user, 'mdp'));
        $access->grantPlan($user, $plan, Entitlement::SOURCE_SUBSCRIPTION, 'sub_1');
        $this->assertTrue($access->hasAccess($user, 'mdp'));
        $this->assertTrue($access->hasAccess($user, 'cards'));

        $access->endSource($user, Entitlement::SOURCE_SUBSCRIPTION, 'sub_1', now()->subMinute());
        $this->assertFalse($access->hasAccess($user->fresh(), 'cards'));
    }

    public function test_paywall_redirect_for_users_without_access(): void
    {
        Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP']]);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/mdp')->assertRedirect('/planes/mdp');
    }

    public function test_paywall_hides_checkout_when_paypal_is_not_configured(): void
    {
        config(['paypal.client_id' => null, 'paypal.secret' => null]);
        $product = Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP']]);
        $plan = Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Mensual'], 'interval' => 'month', 'price_cents' => 499, 'paypal_plan_id' => 'P-1']);
        $plan->products()->sync([$product->id]);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/planes/mdp')
            ->assertOk()
            ->assertSee('Pago en línea no disponible todavía')
            ->assertDontSee('/checkout/');
        $this->actingAs($user)->get('/checkout/'.$plan->id)->assertNotFound();
    }

    public function test_paywall_hides_recurring_plans_without_a_paypal_plan_id(): void
    {
        config(['paypal.client_id' => 'id', 'paypal.secret' => 'secret']);
        $product = Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP']]);
        $monthly = Plan::create(['key' => 'mdp-month', 'name' => ['es' => 'Mensual'], 'interval' => 'month', 'price_cents' => 499]);
        $once = Plan::create(['key' => 'mdp-once', 'name' => ['es' => 'Único'], 'interval' => 'once', 'price_cents' => 1999]);
        $monthly->products()->sync([$product->id]);
        $once->products()->sync([$product->id]);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/planes/mdp')
            ->assertOk()
            ->assertSee('Pago en línea no disponible todavía')
            ->assertSee('/checkout/'.$once->id)
            ->assertDontSee('/checkout/'.$monthly->id);
        $this->actingAs($user)->get('/checkout/'.$monthly->id)->assertNotFound();
    }
}
