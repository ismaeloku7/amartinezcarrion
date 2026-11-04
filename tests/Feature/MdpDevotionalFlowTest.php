<?php

namespace Tests\Feature;

use App\Livewire\Mdp\Devotional;
use App\Models\Mdp\MdpDay;
use App\Models\Mdp\MdpText;
use App\Models\Product;
use App\Models\User;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MdpDevotionalFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-11-02 08:00:00');
        Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP'], 'is_free' => true]);
        $this->user = User::factory()->create(['timezone' => 'UTC']);
        app(MdpService::class)->enroll($this->user, ['rest_weekday' => 6]);
        CarbonImmutable::setTestNow('2026-11-04 08:00:00');
    }

    public function test_phase_one_runs_to_the_end_even_when_the_single_text_is_tapped_again(): void
    {
        $c = Livewire::actingAs($this->user)->test(Devotional::class)
            ->call('next')
            ->set('readingFrom', 'Jn 4:1')->set('readingTo', 'Jn 4:20')->call('next')
            ->set('newText', 'Nadie puede recibir nada')->call('addText')->call('next')
            ->assertSet('step', 'select');

        // The only text is already selected automatically; tapping it again must keep it selected.
        $c->call('selectText', MdpText::first()->id)->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 'written_prayer')
            ->assertSeeHtml('wire:key="next-written_prayer"')
            ->set('prayer', 'Señor, en este día ayúdame')
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('step', 'done');

        $this->assertSame(MdpDay::STATUS_DONE, MdpDay::first()->status);
        $this->assertTrue((bool) MdpText::first()->is_day_text);
    }

    public function test_the_next_button_is_a_fresh_element_on_every_step(): void
    {
        $c = Livewire::actingAs($this->user)->test(Devotional::class);

        foreach (['prayer', 'reading'] as $step) {
            $c->assertSet('step', $step)->assertSeeHtml('wire:key="next-'.$step.'"')->call('next');
        }
    }

    public function test_a_new_day_starts_where_the_previous_reading_ended(): void
    {
        $enrollment = $this->user->mdpEnrollment()->first();
        $service = app(MdpService::class);
        $service->dayFor($enrollment, CarbonImmutable::parse('2026-11-02'))->forceFill(['reading_from' => 'Jn 3:1', 'reading_to' => 'Jn 3:36'])->save();
        $service->dayFor($enrollment, CarbonImmutable::parse('2026-11-03'))->forceFill(['reading_from' => 'Jn 4:1', 'reading_to' => 'Jn 4:26'])->save();

        Livewire::actingAs($this->user)->test(Devotional::class)
            ->assertSet('readingFrom', 'Jn 4:26')
            ->assertSet('readingTo', '')
            ->call('next')
            ->assertSee('Donde terminaste el día anterior')
            ->set('readingTo', 'Jn 4:42')
            ->call('next');

        $today = $enrollment->days()->whereDate('date', '2026-11-04')->first();
        $this->assertSame(['Jn 4:26', 'Jn 4:42'], [$today->reading_from, $today->reading_to]);

        // A day that already has its own start keeps it; changing the suggestion hides the hint.
        Livewire::actingAs($this->user)->test(Devotional::class)
            ->assertSet('readingFrom', 'Jn 4:26')
            ->assertSet('readingFromSuggested', false);
        Livewire::actingAs($this->user)->test(Devotional::class, ['date' => '2026-11-03'])
            ->assertSet('readingFrom', 'Jn 4:1');
    }

    public function test_the_first_day_has_no_suggested_start(): void
    {
        Livewire::actingAs($this->user)->test(Devotional::class)
            ->assertSet('readingFrom', '')
            ->assertSet('readingFromSuggested', false);
    }
}
