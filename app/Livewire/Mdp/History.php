<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use App\Models\Mdp\MdpDay;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use HasEnrollment, WithPagination;

    /** Day whose reading time is being corrected (the timer may have failed). */
    public ?int $editing = null;

    public string $minutes = '';

    public function edit(int $dayId): void
    {
        $day = $this->dayOrFail($dayId);
        $this->editing = $day->id;
        $this->minutes = (string) $day->durationMinutes();
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editing = null;
        $this->minutes = '';
    }

    public function saveMinutes(): void
    {
        $data = $this->validate(['minutes' => ['required', 'integer', 'min:0', 'max:600']]);
        $this->dayOrFail($this->editing)->forceFill(['duration_seconds' => $data['minutes'] * 60])->save();
        $this->cancelEdit();
    }

    private function dayOrFail(?int $dayId): MdpDay
    {
        return $this->enrollment()->days()->findOrFail($dayId);
    }

    public function render()
    {
        $days = $this->enrollment()->days()
            ->whereHas('texts')
            ->with('texts')
            ->orderByDesc('date')
            ->paginate(10);

        return view('livewire.mdp.history', ['days' => $days])
            ->layout('components.layouts.app', $this->layoutData(__('Libreta')));
    }
}
