<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique(); // 1..42
            $table->unsignedTinyInteger('group_index'); // 1..6
            $table->json('title');
            $table->json('front_text')->nullable();
            $table->json('back_text');
            $table->json('section_summary')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('selectable')->default(true);
            $table->timestamps();
        });

        Schema::create('cards_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rest_weekday')->default(6);
            $table->string('morning_time', 5)->default('07:30');
            $table->json('review_times'); // three HH:MM
            $table->string('evening_time', 5)->default('22:30');
            $table->date('started_at');
            $table->unsignedTinyInteger('unlocked_until')->default(0); // highest card incorporated
            $table->unsignedTinyInteger('last_group_reviewed')->default(0);
            $table->boolean('tracking_enabled')->default(true);
            $table->timestamp('tracking_prompted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cards_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('cards_enrollments')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_rest_day')->default(false);
            $table->unsignedTinyInteger('group_index')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('chosen_card_id')->nullable()->constrained('cards')->nullOnDelete();
            $table->unsignedTinyInteger('reviews_done')->default(0);
            $table->unsignedTinyInteger('rating')->nullable(); // 1 apenas, 2 bastante, 3 mucho
            $table->timestamps();
            $table->unique(['enrollment_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards_days');
        Schema::dropIfExists('cards_enrollments');
        Schema::dropIfExists('cards');
    }
};
