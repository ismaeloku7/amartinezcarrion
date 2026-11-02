<div class="space-y-4">
    @unless ($isRest)
        <x-ui.alert type="warning">{{ __('Hoy no es tu día de descanso, pero puedes completar aquí la semana si no lo hiciste.') }}</x-ui.alert>
    @endunless

    @if ($step === 'previous')
        <x-ui.card :title="__('Repaso del día anterior')">
            @if ($previous)
                <ul class="space-y-1 text-sm">
                    @foreach ($previous->texts as $t)<li class="{{ $t->is_day_text ? 'font-medium text-red-800' : '' }}"><x-mdp.text :text="$t" /></li>@endforeach
                </ul>
                @if ($previous->prayer_underline)<p class="mt-2 border-l-2 pl-2 text-sm italic text-stone-600">{{ $previous->prayer_underline }}</p>@endif
            @else
                <p class="text-sm text-stone-500">{{ __('No hay página anterior.') }}</p>
            @endif
        </x-ui.card>
    @endif

    @if ($step === 'words')
        <x-ui.card :title="__('Una palabra de cada texto')">
            <p class="text-sm text-stone-600">{{ __('Lee los textos de la semana y toca una palabra de cada uno para subrayarla en amarillo.') }}</p>
            <ol class="mt-3 space-y-3">
                @foreach ($dayTexts as $t)
                    <li class="rounded-lg border border-stone-200 p-2">
                        <div class="text-xs text-stone-500">{{ $t->day->date->translatedFormat('D j') }}</div>
                        <p class="leading-relaxed">
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
            <p class="text-sm text-stone-600">{{ __('Elige el texto que más resuena en ti hoy.') }}</p>
            @error('select')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            <ul class="mt-3 space-y-2">
                @foreach ($dayTexts as $t)
                    <li><button type="button" wire:click="selectWeekText({{ $t->id }})" class="w-full rounded-lg border p-3 text-left {{ $week->week_text_id === $t->id ? 'border-yellow-500 bg-yellow-100' : 'border-stone-200' }}"><x-mdp.text :text="$t" :word-underline="($underlines[(string) $t->id] ?? null)" /></button></li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif

    @if ($step === 'done')
        <x-ui.card :title="__('Semana completada')">
            @if ($week->weekText)<p class="text-xl font-medium text-yellow-800">{{ $week->weekText->body }}</p>@endif
            <p class="mt-2 text-sm text-stone-600">{{ __('Repásalo tres veces a lo largo del día.') }}</p>
            <div class="mt-3 flex items-center gap-2 text-sm">
                <span class="text-stone-500">{{ __('Repasos') }}: {{ $week->reviews_done }}/3</span>
                @if ($week->reviews_done < 3)<x-ui.button wire:click="markReview" variant="secondary">{{ __('He repasado') }}</x-ui.button>@endif
            </div>
            <x-ui.button :href="route('mdp.today')" class="mt-4">{{ __('Volver a hoy') }}</x-ui.button>
        </x-ui.card>
    @else
        <div class="flex justify-end"><x-ui.button wire:click="next">{{ __('Siguiente') }}</x-ui.button></div>
    @endif
</div>
