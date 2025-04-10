<?php /** @noinspection PhpUnusedParameterInspection */

declare(strict_types=1);

use Fiedsch\Data\Augmentation\Augmentor;
use PHPUnit\Framework\TestCase;


class AugmentorTest extends TestCase
{
    /**
     * Mostly only an example of how to add a utility function with Pimple
     */
    public function testAddFunctionAugmentation(): void
    {
        $augmentor = new Augmentor();
        $augmentor['delim'] = '|';

        // two possible ways:

        /*
        $augmentor['func'] = function($c) {
            return function($value) use ($c) {
                return $c['delim'].strtoupper($value).$c['delim'];
            };
        };
        */

        // or: (see https://pimple.symfony.com/#protecting-parameters)

        $augmentor['func'] = $augmentor->protect(function($value) use ($augmentor) {
            return $augmentor['delim'].strtoupper($value).$augmentor['delim'];
        });

        $this->assertEquals('|FOO|', $augmentor['func']('foo'));
        $this->assertEquals('|BAR|', $augmentor['func']('bar'));
    }

    /**
     * Regeln sollen in der Reihenfolge des Hinzufügens aufgerufen werden
     */
    public function testRulesAreCalledInOrder(): void
    {
        $augmentor = new Augmentor();
        $augmentor->addRule('aaa', function(Augmentor $augmentor, array $data) { return ['one' =>1]; });
        $augmentor->addRule('ccc', function(Augmentor $augmentor, array $data) { return ['two'=>2]; });
        $augmentor->addRule('bbb', function(Augmentor $augmentor, array $data) { return ['three'=>3]; });

        $this->assertEquals(json_encode(['one'=>1, 'two'=>2, 'three'=>3]), json_encode($augmentor->augment([])));
    }

    /**
     * Eine festgelegte Reihenfolge der Spalten bei der Ausgabe wird eingehalten
     */
    public function testColumnOrderSpecification(): void
    {
        $augmentor = new Augmentor();
        $augmentor->addRule('aaa', function(Augmentor $augmentor, array $data) { return ['one'=>1]; });
        $augmentor->addRule('ccc', function(Augmentor $augmentor, array $data) { return ['two'=>2]; });
        $augmentor->addRule('bbb', function(Augmentor $augmentor, array $data) { return ['three'=>3]; });

        $this->assertFalse($augmentor->hasColumnOrderSpecification());
        $augmentor->setColumnOutputOrder(['one', 'three', 'two']);
        $this->assertTrue($augmentor->hasColumnOrderSpecification());

        $this->assertEquals(json_encode(['one'=>1, 'three'=>3, 'two'=>2]), json_encode($augmentor->augment([])));
    }

