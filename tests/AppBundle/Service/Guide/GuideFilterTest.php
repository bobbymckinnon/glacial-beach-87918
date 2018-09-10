<?php

declare(strict_types=1);

namespace Tests\AppBundle\Command\Guide;

use AppBundle\Service\Guide\GuideFilter;
use AppBundle\Service\GuideAPI;
use Prophecy\Prophet;

class GuideFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var Prophet */
    private $prophet;

    public function setUp()
    {
        $this->prophet = new Prophet();
    }

    public function test_Handle()
    {
        $mock = $this->prophet->prophesize(GuideAPI::class);
        $mock->fetch()->willReturn([
            [
                'id' => 7413,
                'duration' => 180,
                'price' => 411,
            ],
            [
                'id' => 7099,
                'duration' => 180,
                'price' => 267,
            ],
            [
                'id' => 6979,
                'duration' => 150,
                'price' => 141,
            ],
            [
                'id' => 6811,
                'duration' => 150,
                'price' => 143,
            ],
            [
                'id' => 7495,
                'duration' => 120,
                'price' => 423,
            ],
        ]);

        $service = new GuideFilter($mock->reveal());
        $result = $service->handle([
            'budget' => 1000,
            'days' => 1,
        ]);

        $this->assertCount(1, $result['schedule']);
        $this->assertCount(3, $result['schedule'][1]);
        $this->assertEquals(819, $result['summary']['budget_spent']);
        $this->assertEquals(90, $result['summary']['time_in_relocation']);
        $this->assertEquals(3, $result['summary']['total_activities']);
    }
}
