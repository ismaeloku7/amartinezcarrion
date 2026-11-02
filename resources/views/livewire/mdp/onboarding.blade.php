<div class="space-y-6">
    <x-ui.card>
        <p class="text-sm text-stone-700">{{ __('El Método Devocional Proyectivo es una lectura diaria de 15 a 20 minutos en la que anotas los textos que resuenan contigo, eliges uno para el día y lo repasas tres veces. La app te acompaña en cada paso.') }}</p>
    </x-ui.card>

    <form wire:submit="start" class="space-y-6">
        <x-ui.card :title="__('¿Cómo quieres usar la app?')">
            <div class="space-y-3">
                <label class="flex cursor-pointer gap-3 rounded-lg border p-3 {{ $mode === 'full' ? 'border-[color:var(--app-color)] bg-red-50' : 'border-stone-200' }}">
                    <input type="radio" wire:model.live="mode" value="full" class="mt-1">
                    <span><span class="block font-medium">{{ __('Modo completo') }}</span><span class="text-sm text-stone-600">{{ __('La app sustituye a la libreta: temporizador, textos, subrayado, oración escrita y fichas.') }}</span></span>
                </label>
                <label class="flex cursor-pointer gap-3 rounded-lg border p-3 {{ $mode === 'light' ? 'border-[color:var(--app-color)] bg-red-50' : 'border-stone-200' }}">
                    <input type="radio" wire:model.live="mode" value="light" class="mt-1">
                    <span><span class="block font-medium">{{ __('Modo ligero') }}</span><span class="text-sm text-stone-600">{{ __('Sigues con la libreta en papel. Aquí solo apuntas los textos, eliges el del día, el de la semana y el del mes, y recibes los recordatorios.') }}</span></span>
                </label>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Horario')">
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Hora de lectura') }}</span>
                    <input type="time" wire:model.live="reading_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Repaso :n', ['n' => $i + 1]) }}</span>
                        <input type="time" wire:model="review_times.{{ $i }}" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
                @endforeach
                <label class="block"><span class="mb-1 block text-sm font-medium">{{ __('Aviso si aún no has hecho el devocional') }}</span>
                    <input type="time" wire:model="minimum_alert_time" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm"></label>
            </div>
            <p class="mt-3 text-xs text-stone-500">{{ __('Los repasos del texto se hacen tres veces al día en horas fijas. Si lees por la mañana: antes de comer, antes de cenar y antes de dormir.') }}</p>
            @error('review_times.*')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </x-ui.card>

        <x-ui.button type="submit" class="w-full">{{ __('Empezar') }}</x-ui.button>
    </form>
</div>
