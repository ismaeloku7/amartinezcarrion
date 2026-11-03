<?php

namespace Tests\Feature;

use App\Models\Cards\Card;
use App\Models\Language;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Tests\Test;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\EnneagramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The deploy script seeds on every release; it must never undo what the admin changed. */
class SeedersKeepPanelChangesTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_keeps_free_products_inactive_plans_and_other_panel_edits(): void
    {
        $this->seed(DatabaseSeeder::class);

        Product::firstWhere('key', 'mdp')->update(['is_free' => true]);
        Product::firstWhere('key', 'cards')->update(['active' => false, 'name' => ['es' => 'Fichas (nuevo nombre)']]);
        $plan = Plan::firstWhere('key', 'mdp-month');
        $plan->update(['active' => false, 'price_cents' => 799, 'paypal_plan_id' => 'P-KEEP']);
        $plan->products()->sync([Product::firstWhere('key', 'cards')->id]);
        Test::firstWhere('key', 'johari')->update(['active' => false]);
        Card::firstWhere('number', 1)->update(['selectable' => false]);
        Language::firstWhere('code', 'en')->update(['active' => false]);
        $counts = [Product::count(), Plan::count(), Card::count(), Language::count()];

        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Product::firstWhere('key', 'mdp')->is_free);
        $this->assertFalse(Product::firstWhere('key', 'cards')->active);
        $this->assertSame('Fichas (nuevo nombre)', Product::firstWhere('key', 'cards')->getTranslation('name', 'es'));
        $plan = Plan::firstWhere('key', 'mdp-month');
        $this->assertFalse($plan->active);
        $this->assertSame(799, $plan->price_cents);
        $this->assertSame('P-KEEP', $plan->paypal_plan_id);
        $this->assertSame(['cards'], $plan->products->pluck('key')->all());
        $this->assertFalse(Test::firstWhere('key', 'johari')->active);
        $this->assertFalse(Card::firstWhere('number', 1)->selectable);
        $this->assertFalse(Language::firstWhere('code', 'en')->active);
        $this->assertSame($counts, [Product::count(), Plan::count(), Card::count(), Language::count()]);
    }

    public function test_reseeding_still_creates_what_is_missing(): void
    {
        $this->seed(DatabaseSeeder::class);
        Plan::firstWhere('key', 'books-year')->delete();
        Card::firstWhere('number', 42)->delete();

        $this->seed(DatabaseSeeder::class);

        $this->assertNotNull(Plan::firstWhere('key', 'books-year'));
        $this->assertNotNull(Card::firstWhere('number', 42));
        $this->assertSame(1, Test::where('key', 'johari')->count());
    }

    public function test_reseeding_keeps_enneagram_panel_edits(): void
    {
        $this->seed(EnneagramSeeder::class);
        $test = Test::firstWhere('key', 'eneagrama');
        $test->update(['active' => false, 'ai_enabled' => false]);
        $test->scales()->where('key', '5')->update(['color' => '#123456']);
        $test->ownProfiles()->where('scale_key', '5')->first()->setTranslation('title', 'es', 'El observador')->save();

        $this->seed(EnneagramSeeder::class);

        $test->refresh();
        $this->assertFalse($test->active);
        $this->assertFalse($test->ai_enabled);
        $this->assertSame('#123456', $test->scales()->where('key', '5')->value('color'));
        $this->assertSame('El observador', $test->profiles()['5']->title);
    }
}
