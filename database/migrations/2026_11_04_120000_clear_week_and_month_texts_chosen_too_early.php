<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Week and month texts are now chosen on their own day only (the rest day / the month's
 * closing day). Clear the ones that were picked automatically before that day, so they are
 * chosen when it comes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $today = now()->toDateString();

        DB::table('mdp_weeks')->where('ends_on', '>', $today)->whereNotNull('week_text_id')
            ->update(['week_text_id' => null]);
        DB::table('mdp_months')->where('ends_on', '>', $today)->whereNotNull('month_text_id')
            ->update(['month_text_id' => null, 'closed_at' => null]);
    }

    public function down(): void
    {
        // Nothing to restore: the texts are chosen again on their day.
    }
};
