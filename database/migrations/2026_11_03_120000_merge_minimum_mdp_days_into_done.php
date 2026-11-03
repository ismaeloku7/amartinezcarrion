<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "minimum day" shortcut is gone: every finished devotional is simply done, and the
 * notebook flags the ones under the full reading time from duration_seconds.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('mdp_days')->where('status', 'minimum')->update(['status' => 'done']);
    }

    public function down(): void
    {
        // Nothing to restore: the distinction is now derived from duration_seconds.
    }
};
