<?php

namespace Database\Seeders;

use App\Models\Tests\Test;
use App\Models\Tests\TestItem;
use App\Models\Tests\TestProfile;
use App\Models\Tests\TestResultRule;
use Illuminate\Database\Seeder;

class EnneagramSeeder extends Seeder
{
    /** Scale colours and groups as in the author's bar chart: red 2,3,7,8 · blue 1,4,5,6 · green 9. */
    public const SCALES = [
        '1' => ['El reformador', '#2b5d8c', 'introvert'],
        '2' => ['El ayudador', '#b5121b', 'extrovert'],
        '3' => ['El motivador', '#b5121b', 'extrovert'],
        '4' => ['El artista', '#2b5d8c', 'introvert'],
        '5' => ['El pensador', '#2b5d8c', 'introvert'],
        '6' => ['El lealista', '#2b5d8c', 'introvert'],
        '7' => ['El generalista', '#b5121b', 'extrovert'],
        '8' => ['El líder', '#b5121b', 'extrovert'],
        '9' => ['El pacificador', '#3c8c5b', 'balanced'],
    ];

    public function run(): void
    {
        $main = $this->test('eneagrama', [
            'name' => ['es' => 'Las nueve personalidades (eneagrama)', 'en' => 'The nine personalities (enneagram)'],
            'description' => ['es' => 'Descubre tu eneatipo: tu zona sana, tus sombras y tu dirección de crecimiento.', 'en' => 'Discover your enneatype: your healthy zone, your shadows and your direction of growth.'],
            'intro' => ['es' => 'No hay respuestas ni resultados mejores ni peores. Se trata de descubrir el esquema psicológico con el que funcionas: cualquiera que sea el resultado, habrá aspectos positivos y otros negativos. El éxito de esta prueba consiste en descubrir para hacer posible la mejora. Responde SÍ si te identificas mucho o bastante con el enunciado completo, NO si no te identificas, «?» si no sabrías decir, y «SÍ, totalmente» cuando parece escrito pensando en ti.'],
            'general_content' => ['es' => 'Ningún sistema puede abarcar a una persona. No te identifiques con una sola característica, por importante que sea; no te identifiques con lo que querrías ser; y no tomes características de aquí y de allá: hay coherencia en la estructura de los eneatipos. Autoobservación, autocomprensión, transformación.'],
            'config' => ['strong_multiplier' => 2, 'per_page' => 18, 'shuffle' => false, 'repeat_allowed' => true, 'repeat_after_days' => 30],
        ], 'enneagram_items.json');

        $this->test('rheti', [
            'name' => ['es' => 'Indicador de tipo Riso-Hudson (adaptación)', 'en' => 'Riso-Hudson type indicator (adaptation)'],
            'description' => ['es' => 'Una versión más extensa, con 75 pares de enunciados, para afinar el eneatipo.', 'en' => 'A longer version, with 75 pairs of statements, to refine the enneatype.'],
            'intro' => ['es' => 'Responde a cada enunciado por separado. SÍ si te identificas mucho o bastante con el enunciado completo, NO si no te identificas, «?» si no sabrías decir, y «SÍ, totalmente» cuando parece escrito pensando en ti. Si no entiendes un enunciado, déjalo en blanco.'],
            'general_content' => ['es' => 'Ningún sistema puede abarcar a una persona. No te identifiques con una sola característica, ni con lo que querrías ser, ni tomes rasgos de aquí y de allá.'],
            'config' => ['strong_multiplier' => 2, 'per_page' => 20, 'shuffle' => false, 'repeat_allowed' => true, 'repeat_after_days' => 30],
            'profiles_from_test_id' => $main->id,
        ], 'rheti_items.json');
    }

    protected function test(string $key, array $attrs, string $itemsFile): Test
    {
        $test = Test::firstOrNew(['key' => $key]);
        $isNew = ! $test->exists;
        $test->type = Test::TYPE_SCORED;
        foreach (['name', 'description', 'intro', 'general_content'] as $f) {
            if ($isNew || blank($test->{$f})) {
                $test->setTranslations($f, $attrs[$f]);
            }
        }
        if ($isNew) {
            $test->config = $attrs['config'];
            $test->active = true;
            $test->position = $key === 'eneagrama' ? 10 : 11;
            $test->ai_enabled = true;
        }
        $test->profiles_from_test_id = $attrs['profiles_from_test_id'] ?? null;
        $test->save();

        foreach (self::SCALES as $k => [$name, $color, $group]) {
            $scale = $test->scales()->firstOrNew(['key' => $k]);
            if (! $scale->exists) {
                $scale->setTranslation('name', 'es', $name);
            }
            $scale->color = $color;
            $scale->group = $group;
            $scale->position = (int) $k;
            $scale->save();
        }

        if ($test->items()->count() === 0) {
            $items = json_decode(file_get_contents(database_path('data/'.$itemsFile)), true);
            foreach ($items as $i => $it) {
                $item = new TestItem(['test_id' => $test->id, 'position' => $i + 1, 'group_key' => (string) $it['group'], 'weights' => $it['weights']]);
                $item->setTranslation('statement', 'es', $it['statement']);
                $item->save();
            }
        }

        if (! isset($attrs['profiles_from_test_id']) && $test->ownProfiles()->count() === 0) {
            $profiles = json_decode(file_get_contents(database_path('data/enneagram_profiles.json')), true);
            foreach ($profiles as $k => $p) {
                $profile = new TestProfile(['test_id' => $test->id, 'scale_key' => (string) $k]);
                foreach ($p as $field => $value) {
                    $profile->setTranslation($field, 'es', is_array($value) ? implode("\n", $value) : $value);
                }
                $profile->save();
            }
        }

        if ($test->rules()->count() === 0) {
            $rules = [
                ['gap', '<=', '2', 'Dos tipos muy igualados', 'Tus dos puntuaciones más altas están casi empatadas. Lee los dos perfiles y fíjate en cuál describe mejor tu motivación de fondo, no solo tu conducta. Con el tiempo, uno de los dos suele resultar más propio.'],
                ['unknown_count', '>=', '15', 'Muchas respuestas con interrogante', 'Has dejado bastantes enunciados en «?». Puede ser prudencia, o puede ser que todavía te cueste observarte. Repite el test dentro de unas semanas, después de un tiempo de autoobservación.'],
                ['group_extrovert_pct', '>=', '65', 'Predominio extrovertido', 'Tus puntuaciones se concentran en los tipos de carácter extrovertido (2, 3, 7 y 8). Tu energía tiende a ir hacia fuera: hacia las personas, los objetivos o la acción.'],
                ['group_introvert_pct', '>=', '65', 'Predominio introvertido', 'Tus puntuaciones se concentran en los tipos de carácter introvertido (1, 4, 5 y 6). Tu energía tiende a ir hacia dentro: hacia la reflexión, los criterios o la seguridad.'],
            ];
            foreach ($rules as $i => [$ind, $op, $val, $title, $content]) {
                $rule = new TestResultRule(['test_id' => $test->id, 'indicator' => $ind, 'operator' => $op, 'value' => $val, 'position' => $i]);
                $rule->setTranslation('title', 'es', $title);
                $rule->setTranslation('content', 'es', $content);
                $rule->save();
            }
        }

        return $test;
    }
}
