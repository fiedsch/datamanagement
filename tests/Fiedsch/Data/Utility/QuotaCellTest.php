<?php

use Fiedsch\Data\Utility\QuotaCell;

class QuotaCellTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test basic data augmentation with multidimensional targets
     */
    public function testMultidimensionalCell()
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

    /**
     * @expectedException \RuntimeException
     */
    public function testUndefinedOffset()
    {
        $targets = ['x' => 10, 'y' => 20, 'z' => 30];
        $cell = new QuotaCell($targets);
        $cell->add(5, 'a'); // index a is not defined
    }

    /**
     * test fallback to univariate target if default arguments are used
     */
    public function testScalarCell()
    {
        $cell = new QuotaCell(100);
        $this->assertTrue($cell->add(40));
        $this->assertEquals(40, $cell->getCount());
        $this->assertTrue($cell->canAdd(60));
        $this->assertFalse($cell->canAdd(61));
        $this->assertFalse($cell->isFull());

        $this->assertTrue($cell->add(100, 0, true));
        $this->assertEquals(140, $cell->getCount());
        $this->assertTrue($cell->isFull());

        // we can also subtract counts
        $this->assertTrue($cell->add(-200, 0, true));
        $this->assertEquals(-60, $cell->getCount());
        $this->assertFalse($cell->isFull());
    }

    public function testHasTarget()
    {
        $targets = ['42' => 10, '43' => 20, '44' => 12];
        $cell = new QuotaCell($targets);
        foreach ($targets as $key => $count) {
            $this->assertTrue($cell->hasTarget($key));
        }
        $this->assertFalse($cell->hasTarget('not in list'));
    }

    public function testMakeArrayKey()
    {
        $cell = new QuotaCell(0);
        $this->assertEquals('a.b.c', $cell->makeArrayKey(['a','b','c']));

        $cell->setCellPathSeparator('|');
        $this->assertEquals('a|b|c', $cell->makeArrayKey(['a','b','c']));
    }

    public function testMultidimensionalTargets()
    {
        $targets = [
            'a' => 42,
            'b' => [
                'a' => 11,
                'b' => 12,
            ],
            'c' => [
                'a' => [
                    'a' => 111,
                    'b' => 112,
                ],
                'b' => [
                    'a' => 211,
                    'b' => 212,
                ],
            ],
        ];
        $flat_targets = [
            'a'     => 42,
            'b.a'   => 11,
            'b.b'   => 12,
            'c.a.a' => 111,
            'c.a.b' => 112,
            'c.b.a' => 211,
            'c.b.b' => 212,
        ];

        $cell = new QuotaCell($targets);
        foreach ($flat_targets as $key => $count) {
            $this->assertTrue($cell->hasTarget($key));
        }
    }

}
