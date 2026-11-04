<div class="space-y-5">
    @if ($step === 'previous')
        <x-ui.card :title="__('Repaso del día anterior')">
            @if ($previous)
                <ul class="space-y-1.5 text-[0.9375rem] leading-relaxed">
                    @foreach ($previous->texts as $t)<li class="{{ $t->is_day_text ? 'font-medium text-red-900' : 'text-stone-700' }}"><x-mdp.text :text="$t" /></li>@endforeach
                </ul>
                @if ($previous->prayer_underline)<p class="mt-3 border-l-2 border-app/30 pl-3 text-sm italic text-stone-600">{{ $previous->prayer_underline }}</p>@endif
            @else
                <p class="text-sm text-muted">{{ __('No hay página anterior.') }}</p>
            @endif
        </x-ui.card>
    @endif

    @if ($step === 'words')
        <x-ui.card :title="__('Una palabra de cada texto')">
            <p class="text-sm text-muted">{{ __('Lee los textos de la semana y toca una palabra de cada uno para subrayarla en amarillo.') }}</p>
            <ol class="mt-3 space-y-3">
                @foreach ($dayTexts as $t)
                    <li class="rounded-2xl bg-fill p-3">
                        <div class="text-xs font-medium capitalize text-stone-500">{{ $t->day->date->translatedFormat('D j') }}</div>
                        <p class="text-lg leading-loose">
                            @foreach ($t->words() as $i => $w)
                                <button type="button" wire:click="underlineWord({{ $t->id }}, {{ $i }})" @class(['rounded px-0.5', 'bg-yellow-200 font-medium' => ($underlines[(string) $t->id] ?? null) === $i])>{{ $w }}</button>
                            @endforeach
                        </p>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>
    @endif

    @if ($step === 'select')
        <x-ui.card :title="__('Texto de la semana')">
            <p class="text-sm text-muted">{{ __('Elige el texto que más resuena en ti hoy.') }}</p>
            @error('select')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <ul class="mt-3 space-y-2">
                @foreach ($dayTexts as $t)
                    <li><button type="button" wire:click="selectWeekText({{ $t->id }})" class="w-full rounded-2xl p-4 text-left text-[0.9375rem] leading-relaxed transition {{ $week->week_text_id === $t->id ? 'bg-yellow-100 ring-2 ring-yellow-500' : 'bg-fill active:bg-stone-200' }}"><x-mdp.text :text="$t" :word-underline="($underlines[(string) $t->id] ?? null)" /></button></li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @if ($step === 'done')
        <x-ui.card :title="__('Semana completada')">
            @if ($week->weekText)<p class="font-serif text-[1.375rem] font-medium leading-snug text-yellow-800">{{ $week->weekText->body }}</p>@endif
            <p class="mt-2 text-sm text-stone-600">{{ __('Repásalo tres veces a lo largo del día.') }}</p>
            <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl bg-fill p-3.5">
                <div>
                    <div class="text-xs font-medium text-stone-500">{{ __('Repasos') }}</div>
                    <x-ui.dots :done="$week->reviews_done" class="mt-2" />
                </div>
                @if ($week->reviews_done < 3)<x-ui.button wire:click="markReview" size="sm"><x-ui.icon name="check" class="h-4 w-4" />{{ __('He repasado') }}</x-ui.button>@endif
            </div>
            @if ($monthToClose)
                <x-ui.button :href="route('mdp.month')" block class="mt-4">{{ __('Elegir el texto del mes') }}</x-ui.button>
            @endif
            <x-ui.button :href="route('mdp.today')" variant="soft" block class="mt-2">{{ __('Volver a hoy') }}</x-ui.button>
        </x-ui.card>
    @else
        <x-ui.action-bar>
            <x-ui.button wire:click="next" block>{{ __('Siguiente') }}<x-ui.icon name="arrow-right" class="h-4 w-4" /></x-ui.button>
        </x-ui.action-bar>
    @endif
</div>
