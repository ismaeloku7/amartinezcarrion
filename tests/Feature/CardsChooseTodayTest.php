<?php

namespace Tests\Feature;

use App\Livewire\Cards\Review;
use App\Models\Cards\Card;
use App\Models\Product;
use App\Models\User;
use App\Services\Cards\CardsService;
use Carbon\CarbonImmutable;
use Database\Seeders\CardsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** In the morning only one card of the group is chosen, and that is the card reviewed. */
class CardsChooseTodayTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CardsSeeder::class);
        Product::create(['key' => 'cards', 'type' => 'practice', 'name' => ['es' => 'Fichas'], 'is_free' => true]);
        $this->user = User::factory()->create(['timezone' => 'UTC']);
        CarbonImmutable::setTestNow('2026-11-04 08:00:00'); // Wednesday
        app(CardsService::class)->enroll($this->user, ['rest_weekday' => 6, 'unlocked_until' => 9]);
    }

    public function test_today_asks_to_choose_one_card_not_to_review_the_whole_group(): void
    {
        $this->actingAs($this->user)->get('/fichas')
            ->assertOk()
            ->assertSee('Escoge una ficha de este grupo para hoy')
            ->assertSee('Escoger la ficha de hoy')
            ->assertDontSee('Repasa las')
            ->assertDontSee('Empezar el repaso');
    }

    public function test_the_card_is_chosen_straight_away_and_becomes_the_card_to_review(): void
    {
        $three = Card::where('number', 3)->first();

        Livewire::actingAs($this->user)->test(Review::class)
            ->assertSee('Escoge la ficha de hoy')
            ->assertDontSee('Terminar repaso')
            ->call('open', $three->id)
            ->assertSee('Escoger esta ficha')
            ->call('flip')
            ->call('choose', $three->id)
            ->assertRedirect(route('cards.day'));

        $day = app(CardsService::class)->dayFor($this->user->cardsEnrollment()->first(), CarbonImmutable::parse('2026-11-04'));
        $this->assertSame($three->id, $day->chosen_card_id);
        $this->assertSame(0, $day->reviews_done, 'choosing is not a review');
        $this->assertNotNull($day->reviewed_at);
        $this->assertSame($day->group_index, $this->user->cardsEnrollment()->first()->last_group_reviewed, 'the rotation moves on');
    }

    public function test_non_selectable_cards_can_be_looked_at_but_not_chosen(): void
    {
        $seven = Card::where('number', 7)->first();

        Livewire::actingAs($this->user)->test(Review::class)
            ->call('open', $seven->id)
            ->assertDontSee('Escoger esta ficha')
            ->assertSee('Esta ficha no se escoge.')
            ->call('choose', $seven->id)
            ->assertHasErrors('choose');
    }
}
