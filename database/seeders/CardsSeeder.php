<?php

namespace Database\Seeders;

use App\Models\Cards\Card;
use Illuminate\Database\Seeder;

class CardsSeeder extends Seeder
{
    public function run(): void
    {
        $cards = json_decode(file_get_contents(database_path('data/cards.json')), true);

        foreach ($cards as $c) {
            $card = Card::firstOrNew(['number' => $c['number']]);
            $card->group_index = $c['group'];
            $card->selectable = $c['selectable'];
            $card->image_path = sprintf('images/cards/card-%02d.png', $c['number']);
            $card->setTranslation('title', 'es', $c['title']);
            $card->setTranslation('back_text', 'es', $c['back_text']);
            if ($c['front_text'] !== '') {
                $card->setTranslation('front_text', 'es', $c['front_text']);
            }
            $card->save();
        }
    }
}
