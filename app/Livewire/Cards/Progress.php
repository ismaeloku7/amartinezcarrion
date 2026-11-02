<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use App\Models\Cards\Card;
use Livewire\Component;

class Progress extends Component
{
    use HasEnrollment;

    public function render()
    {
        $enrollment = $this->enrollment();
        $days = $enrollment->days()->where('is_rest_day', false)->get();
        $active = $days->whereNotNull('chosen_card_id');

        return view('livewire.cards.progress', [
            'enrollment' => $enrollment,
            'daysSinceStart' => $enrollment->daysSinceStart(),
            'activeDays' => $active->count(),
            'reviewsTotal' => $active->sum('reviews_done'),
            'reviewsPossible' => $active->count() * 3,
            'avgRating' => $active->whereNotNull('rating')->avg('rating'),
            'mostChosen' => Card::query()->whereIn('id', $active->pluck('chosen_card_id'))->get()
                ->map(fn (Card $c) => ['card' => $c, 'n' => $active->where('chosen_card_id', $c->id)->count()])
                ->sortByDesc('n')->take(5),
            'sixMonthsPct' => min(100, round($enrollment->daysSinceStart() / 182 * 100)),
        ])->layout('components.layouts.app', $this->layoutData(__('Progreso')));
    }
}
