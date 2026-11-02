<?php

namespace App\Livewire\Mdp\Concerns;

use App\Models\Mdp\MdpEnrollment;
use App\Services\Mdp\MdpService;
use Carbon\CarbonImmutable;

trait HasEnrollment
{
    protected function enrollment(): MdpEnrollment
    {
        return auth()->user()->mdpEnrollment()->firstOrFail();
    }

    protected function today(): CarbonImmutable
    {
        return $this->enrollment()->today();
    }

    protected function mdp(): MdpService
    {
        return app(MdpService::class);
    }

    protected function layoutData(string $title, ?string $back = null): array
    {
        return ['appKey' => 'mdp', 'title' => $title, 'back' => $back];
    }
}
