<?php

namespace Tests\Unit;

use App\Services\Tests\JohariService;
use PHPUnit\Framework\TestCase;

class JohariComputeTest extends TestCase
{
    public function test_words_land_in_the_right_areas(): void
    {
        $service = new JohariService;
        $all = ['able', 'bold', 'calm', 'kind', 'shy', 'wise'];
        $self = ['able', 'bold', 'calm'];
        $others = [['able', 'kind', 'shy'], ['able', 'bold', 'kind']];

        $r = $service->compute($all, $self, $others);
        $words = fn (string $area) => array_column($r['areas'][$area], 'word');

        $this->assertSame(['able', 'bold'], $words('open'));
        $this->assertSame(['kind', 'shy'], $words('blind'));
        $this->assertSame(['calm'], $words('hidden'));
        $this->assertSame(['wise'], $words('unknown'));
        $this->assertSame(2, $r['areas']['open'][0]['count']);
        $this->assertSame(2, $r['indicators']['responses']);
        $this->assertSame('open', $r['indicators']['dominant_area']);
        $this->assertEqualsWithDelta(66.7, $r['indicators']['agreement_pct'], 0.1);
    }
}
