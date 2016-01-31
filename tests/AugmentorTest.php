<?php

use Fiedsch\Data\Augmentation\Augmentor;


class AugmentorTest extends PHPUnit_Framework_TestCase {

    /**
     * Test basic data augmentation
     */
    public function testAugmentation() {
        $a = new Augmentor();

        $data = [
            ['foo' => 'foo1'],
            ['foo' => 'foo2'],
            ['foo' => 'foo3']
        ];

        $a->addRule("rule1", function($a, $data) {
                return ['bar' => strtoupper($data['foo'])];
        });
        $a->addRule("rule2", function($a, $data) {
            $prev = $a->getAugmentedSoFar();
            $this->assertEquals($prev, ['bar'=>strtoupper($data['foo'])]);
            return ['baz' => strtolower($prev['bar'])];
        });

        $result = $a->augment($data[0]);
        $this->assertEquals($result, ['data'=>$data[0], 'augmented'=>['bar'=>'FOO1','baz'=>'foo1']]);
        $this->assertEquals($a->getAugmentedSoFar(), ['bar'=>'FOO1','baz'=>'foo1']);

        $result = $a->augment($data[1]);
        $this->assertEquals($result, ['data'=>$data[1], 'augmented'=>['bar'=>'FOO2','baz'=>'foo2']]);
        $this->assertEquals($a->getAugmentedSoFar(), ['bar'=>'FOO2','baz'=>'foo2']);

        $result = $a->augment($data[2]);
        $this->assertEquals($a->getAugmentedSoFar(), ['bar'=>'FOO3','baz'=>'foo3']);

    }

    /**
     * with a fresh Augmentor $a $a['foo'] is not set
     */
    public function testAppendToUnsetProperty() {
        $a = new Augmentor();
        $a->appendTo('foo', 1);
        $this->assertEquals($a['foo'], [1]);
    }

    /**
     * append to a previously set array
     */
    public function testAppendToExistingArrayProperty() {
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
    public function testAppendToExistingScalarProperty() {
        $a = new Augmentor();
        $a['foo'] = 1;
        $a->appendTo('foo', 2);
        $this->assertEquals($a['foo'], [1,2]);
    }

    /**
     * append to an array and overwrite the values of previously set array keys.
     * (i.e. do what array_merge() does).
     */
    public function testAppendToWithArrayParameter() {

        $a = new Augmentor();
        $a['foo'] = [1,2,3];
        $a->appendTo('foo', ['a'=>'a']);
        $this->assertEquals($a['foo'], [1,2,3,'a'=>'a']);

        $a->appendTo('foo', ['a'=>'A']);
        $this->assertEquals($a['foo'], [1,2,3,'a'=>'A']);

        $a = new Augmentor();
        $a['foo'] = ['a'=>'A','b'=>'b'];
        $a->appendTo('foo', ['a'=>'a','b'=>'b']);
        $this->assertEquals($a['foo'], ['a'=>'a','b'=>'b']);

        $a->appendTo('foo', ['b'=>'B']);
        $this->assertEquals($a['foo'], ['a'=>'a', 'b'=>'B']);
    }

}
