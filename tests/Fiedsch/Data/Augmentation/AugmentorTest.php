<?php

use Fiedsch\Data\Augmentation\Augmentor;


class AugmentorTest extends PHPUnit_Framework_TestCase
{

    /**
     * Test basic data augmentation
     */
    public function testAugmentation()
    {
        $augmentor = new Augmentor();

        $data = [
            ['foo' => 'foo1'],
            ['foo' => 'foo2'],
            ['foo' => 'foo3']
        ];

        $augmentor->addRule("rule1", function (Augmentor $augmentor, array $data) {
            return ['bar' => strtoupper($data['foo'])];
        });
        $augmentor->addRule("rule2", function (Augmentor $augmentor, array $data) {
            $previousStep = $augmentor->getAugmentedSoFar();
            return ['baz' => strtolower($previousStep['bar'])];
        });

        $result = $augmentor->augment($data[0]);
        $this->assertEquals($result, ['bar' => 'FOO1', 'baz' => 'foo1']);
        $this->assertEquals($augmentor->getAugmentedSoFar(), ['bar' => 'FOO1', 'baz' => 'foo1']);

        $result = $augmentor->augment($data[1]);
        $this->assertEquals($result, ['bar' => 'FOO2', 'baz' => 'foo2']);
        $this->assertEquals($augmentor->getAugmentedSoFar(), ['bar' => 'FOO2', 'baz' => 'foo2']);

        $result = $augmentor->augment($data[2]);
        $this->assertEquals($augmentor->getAugmentedSoFar(), ['bar' => 'FOO3', 'baz' => 'foo3']);

    }

    /**
     * with a fresh Augmentor $a $a['foo'] is not set
     */
    public function testAppendToUnsetProperty()
    {
        $a = new Augmentor();
        $a->appendTo('foo', 1);
        $this->assertEquals($a['foo'], [1]);
    }

    /**
     * append to a previously set array
     */
    public function testAppendToExistingArrayProperty()
    {
        $a = new Augmentor();
        $a['foo'] = [1];
        $a->appendTo('foo', 2);
        $this->assertEquals($a['foo'], [1, 2]);

        $a->appendTo('foo', "3");
        $this->assertEquals($a['foo'], [1, 2, '3']);
    }

    /**
     * append to a previously set scalar value. The property
     * should now be an array.
     */
    public function testAppendToExistingScalarProperty()
    {
        $a = new Augmentor();
        $a['foo'] = 1;
        $a->appendTo('foo', 2);
        $this->assertEquals($a['foo'], [1, 2]);
    }

    /**
     * append to an array and overwrite the values of previously set array keys.
     * (i.e. do what array_merge() does).
     */
    public function testAppendToWithArrayParameter()
    {

        $a = new Augmentor();
        $a['foo'] = [1, 2, 3];
        $a->appendTo('foo', ['a' => 'a']);
        $this->assertEquals($a['foo'], [1, 2, 3, 'a' => 'a']);

        $a->appendTo('foo', ['a' => 'A']);
        $this->assertEquals($a['foo'], [1, 2, 3, 'a' => 'A']);

        $a = new Augmentor();
        $a['foo'] = ['a' => 'A', 'b' => 'b'];
        $a->appendTo('foo', ['a' => 'a', 'b' => 'b']);
        $this->assertEquals($a['foo'], ['a' => 'a', 'b' => 'b']);

        $a->appendTo('foo', ['b' => 'B']);
        $this->assertEquals($a['foo'], ['a' => 'a', 'b' => 'B']);
    }

}
