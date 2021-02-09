<?php

declare(strict_types=1);

use Fiedsch\Data\Utility\UniquenessChecker;
use PHPUnit\Framework\TestCase;

class UniquenessCheckerTest extends TestCase
{

    /**
     * Test default comparison of values
     */
    public function testDefault(): void
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
    public function testStrict(): void
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
    public function testCategories(): void
    {
        $checker = new UniquenessChecker();
        $value = 'Andreas Fieger';
        $this->assertEquals(1, $checker->isNew($value, 'name'));

        $this->assertEquals(1, $checker->isNew($value, 'familyname'));
        $this->assertEquals(0, $checker->isNew($value, 'name'));
    }
}
