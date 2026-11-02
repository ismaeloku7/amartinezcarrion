<?php

namespace App\Services\Contents;

use App\Models\Tests\Test;
use Illuminate\Support\Facades\Blade;

/**
 * Replaces [[shortcodes]] inside article HTML with designed Blade components, so the
 * author can drop rich blocks (type cards, diagrams, test calls to action) into any text.
 */
class ShortcodeRenderer
{
    public function render(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        return preg_replace_callback('/\[\[([a-z0-9\-]+)(?::([a-z0-9\-_]+))?\]\]/i', function ($m) {
            $name = strtolower($m[1]);
            $arg = $m[2] ?? null;

            return match ($name) {
                'eneatipos' => $this->component('contents.enneagram-types', ['testKey' => $arg ?: 'eneagrama']),
                'eneagrama-diagrama' => $this->component('contents.enneagram-diagram', []),
                'eneagrama-tabla' => $this->component('contents.enneagram-table', ['testKey' => $arg ?: 'eneagrama']),
                'test' => $this->testCta($arg),
                default => '',
            };
        }, $html);
    }

    protected function testCta(?string $key): string
    {
        $test = $key ? Test::query()->where('key', $key)->where('active', true)->first() : null;

        return $test ? $this->component('contents.test-cta', ['test' => $test]) : '';
    }

    protected function component(string $view, array $data): string
    {
        return Blade::render('<x-'.$view.' '.collect($data)->map(fn ($v, $k) => ':'.$k.'="$'.$k.'"')->implode(' ').' />', $data);
    }
}
