<?php

namespace Tests\Feature;

use App\Models\Tests\Test;
use App\Models\User;
use App\Services\Tests\AiAnalysisService;
use App\Services\Tests\ScoredTestService;
use Database\Seeders\EnneagramSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAnalysisPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_analysis_is_offered_only_with_an_api_key_and_a_finished_scored_take(): void
    {
        $this->seed(EnneagramSeeder::class);
        $test = Test::where('key', 'eneagrama')->first();
        $service = app(ScoredTestService::class);
        $take = $service->start(User::factory()->create(), $test);
        $ai = app(AiAnalysisService::class);

        config(['services.anthropic.key' => null]);
        $this->assertFalse($ai->isAvailableFor($take));

        config(['services.anthropic.key' => 'sk-test']);
        $this->assertFalse($ai->isAvailableFor($take), 'not finished yet');

        $service->finish($take->fresh());
        $this->assertTrue($ai->isAvailableFor($take->fresh()));
    }

    public function test_prompts_carry_the_authors_profiles_scores_and_matched_rules(): void
    {
        $this->seed(EnneagramSeeder::class);
        $test = Test::where('key', 'eneagrama')->first();
        $test->setTranslation('ai_instructions', 'es', 'Cita siempre el botón de acción.');
        $test->save();
        $service = app(ScoredTestService::class);
        $user = User::factory()->create(['name' => 'Lucía', 'locale' => 'es']);
        $take = $service->start($user, $test);
        foreach ($test->items()->where('group_key', '5')->take(12)->get() as $item) {
            $service->answer($take, $item->id, 'strong_yes');
        }
        $service->finish($take->fresh());
        $take->refresh();

        $ai = app(AiAnalysisService::class);
        $system = (new \ReflectionMethod($ai, 'systemPrompt'))->invoke($ai, $take);
        $userPrompt = (new \ReflectionMethod($ai, 'userPrompt'))->invoke($ai, $take, 'es');

        $this->assertStringContainsString('El pensador', $system);
        $this->assertStringContainsString('Zona de sombra', $system);
        $this->assertStringContainsString('Cita siempre el botón de acción.', $system);
        $this->assertStringContainsString('Tipo 5 (El pensador): 24 puntos', $userPrompt);
        $this->assertStringContainsString('Lucía', $userPrompt);
        $this->assertStringContainsString('Predominio introvertido', $userPrompt);
    }
}
