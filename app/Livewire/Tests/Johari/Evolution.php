<?php

namespace App\Livewire\Tests\Johari;

use App\Models\Tests\Test;
use App\Services\Tests\JohariService;
use Livewire\Component;

class Evolution extends Component
{
    public Test $test;

    public ?int $fromId = null;

    public ?int $toId = null;

    public function mount(Test $test): void
    {
        $this->test = $test;
        $takes = $this->takes();
        $this->toId = $takes->first()?->id;
        $this->fromId = $takes->skip(1)->first()?->id;
    }

    protected function takes()
    {
        return auth()->user()->testTakes()->where('test_id', $this->test->id)->whereNotNull('result')->orderByDesc('completed_at')->get();
    }

    public function render(JohariService $johari)
    {
        $takes = $this->takes();
        $from = $takes->firstWhere('id', $this->fromId);
        $to = $takes->firstWhere('id', $this->toId);
        $labels = $this->test->wordList->words->mapWithKeys(fn ($w) => [$w->key => $w->label])->all();

        return view('livewire.tests.johari.evolution', [
            'takes' => $takes,
            'from' => $from,
            'to' => $to,
            'moves' => ($from && $to) ? $johari->diff($from, $to) : [],
            'labels' => $labels,
            'series' => $takes->reverse()->values()->map(fn ($t) => ['date' => $t->completed_at->format('d/m/Y'), 'i' => $t->result['indicators']]),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Evolución'), 'back' => route('tests.show', $this->test)]);
    }
}
