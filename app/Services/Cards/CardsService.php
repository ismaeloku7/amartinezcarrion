<?php

namespace App\Services\Cards;

use App\Models\Cards\Card;
use App\Models\Cards\CardsDay;
use App\Models\Cards\CardsEnrollment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Group rotation and daily flow of the 42-card programme.
 *
 * - Cards unlock as the reader finishes each section of the book.
 * - A group joins the rotation once it holds one unlocked card.
 * - Until the six groups are complete, each active day moves to the next available group.
 * - Once complete, each weekday has a fixed group: day 1 is the day after the rest day.
 */
class CardsService
{
    public const DEFAULT_REVIEW_TIMES = ['13:30', '20:30', '23:00'];

    public function enroll(User $user, array $data): CardsEnrollment
    {
        return CardsEnrollment::create([
            'user_id' => $user->id,
            'rest_weekday' => $data['rest_weekday'] ?? 6,
            'morning_time' => $data['morning_time'] ?? '07:30',
            'review_times' => $data['review_times'] ?? self::DEFAULT_REVIEW_TIMES,
            'evening_time' => $data['evening_time'] ?? '22:30',
            'started_at' => CarbonImmutable::now($user->timezone)->startOfDay(),
            'unlocked_until' => (int) ($data['unlocked_until'] ?? 0),
            'tracking_enabled' => true,
        ]);
    }

    /** Which group is due on a date, or null on the rest day or before any card is unlocked. */
    public function groupForDate(CardsEnrollment $enrollment, CarbonInterface $date): ?int
    {
        $date = CarbonImmutable::parse($date)->startOfDay();

        if ($enrollment->isRestDay($date)) {
            return null;
        }

        $existing = $enrollment->days()->whereDate('date', $date)->first();
        if ($existing?->group_index) {
            return $existing->group_index;
        }

        $available = $enrollment->availableGroups();
        if ($available === []) {
            return null;
        }

        if ($enrollment->allGroupsComplete()) {
            $dayIndex = (($date->dayOfWeek - $enrollment->rest_weekday - 1) % 7 + 7) % 7 + 1; // 1..6

            return min($dayIndex, Card::GROUPS);
        }

        $last = $enrollment->last_group_reviewed;
        $next = collect($available)->first(fn (int $g) => $g > $last);

        return $next ?? $available[0];
    }

