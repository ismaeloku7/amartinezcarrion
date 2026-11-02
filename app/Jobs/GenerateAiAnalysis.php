<?php

namespace App\Jobs;

use App\Models\Tests\TestTake;
use App\Services\Tests\AiAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAiAnalysis implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(public TestTake $take) {}

    public function handle(AiAnalysisService $service): void
    {
        try {
            $service->generate($this->take);
        } catch (\Throwable $e) {
            report($e);
            $result = $this->take->fresh()->result;
            unset($result['ai_pending']);
            $result['ai_error'] = $e->getMessage();
            $this->take->forceFill(['result' => $result])->save();
        }
    }
}
