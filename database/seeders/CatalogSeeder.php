<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Initial catalog. It runs on every deploy, so it only creates what is missing:
 * whatever the admin changed in the panel (free products, inactive plans, prices,
 * names, included products) is never overwritten.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $mdp = $this->product('mdp', [
            'name' => ['es' => 'Práctica del Método Devocional Proyectivo', 'en' => 'Projective Devotional Method practice'],
            'description' => ['es' => 'La práctica guiada del libro «Dios como mi terapeuta a través de la Biblia».', 'en' => 'The guided practice of the book.'],
        ]);

        $cards = $this->product('cards', [
            'name' => ['es' => 'Programa de las 42 fichas', 'en' => 'The 42-card programme'],
            'description' => ['es' => 'La práctica guiada del libro «El arte de dar forma a tu cerebro».', 'en' => 'The guided practice of the book.'],
        ]);

        $plans = [
            ['mdp-month', ['es' => 'Devocional mensual', 'en' => 'Devotional monthly'], 'month', 499, [$mdp]],
            ['mdp-year', ['es' => 'Devocional anual', 'en' => 'Devotional yearly'], 'year', 3999, [$mdp]],
            ['cards-month', ['es' => 'Fichas mensual', 'en' => 'Cards monthly'], 'month', 499, [$cards]],
            ['cards-year', ['es' => 'Fichas anual', 'en' => 'Cards yearly'], 'year', 3999, [$cards]],
            ['books-year', ['es' => 'Los dos libros, anual', 'en' => 'Both books, yearly'], 'year', 5999, [$mdp, $cards]],
        ];

        foreach ($plans as $i => [$key, $name, $interval, $price, $products]) {
            if (Plan::query()->where('key', $key)->exists()) {
                continue;
            }

            $plan = new Plan(['key' => $key]);
            $plan->setTranslations('name', $name);
            $plan->interval = $interval;
            $plan->price_cents = $price;
            $plan->currency = 'EUR';
            $plan->active = true;
            $plan->position = $i;
            $plan->paypal_plan_id = env('PAYPAL_PLAN_'.strtoupper(str_replace('-', '_', $key)));
            $plan->save();
            $plan->products()->sync(collect($products)->pluck('id'));
        }
    }

    private function product(string $key, array $translations): Product
    {
        if ($product = Product::query()->where('key', $key)->first()) {
            return $product;
        }

        $product = new Product(['key' => $key]);
        $product->type = Product::TYPE_PRACTICE;
        $product->setTranslations('name', $translations['name']);
        $product->setTranslations('description', $translations['description']);
        $product->is_free = false;
        $product->active = true;
        $product->save();

        return $product;
    }
}
