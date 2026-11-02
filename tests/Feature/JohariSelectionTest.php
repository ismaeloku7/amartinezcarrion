<?php

namespace Tests\Feature;

use App\Models\Tests\Test;
use App\Models\User;
use App\Services\Tests\JohariService;
use Database\Seeders\JohariSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JohariSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_selection_requires_the_configured_number_of_positive_and_uncomfortable_words(): void
    {
        $this->seed(JohariSeeder::class);
        $test = Test::where('key', 'johari')->first();
        $service = app(JohariService::class);
        $groups = $service->groupedKeys($test);
        $take = $service->start(User::factory()->create(), $test);

        $this->assertCount(7, $groups['negative']);

        try {
            $service->saveSelfSelection($take, array_slice($groups['positive'], 0, 6));
            $this->fail('negative words were required');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('incómodas', $e->getMessage());
        }

        $service->saveSelfSelection($take, array_merge(array_slice($groups['positive'], 0, 6), array_slice($groups['negative'], 0, 2)));
        $this->assertCount(8, $take->fresh()->data('self_words'));
    }
}
