<?php

namespace Tests\Feature;

use App\Models\Cards\Card;
use App\Models\Mdp\MdpDay;
use App\Models\Mdp\MdpEnrollment;
use App\Models\User;
use App\Notifications\ReminderNotification;
use App\Services\Cards\CardsService;
use App\Services\Mdp\MdpService;
use App\Services\Reminders\ReminderService;
use Carbon\CarbonImmutable;
use Database\Seeders\CardsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RemindersTest extends TestCase
{
    use RefreshDatabase;

    private const TZ = 'Europe/Madrid';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CardsSeeder::class);
        Notification::fake();
        $this->user = User::factory()->create(['timezone' => self::TZ]);
        $this->user->updatePushSubscription('https://push.example/1', 'k', 't', 'aes128gcm');
        $this->travelTo(CarbonImmutable::parse('2026-10-20 10:00', self::TZ));
    }

    /** Run the reminders at a local time on Tuesday 3 November 2026 (not a rest day). */
    private function runAt(string $time): int
    {
        $now = CarbonImmutable::parse('2026-11-03 '.$time, self::TZ);
        $this->travelTo($now);

        return app(ReminderService::class)->run($now->utc());
    }

    private function sentTags(): array
    {
        return Notification::sent($this->user, ReminderNotification::class)->map(fn ($n) => $n->tag)->values()->all();
    }

    public function test_a_reminder_missed_at_its_exact_minute_is_caught_up_once(): void
    {
        app(MdpService::class)->enroll($this->user, ['reading_time' => '07:00', 'rest_weekday' => 6]);

        $this->assertSame(0, $this->runAt('06:59'));
        // 07:00 and 07:01 never ran (deploy, slow previous run…): 07:02 still sends it.
        $this->assertSame(1, $this->runAt('07:02'));
        $this->assertSame(0, $this->runAt('07:03'), 'only once per day');
        $this->assertSame(0, $this->runAt('07:30'));
        $this->assertSame(['mdp-reading'], $this->sentTags());
    }

    public function test_reminders_older_than_the_grace_window_are_not_sent(): void
    {
        app(MdpService::class)->enroll($this->user, ['reading_time' => '07:00', 'rest_weekday' => 6]);

        $this->assertSame(0, $this->runAt('08:01'));
        Notification::assertNothingSent();
    }

    public function test_the_reading_reminder_is_not_sent_late_if_the_devotional_is_already_done(): void
    {
        $e = app(MdpService::class)->enroll($this->user, ['reading_time' => '07:00', 'rest_weekday' => 6]);
        $this->travelTo(CarbonImmutable::parse('2026-11-03 06:45', self::TZ));
        app(MdpService::class)->dayFor($e, CarbonImmutable::parse('2026-11-03'))->forceFill(['status' => MdpDay::STATUS_DONE])->save();

        $this->assertSame(0, $this->runAt('07:10'));
    }

    public function test_devotional_and_cards_send_their_whole_day(): void
    {
        $mdp = app(MdpService::class)->enroll($this->user, ['reading_time' => '07:00', 'rest_weekday' => 6]);
        $cards = app(CardsService::class)->enroll($this->user, ['rest_weekday' => 6, 'unlocked_until' => 3, 'morning_time' => '07:30']);

        $this->runAt('07:00');
        $this->runAt('07:30');

        $this->travelTo(CarbonImmutable::parse('2026-11-03 08:30', self::TZ));
        $day = app(MdpService::class)->dayFor($mdp, CarbonImmutable::parse('2026-11-03'));
        app(MdpService::class)->addText($day, 'Nadie puede recibir nada');
        $day->forceFill(['status' => MdpDay::STATUS_DONE])->save();
        app(MdpService::class)->applyDayAutoSelection($day->fresh());
        app(CardsService::class)->chooseCard(app(CardsService::class)->dayFor($cards, CarbonImmutable::parse('2026-11-03')), Card::where('number', 1)->first());

        foreach (['13:30', '20:30', '23:00'] as $t) {
            $this->runAt($t);
        }

        $this->assertSame([
            'mdp-reading', 'cards-morning',
            'mdp-review-0', 'cards-review-0', 'mdp-review-1', 'cards-review-1', 'mdp-review-2', 'cards-review-2',
        ], $this->sentTags());
    }

    public function test_a_failing_enrollment_does_not_block_the_others(): void
    {
        $broken = User::factory()->create(['timezone' => self::TZ]);
        $broken->updatePushSubscription('https://push.example/2', 'k', 't', 'aes128gcm');
        MdpEnrollment::create(['user_id' => $broken->id, 'mode' => 'full', 'rest_weekday' => 6, 'reading_time' => '07:00',
            'review_times' => ['07:00', '13:30', '20:30'], 'started_at' => CarbonImmutable::parse('2026-10-20')]);
        // Corrupt data that makes this enrollment throw; it is processed before the healthy one.
        MdpEnrollment::where('user_id', $broken->id)->update(['review_times' => '"not-a-list"']);
        app(MdpService::class)->enroll($this->user, ['reading_time' => '07:00', 'rest_weekday' => 6]);

        $this->runAt('07:00');

        $this->assertSame(['mdp-reading'], $this->sentTags());
    }
}
