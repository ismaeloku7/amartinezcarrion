<?php

namespace App\Livewire\Tests\Johari;

use App\Models\Tests\JohariInvitation;
use App\Models\Tests\TestTake;
use App\Services\Tests\JohariService;
use Livewire\Component;

class Invite extends Component
{
    public TestTake $take;

    public string $label = '';

    public int $maxUses = 1;

    public function mount(TestTake $take): void
    {
        abort_unless($take->user_id === auth()->id(), 403);
        $this->take = $take;
    }

    public function create(JohariService $johari): void
    {
        try {
            $johari->createInvitation($this->take, trim($this->label) ?: null, max(1, min(20, $this->maxUses)));
            $this->label = '';
            $this->maxUses = 1;
        } catch (\InvalidArgumentException $e) {
            $this->addError('label', $e->getMessage());
        }
    }

    public function revoke(int $id): void
    {
        JohariInvitation::where('take_id', $this->take->id)->findOrFail($id)->forceFill(['revoked_at' => now()])->save();
    }

    public function render(JohariService $johari)
    {
        $this->take->refresh();

        return view('livewire.tests.johari.invite', [
            'invitations' => $this->take->invitations()->latest()->get(),
            'responses' => $this->take->responses()->count(),
            'min' => (int) $this->take->data('min_responses'),
            'max' => (int) $johari->config($this->take->test, 'max_invitations'),
            'hasResults' => $this->take->result !== null,
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Invitar'), 'back' => route('tests.show', $this->take->test)]);
    }
}
