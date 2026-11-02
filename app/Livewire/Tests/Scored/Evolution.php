<?php

namespace App\Livewire\Tests\Scored;

use App\Models\Tests\Test;
use App\Services\Tests\ScoredTestService;
use Livewire\Component;

class Evolution extends Component
{
    public Test $test;

    public function mount(Test $test): void
    {
        abort_unless($test->isScored(), 404);
        $this->test = $test;
    }

    public function render(ScoredTestService $service)
    {
        return view('livewire.tests.scored.evolution', [
            'series' => $service->series(auth()->user(), $this->test),
            'scales' => $this->test->scales()->get(),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Evolución'), 'back' => route('tests.show', $this->test)]);
    }
}
