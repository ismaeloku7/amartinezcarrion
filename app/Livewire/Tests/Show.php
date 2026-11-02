<?php

namespace App\Livewire\Tests;

use App\Models\Tests\Test;
use App\Models\Tests\TestTake;
use App\Services\AccessService;
use App\Services\Tests\JohariService;
use Livewire\Component;

class Show extends Component
{
    public Test $test;

    public function mount(Test $test): void
    {
        abort_unless($test->active, 404);
        $this->test = $test;
    }

    protected function hasAccess(): bool
    {
        if (! $this->test->requiresAccess()) {
            return true;
        }

        return app(AccessService::class)->hasAccess(auth()->user(), $this->test->product->key);
    }

    public function start(JohariService $johari): void
    {
        if (! $this->hasAccess()) {
            $this->redirectRoute('paywall', ['product' => $this->test->product->key]);

            return;
        }

        $open = auth()->user()->testTakes()->where('test_id', $this->test->id)->where('status', '!=', TestTake::STATUS_CLOSED)->latest()->first();
        if ($open) {
            $this->redirectRoute($open->status === TestTake::STATUS_SELF_PENDING ? 'tests.johari.self' : 'tests.johari.results', ['take' => $open]);

            return;
        }

        if (! $johari->canStart(auth()->user(), $this->test)) {
            $this->addError('start', __('Todavía no puedes repetir este test.'));

            return;
        }

        $take = $johari->start(auth()->user(), $this->test);
        $this->redirectRoute('tests.johari.self', ['take' => $take]);
    }

    public function render(JohariService $johari)
    {
        $takes = auth()->user()->testTakes()->where('test_id', $this->test->id)->orderByDesc('started_at')->get();

        return view('livewire.tests.show', [
            'takes' => $takes,
            'hasAccess' => $this->hasAccess(),
            'canStart' => $johari->canStart(auth()->user(), $this->test),
            'open' => $takes->first(fn ($t) => $t->isOpen()),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $this->test->name, 'back' => route('tests.index')]);
    }
}
