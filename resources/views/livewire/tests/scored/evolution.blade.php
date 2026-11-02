<div class="space-y-4">
    @if ($series->count() < 2)
        <x-ui.alert>{{ __('Necesitas al menos dos tomas con resultados para ver la evolución.') }}</x-ui.alert>
    @else
        <x-ui.card :title="__('Puntuaciones por toma')">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-stone-500"><th class="py-1">{{ __('Fecha') }}</th>@foreach ($scales as $s)<th class="py-1 text-center" style="color: {{ $s->color }}">{{ $s->key }}</th>@endforeach<th>{{ __('Principal') }}</th></tr></thead>
                    <tbody>
                        @foreach ($series as $row)
                            <tr class="border-t border-stone-100"><td class="py-1">{{ $row['date']->format('d/m/Y') }}</td>@foreach ($scales as $s)<td class="py-1 text-center {{ $row['top'] === $s->key ? 'font-bold' : '' }}">{{ $row['scores'][$s->key] ?? '—' }}</td>@endforeach<td class="font-semibold">{{ $row['top'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @php $first = $series->first(); $last = $series->last(); @endphp
        <x-ui.card :title="__('De la primera a la última toma')">
            <ul class="grid gap-1 text-sm sm:grid-cols-3">
                @foreach ($scales as $s)
                    @php $d = ($last['scores'][$s->key] ?? 0) - ($first['scores'][$s->key] ?? 0); @endphp
                    <li class="flex justify-between rounded px-2 py-1" style="background: {{ $s->color }}14"><span>{{ $s->key }} · {{ $s->name }}</span><span class="{{ $d > 0 ? 'text-emerald-700' : ($d < 0 ? 'text-red-700' : 'text-stone-500') }}">{{ $d > 0 ? '+' : '' }}{{ $d }}</span></li>
                @endforeach
            </ul>
        </x-ui.card>
    @endif
</div>
