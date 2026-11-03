<div class="space-y-3">
    <x-ui.card class="p-4" x-data="{ d: '' }">
        <div class="field-label">{{ __('Abrir o completar un día') }}</div>
        <div class="mt-1 flex gap-2">
            <input type="date" x-model="d" min="{{ $startedAt }}" max="{{ $today }}" class="field flex-1">
            <x-ui.button type="button" variant="secondary" size="sm" class="shrink-0" x-on:click="if (d) window.location = '{{ url('/mdp/devocional') }}/' + d">{{ __('Abrir') }}</x-ui.button>
        </div>
        <p class="mt-1 text-xs text-muted">{{ __('Puedes editar cualquier página desde el inicio de la práctica, incluidos los días que se quedaron sin hacer.') }}</p>
    </x-ui.card>
    @forelse ($days as $d)
        <x-ui.card class="p-4">
            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-xs">
                <span class="font-semibold text-stone-700 first-letter:uppercase">{{ $d->date->translatedFormat('l, j \d\e F Y') }}</span>
                <span class="flex items-center gap-1.5 text-muted">
                    @if ($d->reading_from){{ $d->reading_from }} – {{ $d->reading_to }}@endif
                    @if ($d->isCompleted() && $editing !== $d->id)
                        {{-- The time is editable in case the timer failed that day. --}}
                        <button type="button" wire:click="edit({{ $d->id }})" class="rounded-full bg-fill px-2 py-0.5 tabular-nums text-stone-600 underline-offset-2 hover:underline" title="{{ __('Corregir el tiempo de lectura') }}">{{ __(':n min', ['n' => $d->durationMinutes()]) }}</button>
                        @if ($d->isShortReading())<span class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700">{{ __('Menos tiempo') }}</span>@endif
                    @endif
                    <a href="{{ route('mdp.devotional', ['date' => $d->date->toDateString()]) }}" class="font-medium text-app">{{ __('Editar') }}</a>
                </span>
            </div>
            @if ($editing === $d->id)
                <form wire:submit="saveMinutes" class="mt-2.5 flex items-end gap-2 rounded-2xl bg-fill p-3">
                    <label class="block grow">
                        <span class="field-label">{{ __('Minutos de lectura') }}</span>
                        <input type="number" min="0" max="600" inputmode="numeric" wire:model="minutes" class="field" autofocus>
                        @error('minutes')<span class="mt-1.5 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <x-ui.button type="submit" size="sm">{{ __('Guardar') }}</x-ui.button>
                    <x-ui.button type="button" wire:click="cancelEdit" variant="ghost" size="sm">{{ __('Cancelar') }}</x-ui.button>
                </form>
            @endif
            <ul class="mt-2.5 space-y-1.5 text-[0.9375rem] leading-relaxed">
                @foreach ($d->texts as $t)<li class="{{ $t->is_day_text ? 'font-medium text-red-900' : 'text-stone-700' }}"><x-mdp.text :text="$t" /></li>@endforeach
            </ul>
            @if ($d->prayer)
                <details class="mt-3 rounded-2xl bg-fill px-3.5 py-2.5 text-sm text-stone-600">
                    <summary class="cursor-pointer text-xs font-medium text-stone-500">{{ __('Oración') }}</summary>
                    <p class="mt-2 whitespace-pre-line">{{ $d->prayer }}</p>
                </details>
            @endif
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Todavía no hay páginas en tu libreta.') }}</x-ui.alert>
    @endforelse
    {{ $days->links() }}
</div>
