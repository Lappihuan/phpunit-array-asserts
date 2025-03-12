<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/tests/Unit/Constraint/AssociativeArrayTest.php>
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
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;

/**
 * PHPUnit unit test for the AssociativeArray constraint.
 *
 * @see AssociativeArray
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray
 */
class AssociativeArrayTest extends TestCase
{
    /**
     *
     * @param Constraint[]|mixed[] $constraints
     * @param bool                 $allowMissing
     * @param bool                 $allowAdditional
     * @param string               $expectedDescription
     */
    #[DataProvider('dataProviderSelfDescribing')]
    public function testSelfDescribing(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        string $expectedDescription
    ): void {
        $mockedConstraints = $this->mockConstraints($constraints, [ 'toString' => $this->once() ]);

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);
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
     * @param Constraint[]|mixed[] $constraints
     * @param bool $allowMissing
     * @param bool $allowAdditional
     * @param mixed $other
     * @param mixed[] $expectedEvaluationValues
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluate')]
    public function testEvaluate(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $other,
        array $expectedEvaluationValues
    ): void {
        $mockedConstraints = [];
        foreach ($constraints as $key => $constraint) {
            if (!isset($expectedEvaluationValues[$key])) {
                $mockedConstraints[$key] = $this->mockConstraint($constraint);
            } else {
                $mockedConstraints[$key] = $this->mockConstraint(
                    $constraint,
                    [ 'evaluate' => $this->once() ],
                    [ $expectedEvaluationValues[$key], '', true ]
                );
            }
        }

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);

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
     * @param Constraint[]|mixed[] $constraints
     * @param bool $allowMissing
     * @param bool $allowAdditional
     * @param mixed $other
     * @param mixed[] $expectedEvaluationValues
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderEvaluateFail')]
    public function testEvaluateFail(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $other,
        array $expectedEvaluationValues,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraints = [];
        foreach ($constraints as $key => $constraint) {
            if (!isset($expectedEvaluationValues[$key])) {
                $mockedConstraints[$key] = $this->mockConstraint($constraint);
            } else {
                $mockedConstraints[$key] = $this->mockConstraint(
                    $constraint,
                    [ 'evaluate' => $this->once() ],
                    [ $expectedEvaluationValues[$key], '', true ]
                );
            }
        }

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);

        self::assertCallableThrows(
            self::callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            $expectedExceptionMessage
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
     * @param Constraint[]|mixed[] $constraints
     * @param bool $allowMissing
     * @param bool $allowAdditional
     * @param mixed $other
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderPreEvaluateFail')]
    public function testPreEvaluateFail(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $other,
        string $expectedExceptionMessage
    ): void {
        $mockedConstraints = $this->mockConstraints($constraints, [ 'evaluate' => $this->atMost(1) ]);

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);

        self::assertCallableThrows(
            self::callableProxy([ $itemConstraint, 'evaluate' ], $other),
            ExpectationFailedException::class,
            $expectedExceptionMessage
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
     * @param Constraint[]|mixed[] $constraints
     * @param bool                 $allowMissing
     * @param bool                 $allowAdditional
     * @param int                  $expectedCount
     */
    #[DataProvider('dataProviderCountable')]
    public function testCountable(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        int $expectedCount
    ): void {
        $mockedConstraints = $this->mockConstraints($constraints, [ 'count' => $this->once() ]);

        $itemConstraint = new AssociativeArray($mockedConstraints, $allowMissing, $allowAdditional);
        $this->assertSame($expectedCount, $itemConstraint->count());
    }

    /**
     * @return array[]
     */
    public static function dataProviderCountable(): array
    {
        return self::getTestDataSets('testCountable');
    }

    /**
     * @param Constraint[]|mixed[] $constraints
     * @param InvocationOrder[]    $invocationRules
     * @param mixed[][]            $evaluateParameters
     *
     * @return Constraint[]
     */
    protected function mockConstraints(
        array $constraints,
        array $invocationRules = [],
        array $evaluateParameters = []
    ): array {
        $mockedConstraints = [];
        foreach ($constraints as $key => $constraint) {
            $constraintInvocationRules = [];
            foreach ($invocationRules as $method => $invocationRule) {
                $constraintInvocationRules[$method] = clone $invocationRule;
            }

            $mockedConstraints[$key] = $this->mockConstraint(
                $constraint,
                $constraintInvocationRules,
                $evaluateParameters[$key] ?? null
            );
        }

        return $mockedConstraints;
    }
}
