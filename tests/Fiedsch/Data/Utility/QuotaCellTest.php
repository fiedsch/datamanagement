<?php

declare(strict_types=1);

use Fiedsch\Data\Utility\QuotaCell;
use PHPUnit\Framework\TestCase;

class QuotaCellTest extends TestCase
{

    public function testOnedimensionalArrayTarget(): void
    {
        $targets = ['x' => 10, 'y' => 20, 'z' => 30];
        $cell = new QuotaCell($targets);
        $expectedinitialcounts = ['x' => 0, 'y' => 0, 'z' => 0];
        $this->assertEquals($expectedinitialcounts, $cell->getCounts());

        $cell->add(5, 'x');
        $this->assertEquals(5, $cell->getCount('x'));
        $this->assertFalse($cell->isFull('x'));

        $this->assertFalse($cell->add(50, 'x'));
        $this->assertEquals(5, $cell->getCount('x'));
    }

    public function testUndefinedOffset(): void
    {
        $targets = ['x' => 10, 'y' => 20, 'z' => 30];
        $cell = new QuotaCell($targets);
        $this->assertFalse($cell->add(5, 'a')); // index a is not defined, so we can't add to it
    }

    public function testHasTarget(): void
    {
        $targets = ['42' => 10, '43' => 20, '44' => 12];
        $cell = new QuotaCell($targets);
        foreach ($targets as $key => $count) {
            $this->assertTrue($cell->hasTarget($key));
        }
        $this->assertFalse($cell->hasTarget('not in list'));
    }

    public function testMultidimensionalTargets(): void
    {
        $targets = [
            0 => 42,
            1 => 42,
            'a' =>
                [
                    0 => 42,
                    1 => 42,
                    'b' => [
                        0 => 42,
                        1 => 4242
                    ],
                ],
        ];

        $cell = new QuotaCell($targets);

        $this->assertEquals($targets, $cell->getTargets());

        $expectedcounts = $targets;
        array_walk_recursive($expectedcounts, function(&$value, $key) { $value = 0; });

        $this->assertEquals($expectedcounts, $cell->getCounts());

        $deepkey = ['a','b',1];

        $this->assertEquals(4242, $cell->getTarget($deepkey));

        $this->assertTrue($cell->add(4241, $deepkey));

        $this->assertEquals(4241, $cell->getCount($deepkey));

        $this->assertFalse($cell->add(2, $deepkey));

        $this->assertEquals(4241, $cell->getCount($deepkey));

        $this->assertTrue($cell->add(-4240, $deepkey));

        $this->assertEquals(1, $cell->getCount($deepkey));

    }

}
