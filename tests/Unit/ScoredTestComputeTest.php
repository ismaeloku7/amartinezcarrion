<?php

namespace Tests\Feature;

use App\Models\Tests\Test;
use App\Models\User;
use App\Services\Tests\ScoredTestService;
use Database\Seeders\EnneagramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoredTestComputeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scores_count_yes_once_and_strong_yes_twice_per_weighted_scale(): void
    {
        $this->seed(EnneagramSeeder::class);
        $test = Test::where('key', 'eneagrama')->first();
        $service = app(ScoredTestService::class);

        $this->assertSame(162, $test->items()->count());
        $this->assertSame(9, $test->scales()->count());
        $this->assertCount(9, $test->profiles());

        $type5 = $test->items()->where('group_key', '5')->pluck('id');
        $type2 = $test->items()->where('group_key', '2')->pluck('id');
        $answers = [];
        foreach ($type5->take(10) as $id) {
            $answers[(string) $id] = 'strong_yes';
        }
        foreach ($type2->take(6) as $id) {
            $answers[(string) $id] = 'yes';
        }
        $answers[(string) $type2[6]] = 'unknown';

        $r = $service->compute($test, $answers);

        $this->assertSame(20, $r['scores']['5']);
        $this->assertSame(6, $r['scores']['2']);
        $this->assertSame(36, $r['max']['5']);
        $this->assertSame('5', $r['indicators']['top_type']);
        $this->assertSame('2', $r['indicators']['second_type']);
        $this->assertSame(14, $r['indicators']['gap']);
        $this->assertSame(1, $r['indicators']['unknown_count']);
        $this->assertSame(17, $r['indicators']['answered']);
        $this->assertGreaterThan(50, $r['indicators']['group_introvert_pct']);
    }

    public function test_rheti_shares_the_profiles_of_the_main_questionnaire(): void
    {
        $this->seed(EnneagramSeeder::class);
        $rheti = Test::where('key', 'rheti')->first();

        $this->assertSame(363, $rheti->items()->count());
        $this->assertSame('El pensador', $rheti->profiles()['5']->title);
    }

    public function test_a_take_can_be_answered_and_finished(): void
    {
        $this->seed(EnneagramSeeder::class);
        $test = Test::where('key', 'eneagrama')->first();
        $service = app(ScoredTestService::class);
        $take = $service->start(User::factory()->create(), $test);
        $first = $test->items()->first();

        $service->answer($take, $first->id, 'yes');
        $result = $service->finish($take->fresh());

        $this->assertSame(1, $result['scores']['1']);
        $this->assertSame('closed', $take->fresh()->status);
    }
}
