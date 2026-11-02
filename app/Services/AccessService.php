<?php

namespace App\Services;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Decides whether a user may use a product, and grants/revokes access from plans.
 * Modules only ever ask hasAccess(); they never look at plans or PayPal.
 */
class AccessService
{
    public function hasAccess(User $user, string $productKey): bool
    {
        $product = $this->product($productKey);

        if (! $product || ! $product->active) {
            return false;
        }

        if ($product->is_free || $user->is_admin) {
            return true;
        }

        return $user->entitlements()
            ->where('product_id', $product->id)
            ->active()
            ->exists();
    }

    /** Grant every product of a plan to the user, keyed by source so it can be revoked later. */
    public function grantPlan(User $user, Plan $plan, string $source, ?string $sourceId, ?CarbonInterface $endsAt = null): void
    {
        foreach ($plan->products as $product) {
            Entitlement::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id, 'source' => $source, 'source_id' => $sourceId],
                ['starts_at' => now(), 'ends_at' => $endsAt],
            );
        }
    }

    /** Close (or extend) the entitlements that came from one source, e.g. a cancelled subscription. */
    public function endSource(User $user, string $source, ?string $sourceId, ?CarbonInterface $endsAt): void
    {
        $user->entitlements()
            ->where('source', $source)
            ->where('source_id', $sourceId)
            ->update(['ends_at' => $endsAt ?? now()]);
    }

    public function grantManual(User $user, Product $product, ?CarbonInterface $endsAt = null): Entitlement
    {
        return Entitlement::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id, 'source' => Entitlement::SOURCE_MANUAL, 'source_id' => null],
            ['starts_at' => now(), 'ends_at' => $endsAt],
        );
    }

    /** Plans that include the given product, for the paywall page. */
    public function plansFor(string $productKey)
    {
        return Plan::query()
            ->where('active', true)
            ->whereHas('products', fn ($q) => $q->where('key', $productKey))
            ->with('products')
            ->orderBy('position')
            ->get();
    }

    public function product(string $key): ?Product
    {
        return Product::query()->where('key', $key)->first();
    }
}
