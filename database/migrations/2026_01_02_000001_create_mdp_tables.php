<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdp_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('mode', 10)->default('full'); // full|light
            $table->unsignedTinyInteger('phase')->nullable(); // null = automatic (1 during first 3 weeks, then 2)
            $table->unsignedTinyInteger('rest_weekday')->default(6); // 0 = Sunday ... 6 = Saturday
            $table->string('reading_time', 5)->default('07:00');
            $table->json('review_times'); // three HH:MM strings
            $table->string('minimum_alert_time', 5)->nullable()->default('21:00');
            $table->date('started_at');
            $table->unsignedSmallInteger('reading_order_position')->default(1);
            $table->timestamps();
        });

        Schema::create('mdp_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('mdp_enrollments')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 10)->default('pending'); // pending|done|minimum|rest
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('reading_from')->nullable();
            $table->string('reading_to')->nullable();
            $table->text('prayer')->nullable(); // encrypted
            $table->text('prayer_underline')->nullable(); // encrypted
            $table->timestamp('visualized_at')->nullable();
            $table->unsignedTinyInteger('reviews_done')->default(0);
            $table->boolean('previous_reviewed')->default(false);
            $table->timestamps();
            $table->unique(['enrollment_id', 'date']);
        });

        Schema::create('mdp_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_id')->constrained('mdp_days')->cascadeOnDelete();
            $table->unsignedTinyInteger('position')->default(0);
            $table->text('body');
            $table->json('underline')->nullable(); // [start, end] character offsets
            $table->boolean('is_day_text')->default(false);
            $table->unsignedTinyInteger('word_count')->default(0);
            $table->string('note_type', 2)->nullable(); // rf|pd|il
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('mdp_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('mdp_enrollments')->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->foreignId('week_text_id')->nullable()->constrained('mdp_texts')->nullOnDelete();
            $table->json('word_underlines')->nullable(); // text_id => word index
            $table->timestamp('rest_day_done_at')->nullable();
            $table->unsignedTinyInteger('reviews_done')->default(0);
            $table->string('memorize_key')->nullable();
            $table->boolean('memorized')->default(false);
            $table->timestamps();
            $table->unique(['enrollment_id', 'starts_on']);
        });

        Schema::create('mdp_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('mdp_enrollments')->cascadeOnDelete();
            $table->unsignedSmallInteger('index');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->foreignId('month_text_id')->nullable()->constrained('mdp_texts')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdp_months');
        Schema::dropIfExists('mdp_weeks');
        Schema::dropIfExists('mdp_texts');
        Schema::dropIfExists('mdp_days');
        Schema::dropIfExists('mdp_enrollments');
    }
};
