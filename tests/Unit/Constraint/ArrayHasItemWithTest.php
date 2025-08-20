<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/ArrayHasItemWithTest.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Unit\Constraint;

use ArrayIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitArrayAsserts\Tests\Utils\TestConstraint;
use SebastianBergmann\Exporter\Exporter;

/**
 * PHPUnit unit test for the ArrayHasItemWith constraint.
 *
 * @see ArrayHasItemWith
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith
 */
class ArrayHasItemWithTest extends TestCase
{
    /**
     *
     * @param int              $index
     * @param Constraint|mixed $constraint
     * @param string           $expectedDescription
     */
    #[DataProvider('dataProviderSelfDescribing')]
    public function testSelfDescribing(int $index, $constraint, string $expectedDescription): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'toString' => $this->once() ]);

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);
        $this->assertSame($expectedDescription, $itemConstraint->toString());
    }

    /**
     * @return array[]
     */
    public static function dataProviderSelfDescribing(): array
    {
        return self::getTestDataSets('testSelfDescribing');
    }

    /**
     * @dataProvider dataProviderEvaluate
     *
     * @param int $index
     * @param Constraint|mixed $constraint
     * @param mixed $other
     * @param mixed $expectedEvaluationValue
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate(int $index, $constraint, $other, $expectedEvaluationValue): void
    {
        $mockedConstraint = $this->mockConstraint(
            $constraint,
            [ 'evaluate' => $this->once() ],
            [ $expectedEvaluationValue, '', true ]
        );

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);

        self::assertCallableThrowsNot(
            self::callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class
        );
    }

    /**
     * @return array
     */
    public static function dataProviderEvaluate(): array
    {
        return self::getTestDataSets('testEvaluate');
    }

    /**
     *
     * @param int $index
     * @param Constraint|mixed $constraint
     * @param mixed $other
     * @param mixed $expectedEvaluationValue
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluateFail')]
    public function testEvaluateFail(
        int $index,
        $constraint,
        $other,
        $expectedEvaluationValue,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraint = $this->mockConstraint(
            $constraint,
            [ 'evaluate' => $this->once() ],
            [ $expectedEvaluationValue, '', true ]
        );

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);

        self::assertCallableThrows(
            self::callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            sprintf($expectedExceptionMessage, (new Exporter)->export($other))
        );
    }

    /**
     * @return array
     */
    public static function dataProviderEvaluateFail(): array
    {
        return self::getTestDataSets('testEvaluateFail');
    }

    /**
     *
     * @param int $index
     * @param Constraint|mixed $constraint
     * @param mixed $other
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderPreEvaluateFail')]
    public function testPreEvaluateFail(int $index, $constraint, $other, string $expectedExceptionMessage): void
    {
        $mockedConstraint = $this->mockConstraint($constraint);

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);

        self::assertCallableThrows(
            self::callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            sprintf($expectedExceptionMessage, (new Exporter)->export($other))
        );
    }

    /**
     * @return array
     */
    public static function dataProviderPreEvaluateFail(): array
    {
        return self::getTestDataSets('testPreEvaluateFail');
    }

    /**
     * @throws \Throwable
     */
    public static function testIteratorWithIntermediatePointer(): void
    {
        $itemConstraint = new ArrayHasItemWith(2, new TestConstraint([ 'matches' => true ]));
        $other = new class extends ArrayIterator {
            public function __construct()
            {
                parent::__construct([ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ]);

                // move pointer after item #4
                foreach ($this as $value) {
                    if ($value === 4) {
                        break;
                    }
                }
            }
        };

        self::assertCallableThrowsNot(
            self::callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class
        );

        self::assertTrue($other->valid());
        self::assertSame(4, $other->current());
    }

    /**
     * @throws \Throwable
     */
    public static function testGeneratorWithIntermediatePointer(): void
    {
        $expectedException = ExpectationFailedException::class;
        $expectedExceptionMessage = 'Failed asserting that %s is an array that has a value at index 2 which exists.';

        $itemConstraint = new ArrayHasItemWith(2, new TestConstraint([ 'toString' => 'exists' ]));
        $other = (function () {
            for ($i = 1; $i <= 10; $i++) {
                yield $i;
            }
        })();

        // move pointer after item #2
        $other->next();
        $other->next();

        self::assertCallableThrows(
            self::callableProxy([ $itemConstraint, 'evaluate' ], $other),
            $expectedException,
            sprintf($expectedExceptionMessage, (new Exporter)->export($other))
        );
    }

    /**
     *
     * @param int              $index
     * @param Constraint|mixed $constraint
     * @param int              $expectedCount
     */
    #[DataProvider('dataProviderCountable')]
    public function testCountable(int $index, $constraint, int $expectedCount): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'count' => $this->once() ]);

        $itemConstraint = new ArrayHasItemWith($index, $mockedConstraint);
        self::assertSame($expectedCount, $itemConstraint->count());
    }

    /**
     * @return array[]
     */
    public static function dataProviderCountable(): array
    {
        return self::getTestDataSets('testCountable');
    }
}
