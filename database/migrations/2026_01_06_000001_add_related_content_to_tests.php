<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->foreignId('related_content_id')->nullable()->after('product_id')->constrained('contents')->nullOnDelete();
        });
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('listed')->default(true)->after('requires_login'); // false = only reachable by link or as a notice
        });
    }

    public function down(): void
    {
        Schema::table('contents', fn (Blueprint $table) => $table->dropColumn('listed'));
        Schema::table('tests', fn (Blueprint $table) => $table->dropConstrainedForeignId('related_content_id'));
    }
};
