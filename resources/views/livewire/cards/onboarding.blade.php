<div class="space-y-5">
    <div class="rounded-3xl bg-app p-5 text-white shadow-float">
        <x-ui.icon name="box" class="mb-3 h-8 w-8 text-white/80" />
        <p class="text-[0.9375rem] leading-relaxed text-white/90">{{ __('Cada mañana repasarás las fichas del grupo que toque y escogerás una ficha para el día. La repasarás tres veces y por la noche valorarás cuánto la has tenido presente. Un día a la semana es de descanso.') }}</p>
    </div>
    <form wire:submit="start" class="space-y-5">
        <x-ui.card :title="__('Horario')">
            <div class="grid grid-cols-2 items-end gap-3">
                <label class="block"><span class="field-label">{{ __('Elección de la ficha (mañana)') }}</span><input type="time" wire:model="morning_time" class="field"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="field-label">{{ __('Repaso :n', ['n' => $i + 1]) }}</span><input type="time" wire:model="review_times.{{ $i }}" class="field"></label>
                @endforeach
                <label class="block"><span class="field-label">{{ __('Valoración nocturna') }}</span><input type="time" wire:model="evening_time" class="field"></label>
            </div>
        </x-ui.card>
        <x-ui.card :title="__('¿Por dónde vas en el libro?')">
            <p class="text-sm leading-relaxed text-muted">{{ __('Si ya has leído parte del libro, indica hasta qué apartado para incorporar sus fichas. Si empiezas ahora, déjalo en 0.') }}</p>
            <input type="number" inputmode="numeric" min="0" max="42" wire:model="unlocked_until" class="field mt-3 w-28 text-center text-lg font-semibold">
            @error('unlocked_until')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        </x-ui.card>
        <x-ui.action-bar>
            <x-ui.button type="submit" block>{{ __('Empezar') }}</x-ui.button>
        </x-ui.action-bar>
    </form>
</div>
