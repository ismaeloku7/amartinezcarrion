<?php

namespace App\Services\Tests;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use App\Models\Tests\TestTake;

/**
 * Personal analysis of a scored take written by Claude from the author's own
 * type profiles, the matched rules and the person's scores. Never a clinical diagnosis.
 */
class AiAnalysisService
{
    public function isConfigured(): bool
    {
        return filled(config('services.anthropic.key'));
    }

    public function isAvailableFor(TestTake $take): bool
    {
        return $this->isConfigured() && $take->test->ai_enabled && $take->test->isScored() && $take->result !== null;
    }

    public function generate(TestTake $take): array
    {
        $client = new Client(apiKey: config('services.anthropic.key'));
        $locale = $take->user->locale ?: config('app.locale');

        try {
            $message = $client->messages->create(
                model: config('services.anthropic.model', 'claude-opus-5-5'),
                maxTokens: 6000,
                system: [['type' => 'text', 'text' => $this->systemPrompt($take), 'cacheControl' => ['type' => 'ephemeral']]],
                messages: [['role' => 'user', 'content' => $this->userPrompt($take, $locale)]],
                outputConfig: ['effort' => config('services.anthropic.effort', 'medium')],
            );
        } catch (APIStatusException $e) {
            throw new \RuntimeException(__('El servicio de análisis no está disponible ahora mismo.').' ('.($e->type?->value ?? $e->getMessage()).')', 0, $e);
        } catch (APIConnectionException $e) {
            throw new \RuntimeException(__('No se pudo conectar con el servicio de análisis.'), 0, $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new \RuntimeException(__('El servicio no ha podido generar este análisis.'));
        }

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        $ai = [
            'text' => trim($text),
            'model' => $message->model,
            'generated_at' => now()->toIso8601String(),
            'input_tokens' => $message->usage->inputTokens ?? null,
            'output_tokens' => $message->usage->outputTokens ?? null,
        ];

        $result = $take->result;
        $result['ai'] = $ai;
        unset($result['ai_pending'], $result['ai_error']);
        $take->forceFill(['result' => $result])->save();

        return $ai;
    }

    protected function systemPrompt(TestTake $take): string
    {
        $test = $take->test;
        $profiles = $test->profiles();
        $lines = [
            'Eres el asistente de Antonio Martínez, pastor y psicoterapeuta, autor del material de autoconocimiento que se te proporciona.',
            'Escribes análisis personales de los resultados de sus tests, siempre desde su marco: no hay tipos mejores ni peores; el éxito de la prueba consiste en descubrir para hacer posible la mejora; el común denominador del crecimiento es el espíritu de servicio a los demás.',
            'Reglas: no haces diagnósticos clínicos ni usas etiquetas patológicas sobre la persona; hablas de tendencias, no de sentencias; recuerdas que ningún sistema abarca a un individuo y que no conviene identificarse con una sola característica, ni con lo que uno querría ser, ni tomar rasgos de aquí y de allá.',
            'Te apoyas únicamente en el material del autor que sigue. Si algo no está en él, no lo inventes.',
            'Tono: cálido, directo, respetuoso, de tú. Formato: Markdown sencillo con títulos cortos; entre 500 y 800 palabras.',
            '',
            "# Test: {$test->name}",
            (string) $test->intro,
            '',
            '# Perfiles de los tipos, según el autor',
        ];
        foreach ($profiles as $profile) {
            $lines[] = $profile->toPromptText();
            $lines[] = '';
        }
        if (filled($test->ai_instructions)) {
            $lines[] = '# Instrucciones adicionales del autor para el análisis';
            $lines[] = (string) $test->ai_instructions;
        }

        return implode("\n", $lines);
    }

    protected function userPrompt(TestTake $take, string $locale): string
    {
        $result = $take->result;
        $scales = $take->test->scales()->get()->keyBy('key');
        $rows = [];
        foreach ($result['ranking'] as $key) {
            $rows[] = sprintf('- Tipo %s (%s): %d puntos de %d (%s%%)', $key, $scales[$key]->name ?? $key, $result['scores'][$key], $result['max'][$key], $result['pct'][$key]);
        }
        $rules = app(ScoredTestService::class)->matchingRules($take)
            ->map(fn ($r) => '- '.($r->title ? $r->title.': ' : '').$r->content)->all();

        $lines = [
            "Idioma de la respuesta: {$locale}.",
            "Nombre de la persona: {$take->user->name}.",
            'Fecha: '.now()->toDateString().'.',
            '',
            'Puntuaciones obtenidas, de mayor a menor:',
            ...$rows,
            '',
            "Respuestas con «?»: {$result['indicators']['unknown_count']}. Enunciados en blanco: {$result['indicators']['blank_count']}.",
            '',
        ];
        if ($rules) {
            $lines[] = 'Conclusiones del autor que se cumplen con estos resultados (intégralas en el análisis):';
            $lines = array_merge($lines, $rules, ['']);
        }
        $lines[] = 'Escribe el análisis con estas secciones: 1) Lo que sugieren tus resultados (tipo principal y, si la diferencia es pequeña, el secundario; qué significa esa combinación). 2) Tu zona sana: lo que puedes potenciar. 3) Sombras que conviene vigilar. 4) Tu dirección de crecimiento (integración y botón de acción del autor). 5) Tres pasos concretos para esta semana. 6) Una frase de cierre.';

        return implode("\n", $lines);
    }
}
