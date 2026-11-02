@php $statusName = ['self_pending' => __('sin completar'), 'collecting' => __('recogiendo respuestas'), 'results' => __('con resultados'), 'closed' => __('cerrada')]; @endphp
<div class="space-y-5">
    <x-ui.card>
        <span class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-app/10 text-app"><x-ui.icon name="sparkles" class="h-6 w-6" /></span>
        <p class="text-[0.9375rem] leading-relaxed text-stone-700">{{ $test->intro ?? $test->description }}</p>
        @error('start')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        <div class="mt-5 space-y-2">
            @if ($open)
                <x-ui.button :href="route($open->status === 'self_pending' ? 'tests.johari.self' : 'tests.johari.results', $open)" block>{{ __('Continuar') }}</x-ui.button>
            @elseif (! $hasAccess)
                <x-ui.button :href="route('paywall', ['product' => $test->product->key])" block><x-ui.icon name="lock" class="h-5 w-5" />{{ __('Ver planes') }}</x-ui.button>
            @elseif ($canStart)
                <x-ui.button wire:click="start" block>{{ $takes->isEmpty() ? __('Empezar') : __('Repetir el test') }}</x-ui.button>
            @else
                <p class="rounded-full bg-fill py-3 text-center text-sm text-muted">{{ __('Podrás repetirlo más adelante.') }}</p>
            @endif
            @if ($takes->whereNotNull('result')->count() > 1)
                <x-ui.button :href="route('tests.johari.evolution', $test)" variant="soft" block><x-ui.icon name="chart" class="h-5 w-5" />{{ __('Ver evolución') }}</x-ui.button>
            @endif
        </div>
    </x-ui.card>

    @if ($takes->isNotEmpty())
        <section>
            <h2 class="mb-3 text-[1.0625rem] font-semibold tracking-tight">{{ __('Mis tomas') }}</h2>
            <div class="space-y-2.5">
                @foreach ($takes as $t)
                    <a href="{{ route($t->status === 'self_pending' ? 'tests.johari.self' : 'tests.johari.results', $t) }}" class="flex items-center gap-3 rounded-2xl bg-white p-3.5 shadow-soft transition active:scale-[0.99]">
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium">{{ $t->started_at->translatedFormat('j M Y') }}</span>
                            <span class="text-sm text-muted">{{ $statusName[$t->status] }}</span>
                        </span>
                        <x-ui.icon name="chevron-right" class="h-5 w-5 text-stone-300" />
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
