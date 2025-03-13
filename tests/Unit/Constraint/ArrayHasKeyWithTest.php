<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/ArrayHasKeyWithTest.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Unit\Constraint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use SebastianBergmann\Exporter\Exporter;

/**
 * PHPUnit unit test for the ArrayHasKeyWith constraint.
 *
 * @see ArrayHasKeyWith
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith
 */
class ArrayHasKeyWithTest extends TestCase
{
    /**
     *
     * @param string|int $key
     * @param Constraint|mixed $constraint
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderInvalidParameters')]
    public function testInvalidParameters(
        $key,
        $constraint,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        self::assertCallableThrows(static function () use ($key, $constraint) {
            new ArrayHasKeyWith($key, $constraint);
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
     * @param string|int       $key
     * @param Constraint|mixed $constraint
     * @param string           $expectedDescription
     */
    #[DataProvider('dataProviderSelfDescribing')]
    public function testSelfDescribing($key, $constraint, string $expectedDescription): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'toString' => $this->once() ]);

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);
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
     * @param string|int $key
     * @param Constraint|mixed $constraint
     * @param mixed $other
     * @param mixed $expectedEvaluationValue
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate($key, $constraint, $other, $expectedEvaluationValue): void
    {
        $mockedConstraint = $this->mockConstraint(
            $constraint,
            [ 'evaluate' => $this->once() ],
            [ $expectedEvaluationValue, '', true ]
        );

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);

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
     * @param string|int $key
     * @param Constraint|mixed $constraint
     * @param mixed $other
     * @param mixed $expectedEvaluationValue
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluateFail')]
    public function testEvaluateFail(
        $key,
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

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);

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
     * @param string|int $key
     * @param Constraint|mixed $constraint
     * @param mixed $other
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderPreEvaluateFail')]
    public function testPreEvaluateFail($key, $constraint, $other, string $expectedExceptionMessage): void
    {
        $mockedConstraint = $this->mockConstraint($constraint);

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);

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
     *
     * @param string|int       $key
     * @param Constraint|mixed $constraint
     * @param int              $expectedCount
     */
    #[DataProvider('dataProviderCountable')]
    public function testCountable($key, $constraint, int $expectedCount): void
    {
        $mockedConstraint = $this->mockConstraint($constraint, [ 'count' => $this->once() ]);

        $itemConstraint = new ArrayHasKeyWith($key, $mockedConstraint);
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
