@php
    $reviewsPct = $reviewsPossible ? round($reviewsTotal / $reviewsPossible * 100) : 0;
    $maxChosen = max(1, (int) ($mostChosen->max('n') ?? 1));
@endphp
<div class="space-y-5">
    {{-- Anillo hacia los seis meses --}}
    <x-ui.card class="flex items-center gap-5">
        <div class="relative grid h-28 w-28 shrink-0 place-items-center">
            <svg class="absolute inset-0 -rotate-90" viewBox="0 0 100 100" aria-hidden="true">
                <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" class="stroke-stone-100" />
                <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" stroke-linecap="round" class="stroke-app" pathLength="100" stroke-dasharray="100" stroke-dashoffset="{{ 100 - min(100, $sixMonthsPct) }}" />
            </svg>
            <span class="text-2xl font-bold">{{ $sixMonthsPct }}%</span>
        </div>
        <div>
            <h2 class="font-semibold">{{ __('Hacia los seis meses') }}</h2>
            <p class="mt-1 text-sm leading-relaxed text-muted">{{ __('Para que el crecimiento sea consistente se necesita un mínimo de seis meses.') }}</p>
            <p class="mt-2 text-xs font-medium text-stone-600">{{ __(':n días desde el inicio', ['n' => $daysSinceStart]) }}</p>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-3xl bg-app p-4 text-white shadow-float">
            <div class="text-3xl font-bold">{{ $activeDays }}</div>
            <div class="mt-0.5 text-xs text-white/75">{{ __('días con ficha') }}</div>
        </div>
        <div class="rounded-3xl bg-white p-4 shadow-soft">
            <div class="text-3xl font-bold">{{ $enrollment->unlocked_until }}</div>
            <div class="mt-0.5 text-xs text-muted">{{ __('fichas incorporadas') }}</div>
        </div>
        <div class="rounded-3xl bg-white p-4 shadow-soft">
            <div class="text-3xl font-bold">{{ $reviewsPct }}%</div>
            <div class="mt-0.5 text-xs text-muted">{{ __('repasos hechos') }}</div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-fill"><div class="h-full rounded-full bg-app" style="width: {{ $reviewsPct }}%"></div></div>
        </div>
        <div class="rounded-3xl bg-white p-4 shadow-soft">
            <div class="text-3xl font-bold">{{ $avgRating ? number_format($avgRating, 1) : '—' }}</div>
            <div class="mt-0.5 text-xs text-muted">{{ __('valoración media') }}</div>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-fill"><div class="h-full rounded-full bg-app" style="width: {{ $avgRating ? round($avgRating / 3 * 100) : 0 }}%"></div></div>
        </div>
    </div>

    @if ($mostChosen->isNotEmpty())
        <x-ui.card :title="__('Fichas más escogidas')">
            <ul class="space-y-3">
                @foreach ($mostChosen as $row)
                    <li>
                        <div class="flex items-baseline justify-between gap-3 text-sm">
                            <span class="min-w-0 truncate"><span class="font-semibold" style="color: {{ $row['card']->color() }}">{{ $row['card']->number }}</span> {{ $row['card']->title }}</span>
                            <span class="shrink-0 text-xs font-medium text-muted">{{ $row['n'] }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-fill"><div class="h-full rounded-full" style="width: {{ round($row['n'] / $maxChosen * 100) }}%; background: {{ $row['card']->color() }}"></div></div>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
</div>