    /**
     * Wenn wir die Ausgabereihenfolge festlegen, müssen wir alle dort angegebenen Spalten auch erzeugen
     */
    public function testColumnOrderSpecificationWithMissingColumn(): void
    {
        $augmentor = new Augmentor();
        $augmentor->addRule('aaa', function(Augmentor $augmentor, array $data) { return ['one'=>1]; });
        $augmentor->addRule('ccc', function(Augmentor $augmentor, array $data) { return ['two'=>2]; });

        $this->assertFalse($augmentor->hasRequiredColumnsSpecification());
        $augmentor->setColumnOutputOrder(['one', 'two', 'three']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("does not exist in augmented data");
        $augmentor->augment([]);
    }

    /**
     * Wenn wir die Ausgabereihenfolge festlegen, müssen wir alle erzeiugten Spalten benennen.
     */
    public function testColumnOrderSpecificationWithExtraColumn(): void
    {
        $augmentor = new Augmentor();
        $augmentor->addRule('aaa', function(Augmentor $augmentor, array $data) { return ['one'=>1]; });
        $augmentor->addRule('bbb', function(Augmentor $augmentor, array $data) { return ['two'=>2]; });
        $augmentor->addRule('ccc', function(Augmentor $augmentor, array $data) { return ['three'=>3]; });
        $augmentor->addRule('ddd', function(Augmentor $augmentor, array $data) { return ['four'=>4]; });

        $this->assertFalse($augmentor->hasRequiredColumnsSpecification());
        $augmentor->setColumnOutputOrder(['one', 'two', 'three']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("found keys not specified in column order");
        $augmentor->augment([]);
    }

    /**
     * Festgelegte Reihenfolge und benötigte Spalten müssen zusammenpassen
     */
    public function testColumnOrderAndRequiredColumnsSpecifications(): void
    {
        $augmentor = new Augmentor();
        $augmentor->addRule('aaa', function(Augmentor $augmentor, array $data) { return ['one'=>1]; });
        $augmentor->addRule('ccc', function(Augmentor $augmentor, array $data) { return ['two'=>2]; });
        $augmentor->addRule('bbb', function(Augmentor $augmentor, array $data) { return ['three'=>3]; });
        $augmentor->addRule('ddd', function(Augmentor $augmentor, array $data) { return ['drei'=>3]; });

        $augmentor->setRequiredColumns(['one', 'two', 'three']);
        $augmentor->setColumnOutputOrder(['one', 'three', 'drei']);

        $this->expectException(RuntimeException::class);
        $augmentor->augment([]);
    }

    public function testGetRequiredColumns(): void
    {
        $augmentor = new Augmentor();

        $augmentor->setRequiredColumns(['one', 'two', 'three']);
        $this->assertEquals(['one', 'two', 'three'], $augmentor->getRequiredColumns());
    }

    public function testGetColumnOutputOrder(): void
    {
        $augmentor = new Augmentor();

        $augmentor->setColumnOutputOrder(['one', 'two', 'three']);
        $this->assertEquals(['one', 'two', 'three'], $augmentor->getColumnOutputOrder());
    }
    /**
     * Test basic data augmentation
     */
    public function testAugmentation(): void
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
        $this->assertEquals(['bar' => 'FOO1', 'baz' => 'foo1'], $result);
        $this->assertEquals(['bar' => 'FOO1', 'baz' => 'foo1'], $augmentor->getAugmentedSoFar());

        $result = $augmentor->augment($data[1]);
        $this->assertEquals(['bar' => 'FOO2', 'baz' => 'foo2'], $result);
        $this->assertEquals(['bar' => 'FOO2', 'baz' => 'foo2'], $augmentor->getAugmentedSoFar());

        $augmentor->augment($data[2]);
        $this->assertEquals(['bar' => 'FOO3', 'baz' => 'foo3'], $augmentor->getAugmentedSoFar());

    }

    /**
     * with a fresh Augmentor $a['foo'] is not set
     */
    public function testAppendToUnsetProperty(): void
    {
        $a = new Augmentor();
        $a->appendTo('foo', 1);
        $this->assertEquals([1], $a['foo']);
    }

    /**
     * append to a previously set array
     */
    public function testAppendToExistingArrayProperty(): void
    {
        $a = new Augmentor();
        $a['foo'] = [1];
        $a->appendTo('foo', 2);
        $this->assertEquals([1, 2], $a['foo']);

        $a->appendTo('foo', "3");
        $this->assertEquals([1, 2, '3'], $a['foo']);
    }

    /**
     * append to a previously set scalar value. The property
     * should now be an array.
     */
    public function testAppendToExistingScalarProperty(): void
    {
        $a = new Augmentor();
        $a['foo'] = 1;
        $a->appendTo('foo', 2);
        $this->assertEquals([1, 2], $a['foo']);
    }

    /**
     * append to an array and overwrite the values of previously set array keys.
     * (i.e. do what array_merge() does).
     */
    public function testAppendToWithArrayParameter(): void
    {

        $a = new Augmentor();
        $a['foo'] = [1, 2, 3];
        $a->appendTo('foo', ['a' => 'a']);
        $this->assertEquals([1, 2, 3, 'a' => 'a'], $a['foo']);

        $a->appendTo('foo', ['a' => 'A']);
        $this->assertEquals([1, 2, 3, 'a' => 'A'], $a['foo']);

        $a = new Augmentor();
        $a['foo'] = ['a' => 'A', 'b' => 'b'];
        $a->appendTo('foo', ['a' => 'a', 'b' => 'b']);
        $this->assertEquals(['a' => 'a', 'b' => 'b'], $a['foo']);

        $a->appendTo('foo', ['b' => 'B']);
        $this->assertEquals(['a' => 'a', 'b' => 'B'], $a['foo']);
    }

    /**
     *
     */
    public function testRuleAlreadyExists(): void
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
    public function testHasRequiredColumnsSpecification(): void
    {
        $a = new Augmentor();
        $this->assertFalse($a->hasRequiredColumnsSpecification());
        $a->setRequiredColumns(['a','b']);
        $this->assertTrue($a->hasRequiredColumnsSpecification());
    }

    /**
     * call to augment() with missing rule that produces 'b' has no effect
     * as long as setRequiredColumns() was not used.
     */
    public function testWithoutRequiredColumnsSpecification(): void
    {
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
    public function testWithRequiredColumnsSpecificationMissingColumn(): void
    {
        $this->expectException(RuntimeException::class);
        $a = new Augmentor();
        $a->addRule('foo', function(Augmentor $a) { return ['a'=>42]; });
        $a->setRequiredColumns(['a','b']);
        $a->augment([]);
    }

    /**
     * call to augment() with rule that produces 'c' which is not specified
     * in setRequiredColumns() has to cause an exception.
     */
    public function testWithRequiredColumnsSpecificationExtraColumn(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('found keys not specified as required field: ["c"]');
        $a = new Augmentor();
        $a->addRule('foo', function(Augmentor $a) { return ['a'=>1,'b'=>2,'c'=>42]; });
        $a->setRequiredColumns(['a','b']);
        $a->augment([]);
    }

    public function testSetAndUnsetRules(): void
    {
        $a = new Augmentor();
        $a->addRule('foo', function(Augmentor $a) { return 'foo'; });
        $a->addRule('bar', function(Augmentor $a) { return 'bar'; });
        $this->assertEquals( ['rule.foo', 'rule.bar'], array_filter($a->keys(), fn($el) => substr($el, 0, 5) === Augmentor::PREFIX_RULE));
        $a->removeRule('foo');
        $this->assertEquals( ['rule.bar'], array_filter($a->keys(), fn($el) => substr($el, 0, 5) === Augmentor::PREFIX_RULE));
        $a->addRule('baz', function(Augmentor $a) { return 'baz'; });
        $a->clearRules();
        $this->assertEquals( [], $a->keys());

    }

}
