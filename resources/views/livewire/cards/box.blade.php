<div class="space-y-4">
    <x-ui.card class="flex items-center justify-between gap-3">
        <div>
            <div class="font-medium">{{ __('Incorporadas: :n de 42', ['n' => $unlocked]) }}</div>
            <p class="text-xs text-stone-600">{{ __('Al terminar cada apartado del libro, incorpora su ficha. La velocidad de lectura no importa; los repasos diarios sí.') }}</p>
        </div>
        @if ($unlocked < 42)
            <x-ui.button wire:click="unlockNext">{{ __('He leído el apartado :n', ['n' => $unlocked + 1]) }}</x-ui.button>
        @endif
    </x-ui.card>

    @foreach ($groups as $g => $cards)
        <section>
            <h2 class="mb-2 text-sm font-semibold" style="color: {{ \App\Models\Cards\Card::GROUP_COLORS[$g] }}">{{ __('Grupo :n', ['n' => $g]) }} · {{ __('fichas :a-:b', ['a' => ($g - 1) * 7 + 1, 'b' => $g * 7]) }}</h2>
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                @foreach ($cards as $c)
                    @if ($c->number <= $unlocked)
                        <a href="{{ route('cards.show', $c->number) }}" class="rounded-lg border-2 bg-white p-2 text-xs hover:shadow" style="border-color: {{ $c->color() }}">
                            <div class="font-semibold" style="color: {{ $c->color() }}">{{ $c->number }}</div>
                            <div class="line-clamp-2 leading-tight">{{ $c->title }}</div>
                        </a>
                    @else
                        <div class="rounded-lg border-2 border-dashed border-stone-200 p-2 text-xs text-stone-400"><div class="font-semibold">{{ $c->number }}</div><div>{{ __('Pendiente') }}</div></div>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach

    <x-ui.card :title="__('Cuando te haga falta')">
        <p class="text-xs text-stone-600">{{ __('Con el tiempo, varias fichas vendrán solas a la mente según la situación.') }}</p>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($situations as $label => $numbers)
                <li><span class="text-stone-700">{{ $label }}:</span> @foreach ($numbers as $n)<a href="{{ $n <= $unlocked ? route('cards.show', $n) : '#' }}" class="ml-1 rounded px-1.5 py-0.5 text-xs text-white {{ $n <= $unlocked ? '' : 'opacity-40' }}" style="background: {{ \App\Models\Cards\Card::GROUP_COLORS[\App\Models\Cards\Card::groupOf($n)] }}">{{ $n }}</a>@endforeach</li>
            @endforeach
        </ul>
    </x-ui.card>
</div>
