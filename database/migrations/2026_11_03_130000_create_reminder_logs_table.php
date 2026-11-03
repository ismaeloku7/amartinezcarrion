<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** One row per reminder actually sent, so a reminder goes out once per day even when caught up late. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key', 40); // e.g. mdp-reading, cards-review-1
            $table->date('local_date');
            $table->timestamp('sent_at');
            $table->unique(['user_id', 'key', 'local_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
