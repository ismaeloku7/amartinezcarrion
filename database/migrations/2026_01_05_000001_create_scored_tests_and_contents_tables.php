<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->boolean('ai_enabled')->default(false)->after('active');
            $table->json('ai_instructions')->nullable()->after('ai_enabled');
            $table->foreignId('profiles_from_test_id')->nullable()->after('word_list_id')->constrained('tests')->nullOnDelete();
        });

        Schema::create('test_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->string('key', 20);
            $table->json('name');
            $table->string('color', 20)->nullable();
            $table->string('group', 20)->nullable(); // e.g. extrovert|introvert|balanced
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['test_id', 'key']);
        });

        Schema::create('test_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('group_key', 20)->nullable(); // page/section of the original questionnaire
            $table->json('statement');
            $table->json('weights'); // {scale_key: points}
            $table->timestamps();
        });

        Schema::create('test_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->string('scale_key', 20);
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->json('character')->nullable();
            $table->json('temperament')->nullable();
            $table->json('danger')->nullable();
            $table->json('basic_desire')->nullable();
            $table->json('basic_fear')->nullable();
            $table->json('hidden_complaint')->nullable();
            $table->json('childhood_origin')->nullable();
            $table->json('integration')->nullable();
            $table->json('disintegration')->nullable();
            $table->json('healthy')->nullable(); // one line per trait
            $table->json('shadow')->nullable();
            $table->json('unhealthy')->nullable();
            $table->json('growth_action')->nullable();
            $table->json('notes')->nullable();
            $table->json('conclusion')->nullable(); // Antonio's own conclusion for this type
            $table->timestamps();
            $table->unique(['test_id', 'scale_key']);
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('color', 20)->default('#1f3a5f');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('type', 20)->default('article'); // article|course
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable(); // HTML with optional [[shortcodes]]
            $table->string('cover_color', 20)->nullable();
            $table->string('author')->nullable();
            $table->unsignedSmallInteger('reading_minutes')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('requires_login')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('test_profiles');
        Schema::dropIfExists('test_items');
        Schema::dropIfExists('test_scales');
        Schema::table('tests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profiles_from_test_id');
            $table->dropColumn(['ai_enabled', 'ai_instructions']);
        });
    }
};
