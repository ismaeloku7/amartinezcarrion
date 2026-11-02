<div class="space-y-4">
    @if ($productModel->description)
        <p class="text-[0.9375rem] leading-relaxed text-stone-600">{{ $productModel->description }}</p>
    @endif

    @if ($hasAccess)
        <x-ui.alert type="success">{{ __('Ya tienes acceso a este contenido.') }}</x-ui.alert>
    @elseif (! $paymentsEnabled)
        <x-ui.alert>{{ __('El pago en línea aún no está activo. Escríbenos para solicitar el acceso.') }}</x-ui.alert>
    @endif

    @forelse ($plans as $plan)
        <x-ui.card>
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="font-semibold">{{ $plan->name }}</div>
                    @if ($plan->description)<div class="mt-0.5 text-sm text-muted">{{ $plan->description }}</div>@endif
                </div>
                <div class="shrink-0 text-right">
                    <div class="text-xl font-bold tracking-tight">{{ $plan->formattedPrice() }}</div>
                    <div class="text-xs text-muted">{{ ['month' => __('al mes'), 'year' => __('al año'), 'once' => __('pago único')][$plan->interval] }}</div>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($plan->products as $product)
                    <span class="rounded-full bg-app/10 px-2.5 py-1 text-xs font-medium text-app">{{ $product->name }}</span>
                @endforeach
                @if ($plan->trial_days)
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">{{ __(':n días de prueba', ['n' => $plan->trial_days]) }}</span>
                @endif
            </div>
            <div class="mt-4">
                @guest
                    <x-ui.button :href="route('login')" variant="secondary" block>{{ __('Entrar') }}</x-ui.button>
                @elseif ($paymentsEnabled && $plan->isPurchasable())
                    <x-ui.button :href="route('checkout', $plan)" block>{{ __('Pagar con PayPal') }}</x-ui.button>
                @else
                    <p class="rounded-full bg-fill py-3 text-center text-xs text-muted">{{ __('Pago en línea no disponible todavía') }}</p>
                @endif
            </div>
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Todavía no hay planes disponibles para este contenido.') }}</x-ui.alert>
    @endforelse
</div>
