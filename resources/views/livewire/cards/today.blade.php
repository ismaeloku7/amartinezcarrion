<div class="space-y-4">
    <div class="flex items-center justify-between text-sm text-stone-600">
        <span>{{ $today->translatedFormat('l, j \d\e F') }}</span>
        <span>{{ __(':n de 42 fichas', ['n' => $enrollment->unlocked_until]) }}</span>
    </div>

    @if ($trackingPrompt)
        <x-ui.alert type="info">
            <p>{{ __('Ha pasado el primer mes. La valoración nocturna ya no es obligatoria. ¿Quieres seguir haciéndola?') }}</p>
            <div class="mt-2 flex gap-2">
                <x-ui.button wire:click="keepTracking(true)" variant="secondary">{{ __('Sí, seguir') }}</x-ui.button>
                <x-ui.button wire:click="keepTracking(false)" variant="ghost">{{ __('No, parar') }}</x-ui.button>
            </div>
        </x-ui.alert>
    @endif

    @if ($day->is_rest_day)
        <x-ui.card :title="__('Hoy es día de descanso')">
            <p class="text-sm text-stone-600">{{ __('Hoy no hay repasos ni elección de ficha. Mañana se retoma la rueda de grupos.') }}</p>
        </x-ui.card>
    @elseif ($enrollment->unlocked_until === 0)
        <x-ui.card :title="__('Incorpora tu primera ficha')">
            <p class="text-sm text-stone-600">{{ __('Cuando termines de leer el primer apartado del libro, incorpora su ficha desde la caja.') }}</p>
            <x-ui.button :href="route('cards.box')" class="mt-3">{{ __('Ir a la caja') }}</x-ui.button>
        </x-ui.card>
    @elseif (! $day->chosen_card_id)
        <x-ui.card :title="__('Repaso de la mañana · grupo :n', ['n' => $group])">
            <p class="text-sm text-stone-600">{{ __('Repasa las :n fichas de este grupo y escoge la que más te apele para hoy.', ['n' => $groupCards->count()]) }}</p>
            <div class="mt-3 flex flex-wrap gap-1">
                @foreach ($groupCards as $c)<span class="rounded px-2 py-0.5 text-xs text-white" style="background: {{ $c->color() }}">{{ $c->number }}</span>@endforeach
            </div>
            <x-ui.button :href="route('cards.review')" class="mt-4">{{ $day->reviewed_at ? __('Escoger la ficha del día') : __('Empezar el repaso') }}</x-ui.button>
        </x-ui.card>
    @else
        <x-ui.card>
            <div class="text-xs uppercase tracking-wide text-stone-500">{{ __('Ficha del día') }}</div>
            <a href="{{ route('cards.day') }}" class="mt-2 block"><x-cards.card :card="$day->chosenCard" /></a>
            <div class="mt-4">
                <div class="mb-1 text-xs text-stone-500">{{ __('Repasos de hoy') }}: {{ $day->reviews_done }}/3</div>
                <div class="flex items-center gap-2">
                    @for ($i = 1; $i <= 3; $i++)<span class="h-3 w-3 rounded-full {{ $i <= $day->reviews_done ? 'bg-[color:var(--app-color)]' : 'bg-stone-200' }}"></span>@endfor
                    @if ($day->reviews_done < 3)<x-ui.button :href="route('cards.day')" variant="secondary" class="ml-auto">{{ __('Repasar') }}</x-ui.button>@endif
                </div>
            </div>
            @if ($visualization && $day->chosenCard->number !== 31)
                <p class="mt-3 rounded bg-stone-50 p-2 text-xs text-stone-600">{{ __('Recuerda la ficha 31: visualiza materializado el contenido de la ficha de hoy.') }}</p>
            @endif
        </x-ui.card>

        @if ($enrollment->tracking_enabled)
            <x-ui.card :title="__('Valoración del día')">
                <p class="text-sm text-stone-600">{{ __('¿En qué medida has tenido presente y aplicado la ficha hoy?') }}</p>
                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach ([1 => __('Apenas'), 2 => __('Bastante'), 3 => __('Mucho')] as $v => $label)
                        <button type="button" wire:click="rate({{ $v }})" class="rounded-lg border py-3 text-sm font-medium {{ $day->rating === $v ? 'border-[color:var(--app-color)] bg-blue-50 text-[color:var(--app-color)]' : 'border-stone-200' }}">{{ $v }}. {{ $label }}</button>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    @endif
</div>
