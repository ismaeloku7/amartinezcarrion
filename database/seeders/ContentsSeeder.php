<?php

namespace Database\Seeders;

use App\Models\Contents\Content;
use App\Models\Contents\Topic;
use Illuminate\Database\Seeder;

class ContentsSeeder extends Seeder
{
    public function run(): void
    {
        $root = Topic::firstOrNew(['slug' => 'autoconocimiento']);
        if (! $root->exists) {
            $root->setTranslations('name', ['es' => 'Autoconocimiento', 'en' => 'Self-knowledge']);
            $root->setTranslations('description', ['es' => 'Conocernos mejor para crecer: temperamento, carácter y personalidad.', 'en' => 'Knowing ourselves better in order to grow.']);
            $root->color = '#5b3a8c';
            $root->position = 1;
            $root->save();
        }

        $enea = Topic::firstOrNew(['slug' => 'eneagrama']);
        if (! $enea->exists) {
            $enea->parent_id = $root->id;
            $enea->setTranslations('name', ['es' => 'Eneagrama', 'en' => 'Enneagram']);
            $enea->setTranslations('description', ['es' => 'Las nueve personalidades: zona sana, sombra y crecimiento.', 'en' => 'The nine personalities.']);
            $enea->color = '#5b3a8c';
            $enea->position = 1;
            $enea->save();
        }

        $article = Content::firstOrNew(['slug' => 'los-nueve-eneatipos']);
        if (! $article->exists) {
            $article->topic_id = $enea->id;
            $article->type = Content::TYPE_ARTICLE;
            $article->setTranslations('title', ['es' => 'Los nueve eneatipos: una guía para conocerte', 'en' => 'The nine enneatypes: a guide to knowing yourself']);
            $article->setTranslations('excerpt', ['es' => 'Qué es el eneagrama, cómo se forma la personalidad, y la zona sana, la sombra y el camino de crecimiento de cada uno de los nueve tipos.']);
            $article->setTranslation('body', 'es', $this->body());
            $article->author = 'Antonio Martínez';
            $article->cover_color = '#5b3a8c';
            $article->reading_minutes = 14;
            $article->published_at = now();
            $article->position = 1;
            $article->save();
        }

        $johari = Content::firstOrNew(['slug' => 'la-ventana-de-johari']);
        if (! $johari->exists) {
            $johari->topic_id = $root->id;
            $johari->type = Content::TYPE_ARTICLE;
            $johari->setTranslations('title', ['es' => 'La ventana de Johari: lo que sé de mí y lo que ven los demás']);
            $johari->setTranslations('excerpt', ['es' => 'Cuatro áreas para entender cómo nos relacionamos: abierta, ciega, oculta y desconocida.']);
            $johari->setTranslation('body', 'es', $this->johariBody());
            $johari->author = 'Antonio Martínez';
            $johari->reading_minutes = 4;
            $johari->published_at = now();
            $johari->position = 2;
            $johari->save();
        }
    }

