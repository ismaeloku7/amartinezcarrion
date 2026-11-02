<?php

namespace App\Livewire\Portal;

use App\Models\Language;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Account extends Component
{
    public string $name = '';

    public string $locale = 'es';

    public string $timezone = 'Europe/Madrid';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->locale = $user->locale;
        $this->timezone = $user->timezone;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', Rule::in(Language::activeCodes())],
            'timezone' => ['required', 'timezone:all'],
        ]);

        auth()->user()->forceFill($data)->save();
        session()->put('locale', $data['locale']);
        $this->redirectRoute('account', navigate: false);
        session()->flash('status', __('Datos guardados.'));
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.portal.account', [
            'languages' => Language::query()->where('active', true)->orderBy('position')->get(),
            'entitlements' => $user->entitlements()->with('product')->active()->get(),
            'subscriptions' => $user->subscriptions()->get(),
            'timezones' => \DateTimeZone::listIdentifiers(),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => __('Mi cuenta'), 'back' => route('home')]);
    }
}
