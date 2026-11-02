<div class="space-y-6">
    <form wire:submit="save" class="space-y-4">
        <x-ui.card :title="__('Modo y fase')">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Modo') }}</span>
                    <select wire:model="mode" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm"><option value="full">{{ __('Completo') }}</option><option value="light">{{ __('Ligero') }}</option></select></label>
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Fase') }}</span>
                    <select wire:model="phase" class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm"><option value="auto">{{ __('Automática (3 semanas en fase 1)') }}</option><option value="1">{{ __('Fase 1') }}</option><option value="2">{{ __('Fase 2') }}</option></select></label>
            </div>
        </x-ui.card>
        <x-ui.card :title="__('Horario')">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Hora de lectura') }}</span><input type="time" wire:model="reading_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Repaso :n', ['n' => $i + 1]) }}</span><input type="time" wire:model="review_times.{{ $i }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                @endforeach
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Aviso de mínimo') }}</span><input type="time" wire:model="minimum_alert_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
            </div>
        </x-ui.card>
        <x-ui.button type="submit">{{ __('Guardar') }}</x-ui.button>
    </form>

    <x-ui.card :title="__('Notificaciones en este dispositivo')">
        <div class="flex gap-2">
            <x-ui.button type="button" onclick="window.appPush.subscribe('mdp')">{{ __('Activar') }}</x-ui.button>
            <x-ui.button type="button" variant="secondary" onclick="window.appPush.unsubscribe()">{{ __('Desactivar') }}</x-ui.button>
        </div>
        <p id="push-status" class="mt-2 text-xs text-stone-500"></p>
    </x-ui.card>

    <x-ui.card :title="__('Instalar como app')">
        <p class="text-sm text-stone-600">{{ __('En el móvil, usa «Añadir a pantalla de inicio» desde el menú del navegador. El Devocional se instala como una app independiente.') }}</p>
    </x-ui.card>
</div>
