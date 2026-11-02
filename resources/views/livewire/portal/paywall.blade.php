<div class="space-y-4">
    @if ($productModel->description)
        <p class="text-stone-600">{{ $productModel->description }}</p>
    @endif

    @if ($hasAccess)
        <x-ui.alert type="success">{{ __('Ya tienes acceso a este contenido.') }}</x-ui.alert>
    @endif

    @forelse ($plans as $plan)
        <x-ui.card class="flex items-center justify-between gap-4">
            <div>
                <div class="font-semibold">{{ $plan->name }}</div>
                @if ($plan->description)<div class="text-sm text-stone-600">{{ $plan->description }}</div>@endif
                <div class="mt-1 text-xs text-stone-500">
                    {{ __('Incluye:') }} {{ $plan->products->map->name->join(', ') }}
                </div>
            </div>
            <div class="text-right">
                <div class="text-lg font-semibold">{{ $plan->formattedPrice() }}</div>
                <div class="text-xs text-stone-500">
                    {{ ['month' => __('al mes'), 'year' => __('al año'), 'once' => __('pago único')][$plan->interval] }}
                    @if ($plan->trial_days) · {{ __(':n días de prueba', ['n' => $plan->trial_days]) }} @endif
                </div>
                @auth
                    <x-ui.button :href="route('checkout', $plan)" class="mt-2">{{ __('Elegir') }}</x-ui.button>
                @else
                    <x-ui.button :href="route('login')" class="mt-2" variant="secondary">{{ __('Entrar') }}</x-ui.button>
                @endauth
            </div>
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Todavía no hay planes disponibles para este contenido.') }}</x-ui.alert>
    @endforelse
</div>
