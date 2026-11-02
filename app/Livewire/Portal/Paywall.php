<?php

namespace App\Livewire\Portal;

use App\Services\AccessService;
use App\Services\PayPal\PayPalClient;
use Livewire\Component;

class Paywall extends Component
{
    public string $product;

    public function mount(string $product): void
    {
        $this->product = $product;
    }

    public function render(AccessService $access)
    {
        $product = $access->product($this->product);
        abort_unless($product, 404);

        return view('livewire.portal.paywall', [
            'productModel' => $product,
            'plans' => $access->plansFor($this->product),
            'hasAccess' => auth()->check() && $access->hasAccess(auth()->user(), $this->product),
            'paymentsEnabled' => PayPalClient::enabled(),
        ])->layout('components.layouts.app', ['appKey' => 'portal', 'title' => $product->name, 'back' => route('home')]);
    }
}
