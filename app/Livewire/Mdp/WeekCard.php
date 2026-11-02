<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use Livewire\Component;

class WeekCard extends Component
{
    use HasEnrollment;

    public string $memorizeKey = '';

    public int $offset = 0; // 0 = current week, -1 = previous...

    public function mount(): void
    {
        $this->memorizeKey = $this->week()->memorize_key ?? '';
    }

    protected function week()
    {
        return $this->mdp()->weekFor($this->enrollment(), $this->today()->addWeeks($this->offset));
    }

    public function previousWeek(): void
    {
        $this->offset--;
        $this->memorizeKey = $this->week()->memorize_key ?? '';
    }

    public function nextWeek(): void
    {
        if ($this->offset < 0) {
            $this->offset++;
            $this->memorizeKey = $this->week()->memorize_key ?? '';
        }
    }

    public function saveMemorizeKey(): void
    {
        $this->week()->forceFill(['memorize_key' => trim($this->memorizeKey) ?: null])->save();
    }

    public function toggleMemorized(): void
    {
        $week = $this->week();
        $week->forceFill(['memorized' => ! $week->memorized])->save();
    }

    public function markWeekReview(): void
    {
        $this->mdp()->markWeekReview($this->week());
    }

    public function render()
    {
        $week = $this->week();
        $this->mdp()->applyWeekAutoSelection($week);

        return view('livewire.mdp.week', [
            'week' => $week->fresh(['weekText']),
            'dayTexts' => $week->dayTexts(),
            'enrollment' => $this->enrollment(),
        ])->layout('components.layouts.app', $this->layoutData(__('Ficha de la semana')));
    }
}
