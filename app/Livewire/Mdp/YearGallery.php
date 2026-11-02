<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use Livewire\Component;

class YearGallery extends Component
{
    use HasEnrollment;

    public function render()
    {
        return view('livewire.mdp.year', [
            'years' => $this->mdp()->yearGallery($this->enrollment()),
        ])->layout('components.layouts.app', $this->layoutData(__('Galería anual'), route('mdp.month')));
    }
}