    /** Get or create the day record, fixing the group for that date. */
    public function dayFor(CardsEnrollment $enrollment, CarbonInterface $date): CardsDay
    {
        $date = CarbonImmutable::parse($date)->startOfDay();
        $isRest = $enrollment->isRestDay($date);

        $day = CardsDay::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'date' => $date->toDateString()],
            ['is_rest_day' => $isRest, 'group_index' => $isRest ? null : $this->groupForDate($enrollment, $date)],
        );

        // The day may have been opened before the first card was incorporated: assign the group now.
        if (! $day->is_rest_day && ! $day->group_index && $enrollment->availableGroups() !== []) {
            $day->forceFill(['group_index' => $this->groupForDate($enrollment, $date)])->save();
        }

        return $day;
    }

    /** Cards of a group that are already unlocked. */
    public function cardsForGroup(CardsEnrollment $enrollment, int $group): Collection
    {
        return Card::query()
            ->where('group_index', $group)
            ->where('number', '<=', $enrollment->unlocked_until)
            ->orderBy('number')
            ->get();
    }

    public function completeMorningReview(CardsDay $day): void
    {
        $day->forceFill(['reviewed_at' => now()])->save();

        if ($day->group_index) {
            $day->enrollment->forceFill(['last_group_reviewed' => $day->group_index])->save();
        }
    }

    public function chooseCard(CardsDay $day, Card $card): void
    {
        if (! $card->selectable) {
            throw new \InvalidArgumentException(__('Esta ficha solo se repasa, no se escoge.'));
        }
        if ($card->group_index !== $day->group_index) {
            throw new \InvalidArgumentException(__('La ficha no pertenece al grupo de hoy.'));
        }
        if ($card->number > $day->enrollment->unlocked_until) {
            throw new \InvalidArgumentException(__('Todavía no has incorporado esta ficha.'));
        }

        // Changing the card restarts the day: the three review reminders and the evening rating
        // refer to the card that is chosen at the moment they are sent.
        $changed = $day->chosen_card_id !== null && $day->chosen_card_id !== $card->id;
        $day->forceFill($changed
            ? ['chosen_card_id' => $card->id, 'reviews_done' => 0, 'rating' => null]
            : ['chosen_card_id' => $card->id])->save();
    }

    /**
     * When the next of the three reviews can be done: the configured time of review
     * number reviews_done + 1 on that day, in the reader's timezone. Null once all are done
     * or before a card is chosen.
     */
    public function nextReviewAt(CardsDay $day): ?CarbonImmutable
    {
        if (! $day->chosen_card_id || $day->reviews_done >= 3) {
            return null;
        }

        $enrollment = $day->enrollment;
        $times = array_values($enrollment->review_times ?: self::DEFAULT_REVIEW_TIMES);
        $time = $times[$day->reviews_done] ?? end($times);
        [$hour, $minute] = array_map('intval', explode(':', $time));

        return CarbonImmutable::parse($day->date->toDateString(), $enrollment->user->timezone ?? config('app.timezone'))
            ->setTime($hour, $minute);
    }

    /** A review only counts from its scheduled time: choosing the card in the morning is not a review. */
    public function reviewDue(CardsDay $day): bool
    {
        $at = $this->nextReviewAt($day);

        return $at !== null && $day->enrollment->now()->greaterThanOrEqualTo($at);
    }

    public function markReview(CardsDay $day): CardsDay
    {
        if ($this->reviewDue($day)) {
            $day->increment('reviews_done');
        }

        return $day->refresh();
    }

    /** Undo a review marked by mistake. Dropping below three reviews also clears the day's rating. */
    public function unmarkReview(CardsDay $day): CardsDay
    {
        if ($day->reviews_done > 0) {
            $day->forceFill(['reviews_done' => $day->reviews_done - 1, 'rating' => null])->save();
        }

        return $day->refresh();
    }

    /** The evening rating comes after the three reviews, not before. */
    public function canRate(CardsDay $day): bool
    {
        return $day->chosen_card_id !== null && $day->reviews_done >= 3;
    }

    public function rate(CardsDay $day, int $rating): void
    {
        if ($rating < 1 || $rating > 3) {
            throw new \InvalidArgumentException('rating 1..3');
        }
        if (! $this->canRate($day)) {
            throw new \InvalidArgumentException(__('Podrás valorar el día cuando hayas hecho los tres repasos.'));
        }
        $day->forceFill(['rating' => $rating])->save();
    }

    /** Incorporate the next card (or jump to a given number) after reading its section. */
    public function unlock(CardsEnrollment $enrollment, ?int $upTo = null): Card
    {
        $target = min(Card::TOTAL, max($enrollment->unlocked_until + 1, (int) $upTo));
        $enrollment->forceFill(['unlocked_until' => $target])->save();

        return Card::query()->where('number', $target)->firstOrFail();
    }

    /** Whether the evening rating should be requested today. */
    public function shouldTrack(CardsEnrollment $enrollment): bool
    {
        return $enrollment->tracking_enabled;
    }

    /** First month is mandatory; after that the user is asked once whether to keep it. */
    public function trackingPromptDue(CardsEnrollment $enrollment): bool
    {
        return $enrollment->tracking_enabled
            && $enrollment->tracking_prompted_at === null
            && $enrollment->daysSinceStart() >= 30;
    }

    /** Ratings for a given calendar month as [day => rating]. */
    public function monthlySheet(CardsEnrollment $enrollment, int $year, int $month): array
    {
        return $enrollment->days()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereNotNull('rating')
            ->get()
            ->mapWithKeys(fn (CardsDay $d) => [$d->date->day => $d->rating])
            ->all();
    }

    /** Situations in which several cards tend to come to mind, from the book. */
    public static function situations(): array
    {
        return [
            __('Ante el impulso a hacer algo que no conviene') => [20, 19, 18],
            __('Cuando estamos bajos de ánimo') => [30, 23, 16, 18, 28],
            __('Cuando cuesta dormir') => [8, 9, 36],
            __('Cuando algo nos molesta') => [25, 34, 21],
            __('Cuando alguien nos ha hecho daño o hemos cometido un error') => [39, 30, 5],
        ];
    }
}
