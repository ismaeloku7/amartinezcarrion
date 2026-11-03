<?php

namespace Tests\Feature;

use App\Livewire\Mdp\History;
use App\Models\Mdp\MdpDay;
use App\Models\Product;
use App\Models\User;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/** The notebook flags short readings from the timer and lets the reader correct the time. */
class MdpHistoryTimeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP'], 'is_free' => true]);
        $this->user = User::factory()->create();
    }

    private function doneDay(string $date, ?int $seconds): MdpDay
    {
        $service = app(MdpService::class);
        $enrollment = $this->user->mdpEnrollment()->first() ?? $service->enroll($this->user, []);
        $day = $service->dayFor($enrollment, CarbonImmutable::parse($date));
        $day->forceFill(['status' => MdpDay::STATUS_DONE, 'finished_at' => now(), 'duration_seconds' => $seconds])->save();
        $day->texts()->create(['position' => 1, 'body' => 'Texto del '.$date, 'is_day_text' => true, 'word_count' => 3]);

        return $day->fresh();
    }

    public function test_notebook_flags_readings_under_fifteen_minutes(): void
    {
        $short = $this->doneDay('2026-11-02', 8 * 60);
        $full = $this->doneDay('2026-11-01', 17 * 60);
        $noTimer = $this->doneDay('2026-10-31', null);

        $this->assertTrue($short->isShortReading());
        $this->assertFalse($full->isShortReading());
        $this->assertTrue($noTimer->isShortReading());

        $html = $this->actingAs($this->user)->get('/mdp/libreta')->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'Menos tiempo'));
        $this->assertStringContainsString('8 min', $html);
        $this->assertStringContainsString('17 min', $html);
        $this->assertStringNotContainsString('mínimo', $html);
    }

    public function test_reading_time_can_be_corrected_from_the_notebook(): void
    {
        $day = $this->doneDay('2026-11-02', 3 * 60);

        Livewire::actingAs($this->user)->test(History::class)
            ->assertSee('Menos tiempo')
            ->call('edit', $day->id)
            ->assertSet('minutes', '3')
            ->set('minutes', '18')
            ->call('saveMinutes')
            ->assertHasNoErrors()
            ->assertSet('editing', null)
            ->assertDontSee('Menos tiempo')
            ->assertSee('18 min');

        $this->assertSame(18 * 60, $day->fresh()->duration_seconds);

        Livewire::actingAs($this->user)->test(History::class)
            ->call('edit', $day->id)
            ->set('minutes', '-1')
            ->call('saveMinutes')
            ->assertHasErrors('minutes');

        // Another reader's notebook cannot touch this day.
        $other = User::factory()->create();
        app(MdpService::class)->enroll($other, []);
        Livewire::actingAs($other)->test(History::class)->call('edit', $day->id)->assertNotFound();
    }

    public function test_the_devotional_no_longer_offers_a_minimum_shortcut(): void
    {
        app(MdpService::class)->enroll($this->user, []);

        $this->actingAs($this->user)->get('/mdp/devocional')
            ->assertOk()
            ->assertDontSee('Hoy solo el mínimo')
            ->assertDontSee('finishMinimum');
    }
}
