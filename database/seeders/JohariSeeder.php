<?php

namespace Database\Seeders;

use App\Models\Tests\Test;
use App\Models\Tests\TestResultRule;
use App\Models\Tests\WordList;
use Illuminate\Database\Seeder;

class JohariSeeder extends Seeder
{
    /** The 56 classic adjectives with Spanish translations. */
    public const WORDS = [
        ['able', 'capaz', 'positive'], ['accepting', 'receptivo', 'positive'], ['adaptable', 'adaptable', 'positive'],
        ['bold', 'audaz', 'neutral'], ['brave', 'valiente', 'positive'], ['calm', 'tranquilo', 'positive'],
        ['caring', 'atento con los demás', 'positive'], ['cheerful', 'alegre', 'positive'], ['clever', 'listo', 'positive'],
        ['complex', 'complejo', 'uncomfortable'], ['confident', 'seguro de sí mismo', 'positive'], ['dependable', 'fiable', 'positive'],
        ['dignified', 'digno', 'positive'], ['empathetic', 'empático', 'positive'], ['energetic', 'enérgico', 'positive'],
        ['extroverted', 'extrovertido', 'neutral'], ['friendly', 'amistoso', 'positive'], ['giving', 'generoso', 'positive'],
        ['happy', 'feliz', 'positive'], ['helpful', 'servicial', 'positive'], ['idealistic', 'idealista', 'neutral'],
        ['independent', 'independiente', 'neutral'], ['ingenious', 'ingenioso', 'positive'], ['intelligent', 'inteligente', 'positive'],
        ['introverted', 'introvertido', 'neutral'], ['kind', 'amable', 'positive'], ['knowledgeable', 'instruido', 'positive'],
        ['logical', 'lógico', 'neutral'], ['loving', 'cariñoso', 'positive'], ['mature', 'maduro', 'positive'],
        ['modest', 'modesto', 'positive'], ['nervous', 'nervioso', 'uncomfortable'], ['observant', 'observador', 'positive'],
        ['organized', 'organizado', 'positive'], ['patient', 'paciente', 'positive'], ['powerful', 'con fuerza', 'neutral'],
        ['proud', 'orgulloso', 'uncomfortable'], ['quiet', 'callado', 'neutral'], ['reflective', 'reflexivo', 'positive'],
        ['relaxed', 'relajado', 'positive'], ['religious', 'religioso', 'neutral'], ['responsive', 'sensible a los demás', 'positive'],
        ['searching', 'inquieto, buscador', 'neutral'], ['self-assertive', 'asertivo', 'positive'], ['self-conscious', 'cohibido', 'uncomfortable'],
        ['sensible', 'sensato', 'positive'], ['sentimental', 'sentimental', 'neutral'], ['shy', 'tímido', 'uncomfortable'],
        ['silly', 'bromista', 'uncomfortable'], ['spontaneous', 'espontáneo', 'neutral'], ['sympathetic', 'comprensivo', 'positive'],
        ['tense', 'tenso', 'uncomfortable'], ['trustworthy', 'digno de confianza', 'positive'], ['warm', 'cálido', 'positive'],
        ['wise', 'sabio', 'positive'], ['witty', 'ocurrente', 'positive'],
    ];

    public function run(): void
    {
        $list = WordList::firstOrNew(['key' => 'johari-classic']);
        $list->setTranslations('name', ['es' => 'Ventana de Johari (lista clásica)', 'en' => 'Johari window (classic list)']);
        $list->active = true;
        $list->is_default = true;
        $list->save();

        foreach (self::WORDS as $i => [$key, $es, $tone]) {
            $word = $list->words()->firstOrNew(['key' => $key]);
            $word->setTranslations('label', ['es' => $es, 'en' => $key]);
            $word->tone = $tone;
            $word->position = $i + 1;
            $word->save();
        }

        $test = Test::firstOrNew(['key' => 'johari']);
        $test->type = Test::TYPE_JOHARI;
        $test->setTranslations('name', ['es' => 'Ventana de Johari', 'en' => 'Johari window']);
        $test->setTranslations('description', [
            'es' => 'Descubre cómo te ves y cómo te ven las personas de tu entorno.',
            'en' => 'Discover how you see yourself and how the people around you see you.',
        ]);
        $test->setTranslations('intro', [
            'es' => 'Elige las palabras que mejor te describen. Después invita a personas de confianza a que elijan las que, según ellas, te describen. Sus respuestas son anónimas.',
            'en' => 'Pick the words that best describe you. Then invite people you trust to pick the words that, in their view, describe you. Their answers are anonymous.',
        ]);
        $test->setTranslations('general_content', [
            'es' => 'El área abierta crece cuando compartes y cuando pides opinión. Vuelve a hacer el test dentro de unos meses para ver tu evolución.',
            'en' => 'The open area grows when you share and when you ask for feedback. Repeat the test in a few months to see how you evolve.',
        ]);
        $test->word_list_id = $list->id;
        $test->config = ['self_positive' => 6, 'self_negative' => 2, 'other_positive' => 6, 'other_negative' => 2, 'min_responses' => 3, 'max_invitations' => 15, 'invitation_days' => 30, 'repeat_allowed' => true, 'repeat_after_days' => 90];
        $test->active = true;
        $test->save();

        if ($test->rules()->count() === 0) {
            $rules = [
                ['blind_count', '>=', '4', 'Área ciega amplia', 'Varias personas ven en ti rasgos que tú no habías señalado. Pregúntales con calma qué les hace verlo así.'],
                ['hidden_count', '>=', '4', 'Área oculta amplia', 'Hay rasgos que tú reconoces y que los demás no perciben. Compartir más de ti puede acercar la imagen que tienen a la que tú tienes.'],
                ['agreement_pct', '>=', '70', 'Alta coincidencia', 'La mayoría de las palabras que elegiste también fueron elegidas por otros. Tu autoconocimiento está bien asentado.'],
            ];
            foreach ($rules as $i => [$ind, $op, $val, $title, $content]) {
                $rule = new TestResultRule(['test_id' => $test->id, 'indicator' => $ind, 'operator' => $op, 'value' => $val, 'position' => $i]);
                $rule->setTranslation('title', 'es', $title);
                $rule->setTranslation('content', 'es', $content);
                $rule->save();
            }
        }
    }
}
