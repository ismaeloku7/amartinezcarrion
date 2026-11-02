<div class="space-y-5">
    <form wire:submit="save" class="space-y-5">
        <x-ui.card :title="__('Modo y fase')">
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="block"><span class="field-label">{{ __('Modo') }}</span>
                    <select wire:model="mode" class="field"><option value="full">{{ __('Completo') }}</option><option value="light">{{ __('Ligero') }}</option></select></label>
                <label class="block"><span class="field-label">{{ __('Fase') }}</span>
                    <select wire:model="phase" class="field"><option value="auto">{{ __('Automática (3 semanas en fase 1)') }}</option><option value="1">{{ __('Fase 1') }}</option><option value="2">{{ __('Fase 2') }}</option></select></label>
            </div>
        </x-ui.card>
        <x-ui.card :title="__('Horario')">
            <div class="grid grid-cols-2 items-end gap-3">
                <label class="block"><span class="field-label">{{ __('Hora de lectura') }}</span><input type="time" wire:model="reading_time" class="field"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="field-label">{{ __('Repaso :n', ['n' => $i + 1]) }}</span><input type="time" wire:model="review_times.{{ $i }}" class="field"></label>
                @endforeach
                <label class="block"><span class="field-label">{{ __('Aviso de mínimo') }}</span><input type="time" wire:model="minimum_alert_time" class="field"></label>
            </div>
        </x-ui.card>
        <x-ui.button type="submit" block>{{ __('Guardar') }}</x-ui.button>
    </form>

    <x-ui.card :title="__('Notificaciones en este dispositivo')">
        <div class="grid grid-cols-2 gap-2">
            <x-ui.button type="button" onclick="window.appPush.subscribe('mdp')">{{ __('Activar') }}</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.appPush.unsubscribe()">{{ __('Desactivar') }}</x-ui.button>
        </div>
        <p id="push-status" class="mt-2 text-center text-xs text-muted"></p>
    </x-ui.card>

    <x-ui.card :title="__('Instalar como app')">
        <p class="text-sm leading-relaxed text-muted">{{ __('En el móvil, usa «Añadir a pantalla de inicio» desde el menú del navegador. El Devocional se instala como una app independiente.') }}</p>
    </x-ui.card>
</div>
