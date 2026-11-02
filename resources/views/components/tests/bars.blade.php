@props(['scores', 'max', 'scales', 'highlight' => null, 'order' => null])
@php $keys = $order ?: array_keys($scores); $top = max(1, max($scores ?: [1])); @endphp
<div class="flex items-end gap-1.5 sm:gap-3" style="height: 180px">
    @foreach ($keys as $k)
        @php $s = $scales[$k] ?? null; $h = round($scores[$k] / $top * 100); @endphp
        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
            <div class="text-xs font-semibold tabular-nums">{{ $scores[$k] }}</div>
            <div class="w-full rounded-t-md transition-all {{ $highlight === $k ? 'ring-2 ring-offset-1 ring-stone-800' : '' }}" style="height: {{ max(2, $h) }}%; background: {{ $s?->color ?? '#777' }}; opacity: {{ $highlight && $highlight !== $k ? 0.55 : 1 }}"></div>
            <div class="text-xs font-bold" style="color: {{ $s?->color ?? '#777' }}">{{ $k }}</div>
        </div>
    @endforeach
</div>
