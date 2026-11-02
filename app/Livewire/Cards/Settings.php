<?php

namespace App\Livewire\Cards;

use App\Livewire\Cards\Concerns\HasEnrollment;
use Livewire\Component;

class Settings extends Component
{
    use HasEnrollment;

    public int $rest_weekday = 6;

    public string $morning_time = '07:30';

    public array $review_times = [];

    public string $evening_time = '22:30';

    public bool $tracking_enabled = true;

    public function mount(): void
    {
        $e = $this->enrollment();
        $this->rest_weekday = $e->rest_weekday;
        $this->morning_time = $e->morning_time;
        $this->review_times = $e->review_times;
        $this->evening_time = $e->evening_time;
        $this->tracking_enabled = $e->tracking_enabled;
    }

    public function save(): void
    {
        $data = $this->validate([
            'rest_weekday' => ['required', 'integer', 'between:0,6'],
            'morning_time' => ['required', 'date_format:H:i'],
            'review_times' => ['required', 'array', 'size:3'],
            'review_times.*' => ['required', 'date_format:H:i'],
            'evening_time' => ['required', 'date_format:H:i'],
            'tracking_enabled' => ['boolean'],
        ]);

        $this->enrollment()->forceFill($data)->save();
        session()->flash('status', __('Ajustes guardados.'));
        $this->redirectRoute('cards.settings');
    }

    public function render()
    {
        return view('livewire.cards.settings')
            ->layout('components.layouts.app', $this->layoutData(__('Ajustes')));
    }
}
