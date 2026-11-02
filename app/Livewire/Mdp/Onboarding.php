<?php

namespace App\Livewire\Mdp;

use App\Models\Mdp\MdpEnrollment;
use App\Services\Mdp\MdpService;
use Livewire\Component;

class Onboarding extends Component
{
    public string $mode = MdpEnrollment::MODE_FULL;

    public int $rest_weekday = 6;

    public string $reading_time = '07:00';

    public array $review_times = ['13:30', '20:30', '23:00'];

    public ?string $minimum_alert_time = '21:00';

    public function mount(): void
    {
        if (auth()->user()->mdpEnrollment) {
            $this->redirectRoute('mdp.today');
        }
    }

    public function updatedReadingTime(string $value): void
    {
        $this->review_times = (int) substr($value, 0, 2) >= 18
            ? MdpService::DEFAULT_REVIEW_TIMES_EVENING
            : MdpService::DEFAULT_REVIEW_TIMES_MORNING;
    }

    public function start(MdpService $service): void
    {
        $data = $this->validate([
            'mode' => ['required', 'in:full,light'],
            'rest_weekday' => ['required', 'integer', 'between:0,6'],
            'reading_time' => ['required', 'date_format:H:i'],
            'review_times' => ['required', 'array', 'size:3'],
            'review_times.*' => ['required', 'date_format:H:i'],
            'minimum_alert_time' => ['nullable', 'date_format:H:i'],
        ]);

        $service->enroll(auth()->user(), $data);
        $this->redirectRoute('mdp.today');
    }

    public function render()
    {
        return view('livewire.mdp.onboarding')
            ->layout('components.layouts.app', ['appKey' => 'mdp', 'title' => __('Empezar el método')]);
    }
}
