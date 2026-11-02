<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use App\Models\Cards\Card;
use Illuminate\Support\Collection;
use Livewire\Component;

/** Morning review: flip through the group's cards one by one, then pick the card of the day. */
class Review extends Component
{
    use HasEnrollment;

    public int $index = 0;

    public bool $flipped = false;

    public string $phase = 'review'; // review|choose

    public function mount(): void
    {
        $day = $this->cards()->dayFor($this->enrollment(), $this->today());
        if ($day->is_rest_day || ! $day->group_index) {
            $this->redirectRoute('cards.today');

            return;
        }
        if ($day->reviewed_at) {
            $this->phase = 'choose';
        }
    }

    protected function group(): Collection
    {
        $day = $this->cards()->dayFor($this->enrollment(), $this->today());

        return $this->cards()->cardsForGroup($this->enrollment(), $day->group_index);
    }

    public function flip(): void
    {
        $this->flipped = ! $this->flipped;
    }

    public function nextCard(): void
    {
        $this->flipped = false;
        if ($this->index < $this->group()->count() - 1) {
            $this->index++;
        } else {
            $this->cards()->completeMorningReview($this->cards()->dayFor($this->enrollment(), $this->today()));
            $this->phase = 'choose';
        }
    }

    public function previousCard(): void
    {
        $this->flipped = false;
        $this->index = max(0, $this->index - 1);
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
            'current' => $cards[$this->index] ?? null,
            'day' => $day,
            'total' => $cards->count(),
        ])->layout('components.layouts.app', $this->layoutData(__('Repaso del grupo :n', ['n' => $day->group_index]), route('cards.today')));
    }
}
