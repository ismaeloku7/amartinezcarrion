<?php

namespace App\Services\Reminders;

use App\Models\Cards\CardsEnrollment;
use App\Models\Mdp\MdpEnrollment;
use App\Notifications\ReminderNotification;
use App\Services\Cards\CardsService;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;

/**
 * Runs every minute. For each enrollment, compares the user's local HH:MM with the
 * configured times and sends the matching reminder. Matching the exact minute keeps it idempotent.
 */
class ReminderService
{
    public function __construct(private MdpService $mdp, private CardsService $cards) {}

    public function run(CarbonImmutable $nowUtc): int
    {
        return $this->runMdp($nowUtc) + $this->runCards($nowUtc);
    }

    public function runMdp(CarbonImmutable $nowUtc): int
    {
        $sent = 0;

        MdpEnrollment::query()->with('user')->whereHas('user.pushSubscriptions')->chunk(200, function ($enrollments) use ($nowUtc, &$sent) {
            foreach ($enrollments as $e) {
                $local = $nowUtc->setTimezone($e->user->timezone ?: 'UTC');
                $hm = $local->format('H:i');
                $today = $local->startOfDay();
                App::setLocale($e->user->locale ?: config('app.locale'));

                $isRest = $e->isRestDay($today);
                $day = $e->days()->whereDate('date', $today)->with('texts')->first();
                $dayText = $day?->texts->firstWhere('is_day_text', true);

                if ($hm === $e->reading_time) {
                    if ($isRest) {
                        $this->send($e->user, 'mdp', __('Día de descanso'), __('Repasa el día anterior y elige el texto de la semana.'), route('mdp.rest'), 'mdp-rest');
                    } else {
                        $pending = $this->mdp->pendingMonthClose($e, $today);
                        $body = $pending ? __('Hoy también toca elegir el texto del mes.') : __('Oración, lectura y anotación de los textos. Repasa antes la página de ayer.');
                        $this->send($e->user, 'mdp', __('Tu devocional de hoy'), $body, route('mdp.today'), 'mdp-reading');
                    }
                    $sent++;
                }

                foreach ($e->review_times as $i => $time) {
                    if ($hm !== $time) {
                        continue;
                    }
                    if ($isRest) {
                        $week = $this->mdp->weekFor($e, $today);
                        if ($week->week_text_id && $week->reviews_done < 3) {
                            $this->send($e->user, 'mdp', __('Repaso del texto de la semana'), __('Intenta recordarlo antes de abrir.'), route('mdp.week'), 'mdp-review-'.$i);
                            $sent++;
                        }
                    } elseif ($dayText && $day->reviews_done < 3) {
                        $this->send($e->user, 'mdp', __('Repaso :n de 3', ['n' => $i + 1]), __('Intenta recordar tu texto de hoy antes de abrirlo.'), route('mdp.today'), 'mdp-review-'.$i);
                        $sent++;
                    }
                }

                if ($e->minimum_alert_time && $hm === $e->minimum_alert_time && ! $isRest && ! ($day?->isCompleted())) {
                    $this->send($e->user, 'mdp', __('Aún no has hecho el devocional'), __('Dedícale al menos 10 minutos, o incluso 5. Lo importante es no romper la cadena.'), route('mdp.devotional'), 'mdp-minimum');
                    $sent++;
                }
            }
        });

        return $sent;
    }

    public function runCards(CarbonImmutable $nowUtc): int
    {
        $sent = 0;

        CardsEnrollment::query()->with('user')->whereHas('user.pushSubscriptions')->chunk(200, function ($enrollments) use ($nowUtc, &$sent) {
            foreach ($enrollments as $e) {
                $local = $nowUtc->setTimezone($e->user->timezone ?: 'UTC');
                $hm = $local->format('H:i');
                $today = $local->startOfDay();
                App::setLocale($e->user->locale ?: config('app.locale'));

                if ($e->isRestDay($today) || $e->unlocked_until === 0) {
                    continue;
                }

                $day = $e->days()->whereDate('date', $today)->first();

                if ($hm === $e->morning_time && ! $day?->chosen_card_id) {
                    $group = $this->cards->groupForDate($e, $today);
                    $this->send($e->user, 'cards', __('Repaso de la mañana'), __('Hoy toca el grupo :n. Repasa sus fichas y escoge la del día.', ['n' => $group]), route('cards.review'), 'cards-morning');
                    $sent++;
                }

                foreach ($e->review_times as $i => $time) {
                    if ($hm === $time && $day?->chosen_card_id && $day->reviews_done < 3) {
                        $this->send($e->user, 'cards', __('Repaso :n de 3', ['n' => $i + 1]), __('Intenta recordar tu ficha de hoy antes de mirarla.'), route('cards.day'), 'cards-review-'.$i);
                        $sent++;
                    }
                }

                if ($hm === $e->evening_time && $e->tracking_enabled && $day?->chosen_card_id && $day->reviews_done >= 3 && $day->rating === null) {
                    $this->send($e->user, 'cards', __('Valoración del día'), __('¿Cuánto has tenido presente la ficha de hoy? Apenas, bastante o mucho.'), route('cards.today'), 'cards-rating');
                    $sent++;
                }
            }
        });

        return $sent;
    }

    private function send($user, string $app, string $title, string $body, string $url, string $tag): void
    {
        try {
            $user->notify(new ReminderNotification($app, $title, $body, $url, $tag));
        } catch (\Throwable $e) {
            report($e); // one bad subscription must not stop the rest of the run
        }
    }
}
