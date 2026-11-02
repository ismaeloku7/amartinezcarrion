<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use App\Models\Mdp\MdpMonth;
use App\Models\Mdp\MdpText;
use Livewire\Component;

class MonthClose extends Component
{
    use HasEnrollment;

    public ?int $monthIndex = null;

    public function mount(?int $month = null): void
    {
        $this->monthIndex = $month;
    }

    protected function month(): MdpMonth
    {
        $enrollment = $this->enrollment();

        if ($this->monthIndex) {
            return $enrollment->months()->where('index', $this->monthIndex)->firstOrFail();
        }

        return $this->mdp()->pendingMonthClose($enrollment, $this->today())
            ?? $this->mdp()->monthFor($enrollment, $this->today());
    }

    public function selectMonthText(int $textId): void
    {
        $month = $this->month();
        $candidates = $month->weeks()->pluck('week_text_id')->filter()->all();
        abort_unless(in_array($textId, $candidates, true), 403);
        $this->mdp()->selectMonthText($month, MdpText::findOrFail($textId));
    }

    public function render()
    {
        $month = $this->month();
        $this->mdp()->applyMonthAutoSelection($month);
        $month->refresh();

        return view('livewire.mdp.month', [
            'month' => $month,
            'weeks' => $month->weeks(),
            'isCurrent' => $month->ends_on->gte($this->today()),
            'months' => $this->enrollment()->months()->with('monthText')->orderByDesc('index')->get(),
        ])->layout('components.layouts.app', $this->layoutData(__('Texto del mes'), route('mdp.today')));
    }
}
