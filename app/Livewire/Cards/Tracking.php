<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use Carbon\CarbonImmutable;
use Livewire\Component;

class Tracking extends Component
{
    use HasEnrollment;

    public int $year;

    public int $month;

    public function mount(?int $year = null, ?int $month = null): void
    {
        $today = $this->today();
        $this->year = $year ?? $today->year;
        $this->month = $month ?? $today->month;
    }

    public function move(int $delta): void
    {
        $d = CarbonImmutable::create($this->year, $this->month, 1)->addMonths($delta);
        $this->year = $d->year;
        $this->month = $d->month;
    }

    public function toggleTracking(): void
    {
        $e = $this->enrollment();
        $e->forceFill(['tracking_enabled' => ! $e->tracking_enabled, 'tracking_prompted_at' => now()])->save();
    }

    public function render()
    {
        $enrollment = $this->enrollment();
        $first = CarbonImmutable::create($this->year, $this->month, 1);

        return view('livewire.cards.tracking', [
            'enrollment' => $enrollment,
            'sheet' => $this->cards()->monthlySheet($enrollment, $this->year, $this->month),
            'daysInMonth' => $first->daysInMonth,
            'label' => $first->translatedFormat('F Y'),
            'restDays' => collect(range(1, $first->daysInMonth))->filter(fn ($d) => $first->day($d)->dayOfWeek === $enrollment->rest_weekday)->all(),
        ])->layout('components.layouts.app', $this->layoutData(__('Planilla')));
    }
}
