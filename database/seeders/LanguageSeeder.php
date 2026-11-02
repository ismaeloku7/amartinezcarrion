<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::updateOrCreate(['code' => 'es'], ['name' => 'Español', 'active' => true, 'is_default' => true, 'position' => 1]);
        Language::updateOrCreate(['code' => 'en'], ['name' => 'English', 'active' => true, 'is_default' => false, 'position' => 2]);
    }
}
