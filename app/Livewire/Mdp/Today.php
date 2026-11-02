<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use App\Models\Mdp\MdpDay;
use Livewire\Component;

class Today extends Component
{
    use HasEnrollment;

    public function markReview(): void
    {
        $day = $this->mdp()->dayFor($this->enrollment(), $this->today());
        $this->mdp()->markReview($day);
    }

    public function markPreviousReviewed(): void
    {
        $day = $this->mdp()->dayFor($this->enrollment(), $this->today());
        $day->forceFill(['previous_reviewed' => true])->save();
    }

    public function render()
    {
        $enrollment = $this->enrollment();
        $today = $this->today();
        $day = $this->mdp()->dayFor($enrollment, $today)->load('texts');

        $previous = $enrollment->days()
            ->where('date', '<', $today->toDateString())
            ->whereIn('status', [MdpDay::STATUS_DONE, MdpDay::STATUS_MINIMUM])
            ->orderByDesc('date')
            ->with('texts')
            ->first();

        return view('livewire.mdp.today', [
            'enrollment' => $enrollment,
            'today' => $today,
            'day' => $day,
            'isRest' => $enrollment->isRestDay($today),
            'phase' => $enrollment->phaseOn($today),
            'dayText' => $day->texts->firstWhere('is_day_text', true),
            'previous' => $previous,
            'week' => $this->mdp()->weekFor($enrollment, $today),
            'pendingMonth' => $this->mdp()->pendingMonthClose($enrollment, $today),
            'daysDone' => $enrollment->days()->whereIn('status', [MdpDay::STATUS_DONE, MdpDay::STATUS_MINIMUM])->count(),
        ])->layout('components.layouts.app', $this->layoutData(__('Hoy')));
    }
}
