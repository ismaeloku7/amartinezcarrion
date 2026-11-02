<?php

namespace App\Livewire\Cards\Concerns;

use App\Models\Cards\CardsEnrollment;
use App\Services\Cards\CardsService;
use Carbon\CarbonImmutable;

trait HasEnrollment
{
    protected function enrollment(): CardsEnrollment
    {
        return auth()->user()->cardsEnrollment()->firstOrFail();
    }

    protected function today(): CarbonImmutable
    {
        return $this->enrollment()->today();
    }

    protected function cards(): CardsService
    {
        return app(CardsService::class);
    }

    protected function layoutData(string $title, ?string $back = null): array
    {
        return ['appKey' => 'cards', 'title' => $title, 'back' => $back];
    }
}
