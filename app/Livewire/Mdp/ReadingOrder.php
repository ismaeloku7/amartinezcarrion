<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use App\Services\Mdp\MdpService;
use Livewire\Component;

class ReadingOrder extends Component
{
    use HasEnrollment;

    public function setPosition(int $position): void
    {
        $max = count(MdpService::readingOrder());
        $this->enrollment()->forceFill(['reading_order_position' => max(1, min($max, $position))])->save();
    }

    public function render()
    {
        return view('livewire.mdp.order', [
            'books' => MdpService::readingOrder(),
            'position' => $this->enrollment()->reading_order_position,
        ])->layout('components.layouts.app', $this->layoutData(__('Orden de lectura')));
    }
}
