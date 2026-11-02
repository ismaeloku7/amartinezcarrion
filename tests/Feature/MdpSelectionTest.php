<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MdpSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_text_is_selected_automatically_at_day_week_and_month_level(): void
    {
        CarbonImmutable::setTestNow('2026-11-02 08:00:00');
        $user = User::factory()->create(['timezone' => 'UTC']);
        $service = app(MdpService::class);
        $e = $service->enroll($user, ['rest_weekday' => 6, 'reading_time' => '07:00']);

        $day = $service->dayFor($e, CarbonImmutable::parse('2026-11-02'));
        $text = $service->addText($day, 'Todo lo puedo en Cristo que me fortalece');
        $this->assertTrue($text->fresh()->is_day_text);

        $week = $service->weekFor($e, $day->date);
        $this->assertSame('2026-11-01', $week->starts_on->toDateString());
        $this->assertSame('2026-11-07', $week->ends_on->toDateString());
        $service->applyWeekAutoSelection($week);
        $this->assertSame($text->id, $week->fresh()->week_text_id);

        $month = $service->monthFor($e, $day->date);
        $service->applyMonthAutoSelection($month);
        $this->assertSame($text->id, $month->fresh()->month_text_id);
    }

    public function test_texts_longer_than_twelve_words_are_rejected(): void
    {
        $user = User::factory()->create(['timezone' => 'UTC']);
        $service = app(MdpService::class);
        $e = $service->enroll($user, []);
        $day = $service->dayFor($e, CarbonImmutable::now());

        $this->expectException(\InvalidArgumentException::class);
        $service->addText($day, 'uno dos tres cuatro cinco seis siete ocho nueve diez once doce trece');
    }

    public function test_phase_switches_after_three_weeks_unless_fixed(): void
    {
        CarbonImmutable::setTestNow('2026-11-02');
        $user = User::factory()->create(['timezone' => 'UTC']);
        $e = app(MdpService::class)->enroll($user, []);

        $this->assertSame(1, $e->phaseOn(CarbonImmutable::parse('2026-11-20')));
        $this->assertSame(2, $e->phaseOn(CarbonImmutable::parse('2026-11-23')));
        $e->forceFill(['phase' => 1])->save();
        $this->assertSame(1, $e->phaseOn(CarbonImmutable::parse('2026-12-23')));
    }
}
