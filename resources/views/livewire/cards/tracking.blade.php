@php
    $levels = [3 => __('Mucho'), 2 => __('Bastante'), 1 => __('Apenas')];
    // Intensidad del color de la app según la valoración del día.
    $levelClass = [3 => 'bg-app text-white', 2 => 'bg-app/45 text-white', 1 => 'bg-app/15 text-app'];
    $counts = collect($sheet)->filter()->countBy();
@endphp
<div class="space-y-5">
    <div class="flex items-center justify-between gap-2 rounded-full bg-white p-1.5 shadow-soft">
        <button type="button" wire:click="move(-1)" class="grid h-10 w-10 place-items-center rounded-full text-stone-600 active:bg-fill" aria-label="{{ __('Mes anterior') }}"><x-ui.icon name="chevron-left" class="h-5 w-5" /></button>
        <div class="text-sm font-semibold capitalize">{{ $label }}</div>
        <button type="button" wire:click="move(1)" class="grid h-10 w-10 place-items-center rounded-full text-stone-600 active:bg-fill" aria-label="{{ __('Mes siguiente') }}"><x-ui.icon name="chevron-right" class="h-5 w-5" /></button>
    </div>

    <x-ui.card class="p-4">
        <div class="grid grid-cols-7 gap-1.5 text-center">
            @for ($w = 0; $w < 7; $w++)
                <div class="pb-1 text-[0.6875rem] font-semibold capitalize text-muted">{{ now()->startOfWeek()->addDays($w)->isoFormat('dd') }}</div>
            @endfor
            @for ($b = 0; $b < $leadingBlanks; $b++)
                <div></div>
            @endfor
            @for ($d = 1; $d <= $daysInMonth; $d++)
                @php $level = $sheet[$d] ?? null; $rest = in_array($d, $restDays); @endphp
                <div @class([
                    'grid aspect-square place-items-center rounded-xl text-sm font-medium',
                    $levelClass[$level] ?? '' => $level,
                    'bg-stone-100 text-stone-300' => ! $level && $rest,
                    'bg-fill text-stone-500' => ! $level && ! $rest,
                    'ring-2 ring-app ring-offset-2' => $todayDay === $d,
                ]) title="{{ $level ? $levels[$level] : ($rest ? __('Descanso') : '') }}">{{ $d }}</div>
            @endfor
        </div>

        <div class="mt-5 grid grid-cols-3 gap-2 border-t border-stone-100 pt-4 text-center">
            @foreach ($levels as $level => $name)
                <div>
                    <div class="mx-auto mb-1.5 h-3 w-8 rounded-full {{ $levelClass[$level] }}"></div>
                    <div class="text-lg font-semibold leading-none">{{ $counts[$level] ?? 0 }}</div>
                    <div class="mt-1 text-xs text-muted">{{ $level }}. {{ $name }}</div>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <x-ui.card class="flex items-center justify-between gap-3 p-4">
        <span class="text-sm">{{ $enrollment->tracking_enabled ? __('Valoración nocturna activa') : __('Valoración nocturna desactivada') }}</span>
        <x-ui.button wire:click="toggleTracking" :variant="$enrollment->tracking_enabled ? 'secondary' : 'soft'" size="sm">{{ $enrollment->tracking_enabled ? __('Desactivar') : __('Activar') }}</x-ui.button>
    </x-ui.card>
</div>
