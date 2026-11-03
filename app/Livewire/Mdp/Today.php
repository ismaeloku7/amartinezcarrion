<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use App\Models\Mdp\MdpDay;
use Carbon\CarbonImmutable;
use Livewire\Component;

/** The day screen. It opens on today, and arrows move to any day since the practice started. */
class Today extends Component
{
    use HasEnrollment;

    public ?string $date = null;

    public function mount(?string $date = null): void
    {
        $enrollment = $this->enrollment();
        $today = $this->today()->toDateString();
        $date = $date ? CarbonImmutable::parse($date)->toDateString() : $today;

        if ($date > $today || $date < $enrollment->started_at->toDateString()) {
            $this->redirectRoute('mdp.today');

            return;
        }

        $this->date = $date;
    }

    protected function selected(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->date ?? $this->today()->toDateString(), $this->enrollment()->user->timezone ?: config('app.timezone'))->startOfDay();
    }

    public function markReview(): void
    {
        $day = $this->mdp()->dayFor($this->enrollment(), $this->selected());
        $this->mdp()->markReview($day);
    }

    public function markPreviousReviewed(): void
    {
        $day = $this->mdp()->dayFor($this->enrollment(), $this->selected());
        $day->forceFill(['previous_reviewed' => true])->save();
    }

    public function render()
    {
        $enrollment = $this->enrollment();
        $today = $this->selected();
        $realToday = $this->today()->toDateString();
        $isToday = $today->toDateString() === $realToday;
        $day = $this->mdp()->dayFor($enrollment, $today)->load('texts');

        $previous = $enrollment->days()
            ->where('date', '<', $today->toDateString())
            ->where('status', MdpDay::STATUS_DONE)
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
            'daysDone' => $enrollment->days()->where('status', MdpDay::STATUS_DONE)->count(),
            'isToday' => $isToday,
            'prevDate' => $today->toDateString() > $enrollment->started_at->toDateString() ? $today->subDay()->toDateString() : null,
            'nextDate' => $isToday ? null : $today->addDay()->toDateString(),
        ])->layout('components.layouts.app', $this->layoutData($isToday ? __('Hoy') : $today->translatedFormat('j \d\e F')));
    }
}
