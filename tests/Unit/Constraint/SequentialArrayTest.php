<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/SequentialArrayTest.php>
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
use PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitThrowableAsserts\CallableProxy;
use SebastianBergmann\Exporter\Exporter;

/**
 * PHPUnit unit test for the SequentialArray constraint.
 *
 * @see SequentialArray
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray
 */
class SequentialArrayTest extends TestCase
{
    /**
     *
     * @param int $minItems
     * @param int|null $maxItems
     * @param Constraint|mixed|null $constraint
     * @param bool $ignoreKeys
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderInvalidParameters')]
    public function testInvalidParameters(
        int $minItems,
        ?int $maxItems,
        $constraint,
        bool $ignoreKeys,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        self::assertCallableThrows(static function () use ($minItems, $maxItems, $constraint, $ignoreKeys) {
            new SequentialArray($minItems, $maxItems, $constraint, $ignoreKeys);
        }, $expectedException, $expectedExceptionMessage);
    }

    /**
     * @return array[]
     */
    public static function dataProviderInvalidParameters(): array
    {
        return self::getTestDataSets('testInvalidParameters');
    }

    /**
     *
     * @param int                   $minItems
     * @param int|null              $maxItems
     * @param Constraint|mixed|null $constraint
     * @param bool                  $ignoreKeys
     * @param string                $expectedDescription
     */
    #[DataProvider('dataProviderSelfDescribing')]
    public function testSelfDescribing(
        int $minItems,
        ?int $maxItems,
        $constraint,
        bool $ignoreKeys,
        string $expectedDescription
    ): void {
        $mockedConstraint = null;
        if ($constraint !== null) {
            $mockedConstraint = $this->mockConstraint($constraint, [ 'toString' => $this->once() ]);
        }

        $itemConstraint = new SequentialArray($minItems, $maxItems, $mockedConstraint, $ignoreKeys);
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
     *
     * @param int $minItems
     * @param int|null $maxItems
     * @param Constraint|mixed|null $constraint
     * @param bool $ignoreKeys
     * @param mixed $other
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate(int $minItems, ?int $maxItems, $constraint, bool $ignoreKeys, $other): void
    {
        $mockedConstraint = null;
        if ($constraint !== null) {
            $mockedConstraint = $this->mockConstraint(
                $constraint,
                [ 'evaluate' => $this->exactly(count($other)) ]
            );
        }

        $itemConstraint = new SequentialArray($minItems, $maxItems, $mockedConstraint, $ignoreKeys);

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
     * @param int $minItems
     * @param int|null $maxItems
     * @param Constraint|mixed|null $constraint
     * @param bool $ignoreKeys
     * @param mixed $other
     * @param int $expectedEvaluationCount
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluateFail')]
    public function testEvaluateFail(
        int $minItems,
        ?int $maxItems,
        $constraint,
        bool $ignoreKeys,
        $other,
        int $expectedEvaluationCount,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraint = null;
        if ($constraint !== null) {
            $mockedConstraint = $this->mockConstraint(
                $constraint,
                [ 'evaluate' => $this->exactly($expectedEvaluationCount) ]
            );
        }

        $itemConstraint = new SequentialArray($minItems, $maxItems, $mockedConstraint, $ignoreKeys);

        self::assertCallableThrows(
            new CallableProxy([ $itemConstraint, 'evaluate' ], $other),
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
     * @param int $minItems
     * @param int|null $maxItems
     * @param Constraint|mixed|null $constraint
     * @param bool $ignoreKeys
     * @param mixed $other
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderPreEvaluateFail')]
    public function testPreEvaluateFail(
        int $minItems,
        ?int $maxItems,
        $constraint,
        bool $ignoreKeys,
        $other,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraint = ($constraint !== null) ? $this->mockConstraint($constraint) : null;

        $itemConstraint = new SequentialArray($minItems, $maxItems, $mockedConstraint, $ignoreKeys);

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
    public function testIteratorWithIntermediatePointer(): void
    {
        $itemConstraint = new SequentialArray();
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

        $this->assertTrue($other->valid());
        $this->assertSame(4, $other->current());
    }

    /**
     * @throws \Throwable
     */
    public function testGeneratorWithIntermediatePointer(): void
    {
        $expectedException = ExpectationFailedException::class;
        $expectedExceptionMessage = 'Failed asserting that %s is a sequential array.';

        $itemConstraint = new SequentialArray();
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
     * @param int                   $minItems
     * @param int|null              $maxItems
     * @param Constraint|mixed|null $constraint
     * @param bool                  $ignoreKeys
     * @param int                   $expectedCount
     */
    #[DataProvider('dataProviderCountable')]
    public function testCountable(
        int $minItems,
        ?int $maxItems,
        $constraint,
        bool $ignoreKeys,
        int $expectedCount
    ): void {
        $mockedConstraint = null;
        if ($constraint !== null) {
            $mockedConstraint = $this->mockConstraint($constraint, [ 'count' => $this->once() ]);
        }

        $itemConstraint = new SequentialArray($minItems, $maxItems, $mockedConstraint, $ignoreKeys);
        $this->assertSame($expectedCount, $itemConstraint->count());
    }

    /**
     * @return array[]
     */
    public static function dataProviderCountable(): array
    {
        return self::getTestDataSets('testCountable');
    }
}
