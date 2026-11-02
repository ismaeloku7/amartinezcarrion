<?php

namespace App\Livewire\Tests\Concerns;

use App\Models\Tests\Test;
use App\Services\Tests\JohariService;

/**
 * Two-step word picking shared by the self-selection and the invited-person pages:
 * first the positive words, then the uncomfortable ones.
 */
trait PicksWordsInTwoSteps
{
    public string $wordStep = 'positive';

    public array $positive = [];

    public array $negative = [];

    abstract protected function wordTest(): Test;

    abstract protected function who(): string; // self|other

    protected function johari(): JohariService
    {
        return app(JohariService::class);
    }

    public function needed(string $group): int
    {
        return $group === 'negative'
            ? $this->johari()->negativeCount($this->wordTest(), $this->who())
            : (int) $this->johari()->config($this->wordTest(), $this->who().'_positive');
    }

    public function toggle(string $key): void
    {
        $groups = $this->johari()->groupedKeys($this->wordTest());
        $group = in_array($key, $groups['negative'], true) ? 'negative' : 'positive';
        $list = $this->{$group};

        if (in_array($key, $list, true)) {
            $list = array_values(array_diff($list, [$key]));
        } elseif (count($list) < $this->needed($group)) {
            $list[] = $key;
        }

        $this->{$group} = $list;
    }

    public function goNegative(): void
    {
        if (count($this->positive) === $this->needed('positive')) {
            $this->wordStep = $this->needed('negative') > 0 ? 'negative' : 'positive';
        }
    }

    public function goPositive(): void
    {
        $this->wordStep = 'positive';
    }

    public function selectionComplete(): bool
    {
        return count($this->positive) === $this->needed('positive')
            && count($this->negative) === $this->needed('negative');
    }

    /** @return array<int, string> */
    public function selectedKeys(): array
    {
        return array_merge($this->positive, $this->negative);
    }

    protected function wordsFor(string $group)
    {
        $words = $this->wordTest()->wordList->words;

        return $group === 'negative'
            ? $words->filter(fn ($w) => JohariService::isNegative($w))->values()
            : $words->reject(fn ($w) => JohariService::isNegative($w))->values();
    }

    protected function splitExisting(array $keys): void
    {
        $groups = $this->johari()->groupedKeys($this->wordTest());
        $this->positive = array_values(array_intersect($keys, $groups['positive']));
        $this->negative = array_values(array_intersect($keys, $groups['negative']));
    }
}
