<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            CatalogSeeder::class,
            CardsSeeder::class,
            JohariSeeder::class,
        ]);

        if (app()->environment('local') && ! User::where('email', 'admin@example.com')->exists()) {
            User::factory()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password', 'is_admin' => true]);
        }
    }
}
