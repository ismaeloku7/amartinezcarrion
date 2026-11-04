<div class="space-y-5">
    <div class="flex items-center gap-2 text-xs">
        {{-- Day navigation: any day since the start can be opened and done as if it were today. --}}
        <a @if ($prevDate) href="{{ route('mdp.day', ['date' => $prevDate]) }}" @endif class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white shadow-soft {{ $prevDate ? 'text-stone-700 active:scale-95' : 'pointer-events-none text-stone-300' }}" aria-label="{{ __('Día anterior') }}"><x-ui.icon name="chevron-left" class="h-5 w-5" /></a>
        <div class="min-w-0 flex-1 text-center">
            <div class="truncate text-sm font-medium text-stone-700 first-letter:uppercase">{{ $today->translatedFormat('l, j \d\e F') }}</div>
            @unless ($isToday)<a href="{{ route('mdp.today') }}" class="text-xs font-medium text-app">{{ __('Volver a hoy') }}</a>@endunless
        </div>
        <a @if ($nextDate) href="{{ route('mdp.day', ['date' => $nextDate]) }}" @endif class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white shadow-soft {{ $nextDate ? 'text-stone-700 active:scale-95' : 'pointer-events-none text-stone-300' }}" aria-label="{{ __('Día siguiente') }}"><x-ui.icon name="chevron-right" class="h-5 w-5" /></a>
    </div>
    <div class="flex flex-wrap items-center gap-2 text-xs">
        <span class="rounded-full bg-app/10 px-2.5 py-1 font-medium text-app">{{ __('Fase :n', ['n' => $phase]) }}</span>
        <span class="rounded-full bg-white px-2.5 py-1 text-stone-600 shadow-soft">{{ $enrollment->isFull() ? __('completo') : __('ligero') }} · {{ __(':n días', ['n' => $daysDone]) }}</span>
    </div>

    @if ($pendingMonth)
        <x-ui.alert type="warning" class="flex items-center justify-between gap-3">
            <span>{{ __('Hoy se cierra el mes: después del texto de la semana, elige el texto del mes.') }}</span>
            <x-ui.button :href="route('mdp.month')" variant="secondary" size="sm">{{ __('Elegir') }}</x-ui.button>
        </x-ui.alert>
    @endif

    @if ($isRest)
        <div class="rounded-3xl bg-app p-6 text-white shadow-float">
            <x-ui.icon name="sun" class="h-8 w-8 text-white/80" />
            <h2 class="mt-4 text-xl font-semibold">{{ $isToday ? __('Hoy es día de descanso') : __('Día de descanso') }}</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-white/80">{{ __('Hoy no hay devocional normal. Repasa el día anterior, subraya una palabra de cada texto de la semana y elige el texto de la semana.') }}</p>
            @if ($isToday)<a href="{{ route('mdp.rest') }}" class="mt-5 flex h-12 items-center justify-center rounded-full bg-white font-semibold text-app active:scale-[0.98]">{{ $week->rest_day_done_at ? __('Ver la semana') : __('Empezar') }}</a>@endif
        </div>
    @elseif (! $day->isCompleted())
        <div class="rounded-3xl bg-app p-6 text-white shadow-float">
            <x-ui.icon name="book" class="h-8 w-8 text-white/80" />
            <h2 class="mt-4 text-xl font-semibold">{{ $isToday ? __('Devocional de hoy') : __('Devocional del :d', ['d' => $today->translatedFormat('j \d\e F')]) }}</h2>
            <p class="mt-1.5 text-sm leading-relaxed text-white/80">{{ __('Oración, lectura de 15 a 20 minutos, anotación de los textos que resuenan contigo y elección del texto del día.') }}</p>
            <a href="{{ route('mdp.devotional', ['date' => $today->toDateString()]) }}" class="mt-5 flex h-12 items-center justify-center gap-2 rounded-full bg-white font-semibold text-app active:scale-[0.98]"><x-ui.icon name="play" class="h-4 w-4" />{{ $day->started_at ? __('Continuar') : __('Empezar') }}</a>
        </div>
    @else
        <x-ui.card :eyebrow="($day->reading_from || $day->reading_to) ? __('Texto del día') . ' · ' . trim($day->reading_from . ' – ' . $day->reading_to, ' –') : __('Texto del día')">
            @if ($dayText)
                <p class="font-serif text-[1.375rem] font-medium leading-snug text-red-900">{{ $dayText->body }}</p>
            @endif
            <div class="mt-5 rounded-2xl bg-fill p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-medium text-stone-500">{{ $isToday ? __('Repasos de hoy') : __('Repasos') }}</div>
                        <x-ui.dots :done="$day->reviews_done" class="mt-2" />
                    </div>
                    @if ($day->reviews_done < 3)
                        <x-ui.button wire:click="markReview" size="sm"><x-ui.icon name="check" class="h-4 w-4" />{{ __('He repasado') }}</x-ui.button>
                    @endif
                </div>
                <p class="mt-3 text-xs leading-relaxed text-stone-500">{{ __('Léelo de nuevo, mejor en voz baja, y recupera la sensación que te produjo al elegirlo.') }}</p>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <x-ui.button :href="route('mdp.devotional', ['date' => $today->toDateString()])" variant="soft" size="sm">{{ __('Ver más') }}</x-ui.button>
                <x-ui.button :href="route('mdp.week')" variant="soft" size="sm">{{ __('Ficha de la semana') }}</x-ui.button>
            </div>
        </x-ui.card>
    @endif

    @if ($previous && ! $isRest)
        <x-ui.card>
            <div class="flex items-center justify-between gap-2">
                <div class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Repaso del día anterior') }} · {{ $previous->date->translatedFormat('j M') }}</div>
                @if ($day->previous_reviewed)<span class="flex items-center gap-1 text-xs font-medium text-emerald-700"><x-ui.icon name="check" class="h-4 w-4" />{{ __('Hecho') }}</span>@endif
            </div>
            <ul class="mt-3 space-y-2 text-[0.9375rem] leading-relaxed">
                @foreach ($previous->texts as $t)
                    <li class="{{ $t->is_day_text ? 'font-medium text-red-900' : 'text-stone-700' }}">
                        <x-mdp.text :text="$t" />
                    </li>
                @endforeach
            </ul>
            @if ($previous->prayer_underline)
                <p class="mt-3 border-l-2 border-app/30 pl-3 text-sm italic text-stone-600">{{ $previous->prayer_underline }}</p>
            @endif
            @unless ($day->previous_reviewed)
                <x-ui.button wire:click="markPreviousReviewed" variant="soft" block size="sm" class="mt-4">{{ __('Repasado') }}</x-ui.button>
            @endunless
        </x-ui.card>
    @endif
</div>
