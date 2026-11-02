<?php

namespace App\Livewire\Tests\Johari;

use App\Livewire\Tests\Concerns\PicksWordsInTwoSteps;
use App\Models\Tests\Test;
use App\Models\Tests\TestTake;
use App\Services\Tests\JohariService;
use Livewire\Component;

class SelfSelect extends Component
{
    use PicksWordsInTwoSteps;

    public TestTake $take;

    public function mount(TestTake $take): void
    {
        abort_unless($take->user_id === auth()->id(), 403);
        $this->take = $take;
        $this->splitExisting($take->data('self_words', []));
    }

    protected function wordTest(): Test
    {
        return $this->take->test;
    }

    protected function who(): string
    {
        return 'self';
    }

    public function save(JohariService $johari): void
    {
        try {
            $johari->saveSelfSelection($this->take, $this->selectedKeys());
        } catch (\InvalidArgumentException $e) {
            $this->addError('selected', $e->getMessage());

            return;
        }

        $this->redirectRoute('tests.johari.invite', ['take' => $this->take]);
    }

    public function render()
    {
        return view('livewire.tests.johari.self', [
            'words' => $this->wordsFor($this->wordStep),
            'selected' => $this->wordStep === 'negative' ? $this->negative : $this->positive,
            'n' => $this->needed($this->wordStep),
            'hasNegative' => $this->needed('negative') > 0,
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $this->take->test->name, 'back' => route('tests.show', $this->take->test)]);
    }
}
