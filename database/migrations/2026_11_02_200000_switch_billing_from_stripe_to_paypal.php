<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments move from Stripe (Cashier) to PayPal. Plans keep a PayPal plan id for
 * subscriptions, purchases store the PayPal order/capture, and PayPal subscriptions
 * get their own table. Cashier's tables and columns are dropped when present.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->renameColumn('stripe_price_id', 'paypal_plan_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('stripe_checkout_session_id', 'paypal_order_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('stripe_payment_intent_id', 'paypal_capture_id');
        });

        Schema::create('paypal_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paypal_id')->unique(); // I-…
            $table->string('paypal_plan_id')->nullable(); // P-…
            $table->string('status', 30);
            $table->timestamp('paid_through_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        // Leftovers from Laravel Cashier, only on databases migrated before this change.
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        if (Schema::hasColumn('users', 'stripe_id')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasIndex('users', ['stripe_id'])) {
                    $table->dropIndex(['stripe_id']);
                }
            });
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(array_values(array_filter(
                    ['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'],
                    fn (string $column) => Schema::hasColumn('users', $column),
                )));
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paypal_subscriptions');

        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('paypal_capture_id', 'stripe_payment_intent_id');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('paypal_order_id', 'stripe_checkout_session_id');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->renameColumn('paypal_plan_id', 'stripe_price_id');
        });
    }
};
