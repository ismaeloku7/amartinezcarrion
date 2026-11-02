<?php

namespace App\Livewire\Tests\Johari;

use App\Models\Tests\JohariInvitation;
use App\Services\Tests\JohariService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Livewire\Component;

/** Public page: an invited person picks N words about the user. No login. */
class Respond extends Component
{
    public string $token;

    public array $selected = [];

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

    public function toggle(string $key): void
    {
        $invitation = $this->invitation();
        $n = (int) app(JohariService::class)->config($invitation->take->test, 'other_words');

        if (in_array($key, $this->selected, true)) {
            $this->selected = array_values(array_diff($this->selected, [$key]));
        } elseif (count($this->selected) < $n) {
            $this->selected[] = $key;
        }
    }

    public function send(JohariService $johari): void
    {
        $invitation = $this->invitation();
        $fingerprint = hash('sha256', $this->browserId().'|'.$invitation->take_id);

        try {
            $johari->respond($invitation, $this->selected, $fingerprint);
            $this->done = true;
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->addError('selected', $e->getMessage());
        }
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

    public function render(JohariService $johari)
    {
        $invitation = $this->invitation();

        return view('livewire.tests.johari.respond', [
            'invitation' => $invitation,
            'words' => $invitation?->take->test->wordList->words ?? collect(),
            'n' => $invitation ? (int) $johari->config($invitation->take->test, 'other_words') : 0,
            'name' => $invitation?->take->user->name,
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Ventana de Johari')]);
    }
}
