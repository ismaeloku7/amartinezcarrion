<?php

namespace App\Livewire\Tests\Johari;

use App\Models\Tests\TestTake;
use App\Services\Tests\JohariService;
use Livewire\Component;

class SelfSelect extends Component
{
    public TestTake $take;

    public array $selected = [];

    public function mount(TestTake $take): void
    {
        abort_unless($take->user_id === auth()->id(), 403);
        $this->take = $take;
        $this->selected = $take->data('self_words', []);
    }

    public function toggle(string $key): void
    {
        $n = (int) app(JohariService::class)->config($this->take->test, 'self_words');

        if (in_array($key, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$key]));
        } elseif (count($this->selected) < $n) {
            $this->selected[] = $key;
        }
    }

    public function save(JohariService $johari): void
    {
        try {
            $johari->saveSelfSelection($this->take, $this->selected);
        } catch (\InvalidArgumentException $e) {
            $this->addError('selected', $e->getMessage());

            return;
        }

        $this->redirectRoute('tests.johari.invite', ['take' => $this->take]);
    }

    public function render(JohariService $johari)
    {
        return view('livewire.tests.johari.self', [
            'words' => $this->take->test->wordList->words,
            'n' => (int) $johari->config($this->take->test, 'self_words'),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $this->take->test->name, 'back' => route('tests.show', $this->take->test)]);
    }
}
