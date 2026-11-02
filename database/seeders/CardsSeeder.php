<?php

namespace Database\Seeders;

use App\Models\Cards\Card;
use Illuminate\Database\Seeder;

class CardsSeeder extends Seeder
{
    public function run(): void
    {
        $cards = json_decode(file_get_contents(database_path('data/cards.json')), true);

        // Runs on every deploy: cards edited in the panel are left as they are.
        $existing = Card::query()->pluck('number')->all();

        foreach ($cards as $c) {
            if (in_array($c['number'], $existing, true)) {
                continue;
            }

            $card = new Card(['number' => $c['number']]);
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
