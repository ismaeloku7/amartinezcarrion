<div class="space-y-6">
    <x-ui.card>
        <p class="text-sm text-stone-700">{{ __('Cada mañana repasarás las fichas del grupo que toque y escogerás una ficha para el día. La repasarás tres veces y por la noche valorarás cuánto la has tenido presente. Un día a la semana es de descanso.') }}</p>
    </x-ui.card>
    <form wire:submit="start" class="space-y-6">
        <x-ui.card :title="__('Horario')">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Repaso de la mañana') }}</span><input type="time" wire:model="morning_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Repaso :n', ['n' => $i + 1]) }}</span><input type="time" wire:model="review_times.{{ $i }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                @endforeach
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Valoración nocturna') }}</span><input type="time" wire:model="evening_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
            </div>
        </x-ui.card>
        <x-ui.card :title="__('¿Por dónde vas en el libro?')">
            <p class="text-sm text-stone-600">{{ __('Si ya has leído parte del libro, indica hasta qué apartado para incorporar sus fichas. Si empiezas ahora, déjalo en 0.') }}</p>
            <input type="number" min="0" max="42" wire:model="unlocked_until" class="mt-2 w-32 rounded-lg border border-stone-300 px-3 py-2 text-sm">
            @error('unlocked_until')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
        </x-ui.card>
        <x-ui.button type="submit" class="w-full">{{ __('Empezar') }}</x-ui.button>
    </form>
</div>
