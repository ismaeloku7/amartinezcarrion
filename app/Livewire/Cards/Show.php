<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use App\Models\Cards\Card;
use Livewire\Component;

class Show extends Component
{
    use HasEnrollment;

    public Card $card;

    public function mount(Card $card): void
    {
        abort_if($card->number > $this->enrollment()->unlocked_until, 403, __('Todavía no has incorporado esta ficha.'));
        $this->card = $card;
    }

    public function render()
    {
        return view('livewire.cards.show', ['card' => $this->card])
            ->layout('components.layouts.app', $this->layoutData(__('Ficha :n', ['n' => $this->card->number]), route('cards.box')));
    }
}
