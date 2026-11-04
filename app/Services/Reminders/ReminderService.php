<?php

namespace App\Services\Reminders;

use App\Models\Cards\CardsEnrollment;
use App\Models\Mdp\MdpEnrollment;
use App\Models\ReminderLog;
use App\Models\User;
use App\Notifications\ReminderNotification;
use App\Services\Cards\CardsService;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\App;

/**
 * Runs every minute. A reminder is due from its configured local time until GRACE_MINUTES
 * later, and is sent once per day (reminder_logs). So a run that is skipped or delayed
 * (deploy, slow previous run, cron hiccup) is caught up on the next one instead of being lost,
 * and one enrollment that fails never stops the others.
 */
class ReminderService
{
    public const GRACE_MINUTES = 60;

    public function __construct(private MdpService $mdp, private CardsService $cards) {}

    public function run(CarbonImmutable $nowUtc): int
    {
        return $this->runMdp($nowUtc) + $this->runCards($nowUtc);
    }

    public function runMdp(CarbonImmutable $nowUtc): int
    {
        $sent = 0;

        MdpEnrollment::query()->with('user')->whereHas('user.pushSubscriptions')->chunkById(200, function ($enrollments) use ($nowUtc, &$sent) {
            foreach ($enrollments as $e) {
                try {
                    $sent += $this->mdpFor($e, $nowUtc);
                } catch (\Throwable $ex) {
                    report($ex);
                }
            }
        });

        return $sent;
    }

    private function mdpFor(MdpEnrollment $e, CarbonImmutable $nowUtc): int
    {
        $sent = 0;
        $local = $nowUtc->setTimezone($e->user->timezone ?: 'UTC');
        $today = $local->startOfDay();
        App::setLocale($e->user->locale ?: config('app.locale'));

        $isRest = $e->isRestDay($today);
        $day = $e->days()->whereDate('date', $today)->with('texts')->first();
        $dayText = $day?->texts->firstWhere('is_day_text', true);

        if ($this->due($local, $e->reading_time)) {
            if ($isRest) {
                $body = __('Repasa el día anterior y elige el texto de la semana.')
                    .($this->mdp->monthClosingOn($e, $today) ? ' '.__('Hoy también toca elegir el texto del mes.') : '');
                $sent += $this->once($e->user, 'mdp-rest', $today, 'mdp', __('Día de descanso'), $body, route('mdp.rest'));
            } elseif (! $day?->isCompleted()) {
                $sent += $this->once($e->user, 'mdp-reading', $today, 'mdp', __('Tu devocional de hoy'), __('Oración, lectura y anotación de los textos. Repasa antes la página de ayer.'), route('mdp.today'));
            }
        }

        foreach (array_values($e->review_times ?? []) as $i => $time) {
            if (! $this->due($local, $time)) {
                continue;
            }
            if ($isRest) {
                $week = $this->mdp->weekFor($e, $today);
                if ($week->week_text_id && $week->reviews_done < 3) {
                    $sent += $this->once($e->user, 'mdp-review-'.$i, $today, 'mdp', __('Repaso del texto de la semana'), __('Intenta recordarlo antes de abrir.'), route('mdp.week'));
                }
            } elseif ($dayText && $day->reviews_done < 3) {
                $sent += $this->once($e->user, 'mdp-review-'.$i, $today, 'mdp', __('Repaso :n de 3', ['n' => $i + 1]), __('Intenta recordar tu texto de hoy antes de abrirlo.'), route('mdp.today'));
            }
        }

        if ($e->minimum_alert_time && $this->due($local, $e->minimum_alert_time) && ! $isRest && ! $day?->isCompleted()) {
            $sent += $this->once($e->user, 'mdp-minimum', $today, 'mdp', __('Aún no has hecho el devocional'), __('Dedícale al menos 10 minutos, o incluso 5. Lo importante es no romper la cadena.'), route('mdp.devotional'));
        }

        return $sent;
    }

    public function runCards(CarbonImmutable $nowUtc): int
    {
        $sent = 0;

        CardsEnrollment::query()->with('user')->whereHas('user.pushSubscriptions')->chunkById(200, function ($enrollments) use ($nowUtc, &$sent) {
            foreach ($enrollments as $e) {
                try {
                    $sent += $this->cardsFor($e, $nowUtc);
                } catch (\Throwable $ex) {
                    report($ex);
                }
            }
        });

        return $sent;
    }

    private function cardsFor(CardsEnrollment $e, CarbonImmutable $nowUtc): int
    {
        $sent = 0;
        $local = $nowUtc->setTimezone($e->user->timezone ?: 'UTC');
        $today = $local->startOfDay();
        App::setLocale($e->user->locale ?: config('app.locale'));

        if ($e->isRestDay($today) || $e->unlocked_until === 0) {
            return 0;
        }

        $day = $e->days()->whereDate('date', $today)->first();

        if ($this->due($local, $e->morning_time) && ! $day?->chosen_card_id) {
            $group = $this->cards->groupForDate($e, $today);
            $sent += $this->once($e->user, 'cards-morning', $today, 'cards', __('Ficha de hoy'), __('Hoy toca el grupo :n. Escoge la ficha que repasarás hoy.', ['n' => $group]), route('cards.review'));
        }

        foreach (array_values($e->review_times ?? []) as $i => $time) {
            if ($this->due($local, $time) && $day?->chosen_card_id && $day->reviews_done < 3) {
                $sent += $this->once($e->user, 'cards-review-'.$i, $today, 'cards', __('Repaso :n de 3', ['n' => $i + 1]), __('Intenta recordar tu ficha de hoy antes de mirarla.'), route('cards.day'));
            }
        }

        if ($this->due($local, $e->evening_time) && $e->tracking_enabled && $day?->chosen_card_id && $day->reviews_done >= 3 && $day->rating === null) {
            $sent += $this->once($e->user, 'cards-rating', $today, 'cards', __('Valoración del día'), __('¿Cuánto has tenido presente la ficha de hoy? Apenas, bastante o mucho.'), route('cards.today'));
        }

        return $sent;
    }

    /** From the configured HH:MM (local, today) until GRACE_MINUTES later. */
    public function due(CarbonImmutable $local, ?string $time): bool
    {
        if (! $time || ! preg_match('/^(\d{1,2}):(\d{2})/', $time, $m)) {
            return false;
        }

        $at = $local->startOfDay()->setTime((int) $m[1], (int) $m[2]);
        $late = $at->diffInMinutes($local->startOfMinute(), false);

        return $late >= 0 && $late <= self::GRACE_MINUTES;
    }

    /** Send unless this reminder already went out today. Returns 1 when sent. */
    private function once(User $user, string $key, CarbonImmutable $localDate, string $app, string $title, string $body, string $url): int
    {
        try {
            ReminderLog::create(['user_id' => $user->id, 'key' => $key, 'local_date' => $localDate->toDateString(), 'sent_at' => now()]);
        } catch (UniqueConstraintViolationException) {
            return 0; // already sent today
        }

        try {
            $user->notify(new ReminderNotification($app, $title, $body, $url, $key));
        } catch (\Throwable $e) {
            report($e); // one bad subscription must not stop the rest of the run
        }

        return 1;
    }
}
