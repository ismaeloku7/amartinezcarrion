@props(['profile', 'scale', 'zone' => 'healthy', 'compact' => false])
@php $color = $scale?->color ?? '#444'; @endphp
<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border bg-white shadow-sm']) }} style="border-color: {{ $color }}">
    <header class="px-4 py-3 text-white" style="background: {{ $color }}">
        <div class="text-xs uppercase tracking-wide opacity-80">{{ __('Tipo :n', ['n' => $profile->scale_key]) }}</div>
        <div class="text-lg font-semibold">{{ $profile->title }}</div>
        @if ($profile->subtitle)<div class="text-sm opacity-90">{{ $profile->subtitle }}</div>@endif
    </header>
    <div class="grid gap-x-4 gap-y-2 px-4 py-3 text-sm sm:grid-cols-2">
        @foreach (['character' => __('Carácter'), 'danger' => __('Peligro'), 'basic_desire' => __('Deseo básico'), 'basic_fear' => __('Temor básico'), 'hidden_complaint' => __('Queja oculta'), 'childhood_origin' => __('Origen infantil')] as $f => $label)
            @if ($profile->{$f})<div><span class="text-xs uppercase tracking-wide text-stone-500">{{ $label }}</span><div>{{ $profile->{$f} }}</div></div>@endif
        @endforeach
    </div>
    <div x-data="{ zone: '{{ $zone }}' }" class="border-t border-stone-100">
        <div class="flex text-xs font-medium">
            <button type="button" x-on:click="zone = 'healthy'" :class="zone === 'healthy' ? 'border-emerald-600 text-emerald-800' : 'border-transparent text-stone-500'" class="flex-1 border-b-2 py-2">{{ __('Zona sana') }}</button>
            <button type="button" x-on:click="zone = 'shadow'" :class="zone === 'shadow' ? 'border-fuchsia-600 text-fuchsia-800' : 'border-transparent text-stone-500'" class="flex-1 border-b-2 py-2">{{ __('Zona de sombra') }}</button>
            <button type="button" x-on:click="zone = 'unhealthy'" :class="zone === 'unhealthy' ? 'border-orange-600 text-orange-800' : 'border-transparent text-stone-500'" class="flex-1 border-b-2 py-2">{{ __('Zona malsana') }}</button>
        </div>
        <ul x-show="zone === 'healthy'" class="grid gap-1 bg-emerald-50 px-4 py-3 text-sm sm:grid-cols-2">@foreach ($profile->lines('healthy') as $l)<li class="flex gap-2"><span class="text-emerald-600">●</span>{{ $l }}</li>@endforeach</ul>
        <ul x-show="zone === 'shadow'" x-cloak class="grid gap-1 bg-fuchsia-50 px-4 py-3 text-sm sm:grid-cols-2">@foreach ($profile->lines('shadow') as $l)<li class="flex gap-2"><span class="text-fuchsia-600">●</span>{{ $l }}</li>@endforeach</ul>
        <ul x-show="zone === 'unhealthy'" x-cloak class="grid gap-1 bg-orange-50 px-4 py-3 text-sm sm:grid-cols-2">@foreach ($profile->lines('unhealthy') as $l)<li class="flex gap-2"><span class="text-orange-600">●</span>{{ $l }}</li>@endforeach</ul>
    </div>
    @unless ($compact)
        <div class="space-y-2 border-t border-stone-100 px-4 py-3 text-sm">
            @if ($profile->integration)<p><span class="font-medium text-emerald-700">{{ __('Integración') }}:</span> {{ $profile->integration }}</p>@endif
            @if ($profile->disintegration)<p><span class="font-medium text-orange-700">{{ __('Desintegración') }}:</span> {{ $profile->disintegration }}</p>@endif
            @if ($profile->growth_action)<p class="rounded-lg bg-stone-50 p-2"><span class="font-medium">{{ __('Botón de acción') }}:</span> {{ $profile->growth_action }}</p>@endif
            @if ($profile->notes)<p class="text-stone-600">{{ $profile->notes }}</p>@endif
            @if ($profile->conclusion)<blockquote class="border-l-4 pl-3 italic text-stone-700" style="border-color: {{ $color }}">{{ $profile->conclusion }}</blockquote>@endif
        </div>
    @endunless
</section>
