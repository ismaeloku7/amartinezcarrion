<?php

namespace Tests\Feature;

use App\Livewire\Mdp\Devotional;
use App\Livewire\Mdp\MonthClose;
use App\Livewire\Mdp\RestDay;
use App\Livewire\Mdp\WeekCard;
use App\Models\Mdp\MdpDay;
use App\Models\Mdp\MdpEnrollment;
use App\Models\Mdp\MdpText;
use App\Models\Product;
use App\Models\User;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Rest day is Saturday. The practice starts on Monday 2 November 2026, so the weeks end on
 * 7, 14, 21 and 28 November, and the first month closes on Saturday 28 November.
 */
class MdpWeekMonthDaysTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private MdpEnrollment $enrollment;

    private MdpService $mdp;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-11-02 08:00:00');
        Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP'], 'is_free' => true]);
        $this->user = User::factory()->create(['timezone' => 'UTC']);
        $this->mdp = app(MdpService::class);
        $this->enrollment = $this->mdp->enroll($this->user, ['rest_weekday' => 6, 'mode' => 'light']);
    }

    private function doneDay(string $date, string $body): MdpText
    {
        $day = $this->mdp->dayFor($this->enrollment, CarbonImmutable::parse($date));
        $text = $this->mdp->addText($day, $body);
        $day->forceFill(['status' => MdpDay::STATUS_DONE, 'finished_at' => now()])->save();

        return $text->fresh();
    }

    private function on(string $date): void
    {
        CarbonImmutable::setTestNow($date.' 09:00:00');
    }

    public function test_the_week_text_is_not_chosen_during_the_week_and_memorization_is_gone(): void
    {
        $this->on('2026-11-03');
        Livewire::actingAs($this->user)->test(Devotional::class)
            ->set('newText', 'Nadie puede recibir nada')->call('addText')->call('next')->call('next')
            ->assertSet('step', 'done');

        $week = $this->mdp->weekFor($this->enrollment, CarbonImmutable::parse('2026-11-03'));
        $this->assertNull($week->fresh()->week_text_id, 'a single day text no longer becomes the week text mid-week');

        Livewire::actingAs($this->user)->test(WeekCard::class)
            ->assertSee('recibir') // words are rendered one by one
            ->assertSee('El texto de la semana se elige el día de descanso')
            ->assertDontSee('Texto de la semana</')
            ->assertDontSee('Memoriz')
            ->assertDontSee('Llave del texto');

        Livewire::actingAs($this->user)->test(RestDay::class)->assertRedirect(route('mdp.week'));
    }

    public function test_the_week_text_is_chosen_on_the_rest_day(): void
    {
        $this->on('2026-11-03');
        $text = $this->doneDay('2026-11-03', 'Nadie puede recibir nada');

        $this->on('2026-11-07');
        Livewire::actingAs($this->user)->test(WeekCard::class)->assertSee('toca elegir el texto de la semana');
        Livewire::actingAs($this->user)->test(RestDay::class)->assertOk();

        $week = $this->mdp->weekFor($this->enrollment, CarbonImmutable::parse('2026-11-07'));
        $this->assertSame($text->id, $week->fresh()->week_text_id, 'the single text is picked on the rest day');

        $month = $this->mdp->monthFor($this->enrollment, CarbonImmutable::parse('2026-11-07'));
        $this->assertNull($month->fresh()->month_text_id, 'the first week does not close the month');
    }

    public function test_the_month_text_is_chosen_only_on_the_closing_day(): void
    {
        $texts = [];
        foreach (['2026-11-03', '2026-11-10'] as $date) {
            $this->on($date);
            $texts[] = $this->doneDay($date, 'Texto del '.$date);
        }
        foreach (['2026-11-07' => 0, '2026-11-14' => 1] as $rest => $i) {
            $this->on($rest);
            $this->mdp->selectWeekText($this->mdp->weekFor($this->enrollment, CarbonImmutable::parse($rest)), $texts[$i]);
        }
        $month = $this->mdp->monthFor($this->enrollment, CarbonImmutable::parse('2026-11-14'));
        $this->assertNull($month->fresh()->month_text_id);

        // Before the closing day: visible, not selectable.
        $this->on('2026-11-21');
        Livewire::actingAs($this->user)->test(MonthClose::class)
            ->assertSet('monthIndex', null)
            ->assertSee('El texto del mes se elige el día que se cierra')
            ->assertDontSeeHtml('wire:click="selectMonthText')
            ->call('selectMonthText', $texts[0]->id)
            ->assertForbidden();
        $this->assertNull($this->mdp->monthClosingOn($this->enrollment, CarbonImmutable::parse('2026-11-21')));

        // The closing day: Today shows the banner and the text can be chosen (and changed).
        $this->on('2026-11-28');
        $this->assertSame($month->id, $this->mdp->monthClosingOn($this->enrollment, CarbonImmutable::parse('2026-11-28'))?->id);
        $this->actingAs($this->user)->get('/mdp')->assertSee('Hoy se cierra el mes');
        Livewire::actingAs($this->user)->test(MonthClose::class)
            ->assertSee('Hoy se cierra el mes')
            ->call('selectMonthText', $texts[0]->id)
            ->call('selectMonthText', $texts[1]->id)
            ->assertOk();
        $this->assertSame($texts[1]->id, $month->fresh()->month_text_id);

        // After the closing day it is fixed.
        $this->on('2026-11-29');
        Livewire::actingAs($this->user)->test(MonthClose::class, ['month' => 1])
            ->assertDontSeeHtml('wire:click="selectMonthText')
            ->call('selectMonthText', $texts[0]->id)
            ->assertForbidden();
        $this->assertSame($texts[1]->id, $month->fresh()->month_text_id);
    }

    public function test_a_single_week_text_becomes_the_month_text_only_on_the_closing_day(): void
    {
        $this->on('2026-11-24');
        $text = $this->doneDay('2026-11-24', 'Un solo texto este mes');

        $this->on('2026-11-28');
        $this->mdp->selectWeekText($this->mdp->weekFor($this->enrollment, CarbonImmutable::parse('2026-11-28')), $text);

        $month = $this->mdp->monthFor($this->enrollment, CarbonImmutable::parse('2026-11-28'));
        $this->assertSame($text->id, $month->fresh()->month_text_id);
    }
}
