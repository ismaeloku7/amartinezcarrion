<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use Livewire\Component;

/** The week's day texts. The week text itself is chosen on the rest day, not here. */
class WeekCard extends Component
{
    use HasEnrollment;

    public int $offset = 0; // 0 = current week, -1 = previous...

    protected function week()
    {
        return $this->mdp()->weekFor($this->enrollment(), $this->today()->addWeeks($this->offset));
    }

    public function previousWeek(): void
    {
        $this->offset--;
    }

    public function nextWeek(): void
    {
        if ($this->offset < 0) {
            $this->offset++;
        }
    }

    public function markWeekReview(): void
    {
        $this->mdp()->markWeekReview($this->week());
    }

    public function render()
    {
        $week = $this->week();
        $today = $this->today();

        return view('livewire.mdp.week', [
            'week' => $week->fresh(['weekText']),
            'dayTexts' => $week->dayTexts(),
            'enrollment' => $this->enrollment(),
            'isRestDay' => $this->offset === 0 && $this->mdp()->canChooseWeekText($week, $today),
        ])->layout('components.layouts.app', $this->layoutData(__('Ficha de la semana')));
    }
}
