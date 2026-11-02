<?php

namespace App\Livewire\Cards;

use App\Services\Cards\CardsService;
use Livewire\Component;

class Onboarding extends Component
{
    public int $rest_weekday = 6;

    public string $morning_time = '07:30';

    public array $review_times = ['13:30', '20:30', '23:00'];

    public string $evening_time = '22:30';

    public int $unlocked_until = 0;

    public function mount(): void
    {
        if (auth()->user()->cardsEnrollment) {
            $this->redirectRoute('cards.today');
        }
    }

    public function start(CardsService $service): void
    {
        $data = $this->validate([
            'rest_weekday' => ['required', 'integer', 'between:0,6'],
            'morning_time' => ['required', 'date_format:H:i'],
            'review_times' => ['required', 'array', 'size:3'],
            'review_times.*' => ['required', 'date_format:H:i'],
            'evening_time' => ['required', 'date_format:H:i'],
            'unlocked_until' => ['required', 'integer', 'between:0,42'],
        ]);

        $service->enroll(auth()->user(), $data);
        $this->redirectRoute('cards.today');
    }

    public function render()
    {
        return view('livewire.cards.onboarding')
            ->layout('components.layouts.app', ['appKey' => 'cards', 'title' => __('Empezar el programa')]);
    }
}
