<?php

namespace Tests\Feature;

use App\Models\Cards\Card;
use App\Models\User;
use App\Services\Cards\CardsService;
use Carbon\CarbonImmutable;
use Database\Seeders\CardsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardsRotationTest extends TestCase
{
    use RefreshDatabase;

    private CardsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CardsSeeder::class);
        $this->service = app(CardsService::class);
    }

    public function test_groups_rotate_among_available_groups_until_complete(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC']);
        $e = $this->service->enroll($user, ['rest_weekday' => 6, 'unlocked_until' => 9]); // groups 1 and 2 available

        $monday = CarbonImmutable::parse('2026-11-02'); // Monday
        $day1 = $this->service->dayFor($e, $monday);
        $this->assertSame(1, $day1->group_index);
        $this->service->completeMorningReview($day1);

        $day2 = $this->service->dayFor($e->fresh(), $monday->addDay());
        $this->assertSame(2, $day2->group_index);
        $this->service->completeMorningReview($day2);

        $day3 = $this->service->dayFor($e->fresh(), $monday->addDays(2));
        $this->assertSame(1, $day3->group_index, 'wraps around to group 1 when only two groups exist');

        $this->assertNull($this->service->groupForDate($e->fresh(), $monday->addDays(5)), 'Saturday is the rest day');
    }

    public function test_fixed_weekday_groups_once_all_42_cards_are_unlocked(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC']);
        $e = $this->service->enroll($user, ['rest_weekday' => 6, 'unlocked_until' => 42]);

        $sunday = CarbonImmutable::parse('2026-11-01'); // day after rest day = day 1
        $this->assertSame(1, $this->service->groupForDate($e, $sunday));
        $this->assertSame(3, $this->service->groupForDate($e, $sunday->addDays(2)));
        $this->assertSame(6, $this->service->groupForDate($e, $sunday->addDays(5)));
        $this->assertNull($this->service->groupForDate($e, $sunday->addDays(6)));
    }

    public function test_non_selectable_cards_cannot_be_chosen(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC']);
        $e = $this->service->enroll($user, ['rest_weekday' => 6, 'unlocked_until' => 7]);
        $day = $this->service->dayFor($e, CarbonImmutable::parse('2026-11-02'));
        $sara = Card::where('number', 7)->first();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->chooseCard($day, $sara);
    }
}
