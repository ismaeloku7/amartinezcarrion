@php $statusName = ['self_pending' => __('sin completar'), 'collecting' => __('recogiendo respuestas'), 'results' => __('con resultados'), 'closed' => __('cerrada')]; @endphp
<div class="space-y-5">
    <x-ui.card>
        <span class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-app/10 text-app"><x-ui.icon name="sparkles" class="h-6 w-6" /></span>
        <p class="text-[0.9375rem] leading-relaxed text-stone-700">{{ $test->intro ?? $test->description }}</p>
        @if ($isScored)<p class="mt-2 text-xs text-muted">{{ __(':n enunciados · unos :m minutos', ['n' => $itemCount, 'm' => max(5, (int) ceil($itemCount / 8))]) }}</p>@endif
        @if ($related)
            <a href="{{ route('contents.show', $related) }}" class="mt-4 flex items-center gap-3 rounded-2xl p-3 text-white transition active:scale-[0.99]" style="background: {{ $related->color() }}">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/15"><x-ui.icon name="book" class="h-5 w-5" /></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-[11px] font-semibold uppercase tracking-wider text-white/75">{{ __('Conviene leerlo antes') }}</span>
                    <span class="block font-medium leading-snug">{{ $related->title }}</span>
                </span>
                <x-ui.icon name="chevron-right" class="h-5 w-5 text-white/70" />
            </a>
        @endif
        @error('start')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        <div class="mt-5 space-y-2">
            @if ($open)
                <x-ui.button :href="route(\App\Livewire\Tests\Show::routeFor($open), $open)" block>{{ __('Continuar') }}</x-ui.button>
            @elseif (! $hasAccess)
                <x-ui.button :href="route('paywall', ['product' => $test->product->key])" block><x-ui.icon name="lock" class="h-5 w-5" />{{ __('Ver planes') }}</x-ui.button>
            @elseif ($canStart)
                @if ($considerations)
                    <x-ui.button type="button" x-on:click="$dispatch('open-considerations')" block>{{ $takes->isEmpty() ? __('Empezar') : __('Repetir el test') }}</x-ui.button>
                @else
                    <x-ui.button wire:click="start" block>{{ $takes->isEmpty() ? __('Empezar') : __('Repetir el test') }}</x-ui.button>
                @endif
            @else
                <p class="rounded-full bg-fill py-3 text-center text-sm text-muted">{{ __('Podrás repetirlo más adelante.') }}</p>
            @endif
            @if ($takes->whereNotNull('result')->count() > 1)
                <x-ui.button :href="route($isScored ? 'tests.scored.evolution' : 'tests.johari.evolution', $test)" variant="soft" block><x-ui.icon name="chart" class="h-5 w-5" />{{ __('Ver evolución') }}</x-ui.button>
            @endif
        </div>
    </x-ui.card>

    @if ($takes->isNotEmpty())
        <section>
            <h2 class="mb-3 text-[1.0625rem] font-semibold tracking-tight">{{ __('Mis tomas') }}</h2>
            <div class="space-y-2.5">
                @foreach ($takes as $t)
                    <a href="{{ route(\App\Livewire\Tests\Show::routeFor($t), $t) }}" class="flex items-center gap-3 rounded-2xl bg-white p-3.5 shadow-soft transition active:scale-[0.99]">
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium">{{ $t->started_at->translatedFormat('j M Y') }}</span>
                            <span class="text-sm text-muted">{{ $isScored ? ($t->result ? __('tipo :n', ['n' => $t->result['indicators']['top_type'] ?? '—']) : __('sin completar')) : $statusName[$t->status] }}</span>
                        </span>
                        <x-ui.icon name="chevron-right" class="h-5 w-5 text-stone-300" />
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($considerations)
        <div x-data="{ open: false }" x-on:open-considerations.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-stone-900/50 p-0 sm:items-center sm:p-4" x-on:keydown.escape.window="open = false">
            <div x-on:click.outside="open = false" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-t-3xl bg-white p-5 shadow-float sm:rounded-3xl">
                <div class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Antes de empezar') }}</div>
                <h2 class="mt-1 text-lg font-semibold">{{ $considerations->title }}</h2>
                <div class="article mt-3 text-[0.9375rem]" style="--article-color: var(--app-color)">{!! $considerationsHtml !!}</div>
                <div class="mt-5 flex flex-col gap-2">
                    <x-ui.button wire:click="start" block>{{ __('Entendido, empezar') }}</x-ui.button>
                    <x-ui.button type="button" variant="ghost" x-on:click="open = false" block>{{ __('Ahora no') }}</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
