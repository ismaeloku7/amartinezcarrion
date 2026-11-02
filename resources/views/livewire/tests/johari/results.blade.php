<div class="space-y-4">
    @if (! $result)
        <x-ui.card :title="__('Esperando respuestas')">
            <p class="text-sm text-stone-600">{{ __('Han respondido :n personas. Los resultados se muestran a partir de :min respuestas para proteger el anonimato.', ['n' => $responses, 'min' => $min]) }}</p>
            <x-ui.button :href="route('tests.johari.invite', $take)" class="mt-3">{{ __('Gestionar invitaciones') }}</x-ui.button>
        </x-ui.card>
    @else
        <x-ui.card>
            <div class="mb-3 text-xs text-stone-500">{{ __(':n respuestas', ['n' => $result['indicators']['responses']]) }} · {{ __('coincidencia :p%', ['p' => $result['indicators']['agreement_pct']]) }} @if ($take->status !== 'closed')· <a class="underline" href="{{ route('tests.johari.invite', $take) }}">{{ __('seguir invitando') }}</a>@endif</div>
            <x-johari.window :result="$result" :labels="$labels" />
        </x-ui.card>

        @foreach ($rules as $rule)
            <x-ui.card :title="$rule->title"><p class="text-sm text-stone-700">{{ $rule->content }}</p></x-ui.card>
        @endforeach

        @if ($take->test->general_content)
            <x-ui.card><p class="text-sm text-stone-700">{{ $take->test->general_content }}</p></x-ui.card>
        @endif

        <div class="flex flex-wrap gap-2">
            @if ($previousTakes > 0)<x-ui.button :href="route('tests.johari.evolution', $take->test)" variant="secondary">{{ __('Ver evolución') }}</x-ui.button>@endif
            @if ($take->status !== 'closed')<x-ui.button wire:click="close" variant="secondary" wire:confirm="{{ __('¿Cerrar esta toma? Los enlaces dejarán de funcionar.') }}">{{ __('Cerrar la toma') }}</x-ui.button>@endif
            <x-ui.button wire:click="delete" variant="ghost" wire:confirm="{{ __('¿Borrar esta toma y todas sus respuestas?') }}">{{ __('Borrar') }}</x-ui.button>
        </div>
    @endif
</div>
