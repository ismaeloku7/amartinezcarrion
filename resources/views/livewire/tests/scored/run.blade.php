<div class="space-y-4" x-data x-on:scroll-top.window="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <div>
        <div class="mb-1 flex justify-between text-xs text-stone-500"><span>{{ __('Página :a de :b', ['a' => $page + 1, 'b' => $pages]) }}</span><span>{{ $answered }} / {{ $total }}</span></div>
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-stone-200"><div class="h-full bg-[color:var(--app-color)] transition-all" style="width: {{ $total ? round($answered / $total * 100) : 0 }}%"></div></div>
    </div>

    @if ($page === 0)
        <x-ui.alert>{{ __('Responde según cómo eres habitualmente, no según cómo te gustaría ser ni según cómo actúas en un marco concreto. Puedes dejar en blanco lo que no entiendas y cambiar cualquier respuesta tocándola de nuevo.') }}
            @if ($considerations)<a href="{{ route('contents.show', $considerations) }}" class="underline" target="_blank">{{ __('Leer las consideraciones') }}</a>@endif</x-ui.alert>
    @endif

    <ol class="space-y-3">
        @foreach ($items as $item)
            @php $a = $answers[(string) $item->id] ?? null; @endphp
            <li class="rounded-xl border bg-white p-3 shadow-sm {{ $a ? 'border-stone-200' : 'border-stone-300' }}" wire:key="item-{{ $item->id }}">
                <p class="text-[15px] leading-snug">{{ $item->statement }}</p>
                <div class="mt-2 grid grid-cols-4 gap-1.5 text-xs">
                    @foreach ([['yes', __('Sí'), 'border-emerald-600 bg-emerald-600 text-white', 'hover:bg-emerald-50'], ['strong_yes', __('Sí, totalmente'), 'border-emerald-800 bg-emerald-800 text-white', 'hover:bg-emerald-50'], ['no', __('No'), 'border-stone-700 bg-stone-700 text-white', 'hover:bg-stone-100'], ['unknown', '?', 'border-amber-500 bg-amber-500 text-white', 'hover:bg-amber-50']] as [$value, $label, $on, $off])
                        <button type="button" wire:click="answer({{ $item->id }}, '{{ $value }}')" class="rounded-lg border px-1 py-2 font-medium transition {{ $a === $value ? $on : 'border-stone-200 bg-white text-stone-700 '.$off }}">{{ $label }}</button>
                    @endforeach
                </div>
            </li>
        @endforeach
    </ol>

    <div class="flex items-center justify-between">
        <x-ui.button wire:click="previous" variant="ghost" :disabled="$page === 0">{{ __('Anterior') }}</x-ui.button>
        @if ($page + 1 < $pages)
            <x-ui.button wire:click="next">{{ __('Siguiente página') }}</x-ui.button>
        @else
            <x-ui.button wire:click="finish" wire:confirm="{{ $answered < $total ? __('Hay :n enunciados sin responder. ¿Terminar igualmente?', ['n' => $total - $answered]) : __('¿Terminar y ver los resultados?') }}">{{ __('Ver resultados') }}</x-ui.button>
        @endif
    </div>
    @if ($pages > 1)
        <div class="flex flex-wrap justify-center gap-1">
            @for ($p = 0; $p < $pages; $p++)
                <button type="button" wire:click="go({{ $p }})" class="h-7 w-7 rounded-full text-xs {{ $p === $page ? 'bg-[color:var(--app-color)] text-white' : 'bg-stone-200 text-stone-600' }}">{{ $p + 1 }}</button>
            @endfor
        </div>
    @endif
</div>
