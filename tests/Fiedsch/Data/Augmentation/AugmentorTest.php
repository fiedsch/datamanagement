<?php

use Fiedsch\Data\Augmentation\Augmentor;
use PHPUnit\Framework\TestCase;


class AugmentorTest extends TestCase
{

  /**
   * Regeln sollen in der Reihenfolge des Hinzufügens aufgerufen werden
   */
  public function testRulesAreCalledInOrder()
  {
    $augmentor = new Augmentor();
    $augmentor->addRule('aaa', function(Augmentor $augmentor, array $data) { return ['one'=>1]; });
    $augmentor->addRule('ccc', function(Augmentor $augmentor, array $data) { return ['two'=>2]; });
    $augmentor->addRule('bbb', function(Augmentor $augmentor, array $data) { return ['three'=>3]; });

    $this->assertEquals(json_encode(['one'=>1, 'two'=>2, 'three'=>3]), json_encode($augmentor->augment([])));
  }

  /**
   * Mostly only an example of how to add a utility function with Pimple
   */
  public function testAddFunctionAugmentation()
  {
      $augmentor = new Augmentor();
      $augmentor['delim'] = '|';

      $augmentor['func'] = function($c) {
        return function($value) use ($c) {
          return $c['delim'].strtoupper($value).$c['delim'];
        };
      };
      $this->assertEquals('|FOO|', $augmentor['func']('foo'));
      $this->assertEquals('|BAR|', $augmentor['func']('bar'));
    }

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

        /*$result = */
        $augmentor->augment($data[2]);
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

    /**
     *
     */
    public function testRuleAlreadyExists()
    {
        $this->expectException(RuntimeException::class);
        $a = new Augmentor();
        $a->addRule('foo', function(Augmentor $augmentor) { return 'foo'; });
        $a->addRule('bar', function(Augmentor $augmentor) { return 'bar'; });
        $a->addRule('foo', function(Augmentor $augmentor) { return 'foo again'; });
    }

    /**
     *
     */
    public function testHasRequiredColumnsSpecification() {
        $a = new Augmentor();
        $this->assertFalse($a->hasRequiredColumnsSpecification());
        $a->setRequiredColumns(['a','b']);
        $this->assertTrue($a->hasRequiredColumnsSpecification());
    }

    /**
     * call to augment() with missing rule that produces 'b' has no effect
     * as long as setRequiredColumns() was not used.
     */
    public function testWithoutRequiredColumnsSpecification() {
        $a = new Augmentor();
        $a->addRule('foo', function(Augmentor $a) { return ['foo'=>42]; });
        $a->augment([]);

        $this->expectException(RuntimeException::class);
        $a->setRequiredColumns(['b','foo']);
        $a->augment([]);

    }

    /**
     * call to augment() with missing rule that produces 'b' has to cause
     * an exception.
     */
    public function testWithRequiredColumnsSpecificationMissingColumn() {
        $this->expectException(RuntimeException::class);
        $a = new Augmentor();
        $a->addRule('foo', function(Augmentor $a) { return ['a'=>42]; });
        $a->setRequiredColumns(['a','b']);
        $a->augment([]);
    }

    /**
     * call to augment() with rule that produces 'c' which is not specified
     * in setRequiredColumns() has to cause an exception.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage found keys not specified as required field: ["c"]
     */
    public function testWithRequiredColumnsSpecificationExtraColumn() {
        $a = new Augmentor();
        $a->addRule('foo', function(Augmentor $a) { return ['a'=>1,'b'=>2,'c'=>42]; });
        $a->setRequiredColumns(['a','b']);
        $a->augment([]);
    }

}
