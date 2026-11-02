<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique(); // stored upper-case
            $table->string('description')->nullable(); // internal note
            $table->string('type', 10); // percent|fixed
            $table->unsignedInteger('value'); // percent (1-100) or cents
            $table->string('duration', 10)->default('once'); // once|forever (subscriptions only)
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->boolean('once_per_user')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Plans a coupon can be used with. No rows = every plan.
        Schema::create('coupon_plan', function (Blueprint $table) {
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->primary(['coupon_id', 'plan_id']);
        });

        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->string('paypal_order_id')->nullable()->index();
            $table->string('paypal_subscription_id')->nullable()->index();
            $table->unsignedInteger('original_cents');
            $table->unsignedInteger('first_cents'); // what the first payment costs
            $table->unsignedInteger('recurring_cents'); // later cycles (subscriptions)
            $table->timestamp('confirmed_at')->nullable(); // set when PayPal confirms the payment
            $table->timestamps();
        });

        // PayPal plans derived from a portal plan with discounted prices, created on demand.
        Schema::create('plan_paypal_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('first_cents');
            $table->unsignedInteger('recurring_cents');
            $table->string('paypal_plan_id');
            $table->timestamps();
            $table->unique(['plan_id', 'first_cents', 'recurring_cents']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_paypal_variants');
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupon_plan');
        Schema::dropIfExists('coupons');
    }
};
