<div class="space-y-4">
    @if ($takes->count() < 2)
        <x-ui.alert>{{ __('Necesitas al menos dos tomas con resultados para ver la evolución.') }}</x-ui.alert>
    @else
        <x-ui.card :title="__('Tamaño de cada área')">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-stone-500"><th>{{ __('Fecha') }}</th><th>{{ __('Abierta') }}</th><th>{{ __('Ciega') }}</th><th>{{ __('Oculta') }}</th><th>{{ __('Desconocida') }}</th><th>{{ __('Coincidencia') }}</th></tr></thead>
                <tbody>
                    @foreach ($series as $row)
                        <tr class="border-t border-stone-100"><td class="py-1">{{ $row['date'] }}</td><td>{{ $row['i']['open_count'] }}</td><td>{{ $row['i']['blind_count'] }}</td><td>{{ $row['i']['hidden_count'] }}</td><td>{{ $row['i']['unknown_count'] }}</td><td>{{ $row['i']['agreement_pct'] }}%</td></tr>
                    @endforeach
                </tbody>
            </table>
        </x-ui.card>

        <x-ui.card :title="__('Comparar dos tomas')">
            <div class="grid grid-cols-2 gap-2">
                <select wire:model.live="fromId" class="rounded-lg border border-stone-300 bg-white px-2 py-1 text-sm">@foreach ($takes as $t)<option value="{{ $t->id }}">{{ $t->completed_at->format('d/m/Y') }}</option>@endforeach</select>
                <select wire:model.live="toId" class="rounded-lg border border-stone-300 bg-white px-2 py-1 text-sm">@foreach ($takes as $t)<option value="{{ $t->id }}">{{ $t->completed_at->format('d/m/Y') }}</option>@endforeach</select>
            </div>
            @php $areaName = ['open' => __('abierta'), 'blind' => __('ciega'), 'hidden' => __('oculta'), 'unknown' => __('desconocida')]; @endphp
            <ul class="mt-3 space-y-1 text-sm">
                @forelse ($moves as $m)
                    <li><strong>{{ $labels[$m['word']] ?? $m['word'] }}</strong>: {{ $areaName[$m['from']] }} → {{ $areaName[$m['to']] }}</li>
                @empty
                    <li class="text-stone-500">{{ __('Ninguna palabra ha cambiado de área entre estas dos tomas.') }}</li>
                @endforelse
            </ul>
        </x-ui.card>
    @endif
</div>
