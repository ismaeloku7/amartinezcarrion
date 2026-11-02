<?php

namespace App\Services\Tests;

use App\Models\Tests\JohariInvitation;
use App\Models\Tests\JohariResponse;
use App\Models\Tests\Test;
use App\Models\Tests\TestResultRule;
use App\Models\Tests\TestTake;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Johari window: the user picks N words, invited people pick N words anonymously,
 * and every word of the list lands in one of four areas.
 */
class JohariService
{
    public const DEFAULTS = [
        'self_words' => 6,
        'other_words' => 6,
        'min_responses' => 3,
        'max_invitations' => 15,
        'invitation_days' => 30,
        'repeat_allowed' => true,
        'repeat_after_days' => 90,
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
            return false; // finish or close the current take first
        }
        if (! $this->config($test, 'repeat_allowed')) {
            return false;
        }

        return $last->started_at->addDays((int) $this->config($test, 'repeat_after_days'))->isPast();
    }

    public function start(User $user, Test $test): TestTake
    {
        return TestTake::create([
            'user_id' => $user->id,
            'test_id' => $test->id,
            'status' => TestTake::STATUS_SELF_PENDING,
            'data' => [
                'word_list_id' => $test->word_list_id,
                'self_words' => [],
                'min_responses' => (int) $this->config($test, 'min_responses'),
            ],
            'started_at' => now(),
        ]);
    }

    /** @param array<int, string> $wordKeys */
    public function saveSelfSelection(TestTake $take, array $wordKeys): void
    {
        $n = (int) $this->config($take->test, 'self_words');
        $valid = $take->test->wordList->words->pluck('key')->all();
        $wordKeys = array_values(array_unique(array_intersect($wordKeys, $valid)));

        if (count($wordKeys) !== $n) {
            throw new \InvalidArgumentException(__('Debes elegir exactamente :n palabras.', ['n' => $n]));
        }

        $take->forceFill([
            'data' => array_merge($take->data ?? [], ['self_words' => $wordKeys]),
            'status' => TestTake::STATUS_COLLECTING,
        ])->save();
    }

    public function createInvitation(TestTake $take, ?string $label = null, int $maxUses = 1): JohariInvitation
    {
        $max = (int) $this->config($take->test, 'max_invitations');
        $current = $take->invitations()->sum('max_uses');

        if ($current + $maxUses > $max) {
            throw new \InvalidArgumentException(__('Has alcanzado el máximo de :n invitaciones.', ['n' => $max]));
        }

        return $take->invitations()->create([
            'token' => Str::random(40),
            'label' => $label,
            'max_uses' => $maxUses,
            'expires_at' => now()->addDays((int) $this->config($take->test, 'invitation_days')),
        ]);
    }

    /** @param array<int, string> $wordKeys */
    public function respond(JohariInvitation $invitation, array $wordKeys, ?string $fingerprint = null): JohariResponse
    {
        if (! $invitation->isUsable()) {
            throw new \RuntimeException(__('Este enlace ya no está disponible.'));
        }

        $take = $invitation->take;
        $n = (int) $this->config($take->test, 'other_words');
        $valid = $take->test->wordList->words->pluck('key')->all();
        $wordKeys = array_values(array_unique(array_intersect($wordKeys, $valid)));

        if (count($wordKeys) !== $n) {
            throw new \InvalidArgumentException(__('Debes elegir exactamente :n palabras.', ['n' => $n]));
        }

        if ($fingerprint && $take->responses()->where('fingerprint', $fingerprint)->exists()) {
            throw new \RuntimeException(__('Ya has respondido a esta invitación.'));
        }

        $response = $take->responses()->create([
            'invitation_id' => $invitation->id,
            'words' => $wordKeys,
            'fingerprint' => $fingerprint,
        ]);

        $invitation->increment('uses');
        $this->recompute($take);

        return $response;
    }

    /** Recalculate the window and store a snapshot on the take. */
    public function recompute(TestTake $take): ?array
    {
        $take->refresh();
        $responses = $take->responses;
        $min = (int) $take->data('min_responses', self::DEFAULTS['min_responses']);

        if ($responses->count() < $min) {
            return null;
        }

        $result = $this->compute(
            $take->test->wordList->words->pluck('key')->all(),
            $take->data('self_words', []),
            $responses->pluck('words')->all(),
        );

        $take->forceFill([
            'result' => $result,
            'status' => $take->status === TestTake::STATUS_CLOSED ? TestTake::STATUS_CLOSED : TestTake::STATUS_RESULTS,
            'completed_at' => $take->completed_at ?? now(),
        ])->save();

        return $result;
    }

    /**
     * Pure computation, unit-testable.
     *
     * @param  array<int, string>  $allWords
     * @param  array<int, string>  $selfWords
     * @param  array<int, array<int, string>>  $otherSelections
     */
    public function compute(array $allWords, array $selfWords, array $otherSelections): array
    {
        $counts = [];
        foreach ($otherSelections as $selection) {
            foreach ($selection as $w) {
                $counts[$w] = ($counts[$w] ?? 0) + 1;
            }
        }

        $areas = ['open' => [], 'blind' => [], 'hidden' => [], 'unknown' => []];

        foreach ($allWords as $w) {
            $bySelf = in_array($w, $selfWords, true);
            $byOthers = ($counts[$w] ?? 0) > 0;

            $area = match (true) {
                $bySelf && $byOthers => 'open',
                ! $bySelf && $byOthers => 'blind',
                $bySelf && ! $byOthers => 'hidden',
                default => 'unknown',
            };

            $areas[$area][] = ['word' => $w, 'count' => $counts[$w] ?? 0];
        }

        foreach (['open', 'blind'] as $a) {
            usort($areas[$a], fn ($x, $y) => $y['count'] <=> $x['count']);
        }

        $total = max(1, count($allWords));
        $selfCount = max(1, count($selfWords));
        $known = ['open' => count($areas['open']), 'blind' => count($areas['blind']), 'hidden' => count($areas['hidden'])];
        arsort($known);

        $indicators = [
            'open_count' => count($areas['open']),
            'blind_count' => count($areas['blind']),
            'hidden_count' => count($areas['hidden']),
            'unknown_count' => count($areas['unknown']),
            'open_pct' => round(count($areas['open']) / $total * 100, 1),
            'blind_pct' => round(count($areas['blind']) / $total * 100, 1),
            'hidden_pct' => round(count($areas['hidden']) / $total * 100, 1),
            'unknown_pct' => round(count($areas['unknown']) / $total * 100, 1),
            'dominant_area' => array_key_first($known),
            'agreement_pct' => round(count($areas['open']) / $selfCount * 100, 1),
            'responses' => count($otherSelections),
            'top_blind' => array_slice(array_column($areas['blind'], 'word'), 0, 3),
        ];

        return ['areas' => $areas, 'indicators' => $indicators, 'computed_at' => now()->toIso8601String()];
    }

    /** Rules whose condition the take satisfies. */
    public function matchingRules(TestTake $take)
    {
        $indicators = $take->result['indicators'] ?? [];

        return $take->test->rules->filter(fn (TestResultRule $r) => $r->matches($indicators))->values();
    }

    public function close(TestTake $take): void
    {
        $this->recompute($take);
        $take->forceFill(['status' => TestTake::STATUS_CLOSED, 'closed_at' => now()])->save();
        $take->invitations()->whereNull('revoked_at')->update(['revoked_at' => now()]);
    }

    /** Words that moved between areas from one take to another, for the evolution screen. */
    public function diff(TestTake $from, TestTake $to): array
    {
        $map = fn (TestTake $t) => collect($t->result['areas'] ?? [])
            ->flatMap(fn ($words, $area) => collect($words)->mapWithKeys(fn ($w) => [$w['word'] => $area]))
            ->all();

        $a = $map($from);
        $b = $map($to);
        $moves = [];

        foreach ($b as $word => $area) {
            if (isset($a[$word]) && $a[$word] !== $area) {
                $moves[] = ['word' => $word, 'from' => $a[$word], 'to' => $area];
            }
        }

        return $moves;
    }
}
