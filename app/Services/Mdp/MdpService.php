<?php

namespace App\Services\Mdp;

use App\Models\Mdp\MdpDay;
use App\Models\Mdp\MdpEnrollment;
use App\Models\Mdp\MdpMonth;
use App\Models\Mdp\MdpText;
use App\Models\Mdp\MdpWeek;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Calendar and selection rules of the Método Devocional Proyectivo.
 *
 * - A week ends on the enrollment's rest day and starts six days earlier.
 * - A month is a block of four consecutive weeks counted from the first week.
 * - When a period holds a single candidate text, it is selected automatically.
 */
class MdpService
{
    public const DEFAULT_REVIEW_TIMES_MORNING = ['13:30', '20:30', '23:00'];

    public const DEFAULT_REVIEW_TIMES_EVENING = ['07:30', '13:30', '17:30'];

    public function enroll(User $user, array $data): MdpEnrollment
    {
        $readingTime = $data['reading_time'] ?? '07:00';
        $evening = (int) substr($readingTime, 0, 2) >= 18;

        return MdpEnrollment::create([
            'user_id' => $user->id,
            'mode' => $data['mode'] ?? MdpEnrollment::MODE_FULL,
            'rest_weekday' => $data['rest_weekday'] ?? 6,
            'reading_time' => $readingTime,
            'review_times' => $data['review_times'] ?? ($evening ? self::DEFAULT_REVIEW_TIMES_EVENING : self::DEFAULT_REVIEW_TIMES_MORNING),
            'minimum_alert_time' => $data['minimum_alert_time'] ?? '21:00',
            'started_at' => CarbonImmutable::now($user->timezone)->startOfDay(),
        ]);
    }

