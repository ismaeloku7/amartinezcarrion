@props(['testKey' => 'eneagrama'])
@php
    $test = \App\Models\Tests\Test::query()->where('key', $testKey)->first();
    $profiles = $test ? $test->profiles() : collect();
    $scales = $test ? $test->scales()->get()->keyBy('key') : collect();
    $order = ['2', '3', '4', '5', '6', '7', '8', '9', '1'];
@endphp
@if ($profiles->isNotEmpty())
<div class="my-6 space-y-3" x-data="{ open: '{{ $order[0] }}' }">
    <div class="flex flex-wrap gap-1.5">
        @foreach ($order as $k)
            @if (isset($profiles[$k]))
                <button type="button" x-on:click="open = '{{ $k }}'" :class="open === '{{ $k }}' ? 'text-white' : 'bg-white'" class="rounded-full border px-3 py-1 text-sm font-medium transition" style="border-color: {{ $scales[$k]->color ?? '#444' }}; color: {{ $scales[$k]->color ?? '#444' }}" :style="open === '{{ $k }}' ? 'background: {{ $scales[$k]->color ?? '#444' }}; color: white' : ''">{{ $k }} · {{ $profiles[$k]->title }}</button>
            @endif
        @endforeach
    </div>
    @foreach ($order as $k)
        @if (isset($profiles[$k]))
            <div x-show="open === '{{ $k }}'" x-cloak>
                <x-tests.profile :profile="$profiles[$k]" :scale="$scales[$k] ?? null" />
            </div>
        @endif
    @endforeach
</div>
@endif
