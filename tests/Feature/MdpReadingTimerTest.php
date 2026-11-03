<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\Mdp\MdpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MdpReadingTimerTest extends TestCase
{
    use RefreshDatabase;

    public function test_devotional_page_renders_the_clock_based_timer(): void
    {
        Product::create(['key' => 'mdp', 'type' => 'practice', 'name' => ['es' => 'MDP'], 'is_free' => true]);
        $user = User::factory()->create();
        app(MdpService::class)->enroll($user, []);

        $this->actingAs($user)->get('/mdp/devocional')
            ->assertOk()
            ->assertSee('mdp-timer-', false)
            ->assertSee('Date.now() - this.startedAt', false)
            ->assertSee('localStorage.setItem(this.storageKey', false);
    }
}
