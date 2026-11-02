<?php

namespace App\Livewire\Portal;

use App\Models\Coupon;
use App\Services\AccessService;
use App\Services\Coupons\CouponException;
use App\Services\Coupons\CouponService;
use App\Services\PayPal\PayPalClient;
use Livewire\Component;

class Paywall extends Component
{
    public string $product;

    /** Code typed in the box. */
    public string $couponCode = '';

    /** Code currently applied to the prices (validated per plan on render). */
    public ?string $appliedCoupon = null;

    public ?string $couponError = null;

    public function mount(string $product): void
    {
        $this->product = $product;

        // Marketing links can preselect a coupon: /planes/mdp?cupon=VERANO
        if ($code = request()->query('cupon')) {
            $this->couponCode = Coupon::normalize($code);
            $this->applyCoupon();
        }
        if ($error = session('coupon_error')) {
            $this->couponError = $error;
        }
    }

    public function applyCoupon(): void
    {
        $this->couponError = null;
        $this->appliedCoupon = null;
        $code = Coupon::normalize($this->couponCode);
        if ($code === '') {
            return;
        }

        $service = app(CouponService::class);
        $access = app(AccessService::class);
        $coupon = $service->find($code);
        $plans = $access->plansFor($this->product);

        $applicable = $coupon && $plans->contains(fn ($plan) => $service->preview($coupon, $plan, auth()->user()) !== null);

        if ($applicable) {
            $this->appliedCoupon = $code;
        } else {
            $this->couponError = $this->reasonFor($service, $coupon, $plans);
        }
    }

    public function removeCoupon(): void
    {
        $this->appliedCoupon = null;
        $this->couponCode = '';
        $this->couponError = null;
    }

    /** First error the service raises for any plan, so the user knows why. */
    private function reasonFor(CouponService $service, ?Coupon $coupon, $plans): string
    {
        if (! $coupon) {
            return __('Este cupón no existe.');
        }
        foreach ($plans as $plan) {
            try {
                $service->applyCoupon($coupon, $plan, auth()->user());
            } catch (CouponException $e) {
                return $e->getMessage();
            }
        }

        return __('Este cupón no es válido para este plan.');
    }

    public function render(AccessService $access, CouponService $coupons)
    {
        $product = $access->product($this->product);
        abort_unless($product, 404);

        $plans = $access->plansFor($this->product);
        $coupon = $this->appliedCoupon ? $coupons->find($this->appliedCoupon) : null;
        $discounts = $plans->mapWithKeys(fn ($plan) => [$plan->id => $coupons->preview($coupon, $plan, auth()->user())]);

        return view('livewire.portal.paywall', [
            'productModel' => $product,
            'plans' => $plans,
            'discounts' => $discounts,
            'hasAccess' => auth()->check() && $access->hasAccess(auth()->user(), $this->product),
            'paymentsEnabled' => PayPalClient::enabled(),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $product->name, 'back' => route('home')]);
    }
}
