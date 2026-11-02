<div class="space-y-4">
    @if ($productModel->description)
        <p class="text-stone-600">{{ $productModel->description }}</p>
    @endif

    @if ($hasAccess)
        <x-ui.alert type="success">{{ __('Ya tienes acceso a este contenido.') }}</x-ui.alert>
    @elseif (! $paymentsEnabled)
        <x-ui.alert>{{ __('El pago en línea aún no está activo. Escríbenos para solicitar el acceso.') }}</x-ui.alert>
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
                @guest
                    <x-ui.button :href="route('login')" class="mt-2" variant="secondary">{{ __('Entrar') }}</x-ui.button>
                @elseif ($paymentsEnabled && $plan->stripe_price_id)
                    <x-ui.button :href="route('checkout', $plan)" class="mt-2">{{ __('Elegir') }}</x-ui.button>
                @else
                    <span class="mt-2 inline-block text-xs text-stone-500">{{ __('Pago en línea no disponible todavía') }}</span>
                @endif
            </div>
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Todavía no hay planes disponibles para este contenido.') }}</x-ui.alert>
    @endforelse
</div>
