<div class="space-y-4">
    @if ($productModel->description)
        <p class="text-[0.9375rem] leading-relaxed text-stone-600">{{ $productModel->description }}</p>
    @endif

    @if ($hasAccess)
        <x-ui.alert type="success">{{ __('Ya tienes acceso a este contenido.') }}</x-ui.alert>
    @elseif (! $paymentsEnabled)
        <x-ui.alert>{{ __('El pago en línea aún no está activo. Escríbenos para solicitar el acceso.') }}</x-ui.alert>
    @endif

    @if (session('status'))
        <x-ui.alert type="warning">{{ session('status') }}</x-ui.alert>
    @endif

    @forelse ($plans as $plan)
        @php $discount = $discounts[$plan->id] ?? null; @endphp
        <x-ui.card>
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="font-semibold">{{ $plan->name }}</div>
                    @if ($plan->description)<div class="mt-0.5 text-sm text-muted">{{ $plan->description }}</div>@endif
                </div>
                <div class="shrink-0 text-right">
                    @if ($discount)
                        <div class="text-xs text-muted line-through">{{ $plan->formattedPrice() }}</div>
                        <div class="text-xl font-bold tracking-tight text-emerald-700">{{ $discount->format($discount->firstCents) }}</div>
                    @else
                        <div class="text-xl font-bold tracking-tight">{{ $plan->formattedPrice() }}</div>
                    @endif
                    <div class="text-xs text-muted">{{ ['month' => __('al mes'), 'year' => __('al año'), 'once' => __('pago único')][$plan->interval] }}</div>
                </div>
            </div>
            @if ($discount && $plan->isRecurring())
                <p class="mt-2 text-xs text-emerald-700">
                    @if ($discount->recurringCents === $discount->firstCents)
                        {{ __('Cupón :code: precio con descuento mientras dure la suscripción.', ['code' => $discount->coupon->code]) }}
                    @else
                        {{ __('Cupón :code: :first el primer periodo, después :regular.', ['code' => $discount->coupon->code, 'first' => $discount->format($discount->firstCents), 'regular' => $discount->format($discount->recurringCents)]) }}
                    @endif
                </p>
            @elseif ($discount)
                <p class="mt-2 text-xs text-emerald-700">{{ __('Cupón :code aplicado: ahorras :amount.', ['code' => $discount->coupon->code, 'amount' => $discount->format($discount->savesCents())]) }}</p>
            @endif
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
                    <x-ui.button :href="route('checkout', ['plan' => $plan] + ($discount ? ['coupon' => $discount->coupon->code] : []))" block>
                        {{ $discount && $discount->firstCents === 0 && ! $plan->isRecurring() ? __('Activar gratis con el cupón') : __('Pagar con PayPal') }}
                    </x-ui.button>
                @else
                    <p class="rounded-full bg-fill py-3 text-center text-xs text-muted">{{ __('Pago en línea no disponible todavía') }}</p>
                @endif
            </div>
        </x-ui.card>
    @empty
        <x-ui.alert>{{ __('Todavía no hay planes disponibles para este contenido.') }}</x-ui.alert>
    @endforelse

    @auth
        @if ($paymentsEnabled && $plans->isNotEmpty() && ! $hasAccess)
            <x-ui.card>
                @if ($appliedCoupon && ! $couponError)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span>{{ __('Cupón aplicado:') }} <strong>{{ $appliedCoupon }}</strong></span>
                        <button type="button" wire:click="removeCoupon" class="text-xs font-medium text-stone-500 underline">{{ __('Quitar') }}</button>
                    </div>
                @else
                    <form wire:submit="applyCoupon" class="flex items-end gap-2">
                        <label class="block grow">
                            <span class="field-label">{{ __('¿Tienes un cupón?') }}</span>
                            <input wire:model="couponCode" class="field uppercase" autocomplete="off" autocapitalize="characters" placeholder="{{ __('Código') }}">
                        </label>
                        <x-ui.button type="submit" variant="soft">{{ __('Aplicar') }}</x-ui.button>
                    </form>
                    @if ($couponError)
                        <p class="mt-2 text-xs text-red-600">{{ $couponError }}</p>
                    @endif
                @endif
            </x-ui.card>
        @endif
    @endauth
</div>
