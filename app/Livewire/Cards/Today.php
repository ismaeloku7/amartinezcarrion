<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use App\Models\Cards\Card;
use Livewire\Component;

class Today extends Component
{
    use HasEnrollment;

    public function markReview(): void
    {
        $this->cards()->markReview($this->cards()->dayFor($this->enrollment(), $this->today()));
    }

    public function rate(int $rating): void
    {
        $this->cards()->rate($this->cards()->dayFor($this->enrollment(), $this->today()), $rating);
    }

    public function keepTracking(bool $keep): void
    {
        $this->enrollment()->forceFill(['tracking_enabled' => $keep, 'tracking_prompted_at' => now()])->save();
    }

    public function render()
    {
        $enrollment = $this->enrollment();
        $today = $this->today();
        $day = $this->cards()->dayFor($enrollment, $today)->load('chosenCard');

        return view('livewire.cards.today', [
            'enrollment' => $enrollment,
            'today' => $today,
            'day' => $day,
            'group' => $day->group_index,
            'groupCards' => $day->group_index ? $this->cards()->cardsForGroup($enrollment, $day->group_index) : collect(),
            'trackingPrompt' => $this->cards()->trackingPromptDue($enrollment),
            'visualization' => $enrollment->unlocked_until >= 31 ? Card::where('number', 31)->first() : null,
        ])->layout('components.layouts.app', $this->layoutData(__('Hoy')));
    }
}
