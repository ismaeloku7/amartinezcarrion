@props(['step', 'hasNegative', 'complete', 'action', 'actionLabel'])
<div class="mt-4 flex items-center justify-between">
    @if ($step === 'negative')
        <x-ui.button wire:click="goPositive" variant="ghost">{{ __('Atrás') }}</x-ui.button>
    @else
        <span></span>
    @endif
    @if ($step === 'positive' && $hasNegative)
        <x-ui.button wire:click="goNegative" :disabled="! $complete">{{ __('Siguiente') }}</x-ui.button>
    @else
        <x-ui.button wire:click="{{ $action }}" :disabled="! $complete">{{ $actionLabel }}</x-ui.button>
    @endif
</div>
