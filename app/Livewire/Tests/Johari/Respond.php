<?php

namespace App\Livewire\Tests\Johari;

use App\Livewire\Tests\Concerns\PicksWordsInTwoSteps;
use App\Models\Tests\JohariInvitation;
use App\Models\Tests\Test;
use App\Services\Tests\JohariService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Livewire\Component;

/** Public page: an invited person picks words about the user, in two steps. No login. */
class Respond extends Component
{
    use PicksWordsInTwoSteps;

    public string $token;

    public bool $done = false;

    public ?string $error = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $invitation = $this->invitation();

        if (! $invitation || ! $invitation->isUsable()) {
            $this->error = __('Este enlace ya no está disponible.');

            return;
        }

        $this->browserId();
    }

    protected function invitation(): ?JohariInvitation
    {
        return JohariInvitation::query()->where('token', $this->token)->with('take.test.wordList.words', 'take.user')->first();
    }

    protected function wordTest(): Test
    {
        return $this->invitation()->take->test;
    }

    protected function who(): string
    {
        return 'other';
    }

    /** A random id kept in a cookie, so one browser answers one invitation once (no IP, no user agent). */
    protected function browserId(): string
    {
        $id = request()->cookie('johari_bid');

        if (! $id) {
            $id = Str::random(32);
            Cookie::queue('johari_bid', $id, 60 * 24 * 365, null, null, null, true, false, 'Lax');
        }

        return $id;
    }

    public function send(JohariService $johari): void
    {
        $invitation = $this->invitation();
        $fingerprint = hash('sha256', $this->browserId().'|'.$invitation->take_id);

        try {
            $johari->respond($invitation, $this->selectedKeys(), $fingerprint);
            $this->done = true;
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->addError('selected', $e->getMessage());
        }
    }

    public function render()
    {
        $invitation = $this->invitation();
        $usable = $invitation && ! $this->error;

        return view('livewire.tests.johari.respond', [
            'invitation' => $invitation,
            'words' => $usable ? $this->wordsFor($this->wordStep) : collect(),
            'selected' => $this->wordStep === 'negative' ? $this->negative : $this->positive,
            'n' => $usable ? $this->needed($this->wordStep) : 0,
            'hasNegative' => $usable && $this->needed('negative') > 0,
            'name' => $invitation?->take->user->name,
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Ventana de Johari')]);
    }
}
