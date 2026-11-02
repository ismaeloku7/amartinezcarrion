<?php

namespace App\Livewire\Tests;

use App\Models\Tests\Test;
use App\Models\Tests\TestTake;
use App\Services\AccessService;
use App\Services\Tests\JohariService;
use App\Services\Tests\ScoredTestService;
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

    /** Route name that continues or shows a take, depending on the test type and the take state. */
    public static function routeFor(TestTake $take): string
    {
        if ($take->test->isScored()) {
            return $take->status === TestTake::STATUS_CLOSED ? 'tests.scored.results' : 'tests.scored.run';
        }

        return $take->status === TestTake::STATUS_SELF_PENDING ? 'tests.johari.self' : 'tests.johari.results';
    }

    public function start(JohariService $johari, ScoredTestService $scored): void
    {
        if (! $this->hasAccess()) {
            $this->redirectRoute('paywall', ['product' => $this->test->product->key]);

            return;
        }

        $open = auth()->user()->testTakes()->where('test_id', $this->test->id)->where('status', '!=', TestTake::STATUS_CLOSED)->latest()->first();
        if ($open) {
            $this->redirectRoute(self::routeFor($open), ['take' => $open]);

            return;
        }

        $engine = $this->test->isScored() ? $scored : $johari;
        if (! $engine->canStart(auth()->user(), $this->test)) {
            $this->addError('start', __('Todavía no puedes repetir este test.'));

            return;
        }

        $take = $engine->start(auth()->user(), $this->test);
        $this->redirectRoute(self::routeFor($take), ['take' => $take]);
    }

    public function render(JohariService $johari, ScoredTestService $scored)
    {
        $takes = auth()->user()->testTakes()->where('test_id', $this->test->id)->orderByDesc('started_at')->get();
        $engine = $this->test->isScored() ? $scored : $johari;

        return view('livewire.tests.show', [
            'takes' => $takes,
            'hasAccess' => $this->hasAccess(),
            'canStart' => $engine->canStart(auth()->user(), $this->test),
            'open' => $takes->first(fn ($t) => $t->isOpen()),
            'isScored' => $this->test->isScored(),
            'itemCount' => $this->test->isScored() ? $this->test->items()->count() : null,
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $this->test->name, 'back' => route('tests.index')]);
    }
}
