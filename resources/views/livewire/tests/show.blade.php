<div class="space-y-4">
    <x-ui.card>
        <p class="text-stone-700">{{ $test->intro ?? $test->description }}</p>
        @error('start')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        <div class="mt-4 flex flex-wrap gap-2">
            @if ($open)
                <x-ui.button :href="route($open->status === 'self_pending' ? 'tests.johari.self' : 'tests.johari.results', $open)">{{ __('Continuar') }}</x-ui.button>
            @elseif (! $hasAccess)
                <x-ui.button :href="route('paywall', ['product' => $test->product->key])">{{ __('Ver planes') }}</x-ui.button>
            @elseif ($canStart)
                <x-ui.button wire:click="start">{{ $takes->isEmpty() ? __('Empezar') : __('Repetir el test') }}</x-ui.button>
            @else
                <span class="text-sm text-stone-500">{{ __('Podrás repetirlo más adelante.') }}</span>
            @endif
            @if ($takes->whereNotNull('result')->count() > 1)
                <x-ui.button :href="route('tests.johari.evolution', $test)" variant="secondary">{{ __('Ver evolución') }}</x-ui.button>
            @endif
        </div>
    </x-ui.card>

    @if ($takes->isNotEmpty())
        <x-ui.card :title="__('Mis tomas')">
            <ul class="divide-y divide-stone-100 text-sm">
                @foreach ($takes as $t)
                    <li class="flex items-center justify-between py-2">
                        <span>{{ $t->started_at->translatedFormat('j M Y') }} · {{ ['self_pending' => __('sin completar'), 'collecting' => __('recogiendo respuestas'), 'results' => __('con resultados'), 'closed' => __('cerrada')][$t->status] }}</span>
                        <a class="underline" href="{{ route($t->status === 'self_pending' ? 'tests.johari.self' : 'tests.johari.results', $t) }}">{{ __('Ver') }}</a>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
</div>
