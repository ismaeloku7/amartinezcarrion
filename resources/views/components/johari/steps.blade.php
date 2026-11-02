@props(['step', 'hasNegative', 'complete', 'action', 'actionLabel'])
{{-- Navegación de los dos pasos (palabras positivas e incómodas), en la barra de acción inferior. --}}
<x-ui.action-bar>
    @if ($step === 'negative')
        <x-ui.button wire:click="goPositive" variant="secondary" class="w-12 shrink-0 px-0!" aria-label="{{ __('Atrás') }}"><x-ui.icon name="chevron-left" class="h-5 w-5" /></x-ui.button>
    @endif
    @if ($step === 'positive' && $hasNegative)
        <x-ui.button wire:click="goNegative" :disabled="! $complete" class="flex-1" size="lg">{{ __('Siguiente') }}<x-ui.icon name="arrow-right" class="h-4 w-4" /></x-ui.button>
    @else
        <x-ui.button wire:click="{{ $action }}" :disabled="! $complete" class="flex-1" size="lg">{{ $actionLabel }}</x-ui.button>
    @endif
</x-ui.action-bar>
