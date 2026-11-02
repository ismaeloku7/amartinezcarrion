<div class="space-y-5">
    <form wire:submit="save" class="space-y-5">
        <x-ui.card :title="__('Horario')">
            <div class="grid grid-cols-2 items-end gap-3">
                <label class="block"><span class="field-label">{{ __('Repaso de la mañana') }}</span><input type="time" wire:model="morning_time" class="field"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="field-label">{{ __('Repaso :n', ['n' => $i + 1]) }}</span><input type="time" wire:model="review_times.{{ $i }}" class="field"></label>
                @endforeach
                <label class="block"><span class="field-label">{{ __('Valoración nocturna') }}</span><input type="time" wire:model="evening_time" class="field"></label>
            </div>
            <label class="mt-4 flex items-center gap-3 rounded-2xl bg-fill p-4 text-sm"><input type="checkbox" wire:model="tracking_enabled" class="rounded"> {{ __('Pedir la valoración cada noche') }}</label>
        </x-ui.card>
        <x-ui.button type="submit" block>{{ __('Guardar') }}</x-ui.button>
    </form>
    <x-ui.card :title="__('Notificaciones en este dispositivo')">
        <div class="grid grid-cols-2 gap-2">
            <x-ui.button type="button" onclick="window.appPush.subscribe('cards')">{{ __('Activar') }}</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.appPush.unsubscribe()">{{ __('Desactivar') }}</x-ui.button>
        </div>
        <p id="push-status" class="mt-2 text-center text-xs text-muted"></p>
    </x-ui.card>
</div>
