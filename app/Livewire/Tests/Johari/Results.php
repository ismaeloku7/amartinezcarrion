<?php

namespace App\Livewire\Tests\Johari;

use App\Models\Tests\TestTake;
use App\Services\Tests\JohariService;
use Livewire\Component;

class Results extends Component
{
    public TestTake $take;

    public function mount(TestTake $take): void
    {
        abort_unless($take->user_id === auth()->id(), 403);
        $this->take = $take;
    }

    public function close(JohariService $johari): void
    {
        $johari->close($this->take);
        $this->take->refresh();
    }

    public function delete(): void
    {
        $test = $this->take->test;
        $this->take->delete();
        $this->redirectRoute('tests.show', ['test' => $test]);
    }

    public function render(JohariService $johari)
    {
        $this->take->refresh();
        $labels = $this->take->test->wordList->words->mapWithKeys(fn ($w) => [$w->key => $w->label])->all();

        return view('livewire.tests.johari.results', [
            'result' => $this->take->result,
            'labels' => $labels,
            'responses' => $this->take->responses()->count(),
            'min' => (int) $this->take->data('min_responses'),
            'rules' => $this->take->result ? $johari->matchingRules($this->take) : collect(),
            'previousTakes' => auth()->user()->testTakes()->where('test_id', $this->take->test_id)->whereNotNull('result')->where('id', '!=', $this->take->id)->count(),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Resultados'), 'back' => route('tests.show', $this->take->test)]);
    }
}
