<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use App\Models\Cards\Card;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Morning: look at the group's cards and choose the card of the day. Only that card is
 * reviewed afterwards (three times); the group is not gone through card by card.
 */
class Review extends Component
{
    use HasEnrollment;

    /** Card opened to see both sides before choosing it. */
    public ?int $preview = null;

    public bool $flipped = false;

    public function mount(): void
    {
        $day = $this->cards()->dayFor($this->enrollment(), $this->today());
        if ($day->is_rest_day || ! $day->group_index) {
            $this->redirectRoute('cards.today');
        }
    }

    protected function group(): Collection
    {
        $day = $this->cards()->dayFor($this->enrollment(), $this->today());

        return $this->cards()->cardsForGroup($this->enrollment(), $day->group_index);
    }

    public function open(int $cardId): void
    {
        $this->preview = $this->group()->firstWhere('id', $cardId)?->id;
        $this->flipped = false;
    }

    public function close(): void
    {
        $this->preview = null;
        $this->flipped = false;
    }

    public function flip(): void
    {
        $this->flipped = ! $this->flipped;
    }

    public function choose(int $cardId): void
    {
        $day = $this->cards()->dayFor($this->enrollment(), $this->today());

        try {
            $this->cards()->chooseCard($day, Card::findOrFail($cardId));
        } catch (\InvalidArgumentException $e) {
            $this->addError('choose', $e->getMessage());

            return;
        }

        $this->redirectRoute('cards.day');
    }

    public function render()
    {
        $cards = $this->group();
        $day = $this->cards()->dayFor($this->enrollment(), $this->today());

        return view('livewire.cards.review', [
            'cards' => $cards,
            'day' => $day,
            'previewCard' => $this->preview ? $cards->firstWhere('id', $this->preview) : null,
        ])->layout('components.layouts.app', $this->layoutData(__('Ficha de hoy · grupo :n', ['n' => $day->group_index]), route('cards.today')));
    }
}