    public function dayFor(MdpEnrollment $enrollment, CarbonInterface $date): MdpDay
    {
        $date = CarbonImmutable::parse($date)->startOfDay();

        return MdpDay::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'date' => $date->toDateString()],
            ['status' => $enrollment->isRestDay($date) ? MdpDay::STATUS_REST : MdpDay::STATUS_PENDING],
        );
    }

    public function weekFor(MdpEnrollment $enrollment, CarbonInterface $date): MdpWeek
    {
        $date = CarbonImmutable::parse($date)->startOfDay();
        $daysUntilRest = ($enrollment->rest_weekday - $date->dayOfWeek + 7) % 7;
        $endsOn = $date->addDays($daysUntilRest);
        $startsOn = $endsOn->subDays(6);

        return MdpWeek::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'starts_on' => $startsOn->toDateString()],
            ['ends_on' => $endsOn->toDateString()],
        );
    }

    public function firstWeekStart(MdpEnrollment $enrollment): CarbonImmutable
    {
        $daysUntilRest = ($enrollment->rest_weekday - $enrollment->started_at->dayOfWeek + 7) % 7;

        return $enrollment->started_at->addDays($daysUntilRest)->subDays(6);
    }

    public function monthFor(MdpEnrollment $enrollment, CarbonInterface $date): MdpMonth
    {
        $date = CarbonImmutable::parse($date)->startOfDay();
        $first = $this->firstWeekStart($enrollment);
        $index = intdiv(max(0, $first->diffInDays($date, false)), 28) + 1;
        $startsOn = $first->addDays(28 * ($index - 1));

        return MdpMonth::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'index' => $index],
            ['starts_on' => $startsOn->toDateString(), 'ends_on' => $startsOn->addDays(27)->toDateString()],
        );
    }

    /** Add a text to a day, respecting the 12-word limit and the single-text auto selection. */
    public function addText(MdpDay $day, string $body, ?string $noteType = null, ?string $note = null): MdpText
    {
        $body = trim(preg_replace('/\s+/u', ' ', $body));
        $words = MdpText::countWords($body);

        if ($words === 0 || $words > MdpText::MAX_WORDS) {
            throw new \InvalidArgumentException(__('El texto debe tener entre 1 y :max palabras.', ['max' => MdpText::MAX_WORDS]));
        }

        $text = $day->texts()->create([
            'position' => ($day->texts()->max('position') ?? 0) + 1,
            'body' => $body,
            'word_count' => $words,
            'note_type' => $noteType,
            'note' => $note,
        ]);

        $this->applyDayAutoSelection($day);

        return $text;
    }

    public function removeText(MdpText $text): void
    {
        $day = $text->day;
        $text->delete();
        $this->applyDayAutoSelection($day);
    }

    public function selectDayText(MdpDay $day, MdpText $text): void
    {
        $day->texts()->update(['is_day_text' => false]);
        $text->forceFill(['is_day_text' => true])->save();
    }

    /** A day with exactly one text selects it without asking. */
    public function applyDayAutoSelection(MdpDay $day): void
    {
        $texts = $day->texts()->get();

        if ($texts->count() === 1) {
            $texts->first()->forceFill(['is_day_text' => true])->save();
        } elseif ($texts->where('is_day_text', true)->isEmpty()) {
            // nothing selected yet: leave it to the user
        }
    }

    /** A week with a single day text selects it as week text. */
    public function applyWeekAutoSelection(MdpWeek $week): void
    {
        $dayTexts = $week->dayTexts();

        if ($dayTexts->count() === 1 && ! $week->week_text_id) {
            $week->forceFill(['week_text_id' => $dayTexts->first()->id])->save();
        }
    }

    /** A month with a single week text selects it as month text. */
    public function applyMonthAutoSelection(MdpMonth $month): void
    {
        $weekTexts = $month->weeks()->filter(fn (MdpWeek $w) => $w->week_text_id);

        if ($weekTexts->count() === 1 && ! $month->month_text_id) {
            $month->forceFill(['month_text_id' => $weekTexts->first()->week_text_id])->save();
        }
    }

    public function selectWeekText(MdpWeek $week, MdpText $text): void
    {
        $week->forceFill(['week_text_id' => $text->id])->save();
        $this->applyMonthAutoSelection($this->monthFor($week->enrollment, $week->starts_on));
    }

    public function selectMonthText(MdpMonth $month, MdpText $text): void
    {
        $month->forceFill(['month_text_id' => $text->id, 'closed_at' => now()])->save();
    }

    /** Whether the month that ended before $date still needs its text chosen. */
    public function pendingMonthClose(MdpEnrollment $enrollment, CarbonInterface $date): ?MdpMonth
    {
        $date = CarbonImmutable::parse($date)->startOfDay();
        $current = $this->monthFor($enrollment, $date);

        if ($current->index === 1) {
            return null;
        }

        $previous = MdpMonth::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('index', $current->index - 1)
            ->first() ?? $this->monthFor($enrollment, $current->starts_on->subDay());

        $this->applyMonthAutoSelection($previous);

        return $previous->month_text_id ? null : $previous;
    }

    /** Month texts grouped in blocks of twelve: the yearly gallery. */
    public function yearGallery(MdpEnrollment $enrollment): array
    {
        return $enrollment->months()
            ->whereNotNull('month_text_id')
            ->with('monthText')
            ->orderBy('index')
            ->get()
            ->groupBy(fn (MdpMonth $m) => intdiv($m->index - 1, 12) + 1)
            ->all();
    }

    public function markReview(MdpDay $day): MdpDay
    {
        if ($day->reviews_done < 3) {
            $day->increment('reviews_done');
        }

        return $day->refresh();
    }

    public function markWeekReview(MdpWeek $week): MdpWeek
    {
        if ($week->reviews_done < 3) {
            $week->increment('reviews_done');
        }

        return $week->refresh();
    }

    /** Suggested reading order, book-by-book, with the historical OT interleaved. */
    public static function readingOrder(): array
    {
        return [
            'Epístolas de Juan', 'Evangelio de Juan', 'Epístolas de Pedro', 'Evangelio de Mateo',
            'Epístola de Santiago', 'Epístola a los Hebreos', 'Evangelio de Marcos', 'Proverbios',
            'Evangelio de Lucas', 'Eclesiastés', 'Cantares', 'Hechos de los Apóstoles', 'Salmos',
            'Epístola a los Romanos', '1 y 2 Epístolas a los Corintios', 'Isaías', 'Epístola a los Gálatas',
            'Epístola a los Efesios', 'Jeremías', 'Epístola a los Filipenses', 'Epístola a los Colosenses',
            '1 y 2 Epístolas a los Tesalonicenses', 'Daniel', '1 y 2 Epístolas a Timoteo', 'Epístola a Tito',
            'Epístola a Filemón', 'Epístola de Judas', 'Apocalipsis', 'Oseas', 'Joel', 'Amós', 'Abdías', 'Jonás',
            'Miqueas', 'Nahum', 'Habacuc', 'Sofonías', 'Hageo', 'Zacarías', 'Malaquías', 'Ezequiel',
        ];
    }
}
