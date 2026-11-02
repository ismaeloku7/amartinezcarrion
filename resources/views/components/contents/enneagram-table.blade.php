@props(['testKey' => 'eneagrama'])
@php
    $test = \App\Models\Tests\Test::query()->where('key', $testKey)->first();
    $profiles = $test ? $test->profiles() : collect();
    $scales = $test ? $test->scales()->get()->keyBy('key') : collect();
@endphp
@if ($profiles->isNotEmpty())
<div class="my-6 overflow-x-auto rounded-xl border border-stone-200">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 text-left text-xs uppercase tracking-wide text-stone-500"><tr><th class="p-2">{{ __('Tipo') }}</th><th class="p-2">{{ __('Peligro') }}</th><th class="p-2">{{ __('Deseo') }}</th><th class="p-2">{{ __('Temor') }}</th><th class="p-2">{{ __('Queja oculta') }}</th><th class="p-2">{{ __('Botón de acción') }}</th></tr></thead>
        <tbody>
            @foreach (['2', '3', '4', '5', '6', '7', '8', '9', '1'] as $k)
                @if (isset($profiles[$k]))
                    <tr class="border-t border-stone-100 align-top">
                        <td class="whitespace-nowrap p-2 font-semibold" style="color: {{ $scales[$k]->color ?? '#444' }}">{{ $k }} · {{ $profiles[$k]->title }}</td>
                        <td class="p-2">{{ $profiles[$k]->danger }}</td><td class="p-2">{{ $profiles[$k]->basic_desire }}</td><td class="p-2">{{ $profiles[$k]->basic_fear }}</td><td class="p-2 italic">{{ $profiles[$k]->hidden_complaint }}</td><td class="p-2">{{ $profiles[$k]->growth_action }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif
