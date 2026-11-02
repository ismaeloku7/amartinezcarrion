@php
    // Colores de las cuatro áreas, los mismos que la ventana de resultados.
    $areas = [
        'open' => [__('Abierta'), 'bg-emerald-400'],
        'blind' => [__('Ciega'), 'bg-amber-400'],
        'hidden' => [__('Oculta'), 'bg-sky-400'],
        'unknown' => [__('Desconocida'), 'bg-stone-300'],
    ];
@endphp
<div class="space-y-5">
    @if ($takes->count() < 2)
        <x-ui.alert>{{ __('Necesitas al menos dos tomas con resultados para ver la evolución.') }}</x-ui.alert>
    @else
        <x-ui.card :title="__('Tamaño de cada área')">
            {{-- Leyenda --}}
            <div class="mb-4 grid grid-cols-2 gap-x-3 gap-y-1.5 text-xs text-stone-600">
                @foreach ($areas as [$name, $color])
                    <span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full {{ $color }}"></span>{{ $name }}</span>
                @endforeach
            </div>

            {{-- Una barra apilada por toma: cada tramo es el número de palabras de un área. --}}
            <ol class="space-y-4">
                @foreach ($series as $row)
                    @php
                        $counts = collect(array_keys($areas))->mapWithKeys(fn ($k) => [$k => (int) $row['i'][$k.'_count']]);
                        $total = max(1, $counts->sum());
                    @endphp
                    <li>
                        <div class="mb-1.5 flex items-baseline justify-between text-xs">
                            <span class="font-medium text-stone-700">{{ $row['date'] }}</span>
                            <span class="text-muted">{{ __('coincidencia :p%', ['p' => $row['i']['agreement_pct']]) }}</span>
                        </div>
                        <div class="flex h-7 w-full gap-0.5 overflow-hidden rounded-lg">
                            @foreach ($areas as $key => [$name, $color])
                                @if ($counts[$key] > 0)
                                    <div class="{{ $color }} grid min-w-5 place-items-center text-[0.6875rem] font-semibold text-white/95" style="width: {{ round($counts[$key] / $total * 100, 2) }}%" title="{{ $name }}: {{ $counts[$key] }}">{{ $counts[$key] }}</div>
                                @endif
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>

        <x-ui.card :title="__('Comparar dos tomas')">
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">
                <select wire:model.live="fromId" class="field">@foreach ($takes as $t)<option value="{{ $t->id }}">{{ $t->completed_at->format('d/m/Y') }}</option>@endforeach</select>
                <x-ui.icon name="arrow-right" class="h-4 w-4 text-muted" />
                <select wire:model.live="toId" class="field">@foreach ($takes as $t)<option value="{{ $t->id }}">{{ $t->completed_at->format('d/m/Y') }}</option>@endforeach</select>
            </div>
            @php $areaName = ['open' => __('abierta'), 'blind' => __('ciega'), 'hidden' => __('oculta'), 'unknown' => __('desconocida')]; @endphp
            <ul class="mt-4 divide-y divide-stone-100 text-sm">
                @forelse ($moves as $m)
                    <li class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 py-2.5">
                        <strong class="font-medium">{{ $labels[$m['word']] ?? $m['word'] }}</strong>
                        <span class="flex items-center gap-1.5 text-xs">
                            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full {{ $areas[$m['from']][1] }}"></span>{{ $areaName[$m['from']] }}</span>
                            <x-ui.icon name="arrow-right" class="h-3 w-3 text-muted" />
                            <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full {{ $areas[$m['to']][1] }}"></span>{{ $areaName[$m['to']] }}</span>
                        </span>
                    </li>
                @empty
                    <li class="py-2 text-muted">{{ __('Ninguna palabra ha cambiado de área entre estas dos tomas.') }}</li>
                @endforelse
            </ul>
        </x-ui.card>
    @endif
</div>
