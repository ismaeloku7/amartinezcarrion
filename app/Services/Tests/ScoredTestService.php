<?php

namespace App\Services\Tests;

use App\Models\Tests\Test;
use App\Models\Tests\TestItem;
use App\Models\Tests\TestResultRule;
use App\Models\Tests\TestTake;
use App\Models\User;

/**
 * Yes/no questionnaires where every statement adds points to one or more scales.
 * Answers: yes (1 point), strong_yes (double, the "rounded yes"), no, unknown ("?"), blank.
 */
class ScoredTestService
{
    public const ANSWERS = ['yes', 'strong_yes', 'no', 'unknown'];

    public const DEFAULTS = [
        'strong_multiplier' => 2,
        'per_page' => 18,
        'shuffle' => false,
        'repeat_allowed' => true,
        'repeat_after_days' => 30,
    ];

    public function config(Test $test, string $key): mixed
    {
        return $test->config($key, self::DEFAULTS[$key] ?? null);
    }

    public function canStart(User $user, Test $test): bool
    {
        $last = $user->testTakes()->where('test_id', $test->id)->latest('started_at')->first();

        if (! $last) {
            return true;
        }
        if ($last->isOpen()) {
            return false;
        }
        if (! $this->config($test, 'repeat_allowed')) {
            return false;
        }

        return $last->started_at->addDays((int) $this->config($test, 'repeat_after_days'))->isPast();
    }

    public function start(User $user, Test $test): TestTake
    {
        $ids = $test->items()->pluck('id')->all();
        if ($this->config($test, 'shuffle')) {
            shuffle($ids);
        }

        return TestTake::create([
            'user_id' => $user->id,
            'test_id' => $test->id,
            'status' => TestTake::STATUS_COLLECTING,
            'data' => ['order' => $ids, 'answers' => [], 'page' => 0],
            'started_at' => now(),
        ]);
    }

    public function answer(TestTake $take, int $itemId, ?string $answer): void
    {
        if ($answer !== null && ! in_array($answer, self::ANSWERS, true)) {
            throw new \InvalidArgumentException('answer');
        }
        $data = $take->data ?? [];
        if ($answer === null) {
            unset($data['answers'][(string) $itemId]);
        } else {
            $data['answers'][(string) $itemId] = $answer;
        }
        $take->forceFill(['data' => $data])->save();
    }

    public function setPage(TestTake $take, int $page): void
    {
        $data = $take->data ?? [];
        $data['page'] = max(0, $page);
        $take->forceFill(['data' => $data])->save();
    }

    public function finish(TestTake $take): array
    {
        $result = $this->compute($take->test, $take->data('answers', []));
        $take->forceFill([
            'result' => $result,
            'status' => TestTake::STATUS_CLOSED,
            'completed_at' => now(),
            'closed_at' => now(),
        ])->save();

        return $result;
    }

    /**
     * Pure computation.
     *
     * @param  array<string, string>  $answers  item id => answer
     */
    public function compute(Test $test, array $answers): array
    {
        $scales = $test->scales()->get();
        $items = $test->items()->get();
        $multiplier = (int) $this->config($test, 'strong_multiplier');

        $scores = $max = [];
        foreach ($scales as $s) {
            $scores[$s->key] = 0;
            $max[$s->key] = 0;
        }

        $unknown = 0;
        $blank = 0;

        foreach ($items as $item) {
            /** @var TestItem $item */
            $answer = $answers[(string) $item->id] ?? null;
            $points = match ($answer) {
                'yes' => 1,
                'strong_yes' => $multiplier,
                default => 0,
            };
            if ($answer === 'unknown') {
                $unknown++;
            }
            if ($answer === null) {
                $blank++;
            }
            foreach ($item->weights as $scale => $w) {
                if (! array_key_exists($scale, $scores)) {
                    continue;
                }
                $scores[$scale] += $points * $w;
                $max[$scale] += $multiplier * $w;
            }
        }

        $ranking = collect($scores)->sortDesc()->keys()->map(fn ($k) => (string) $k)->values()->all();
        $top = $ranking[0] ?? null;
        $second = $ranking[1] ?? null;
        $pct = [];
        foreach ($scores as $k => $v) {
            $pct[$k] = $max[$k] ? round($v / $max[$k] * 100, 1) : 0;
        }

        $groups = [];
        foreach ($scales as $s) {
            if ($s->group) {
                $groups[$s->group] = ($groups[$s->group] ?? 0) + $scores[$s->key];
            }
        }
        $groupTotal = array_sum($groups) ?: 1;

        $indicators = [
            'top_type' => $top,
            'second_type' => $second,
            'gap' => $top !== null && $second !== null ? $scores[$top] - $scores[$second] : 0,
            'unknown_count' => $unknown,
            'blank_count' => $blank,
            'answered' => count($items) - $blank,
        ];
        foreach ($scores as $k => $v) {
            $indicators["score_{$k}"] = $v;
            $indicators["pct_{$k}"] = $pct[$k];
        }
        foreach ($groups as $g => $v) {
            $indicators["group_{$g}_pct"] = round($v / $groupTotal * 100, 1);
        }

        return [
            'scores' => $scores,
            'max' => $max,
            'pct' => $pct,
            'ranking' => $ranking,
            'indicators' => $indicators,
            'computed_at' => now()->toIso8601String(),
        ];
    }

    public function matchingRules(TestTake $take)
    {
        $indicators = $take->result['indicators'] ?? [];

        return $take->test->rules->filter(fn (TestResultRule $r) => $r->matches($indicators))->values();
    }

    /** Scores of every completed take, oldest first, for the evolution screen. */
    public function series(User $user, Test $test)
    {
        return $user->testTakes()->where('test_id', $test->id)->whereNotNull('result')->orderBy('completed_at')->get()
            ->map(fn (TestTake $t) => ['date' => $t->completed_at, 'scores' => $t->result['scores'], 'top' => $t->result['indicators']['top_type'] ?? null]);
    }
}
