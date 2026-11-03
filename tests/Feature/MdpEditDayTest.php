<?php

namespace Tests\Feature;

use App\Livewire\Mdp\Devotional;
use App\Models\Product;
use App\Models\User;
use App\Services\AccessService;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MdpEditDayTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_past_day_opens_as_an_editable_page_and_can_be_completed(): void
    {
        CarbonImmutable::setTestNow('2026-11-01 08:00:00');
        $product = Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP']]);
        $user = User::factory()->create(['timezone' => 'UTC']);
        app(AccessService::class)->grantManual($user, $product);
        $enrollment = app(MdpService::class)->enroll($user, ['rest_weekday' => 6]);

        CarbonImmutable::setTestNow('2026-11-03 08:00:00');

        Livewire::actingAs($user)->test(Devotional::class, ['date' => '2026-11-02'])
            ->assertSet('step', 'page')
            ->set('readingFrom', 'Sal 23:1')->set('readingTo', 'Sal 23:6')
            ->set('newText', 'El Señor es mi pastor nada me faltará')->call('addText')
            ->set('prayer', 'Gracias, Señor.')
            ->call('markDone')
            ->assertSet('step', 'page');

        $day = $enrollment->days()->whereDate('date', '2026-11-02')->first();
        $this->assertSame('done', $day->status);
        $this->assertSame('Sal 23:1', $day->reading_from);
        $this->assertSame('Gracias, Señor.', $day->prayer);
        $this->assertTrue($day->texts()->first()->is_day_text);

        // A finished day reopens as a page and can be corrected.
        Livewire::actingAs($user)->test(Devotional::class, ['date' => '2026-11-02'])
            ->assertSet('step', 'page')
            ->set('prayer', 'Gracias, Señor, por tu cuidado.')->call('savePage');
        $this->assertSame('Gracias, Señor, por tu cuidado.', $day->fresh()->prayer);

        // Today still runs step by step; the future and days before the start are refused.
        Livewire::actingAs($user)->test(Devotional::class)->assertSet('step', 'prayer');
        Livewire::actingAs($user)->test(Devotional::class, ['date' => '2026-11-04'])->assertRedirect(route('mdp.today'));
        Livewire::actingAs($user)->test(Devotional::class, ['date' => '2026-10-20'])->assertRedirect(route('mdp.today'));
    }
}
