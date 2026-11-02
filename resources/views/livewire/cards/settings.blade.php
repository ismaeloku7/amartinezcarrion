<div class="space-y-6">
    <form wire:submit="save" class="space-y-4">
        <x-ui.card :title="__('Horario')">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Repaso de la mañana') }}</span><input type="time" wire:model="morning_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Repaso :n', ['n' => $i + 1]) }}</span><input type="time" wire:model="review_times.{{ $i }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                @endforeach
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Valoración nocturna') }}</span><input type="time" wire:model="evening_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" wire:model="tracking_enabled" class="rounded"> {{ __('Pedir la valoración cada noche') }}</label>
        </x-ui.card>
        <x-ui.button type="submit">{{ __('Guardar') }}</x-ui.button>
    </form>
    <x-ui.card :title="__('Notificaciones en este dispositivo')">
        <div class="flex gap-2">
            <x-ui.button type="button" onclick="window.appPush.subscribe('cards')">{{ __('Activar') }}</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.appPush.unsubscribe()">{{ __('Desactivar') }}</x-ui.button>
        </div>
        <p id="push-status" class="mt-2 text-xs text-stone-500"></p>
    </x-ui.card>
</div>
