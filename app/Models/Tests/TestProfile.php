<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class TestProfile extends Model
{
    use HasTranslations;

    protected $fillable = [
        'test_id', 'scale_key', 'title', 'subtitle', 'character', 'temperament', 'danger', 'basic_desire',
        'basic_fear', 'hidden_complaint', 'childhood_origin', 'integration', 'disintegration',
        'healthy', 'shadow', 'unhealthy', 'growth_action', 'notes', 'conclusion',
    ];

    public array $translatable = [
        'title', 'subtitle', 'character', 'temperament', 'danger', 'basic_desire', 'basic_fear',
        'hidden_complaint', 'childhood_origin', 'integration', 'disintegration', 'healthy', 'shadow',
        'unhealthy', 'growth_action', 'notes', 'conclusion',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /** Multiline trait fields are stored as text, one trait per line. */
    public function lines(string $field): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $this->{$field}))));
    }

    /** Plain-text summary used as context for the AI analysis. */
    public function toPromptText(): string
    {
        $parts = ["## {$this->scale_key}. {$this->title}"];
        foreach (['subtitle' => 'Rasgo central', 'character' => 'Carácter', 'danger' => 'Peligro', 'basic_desire' => 'Deseo básico', 'basic_fear' => 'Temor básico',
            'hidden_complaint' => 'Queja oculta', 'childhood_origin' => 'Origen infantil', 'integration' => 'Integración', 'disintegration' => 'Desintegración',
            'growth_action' => 'Botón de acción para el crecimiento', 'notes' => 'Notas', 'conclusion' => 'Conclusión del autor'] as $field => $label) {
            if (filled($this->{$field})) {
                $parts[] = "{$label}: {$this->{$field}}";
            }
        }
        foreach (['healthy' => 'Zona sana', 'shadow' => 'Zona de sombra', 'unhealthy' => 'Zona malsana'] as $field => $label) {
            if ($lines = $this->lines($field)) {
                $parts[] = "{$label}:\n- ".implode("\n- ", $lines);
            }
        }

        return implode("\n", $parts);
    }
}
