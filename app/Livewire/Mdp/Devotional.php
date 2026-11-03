<?php

namespace App\Livewire\Mdp;

use App\Livewire\Mdp\Concerns\HasEnrollment;
use App\Models\Mdp\MdpDay;
use App\Models\Mdp\MdpEnrollment;
use App\Models\Mdp\MdpText;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Step-by-step devotional of one day. Steps depend on mode and phase.
 */
class Devotional extends Component
{
    use HasEnrollment;

    public string $date;

    public string $step = 'prayer';

    public string $newText = '';

    public ?string $noteType = null;

    public string $readingFrom = '';

    public string $readingTo = '';

    public string $prayer = '';

    public string $prayerUnderline = '';

    public ?int $underliningText = null;

    public ?int $underlineStart = null;

    public function mount(?string $date = null): void
    {
        $enrollment = $this->enrollment();
        $this->date = $date ? CarbonImmutable::parse($date)->toDateString() : $this->today()->toDateString();

        if ($enrollment->isRestDay(CarbonImmutable::parse($this->date))) {
            $this->redirectRoute('mdp.rest');

            return;
        }

        $day = $this->day();
        $this->readingFrom = $day->reading_from ?? '';
        $this->readingTo = $day->reading_to ?? '';
        $this->prayer = $day->prayer ?? '';
        $this->prayerUnderline = $day->prayer_underline ?? '';
        $this->step = $this->steps()[0];

        if (! $day->started_at) {
            $day->forceFill(['started_at' => now()])->save();
        }
    }

    protected function day(): MdpDay
    {
        return $this->mdp()->dayFor($this->enrollment(), CarbonImmutable::parse($this->date));
    }

    /** @return array<int, string> */
    public function steps(): array
    {
        $enrollment = $this->enrollment();
        $phase = $enrollment->phaseOn(CarbonImmutable::parse($this->date));

        if (! $enrollment->isFull()) {
            return ['texts', 'select', 'done'];
        }

        return $phase === 1
            ? ['prayer', 'reading', 'texts', 'select', 'written_prayer', 'done']
            : ['prayer', 'reading', 'texts', 'underline', 'select', 'visualize', 'written_prayer', 'done'];
    }

    public function next(): void
    {
        $steps = $this->steps();
        $i = array_search($this->step, $steps, true);
        $this->persistStep();

        if ($i !== false && $i < count($steps) - 1) {
            $this->step = $steps[$i + 1];
        }

        if ($this->step === 'done') {
            $this->finish();
        }
    }

    public function back(): void
    {
        $steps = $this->steps();
        $i = array_search($this->step, $steps, true);
        if ($i > 0) {
            $this->step = $steps[$i - 1];
        }
    }

    public function goTo(string $step): void
    {
        if (in_array($step, $this->steps(), true)) {
            $this->persistStep();
            $this->step = $step;
        }
    }

    protected function persistStep(): void
    {
        $day = $this->day();

        if (in_array($this->step, ['reading', 'texts'], true)) {
            $this->saveReadingRange();
        }
        if ($this->step === 'select' && $day->texts()->where('is_day_text', true)->doesntExist()) {
            throw ValidationException::withMessages(['select' => __('Elige el texto del día.')]);
        }
        if ($this->step === 'texts' && $day->texts()->doesntExist()) {
            throw ValidationException::withMessages(['newText' => __('Anota al menos un texto.')]);
        }
        if ($this->step === 'visualize') {
            $day->forceFill(['visualized_at' => now()])->save();
        }
        if ($this->step === 'written_prayer') {
            $this->validate(['prayer' => ['required', 'string', 'max:2000']]);
            $day->forceFill(['prayer' => $this->prayer, 'prayer_underline' => $this->prayerUnderline ?: null])->save();
        }
    }

    public function addText(): void
    {
        $this->validate(['newText' => ['required', 'string', 'max:300']]);

        try {
            $this->mdp()->addText($this->day(), $this->newText, $this->noteType);
            $this->newText = '';
            $this->noteType = null;
        } catch (\InvalidArgumentException $e) {
            $this->addError('newText', $e->getMessage());
        }
    }

    public function removeText(int $textId): void
    {
        $text = $this->day()->texts()->findOrFail($textId);
        $this->mdp()->removeText($text);
    }

    public function selectText(int $textId): void
    {
        $day = $this->day();
        $this->mdp()->selectDayText($day, $day->texts()->findOrFail($textId));
    }

    /** Tap the first and the last word to underline a span. */
    public function tapWord(int $textId, int $index): void
    {
        if ($this->underliningText !== $textId || $this->underlineStart === null) {
            $this->underliningText = $textId;
            $this->underlineStart = $index;

            return;
        }

        $text = $this->day()->texts()->findOrFail($textId);
        $text->forceFill(['underline' => [min($this->underlineStart, $index), max($this->underlineStart, $index)]])->save();
        $this->underliningText = null;
        $this->underlineStart = null;
    }

    public function clearUnderline(int $textId): void
    {
        $this->day()->texts()->findOrFail($textId)->forceFill(['underline' => null])->save();
    }

    public function finish(?int $durationSeconds = null): void
    {
        $day = $this->day();
        $day->forceFill([
            'status' => MdpDay::STATUS_DONE,
            'finished_at' => now(),
            'duration_seconds' => $durationSeconds ?? $day->duration_seconds,
        ])->save();

        $this->mdp()->applyDayAutoSelection($day);
        $this->mdp()->applyWeekAutoSelection($this->mdp()->weekFor($this->enrollment(), $day->date));
        $this->step = 'done';
    }

    /** "If some day we cannot dedicate the ideal time": a 5-10 minute version still counts. */
    public function finishMinimum(): void
    {
        $day = $this->day();
        if ($day->texts()->doesntExist()) {
            throw ValidationException::withMessages(['newText' => __('Anota al menos un texto, aunque sea uno solo.')]);
        }
        $this->mdp()->applyDayAutoSelection($day);
        $day->forceFill(['status' => MdpDay::STATUS_MINIMUM, 'finished_at' => now()])->save();
        $this->mdp()->applyWeekAutoSelection($this->mdp()->weekFor($this->enrollment(), $day->date));
        $this->step = 'done';
    }

    /** Saved as soon as a field loses focus, so the header survives a reload. */
    public function updatedReadingFrom(): void
    {
        $this->saveReadingRange();
    }

    public function updatedReadingTo(): void
    {
        $this->saveReadingRange();
    }

    protected function saveReadingRange(): void
    {
        $this->day()->forceFill([
            'reading_from' => trim($this->readingFrom) ?: null,
            'reading_to' => trim($this->readingTo) ?: null,
        ])->save();
    }

    public function saveDuration(int $seconds): void
    {
        $this->day()->forceFill(['duration_seconds' => $seconds])->save();
    }

    public function render()
    {
        $enrollment = $this->enrollment();
        $day = $this->day()->load('texts');

        return view('livewire.mdp.devotional', [
            'enrollment' => $enrollment,
            'day' => $day,
            'texts' => $day->texts,
            'dayText' => $day->texts->firstWhere('is_day_text', true),
            'steps' => $this->steps(),
            'phase' => $enrollment->phaseOn($day->date),
            'maxWords' => MdpText::MAX_WORDS,
            'warnWords' => MdpText::WARN_WORDS,
            'isFull' => $enrollment->mode === MdpEnrollment::MODE_FULL,
        ])->layout('components.layouts.app', $this->layoutData(__('Devocional'), route('mdp.today')));
    }
}
