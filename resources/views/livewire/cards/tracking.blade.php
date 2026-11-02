<div class="space-y-4">
    <div class="flex items-center justify-between">
        <x-ui.button wire:click="move(-1)" variant="ghost">‹</x-ui.button>
        <div class="font-medium capitalize">{{ $label }}</div>
        <x-ui.button wire:click="move(1)" variant="ghost">›</x-ui.button>
    </div>
    <x-ui.card class="overflow-x-auto">
        <table class="w-full border-collapse text-[11px]">
            <thead><tr><th class="w-20 border border-stone-300 p-1 text-left"></th>@for ($d = 1; $d <= $daysInMonth; $d++)<th class="border border-stone-300 p-1 text-center {{ in_array($d, $restDays) ? 'bg-stone-100 text-stone-400' : '' }}">{{ $d }}</th>@endfor</tr></thead>
            <tbody>
                @foreach ([3 => __('Mucho'), 2 => __('Bastante'), 1 => __('Apenas')] as $level => $name)
                    <tr><td class="border border-stone-300 p-1 font-medium">{{ $level }}. {{ $name }}</td>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            <td class="h-7 border border-stone-300 text-center {{ in_array($d, $restDays) ? 'bg-stone-100' : '' }}">@if (($sheet[$d] ?? null) === $level)<span class="inline-block h-3 w-3 rounded-full bg-[color:var(--app-color)]"></span>@endif</td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-ui.card>
    <x-ui.card class="flex items-center justify-between">
        <span class="text-sm">{{ $enrollment->tracking_enabled ? __('Valoración nocturna activa') : __('Valoración nocturna desactivada') }}</span>
        <x-ui.button wire:click="toggleTracking" variant="secondary">{{ $enrollment->tracking_enabled ? __('Desactivar') : __('Activar') }}</x-ui.button>
    </x-ui.card>
</div>
