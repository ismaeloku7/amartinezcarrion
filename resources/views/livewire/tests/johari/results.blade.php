<div class="space-y-5">
    @if (! $result)
        <x-ui.card :title="__('Esperando respuestas')">
            <p class="text-sm leading-relaxed text-muted">{{ __('Han respondido :n personas. Los resultados se muestran a partir de :min respuestas para proteger el anonimato.', ['n' => $responses, 'min' => $min]) }}</p>
            <x-ui.button :href="route('tests.johari.invite', $take)" block class="mt-4">{{ __('Gestionar invitaciones') }}</x-ui.button>
        </x-ui.card>
    @else
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-3xl bg-app p-4 text-white shadow-float">
                <div class="text-3xl font-bold">{{ $result['indicators']['responses'] }}</div>
                <div class="text-xs text-white/75">{{ __('respuestas') }}</div>
            </div>
            <div class="rounded-3xl bg-white p-4 shadow-soft">
                <div class="text-3xl font-bold">{{ $result['indicators']['agreement_pct'] }}%</div>
                <div class="text-xs text-muted">{{ __('coincidencia') }}</div>
            </div>
        </div>

        <x-johari.window :result="$result" :labels="$labels" />

        @if ($take->status !== 'closed')
            <x-ui.button :href="route('tests.johari.invite', $take)" variant="soft" block><x-ui.icon name="link" class="h-5 w-5" />{{ __('Seguir invitando') }}</x-ui.button>
        @endif

        @foreach ($rules as $rule)
            <x-ui.card :title="$rule->title"><p class="text-sm leading-relaxed text-stone-700">{{ $rule->content }}</p></x-ui.card>
        @endforeach

        @if ($take->test->general_content)
            <x-ui.card><p class="text-sm leading-relaxed text-stone-700">{{ $take->test->general_content }}</p></x-ui.card>
        @endif

        <div class="space-y-2">
            @if ($previousTakes > 0)<x-ui.button :href="route('tests.johari.evolution', $take->test)" variant="secondary" block><x-ui.icon name="chart" class="h-5 w-5" />{{ __('Ver evolución') }}</x-ui.button>@endif
            @if ($take->status !== 'closed')<x-ui.button wire:click="close" variant="secondary" block wire:confirm="{{ __('¿Cerrar esta toma? Los enlaces dejarán de funcionar.') }}">{{ __('Cerrar la toma') }}</x-ui.button>@endif
            <x-ui.button wire:click="delete" variant="ghost" block class="text-red-600" wire:confirm="{{ __('¿Borrar esta toma y todas sus respuestas?') }}">{{ __('Borrar') }}</x-ui.button>
        </div>
    @endif
</div>
