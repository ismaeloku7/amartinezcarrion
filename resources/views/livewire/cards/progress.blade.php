<div class="space-y-4">
    <x-ui.card :title="__('Hacia los seis meses')">
        <p class="text-sm text-stone-600">{{ __('Para que el crecimiento sea consistente se necesita un mínimo de seis meses.') }}</p>
        <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-stone-200"><div class="h-full bg-[color:var(--app-color)]" style="width: {{ $sixMonthsPct }}%"></div></div>
        <div class="mt-1 text-xs text-stone-500">{{ __(':n días desde el inicio', ['n' => $daysSinceStart]) }} · {{ $sixMonthsPct }}%</div>
    </x-ui.card>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-ui.card><div class="text-2xl font-semibold">{{ $activeDays }}</div><div class="text-xs text-stone-500">{{ __('días con ficha') }}</div></x-ui.card>
        <x-ui.card><div class="text-2xl font-semibold">{{ $enrollment->unlocked_until }}</div><div class="text-xs text-stone-500">{{ __('fichas incorporadas') }}</div></x-ui.card>
        <x-ui.card><div class="text-2xl font-semibold">{{ $reviewsPossible ? round($reviewsTotal / $reviewsPossible * 100) : 0 }}%</div><div class="text-xs text-stone-500">{{ __('repasos hechos') }}</div></x-ui.card>
        <x-ui.card><div class="text-2xl font-semibold">{{ $avgRating ? number_format($avgRating, 1) : '—' }}</div><div class="text-xs text-stone-500">{{ __('valoración media') }}</div></x-ui.card>
    </div>
    @if ($mostChosen->isNotEmpty())
        <x-ui.card :title="__('Fichas más escogidas')">
            <ul class="space-y-1 text-sm">
                @foreach ($mostChosen as $row)<li class="flex justify-between"><span>{{ $row['card']->number }}. {{ $row['card']->title }}</span><span class="text-stone-500">{{ $row['n'] }}</span></li>@endforeach
            </ul>
        </x-ui.card>
    @endif
</div>
