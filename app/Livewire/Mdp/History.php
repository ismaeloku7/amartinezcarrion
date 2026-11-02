<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use HasEnrollment, WithPagination;

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
