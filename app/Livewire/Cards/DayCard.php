<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use App\Models\Cards\Card;
use Livewire\Component;

/** The chosen card, behind a "try to recall it first" screen. */
class DayCard extends Component
{
    use HasEnrollment;

    public bool $revealed = false;

    public function reveal(): void
    {
        $this->revealed = true;
    }

    public function markReview(): void
    {
        $this->cards()->markReview($this->cards()->dayFor($this->enrollment(), $this->today()));
    }

    public function render()
    {
        $day = $this->cards()->dayFor($this->enrollment(), $this->today())->load('chosenCard');

        return view('livewire.cards.day', [
            'day' => $day,
            'card' => $day->chosenCard,
            'visualization' => $this->enrollment()->unlocked_until >= 31 ? Card::where('number', 31)->first() : null,
        ])->layout('components.layouts.app', $this->layoutData(__('Ficha del día'), route('cards.today')));
    }
}
