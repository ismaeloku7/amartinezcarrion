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

class MdpReadingRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reading_start_and_end_are_saved_in_every_mode_and_phase(): void
    {
        CarbonImmutable::setTestNow('2026-11-03 08:00:00');
        $product = Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP']]);
        $user = User::factory()->create(['timezone' => 'UTC']);
        app(AccessService::class)->grantManual($user, $product);
        $enrollment = app(MdpService::class)->enroll($user, ['mode' => 'light', 'rest_weekday' => 6]);

        Livewire::actingAs($user)->test(Devotional::class)
            ->assertSet('step', 'texts')
            ->set('readingFrom', 'Jn 1:1')
            ->set('readingTo', 'Jn 1:18');

        $day = $enrollment->days()->first();
        $this->assertSame('Jn 1:1', $day->reading_from);
        $this->assertSame('Jn 1:18', $day->reading_to);

        $enrollment->update(['mode' => 'full']);
        Livewire::actingAs($user)->test(Devotional::class)
            ->assertSet('readingFrom', 'Jn 1:1')
            ->call('next')
            ->assertSet('step', 'reading')
            ->assertSee('Empiezo en')
            ->set('readingTo', 'Jn 2:11')
            ->call('next');

        $this->assertSame('Jn 2:11', $day->fresh()->reading_to);
    }
}
