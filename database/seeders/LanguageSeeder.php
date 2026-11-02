<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /** Only creates the two initial languages; what the panel changes afterwards stays. */
    public function run(): void
    {
        Language::firstOrCreate(['code' => 'es'], ['name' => 'Español', 'active' => true, 'is_default' => true, 'position' => 1]);
        Language::firstOrCreate(['code' => 'en'], ['name' => 'English', 'active' => true, 'is_default' => false, 'position' => 2]);
    }
}
