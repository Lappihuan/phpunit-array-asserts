<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2020, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/ArrayAssertsTrait.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Unit;

use ArrayAccess;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait;
use PhrozenByte\PHPUnitArrayAsserts\Tests\Assert;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitThrowableAsserts\CachedCallableProxy;
use PhrozenByte\PHPUnitArrayAsserts\InvalidArrayAssertArgumentException;
use Traversable;

/**
 * PHPUnit unit test for the ArrayAssertsTrait trait using the Assert class.
 *
 * This unit test uses Mockery instance mocking. This is affected by other unit
 * tests and will affect other unit tests. Thus we run all tests in separate
 * processes and without preserving the global state.
 *
 * @see ArrayAssertsTrait
 * @see Assert
 *
 * @covers \PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait
 * @covers \PhrozenByte\PHPUnitArrayAsserts\Tests\Assert
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ArrayAssertsTraitTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     *
     * @param Constraint[] $constraints
     * @param bool $allowMissing
     * @param bool $allowAdditional
     * @throws \Throwable
     */
    #[DataProvider('dataProviderAssociativeArray')]
    public static function testAssociativeArray(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $array
    ): void {
        self::mockConstraintInstance(
            AssociativeArray::class,
            [ $constraints, $allowMissing, $allowAdditional ],
            [ $array, '' ]
        );

        $callableProxy = new CachedCallableProxy(
            [ Assert::class, 'associativeArray' ],
            $constraints,
            $allowMissing,
            $allowAdditional
        );

        self::assertCallableThrowsNot($callableProxy, InvalidArrayAssertArgumentException::class);
        self::assertInstanceOf(AssociativeArray::class, $callableProxy->getReturnValue());
    }

    /**
     *
     * @param Constraint[] $constraints
     * @param bool $allowMissing
     * @param bool $allowAdditional
     * @param array|ArrayAccess $array
     * @throws \Throwable
     */
    #[DataProvider('dataProviderAssociativeArray')]
    public static function testAssertAssociativeArray(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $array
    ): void {
        self::mockConstraintInstance(
            AssociativeArray::class,
            [ $constraints, $allowMissing, $allowAdditional ],
            [ $array, '' ]
        );

        self::assertCallableThrowsNot(
            self::callableProxy(
                [ Assert::class, 'assertAssociativeArray' ],
                $constraints,
                $array,
                $allowMissing,
                $allowAdditional
            ),
            InvalidArrayAssertArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderAssociativeArray(): array
    {
        return self::getTestDataSets('testAssociativeArray');
    }

    /**
     *
     * @param Constraint[] $constraints
     * @param bool $allowMissing
     * @param bool $allowAdditional
     * @param array|ArrayAccess $array
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderAssociativeArrayFail')]
    public function testAssertAssociativeArrayFail(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        self::mockConstraintInstance(
            AssociativeArray::class,
            [ $constraints, $allowMissing, $allowAdditional ],
            [ $array, '' ]
        );

        self::assertCallableThrows(
            self::callableProxy(
                [ Assert::class, 'assertAssociativeArray' ],
                $constraints,
                $array,
                $allowMissing,
                $allowAdditional
            ),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderAssociativeArrayFail(): array
    {
        return self::getTestDataSets('testAssociativeArrayFail');
    }

    /**
     *
     * @param int|string $key
     * @param Constraint $constraint
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasKeyWith')]
    public static function testArrayHasKeyWith(
        $key,
        Constraint $constraint,
        $array
    ): void {
        self::mockConstraintInstance(
            ArrayHasKeyWith::class,
            [ $key, $constraint ],
            [ $array, '']
        );

        $callableProxy = new CachedCallableProxy([ Assert::class, 'arrayHasKeyWith' ], $key, $constraint);
        self::assertCallableThrowsNot($callableProxy, InvalidArrayAssertArgumentException::class);
        self::assertInstanceOf(ArrayHasKeyWith::class, $callableProxy->getReturnValue());
    }

    /**
     *
     * @param int|string $key
     * @param Constraint $constraint
     * @param array|ArrayAccess $array
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasKeyWith')]
    public static function testAssertArrayHasKeyWith(
        $key,
        Constraint $constraint,
        $array
    ): void {
        self::mockConstraintInstance(
            ArrayHasKeyWith::class,
            [ $key, $constraint ],
            [ $array, '' ]
        );

        self::assertCallableThrowsNot(
            self::callableProxy([ Assert::class, 'assertArrayHasKeyWith' ], $key, $constraint, $array),
            InvalidArrayAssertArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderArrayHasKeyWith(): array
    {
        return self::getTestDataSets('testArrayHasKeyWith');
    }

    /**
     *
     * @param int|string $key
     * @param Constraint $constraint
     * @param array|ArrayAccess $array
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasKeyWithFail')]
    public static function testAssertArrayHasKeyWithFail(
        $key,
        Constraint $constraint,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        self::mockConstraintInstance(
            ArrayHasKeyWith::class,
            [ $key, $constraint ],
            [ $array, '' ]
        );

        self::assertCallableThrows(
            self::callableProxy([ Assert::class, 'assertArrayHasKeyWith' ], $key, $constraint, $array),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderArrayHasKeyWithFail(): array
    {
        return self::getTestDataSets('testArrayHasKeyWithFail');
    }

    /**
     *
     * @param int $minItems
     * @param int|null $maxItems
     * @param Constraint|null $constraint
     * @param bool $ignoreKeys
     * @throws \Throwable
     */
    #[DataProvider('dataProviderSequentialArray')]
    public static function testSequentialArray(
        int $minItems,
        ?int $maxItems,
        ?Constraint $constraint,
        bool $ignoreKeys,
        $array
    ): void {
        $constraintArgs = [ $minItems, $maxItems, $constraint, $ignoreKeys ];
        self::mockConstraintInstance(SequentialArray::class, $constraintArgs, [ $array, '' ]);

        $callableProxy = new CachedCallableProxy([ Assert::class, 'sequentialArray' ], ...$constraintArgs);

        self::assertCallableThrowsNot($callableProxy, InvalidArrayAssertArgumentException::class);
        self::assertInstanceOf(SequentialArray::class, $callableProxy->getReturnValue());
    }

    /**
     *
     * @param int $minItems
     * @param int|null $maxItems
     * @param Constraint|null $constraint
     * @param bool $ignoreKeys
     * @param array|Traversable $array
     * @throws \Throwable
     */
    #[DataProvider('dataProviderSequentialArray')]
    public static function testAssertSequentialArray(
        int $minItems,
        ?int $maxItems,
        ?Constraint $constraint,
        bool $ignoreKeys,
        $array
    ): void {
        $constraintArgs = [ $minItems, $maxItems, $constraint, $ignoreKeys ];
        self::mockConstraintInstance(SequentialArray::class, $constraintArgs, [ $array, '' ]);

        self::assertCallableThrowsNot(
            self::callableProxy([ Assert::class, 'assertSequentialArray' ], $array, ...$constraintArgs),
            InvalidArrayAssertArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderSequentialArray(): array
    {
        return self::getTestDataSets('testSequentialArray');
    }

    /**
     *
     * @param int $minItems
     * @param int|null $maxItems
     * @param Constraint|null $constraint
     * @param bool $ignoreKeys
     * @param array|Traversable $array
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderSequentialArrayFail')]
    public static function testAssertSequentialArrayFail(
        int $minItems,
        ?int $maxItems,
        ?Constraint $constraint,
        bool $ignoreKeys,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $constraintArgs = [ $minItems, $maxItems, $constraint, $ignoreKeys ];
        self::mockConstraintInstance(SequentialArray::class, $constraintArgs, [ $array, '' ]);

        self::assertCallableThrows(
            self::callableProxy([ Assert::class, 'assertSequentialArray' ], $array, ...$constraintArgs),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderSequentialArrayFail(): array
    {
        return self::getTestDataSets('testSequentialArrayFail');
    }

    /**
     *
     * @param int $index
     * @param Constraint $constraint
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasItemWith')]
    public static function testArrayHasItemWith(
        int $index,
        Constraint $constraint,
        $array
    ): void {
        self::mockConstraintInstance(
            ArrayHasItemWith::class,
            [ $index, $constraint ],
            [ $array, '' ]
        );

        $callableProxy = new CachedCallableProxy([ Assert::class, 'arrayHasItemWith' ], $index, $constraint);
        self::assertCallableThrowsNot($callableProxy, InvalidArrayAssertArgumentException::class);
        self::assertInstanceOf(ArrayHasItemWith::class, $callableProxy->getReturnValue());
    }

    /**
     *
     * @param int $index
     * @param Constraint $constraint
     * @param array|Traversable $array
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasItemWith')]
    public static function testAssertArrayHasItemWith(
        int $index,
        Constraint $constraint,
        $array
    ): void {
        self::mockConstraintInstance(
            ArrayHasItemWith::class,
            [ $index, $constraint ],
            [ $array, '' ]
        );

        self::assertCallableThrowsNot(
            self::callableProxy([ Assert::class, 'assertArrayHasItemWith' ], $index, $constraint, $array),
            InvalidArrayAssertArgumentException::class
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderArrayHasItemWith(): array
    {
        return self::getTestDataSets('testArrayHasItemWith');
    }

    /**
     *
     * @param int $index
     * @param Constraint $constraint
     * @param array|Traversable $array
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasItemWithFail')]
    public static function testAssertArrayHasItemWithFail(
        int $index,
        Constraint $constraint,
        $array,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        self::mockConstraintInstance(
            ArrayHasItemWith::class,
            [ $index, $constraint ],
            [ $array, '' ]
        );

        self::assertCallableThrows(
            self::callableProxy([ Assert::class, 'assertArrayHasItemWith' ], $index, $constraint, $array),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /**
     * @return array[]
     */
    public static function dataProviderArrayHasItemWithFail(): array
    {
        return self::getTestDataSets('testArrayHasItemWithFail');
    }

    /**
     * @param string     $className
     * @param array      $constructorArguments
     * @param array|null $evaluateArguments
     *
     * @return MockInterface
     */
    private static function mockConstraintInstance(
        string $className,
        array $constructorArguments = [],
        ?array $evaluateArguments = null
    ): MockInterface {
        $instanceMock = Mockery::mock('overload:' . $className, Constraint::class);

        $instanceMock->shouldReceive('__construct')
            ->with(...$constructorArguments)
            ->once();

        if ($evaluateArguments !== null) {
            $instanceMock->shouldReceive('evaluate')
                ->with(...$evaluateArguments)
                ->atMost()->once();
        } else {
            $instanceMock->shouldNotReceive('evaluate');
        }

        $instanceMock->shouldReceive([
            'count'    => 1,
            'toString' => 'is tested'
        ]);

        return $instanceMock;
    }
}
