<?php

namespace App\Livewire\Tests\Scored;

use App\Jobs\GenerateAiAnalysis;
use App\Models\Tests\TestTake;
use App\Services\Tests\AiAnalysisService;
use App\Services\Tests\ScoredTestService;
use Livewire\Component;

class Results extends Component
{
    public TestTake $take;

    public string $zone = 'healthy';

    public ?string $viewType = null;

    public function mount(TestTake $take): void
    {
        abort_unless($take->user_id === auth()->id(), 403);
        abort_unless($take->test->isScored() && $take->result, 404);
        $this->take = $take;
        $this->viewType = $take->result['indicators']['top_type'] ?? null;
    }

    public function requestAi(AiAnalysisService $ai): void
    {
        if (! $ai->isAvailableFor($this->take)) {
            return;
        }
        $result = $this->take->result;
        unset($result['ai_error']);
        $result['ai_pending'] = now()->toIso8601String();
        $this->take->forceFill(['result' => $result])->save();
        GenerateAiAnalysis::dispatch($this->take);
    }

    public function delete(): void
    {
        $test = $this->take->test;
        $this->take->delete();
        $this->redirectRoute('tests.show', ['test' => $test]);
    }

    public function render(ScoredTestService $service, AiAnalysisService $ai)
    {
        $this->take->refresh();
        $result = $this->take->result;
        $test = $this->take->test;
        $scales = $test->scales()->get()->keyBy('key');
        $profiles = $test->profiles();
        $ranking = $result['ranking'];

        return view('livewire.tests.scored.results', [
            'test' => $test,
            'result' => $result,
            'scales' => $scales,
            'profiles' => $profiles,
            'top' => $ranking[0] ?? null,
            'second' => $ranking[1] ?? null,
            'gap' => $result['indicators']['gap'] ?? 0,
            'profile' => $profiles[$this->viewType] ?? null,
            'rules' => $service->matchingRules($this->take),
            'aiAvailable' => $ai->isAvailableFor($this->take),
            'ai' => $result['ai'] ?? null,
            'aiPending' => isset($result['ai_pending']),
            'aiError' => $result['ai_error'] ?? null,
            'previousTakes' => auth()->user()->testTakes()->where('test_id', $test->id)->whereNotNull('result')->where('id', '!=', $this->take->id)->count(),
            'maxScore' => max(1, max($result['scores'] ?: [1])),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Resultados'), 'back' => route('tests.show', $test)]);
    }
}
