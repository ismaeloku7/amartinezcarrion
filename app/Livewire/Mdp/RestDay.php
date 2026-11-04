<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use App\Models\Mdp\MdpDay;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Rest-day flow: review yesterday, underline one word per day text, choose the week text.
 */
class RestDay extends Component
{
    use HasEnrollment;

    public string $step = 'previous';

    /** The week text is chosen on the rest day only; any other day goes to the week card. */
    public function mount(): void
    {
        if (! $this->enrollment()->isRestDay($this->today())) {
            $this->redirectRoute('mdp.week');
        }
    }

    protected function week()
    {
        return $this->mdp()->weekFor($this->enrollment(), $this->today());
    }

    public function next(): void
    {
        $order = ['previous', 'words', 'select', 'done'];
        $i = array_search($this->step, $order, true);

        if ($this->step === 'select' && ! $this->week()->week_text_id) {
            throw ValidationException::withMessages(['select' => __('Elige el texto de la semana.')]);
        }

        $this->step = $order[min($i + 1, count($order) - 1)];

        if ($this->step === 'done') {
            $week = $this->week();
            $week->forceFill(['rest_day_done_at' => $week->rest_day_done_at ?? now()])->save();
            $day = $this->mdp()->dayFor($this->enrollment(), $this->today());
            $day->forceFill(['status' => MdpDay::STATUS_REST, 'previous_reviewed' => true, 'finished_at' => now()])->save();
        }
    }

    public function underlineWord(int $textId, int $index): void
    {
        $week = $this->week();
        $map = $week->word_underlines ?? [];
        $map[(string) $textId] = $index;
        $week->forceFill(['word_underlines' => $map])->save();
    }

    public function selectWeekText(int $textId): void
    {
        $week = $this->week();
        abort_unless($this->mdp()->canChooseWeekText($week, $this->today()), 403);
        $text = $week->dayTexts()->firstWhere('id', $textId);
        abort_unless($text, 404);
        $this->mdp()->selectWeekText($week, $text);
    }

    public function markReview(): void
    {
        $this->mdp()->markWeekReview($this->week());
    }

    public function render()
    {
        $enrollment = $this->enrollment();
        $week = $this->week();
        $this->mdp()->applyWeekAutoSelection($week);
        $week->refresh();

        $previous = $enrollment->days()
            ->where('date', '<', $this->today()->toDateString())
            ->where('status', MdpDay::STATUS_DONE)
            ->orderByDesc('date')->with('texts')->first();

        return view('livewire.mdp.rest', [
            'enrollment' => $enrollment,
            'isRest' => $enrollment->isRestDay($this->today()),
            'week' => $week,
            'dayTexts' => $week->dayTexts(),
            'previous' => $previous,
            'underlines' => $week->word_underlines ?? [],
            'monthToClose' => $this->mdp()->monthClosingOn($enrollment, $this->today()),
        ])->layout('components.layouts.app', $this->layoutData(__('Día de descanso'), route('mdp.today')));
    }
}
