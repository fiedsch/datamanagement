<?php

declare(strict_types=1);

use Fiedsch\Data\Utility\VariablenameMapper;
use PHPUnit\Framework\TestCase;

class VariablenameMapperTest extends TestCase
{

    /**
     *
     */
    public function testMappingFound(): void
    {
        $names = ['a', 'b', 'c', 'd'];
        $mapper = new VariablenameMapper($names);
        foreach ($names as $i => $name) {
            $this->assertEquals($i, $mapper->getColumnNumber($name));
        }
    }

    /**
     *
     */
    public function testMappingNotFound(): void
    {
        $names = ['a', 'b', 'c', 'd'];
        $mapper = new VariablenameMapper($names);
        $this->assertEquals(-1, $mapper->getColumnNumber('not_in_array'));
        // has to throw a \RuntimeException:
        $mapper = new VariablenameMapper($names, true);
        $this->expectException(RuntimeException::class);
        $this->assertEquals(-1, $mapper->getColumnNumber('not_in_array'));
    }

    /**
     *
     */
    public function testBadConstructorInput(): void
    {
        // has to throw a \RuntimeException as we have duplicate name 'a'
        $this->expectException(RuntimeException::class);
        new VariablenameMapper(['a', 'b', 'a']);
    }

    /**
     *
     */
    public function testBadConstructorInput2(): void
    {
        // has to throw a \RuntimeException as we have an empty name
        $this->expectException(RuntimeException::class);
        new VariablenameMapper(['a', 'b', '']);
    }

    public function testGetMapping(): void
    {
        $names = ['a', 'b', 'c'];
        $expectedMapping = ['a' => 0, 'b' => 1, 'c' => 2];

        $mapper = new VariablenameMapper($names);
        $this->assertEquals($mapper->getMapping(), $expectedMapping);

        $names = ['a ', ' b', ' c '];
        $mapper = new VariablenameMapper($names);
        $this->assertEquals($mapper->getMapping(), $expectedMapping);
    }
}