    protected function body(): string
    {
        return <<<'HTML'
<p class="lead">Ningún sistema puede abarcar a un individuo. Pero hay procesos y características comunes en el área de la personalidad, y conocerlos tiene una utilidad enorme: conocernos mejor, conocer mejor a los demás, conocer nuestras tendencias negativas y, sobre todo, conocer la dirección de nuestro mayor crecimiento psicológico.</p>

<div class="steps">
  <div><span>1</span><strong>Autoobservación</strong><em>Observar al otro</em></div>
  <div><span>2</span><strong>Autocomprensión</strong><em>Comprenderle</em></div>
  <div><span>3</span><strong>Transformación</strong><em>Ayudarle en su transformación</em></div>
</div>

<h2>Cómo se forma la personalidad</h2>
<p>Partimos del cuerpo físico, con su sistema nervioso y su sistema endocrino: la herencia. Sobre esa base se asienta el <strong>temperamento</strong>, que es innato. Los cuatro temperamentos clásicos son el melancólico, el flemático, el colérico y el sanguíneo, y se ordenan según dos ejes: introversión y extraversión, estabilidad e inestabilidad. No hay temperamentos buenos ni malos, mejores ni peores.</p>
<p>Al temperamento se le suma la actitud personal y el ambiente, y así se va formando el <strong>carácter</strong>. Los eneatipos describen el carácter. Y el carácter, convertido en hábitos, da lugar a la <strong>personalidad</strong>: la estructura que determina cómo sentimos, cómo pensamos y cómo actuamos.</p>
<blockquote>Los sentidos reciben estímulos; de la sensación nace el pensamiento y la emoción; de ahí los sentimientos, las actitudes y las motivaciones, y de ellas las conductas: palabras y hechos. Repetidas, se convierten en hábitos. Y los hábitos forman la personalidad.</blockquote>

<h2>Tres advertencias antes de empezar</h2>
<div class="cards">
  <div class="card warn"><strong>No te identifiques con una sola característica</strong>, por importante que sea.</div>
  <div class="card warn"><strong>No te identifiques con lo que querrías ser.</strong> Se trata de lo que eres hoy.</div>
  <div class="card warn"><strong>No tomes rasgos de aquí y de allá.</strong> Hay coherencia en la estructura de cada eneatipo.</div>
</div>

<h2>El eneagrama</h2>
<p>Los nueve tipos se disponen en un círculo. Las líneas interiores marcan hacia dónde se mueve cada tipo cuando crece (integración) y hacia dónde cae cuando se deteriora (desintegración). Los tipos 2, 3, 7 y 8 son de carácter extrovertido; el 1, el 4, el 5 y el 6, introvertido; el 9 puede ser cualquiera de los dos.</p>
[[eneagrama-diagrama]]

<h2>Cada tipo tiene tres zonas</h2>
<p>Todos los eneatipos se analizan con la misma estructura: una <strong>zona sana</strong>, en la que el tipo da lo mejor de sí; una <strong>zona de sombra</strong>, en la que las mismas tendencias empiezan a jugar en contra; y una <strong>zona malsana</strong>, de deterioro. A ellas se añaden el peligro propio de cada tipo, su deseo y su temor básicos, su queja oculta y el posible origen infantil.</p>
[[eneatipos]]

<h2>Resumen de los nueve tipos</h2>
[[eneagrama-tabla]]

<h2>Botones de acción para el crecimiento</h2>
<p>Cada tipo tiene una dirección concreta de crecimiento, algo que conviene fomentar de forma diferenciada. El común denominador, en todos los casos, para conseguir el máximo crecimiento, es el <strong>espíritu de servicio a los demás</strong>.</p>

<h2>Descubre tu eneatipo</h2>
<p>El cuestionario tiene 162 enunciados, dieciocho por cada tipo. Se responde con sí o no, y se marca «sí, totalmente» cuando un enunciado parece escrito pensando en ti. Al terminar verás tus puntuaciones en un diagrama de barras y el perfil de tu tipo principal.</p>
[[test:eneagrama]]
<p>Si quieres afinar el resultado, existe una versión más extensa, con 75 pares de enunciados:</p>
[[test:rheti]]
HTML;
    }

    protected function johariBody(): string
    {
        return <<<'HTML'
<p class="lead">La ventana de Johari cruza dos ejes: lo que yo sé de mí y lo que los demás saben de mí. De ese cruce salen cuatro áreas.</p>
<div class="cards">
  <div class="card"><strong>Área de libre relación.</strong> Lo que yo sé y los demás saben. Es el espacio de la apertura.</div>
  <div class="card"><strong>Área ciega.</strong> Lo que los demás ven y yo no. Crece cuando no quiero escuchar.</div>
  <div class="card"><strong>Área oculta.</strong> Lo que yo sé y no muestro. Crece cuando no quiero mostrarme.</div>
  <div class="card"><strong>Área desconocida.</strong> Lo que ni yo ni los demás sabemos todavía.</div>
</div>
<p>Hay situaciones en las que no queremos escuchar a los demás, y el área ciega se agranda. Otras en las que no queremos mostrarnos, y crece el área oculta. Y situaciones de apertura, en las que el área de libre relación se amplía en las dos direcciones. Esa es la dirección sana.</p>
<p>El ejercicio práctico consiste en elegir las palabras que te describen y pedir a personas de confianza que elijan las que, según ellas, te describen. Sus respuestas son anónimas. Al cruzarlas aparece tu ventana.</p>
[[test:johari]]
HTML;
    }
}
