<div class="space-y-5">
    <div class="rounded-3xl bg-app p-5 text-white shadow-float">
        <x-ui.icon name="book" class="mb-3 h-8 w-8 text-white/80" />
        <p class="text-[0.9375rem] leading-relaxed text-white/90">{{ __('El Método Devocional Proyectivo es una lectura diaria de 15 a 20 minutos en la que anotas los textos que resuenan contigo, eliges uno para el día y lo repasas tres veces. La app te acompaña en cada paso.') }}</p>
    </div>

    <form wire:submit="start" class="space-y-5">
        <x-ui.card :title="__('¿Cómo quieres usar la app?')">
            <div class="space-y-3">
                <label class="flex cursor-pointer gap-3 rounded-2xl p-4 transition {{ $mode === 'full' ? 'bg-app/5 ring-2 ring-app' : 'bg-fill' }}">
                    <input type="radio" wire:model.live="mode" value="full" class="mt-0.5">
                    <span><span class="block font-medium">{{ __('Modo completo') }}</span><span class="mt-0.5 block text-sm text-muted">{{ __('La app sustituye a la libreta: temporizador, textos, subrayado, oración escrita y fichas.') }}</span></span>
                </label>
                <label class="flex cursor-pointer gap-3 rounded-2xl p-4 transition {{ $mode === 'light' ? 'bg-app/5 ring-2 ring-app' : 'bg-fill' }}">
                    <input type="radio" wire:model.live="mode" value="light" class="mt-0.5">
                    <span><span class="block font-medium">{{ __('Modo ligero') }}</span><span class="mt-0.5 block text-sm text-muted">{{ __('Sigues con la libreta en papel. Aquí solo apuntas los textos, eliges el del día, el de la semana y el del mes, y recibes los recordatorios.') }}</span></span>
                </label>
            </div>
        </x-ui.card>

        <x-ui.card :title="__('Horario')">
            <div class="grid grid-cols-2 items-end gap-3">
                <label class="block"><span class="field-label">{{ __('Hora de lectura') }}</span>
                    <input type="time" wire:model.live="reading_time" class="field"></label>
                <x-ui.weekday-select model="rest_weekday" :label="__('Día de descanso')" />
                @foreach ([0, 1, 2] as $i)
                    <label class="block"><span class="field-label">{{ __('Repaso :n', ['n' => $i + 1]) }}</span>
                        <input type="time" wire:model="review_times.{{ $i }}" class="field"></label>
                @endforeach
                <label class="block"><span class="field-label">{{ __('Aviso si aún no has hecho el devocional') }}</span>
                    <input type="time" wire:model="minimum_alert_time" class="field"></label>
            </div>
            <p class="mt-3 text-xs leading-relaxed text-muted">{{ __('Los repasos del texto se hacen tres veces al día en horas fijas. Si lees por la mañana: antes de comer, antes de cenar y antes de dormir.') }}</p>
            @error('review_times.*')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </x-ui.card>

        <x-ui.action-bar>
            <x-ui.button type="submit" block>{{ __('Empezar') }}</x-ui.button>
        </x-ui.action-bar>
    </form>
</div>
