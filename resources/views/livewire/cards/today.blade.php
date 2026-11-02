<div class="space-y-5">
    <div class="flex items-center gap-2 text-xs">
        <span class="text-sm font-medium text-stone-600 first-letter:uppercase">{{ $today->translatedFormat('l, j \d\e F') }}</span>
        <span class="ml-auto rounded-full bg-app/10 px-2.5 py-1 font-medium text-app">{{ __(':n de 42 fichas', ['n' => $enrollment->unlocked_until]) }}</span>
    </div>

    @if ($trackingPrompt)
        <x-ui.alert type="info">
            <p>{{ __('Ha pasado el primer mes. La valoración nocturna ya no es obligatoria. ¿Quieres seguir haciéndola?') }}</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <x-ui.button wire:click="keepTracking(true)" variant="secondary" size="sm">{{ __('Sí, seguir') }}</x-ui.button>
                <x-ui.button wire:click="keepTracking(false)" variant="ghost" size="sm">{{ __('No, parar') }}</x-ui.button>
            </div>
        </x-ui.alert>
    @endif

    @if ($day->is_rest_day)
        <div class="rounded-3xl bg-app p-6 text-white shadow-float">
            <x-ui.icon name="sun" class="h-8 w-8 text-white/80" />
            <h2 class="mt-4 text-xl font-semibold">{{ __('Hoy es día de descanso') }}</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-white/80">{{ __('Hoy no hay repasos ni elección de ficha. Mañana se retoma la rueda de grupos.') }}</p>
        </div>
    @elseif ($enrollment->unlocked_until === 0)
        <div class="rounded-3xl bg-app p-6 text-white shadow-float">
            <x-ui.icon name="box" class="h-8 w-8 text-white/80" />
            <h2 class="mt-4 text-xl font-semibold">{{ __('Incorpora tu primera ficha') }}</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-white/80">{{ __('Cuando termines de leer el primer apartado del libro, incorpora su ficha desde la caja.') }}</p>
            <a href="{{ route('cards.box') }}" class="mt-5 flex h-12 items-center justify-center rounded-full bg-white font-semibold text-app active:scale-[0.98]">{{ __('Ir a la caja') }}</a>
        </div>
    @elseif (! $day->chosen_card_id)
        <div class="rounded-3xl bg-app p-6 text-white shadow-float">
            <div class="text-xs font-semibold uppercase tracking-wider text-white/70">{{ __('Repaso de la mañana · grupo :n', ['n' => $group]) }}</div>
            <p class="mt-2 text-[0.9375rem] leading-relaxed text-white/90">{{ __('Repasa las :n fichas de este grupo y escoge la que más te apele para hoy.', ['n' => $groupCards->count()]) }}</p>
            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach ($groupCards as $c)<span class="grid h-8 min-w-8 place-items-center rounded-lg bg-white/15 px-1.5 text-xs font-semibold">{{ $c->number }}</span>@endforeach
            </div>
            <a href="{{ route('cards.review') }}" class="mt-5 flex h-12 items-center justify-center gap-2 rounded-full bg-white font-semibold text-app active:scale-[0.98]"><x-ui.icon name="play" class="h-4 w-4" />{{ $day->reviewed_at ? __('Escoger la ficha del día') : __('Empezar el repaso') }}</a>
        </div>
    @else
        <x-ui.card :eyebrow="__('Ficha del día')">
            <a href="{{ route('cards.day') }}" class="mt-2 block active:scale-[0.99]"><x-cards.card :card="$day->chosenCard" /></a>
            <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl bg-fill p-3.5">
                <div>
                    <div class="text-xs font-medium text-stone-500">{{ __('Repasos de hoy') }}</div>
                    <x-ui.dots :done="$day->reviews_done" class="mt-2" />
                </div>
                @if ($day->reviews_done < 3)<x-ui.button :href="route('cards.day')" size="sm">{{ __('Repasar') }}</x-ui.button>@endif
            </div>
            @if ($visualization && $day->chosenCard->number !== 31)
                <p class="mt-3 rounded-2xl bg-app/5 p-3.5 text-xs leading-relaxed text-stone-600">{{ __('Recuerda la ficha 31: visualiza materializado el contenido de la ficha de hoy.') }}</p>
            @endif
        </x-ui.card>

        @if ($enrollment->tracking_enabled)
            <x-ui.card :title="__('Valoración del día')">
                <p class="text-sm text-muted">{{ __('¿En qué medida has tenido presente y aplicado la ficha hoy?') }}</p>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    @foreach ([1 => __('Apenas'), 2 => __('Bastante'), 3 => __('Mucho')] as $v => $label)
                        <button type="button" wire:click="rate({{ $v }})" class="flex flex-col items-center gap-1 rounded-2xl py-3.5 text-sm font-medium transition active:scale-95 {{ $day->rating === $v ? 'bg-app text-white shadow-float' : 'bg-fill text-stone-700' }}">
                            <span class="text-xl font-bold">{{ $v }}</span>{{ $label }}
                        </button>
                    @endforeach
                </div>
            </x-ui.card>
        @endif
    @endif
</div>
