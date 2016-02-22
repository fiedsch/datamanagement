<?php

use Fiedsch\Data\Utility\UniquenessChecker;

class UniquenessCheckerTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test default comparison of values
     */
    public function testDefault()
    {
        $checker = new UniquenessChecker();
        $value = 'Andreas Fieger';
        $this->assertEquals(1, $checker->isNew($value, 'name'));

        $value = 'ANDREAS FIEGER';
        $this->assertEquals(0, $checker->isNew($value, 'name'));
    }

    /**
     * Test strict comparison of values
     */
    public function testStrict()
    {
        $checker = new UniquenessChecker();
        $value = 'Andreas Fieger';
        $this->assertEquals(1, $checker->isNew($value, 'name'));

        $value = 'ANDREAS FIEGER';
        $this->assertEquals(1, $checker->isNew($value, 'name', true));
        $this->assertEquals(0, $checker->isNew($value, 'name', true));
    }

    /**
     * Test different categories
     */
    public function testCategories()
    {
        $checker = new UniquenessChecker();
        $value = 'Andreas Fieger';
        $this->assertEquals(1, $checker->isNew($value, 'name'));

        $this->assertEquals(1, $checker->isNew($value, 'familyname'));
        $this->assertEquals(0, $checker->isNew($value, 'name'));
    }
}
