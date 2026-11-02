<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_lists', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_list_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('label');
            $table->json('hint')->nullable();
            $table->string('tone', 15)->nullable(); // positive|neutral|uncomfortable
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['word_list_id', 'key']);
        });

        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('type'); // johari|scored (future)
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('intro')->nullable();
            $table->json('general_content')->nullable(); // shown to everyone who finishes
            $table->json('config')->nullable(); // type-specific parameters
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('word_list_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('test_result_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->string('indicator'); // e.g. blind_count, open_pct, dominant_area, agreement
            $table->string('operator', 5); // =, !=, >, >=, <, <=
            $table->string('value');
            $table->json('title')->nullable();
            $table->json('content'); // translatable rich text shown when the rule matches
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('test_takes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('self_pending'); // self_pending|collecting|results|closed
            $table->json('data')->nullable(); // type-specific input (johari: self_words, min_responses, ...)
            $table->json('result')->nullable(); // computed result snapshot
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('johari_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('take_id')->constrained('test_takes')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('label')->nullable(); // familia, trabajo... only the owner sees it
            $table->unsignedSmallInteger('max_uses')->default(1);
            $table->unsignedSmallInteger('uses')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('johari_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('take_id')->constrained('test_takes')->cascadeOnDelete();
            $table->foreignId('invitation_id')->constrained('johari_invitations')->cascadeOnDelete();
            $table->json('words'); // word keys
            $table->string('fingerprint', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('johari_responses');
        Schema::dropIfExists('johari_invitations');
        Schema::dropIfExists('test_takes');
        Schema::dropIfExists('test_result_rules');
        Schema::dropIfExists('tests');
        Schema::dropIfExists('words');
        Schema::dropIfExists('word_lists');
    }
};
