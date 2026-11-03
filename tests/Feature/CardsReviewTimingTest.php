<?php

namespace Tests\Feature;

use App\Models\Cards\Card;
use App\Models\Product;
use App\Models\User;
use App\Services\Cards\CardsService;
use Carbon\CarbonImmutable;
use Database\Seeders\CardsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The three reviews of the card of the day can only be marked from their scheduled times. */
class CardsReviewTimingTest extends TestCase
{
    use RefreshDatabase;

    private CardsService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CardsSeeder::class);
        Product::create(['key' => 'cards', 'type' => 'practice', 'name' => ['es' => 'Fichas'], 'is_free' => true]);
        $this->service = app(CardsService::class);
        $this->user = User::factory()->create(['timezone' => 'Europe/Madrid']);
    }

    private function at(string $time): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-04 '.$time, 'Europe/Madrid')); // Wednesday, not a rest day
    }

    public function test_reviews_cannot_be_marked_before_their_times(): void
    {
        $e = $this->service->enroll($this->user, ['rest_weekday' => 6, 'unlocked_until' => 3, 'review_times' => ['13:30', '20:30', '23:00']]);

        $this->at('07:45');
        $day = $this->service->dayFor($e, $e->today());
        $this->service->chooseCard($day, Card::where('number', 1)->first());

        $this->assertFalse($this->service->reviewDue($day->fresh()));
        $this->assertSame('13:30', $this->service->nextReviewAt($day->fresh())->format('H:i'));
        $this->assertSame(0, $this->service->markReview($day->fresh())->reviews_done, 'the morning visit is not a review');

        $this->at('13:31');
        $this->assertTrue($this->service->reviewDue($day->fresh()));
        $this->assertSame(1, $this->service->markReview($day->fresh())->reviews_done);
        $this->assertFalse($this->service->reviewDue($day->fresh()), 'the second review waits for 20:30');
        $this->assertSame(1, $this->service->markReview($day->fresh())->reviews_done);

        $this->at('23:05');
        $this->assertSame(2, $this->service->markReview($day->fresh())->reviews_done);
        $this->assertSame(3, $this->service->markReview($day->fresh())->reviews_done);
        $this->assertNull($this->service->nextReviewAt($day->fresh()));
        $this->assertSame(3, $this->service->markReview($day->fresh())->reviews_done);
    }

    public function test_a_review_marked_by_mistake_can_be_undone(): void
    {
        $e = $this->service->enroll($this->user, ['rest_weekday' => 6, 'unlocked_until' => 3]);
        $this->at('13:45');
        $day = $this->service->dayFor($e, $e->today());
        $this->service->chooseCard($day, Card::where('number', 1)->first());

        $this->assertSame(1, $this->service->markReview($day->fresh())->reviews_done);
        $this->assertSame(0, $this->service->unmarkReview($day->fresh())->reviews_done);
        $this->assertSame(0, $this->service->unmarkReview($day->fresh())->reviews_done, 'never below zero');
        $this->assertTrue($this->service->reviewDue($day->fresh()), 'the first review can be marked again');

        $this->actingAs($this->user)->get('/fichas')->assertDontSee('Deshacer');
        $this->service->markReview($day->fresh());
        $this->actingAs($this->user)->get('/fichas')->assertSee('Deshacer');
        $this->actingAs($this->user)->get('/fichas/ficha-del-dia')->assertSee('Deshacer');
    }

    public function test_the_day_cannot_be_rated_until_the_three_reviews_are_done(): void
    {
        $e = $this->service->enroll($this->user, ['rest_weekday' => 6, 'unlocked_until' => 3]);
        $this->at('23:30');
        $day = $this->service->dayFor($e, $e->today());
        $this->service->chooseCard($day, Card::where('number', 1)->first());

        $this->assertFalse($this->service->canRate($day->fresh()));
        try {
            $this->service->rate($day->fresh(), 2);
            $this->fail('rating before the reviews should be refused');
        } catch (\InvalidArgumentException $e2) {
            $this->assertSame('Podrás valorar el día cuando hayas hecho los tres repasos.', $e2->getMessage());
        }
        $this->actingAs($this->user)->get('/fichas')
            ->assertSee('cuando hayas hecho los tres repasos (0 de 3)')
            ->assertDontSee('wire:click="rate(', false);

        foreach (range(1, 3) as $i) {
            $this->service->markReview($day->fresh());
        }
        $this->assertTrue($this->service->canRate($day->fresh()));
        $this->service->rate($day->fresh(), 2);
        $this->assertSame(2, $day->fresh()->rating);
        $this->actingAs($this->user)->get('/fichas')->assertSee('wire:click="rate(', false);

        // Undoing the third review reopens the day: the rating goes with it.
        $this->service->unmarkReview($day->fresh());
        $this->assertNull($day->fresh()->rating);
        $this->assertFalse($this->service->canRate($day->fresh()));
    }

    public function test_day_card_shows_the_card_in_the_morning_and_the_recall_screen_when_a_review_is_due(): void
    {
        $e = $this->service->enroll($this->user, ['rest_weekday' => 6, 'unlocked_until' => 3]);

        $this->at('07:45');
        $this->service->chooseCard($this->service->dayFor($e, $e->today()), Card::where('number', 1)->first());

        $this->actingAs($this->user)->get('/fichas/ficha-del-dia')
            ->assertOk()
            ->assertDontSee('He repasado')
            ->assertDontSee('intenta recordar tu ficha de hoy')
            ->assertSee('Repaso 1 a las 13:30');
        $this->actingAs($this->user)->get('/fichas')
            ->assertOk()
            ->assertDontSee('>Repasar<', false)
            ->assertSee('Próximo repaso');

        $this->at('13:30');
        $this->actingAs($this->user)->get('/fichas/ficha-del-dia')
            ->assertOk()
            ->assertSee('intenta recordar tu ficha de hoy')
            ->assertDontSee('He repasado');
        $this->actingAs($this->user)->get('/fichas')->assertSee('Repasar');
    }
}
