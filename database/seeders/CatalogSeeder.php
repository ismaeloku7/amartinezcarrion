<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $mdp = Product::firstOrNew(['key' => 'mdp']);
        $mdp->type = Product::TYPE_PRACTICE;
        $mdp->setTranslations('name', ['es' => 'Práctica del Método Devocional Proyectivo', 'en' => 'Projective Devotional Method practice']);
        $mdp->setTranslations('description', ['es' => 'La práctica guiada del libro «Dios como mi terapeuta a través de la Biblia».', 'en' => 'The guided practice of the book.']);
        $mdp->is_free = false;
        $mdp->save();

        $cards = Product::firstOrNew(['key' => 'cards']);
        $cards->type = Product::TYPE_PRACTICE;
        $cards->setTranslations('name', ['es' => 'Programa de las 42 fichas', 'en' => 'The 42-card programme']);
        $cards->setTranslations('description', ['es' => 'La práctica guiada del libro «El arte de dar forma a tu cerebro».', 'en' => 'The guided practice of the book.']);
        $cards->is_free = false;
        $cards->save();

        $plans = [
            ['mdp-month', ['es' => 'Devocional mensual', 'en' => 'Devotional monthly'], 'month', 499, [$mdp]],
            ['mdp-year', ['es' => 'Devocional anual', 'en' => 'Devotional yearly'], 'year', 3999, [$mdp]],
            ['cards-month', ['es' => 'Fichas mensual', 'en' => 'Cards monthly'], 'month', 499, [$cards]],
            ['cards-year', ['es' => 'Fichas anual', 'en' => 'Cards yearly'], 'year', 3999, [$cards]],
            ['books-year', ['es' => 'Los dos libros, anual', 'en' => 'Both books, yearly'], 'year', 5999, [$mdp, $cards]],
        ];

        foreach ($plans as $i => [$key, $name, $interval, $price, $products]) {
            $plan = Plan::firstOrNew(['key' => $key]);
            $plan->setTranslations('name', $name);
            $plan->interval = $interval;
            $plan->price_cents = $price;
            $plan->currency = 'EUR';
            $plan->active = true;
            $plan->position = $i;
            $plan->paypal_plan_id = $plan->paypal_plan_id ?? env('PAYPAL_PLAN_'.strtoupper(str_replace('-', '_', $key)));
            $plan->save();
            $plan->products()->sync(collect($products)->pluck('id'));
        }
    }
}
