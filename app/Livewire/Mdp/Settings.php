<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use Livewire\Component;

class Settings extends Component
{
    use HasEnrollment;

    public string $mode = 'full';

    public string $phase = 'auto';

    public int $rest_weekday = 6;

    public string $reading_time = '07:00';

    public array $review_times = [];

    public ?string $minimum_alert_time = null;

    public function mount(): void
    {
        $e = $this->enrollment();
        $this->mode = $e->mode;
        $this->phase = $e->phase ? (string) $e->phase : 'auto';
        $this->rest_weekday = $e->rest_weekday;
        $this->reading_time = $e->reading_time;
        $this->review_times = $e->review_times;
        $this->minimum_alert_time = $e->minimum_alert_time;
    }

    public function save(): void
    {
        $data = $this->validate([
            'mode' => ['required', 'in:full,light'],
            'phase' => ['required', 'in:auto,1,2'],
            'rest_weekday' => ['required', 'integer', 'between:0,6'],
            'reading_time' => ['required', 'date_format:H:i'],
            'review_times' => ['required', 'array', 'size:3'],
            'review_times.*' => ['required', 'date_format:H:i'],
            'minimum_alert_time' => ['nullable', 'date_format:H:i'],
        ]);

        $data['phase'] = $data['phase'] === 'auto' ? null : (int) $data['phase'];
        $this->enrollment()->forceFill($data)->save();
        session()->flash('status', __('Ajustes guardados.'));
        $this->redirectRoute('mdp.settings');
    }

    public function render()
    {
        return view('livewire.mdp.settings', ['enrollment' => $this->enrollment()])
            ->layout('components.layouts.app', $this->layoutData(__('Ajustes')));
    }
}
