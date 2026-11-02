<?php

namespace App\Livewire\Tests\Scored;

use App\Models\Tests\TestTake;
use App\Services\Tests\ScoredTestService;
use Livewire\Component;

/** Paged questionnaire: every statement gets yes / strong yes / no / ?, progress is saved on each tap. */
class Run extends Component
{
    public TestTake $take;

    public int $page = 0;

    public function mount(TestTake $take): void
    {
        abort_unless($take->user_id === auth()->id(), 403);
        abort_unless($take->test->isScored(), 404);
        if ($take->status === TestTake::STATUS_CLOSED) {
            $this->redirectRoute('tests.scored.results', ['take' => $take]);

            return;
        }
        $this->take = $take;
        $this->page = (int) $take->data('page', 0);
    }

    protected function perPage(): int
    {
        return (int) app(ScoredTestService::class)->config($this->take->test, 'per_page');
    }

    protected function orderedIds(): array
    {
        return $this->take->data('order') ?: $this->take->test->items()->pluck('id')->all();
    }

    public function answer(int $itemId, string $answer): void
    {
        $current = $this->take->data("answers.$itemId");
        app(ScoredTestService::class)->answer($this->take, $itemId, $current === $answer ? null : $answer);
        $this->take->refresh();
    }

    public function next(): void
    {
        $this->go($this->page + 1);
    }

    public function previous(): void
    {
        $this->go($this->page - 1);
    }

    public function go(int $page): void
    {
        $pages = (int) ceil(count($this->orderedIds()) / $this->perPage());
        $this->page = max(0, min($pages - 1, $page));
        app(ScoredTestService::class)->setPage($this->take, $this->page);
        $this->dispatch('scroll-top');
    }

    public function finish(ScoredTestService $service): void
    {
        $service->finish($this->take);
        $this->redirectRoute('tests.scored.results', ['take' => $this->take]);
    }

    public function render()
    {
        $ids = $this->orderedIds();
        $perPage = $this->perPage();
        $pageIds = array_slice($ids, $this->page * $perPage, $perPage);
        $items = $this->take->test->items()->whereIn('id', $pageIds)->get()->sortBy(fn ($i) => array_search($i->id, $pageIds))->values();
        $answers = $this->take->data('answers', []);

        return view('livewire.tests.scored.run', [
            'items' => $items,
            'answers' => $answers,
            'pages' => (int) ceil(count($ids) / $perPage),
            'answered' => count($answers),
            'total' => count($ids),
            'pageAnswered' => collect($pageIds)->every(fn ($id) => isset($answers[(string) $id])),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $this->take->test->name, 'back' => route('tests.show', $this->take->test)]);
    }
}
