<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use App\Models\Cards\Card;
use App\Services\Cards\CardsService;
use Livewire\Component;

class Box extends Component
{
    use HasEnrollment;

    public function unlockNext(): void
    {
        $card = $this->cards()->unlock($this->enrollment());
        $this->redirectRoute('cards.show', ['card' => $card->number]);
    }

    public function render()
    {
        $enrollment = $this->enrollment();

        return view('livewire.cards.box', [
            'enrollment' => $enrollment,
            'groups' => Card::query()->orderBy('number')->get()->groupBy('group_index'),
            'unlocked' => $enrollment->unlocked_until,
            'situations' => CardsService::situations(),
        ])->layout('components.layouts.app', $this->layoutData(__('Caja de fichas')));
    }
}
