<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * SPDX-License-Identifier: MIT
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Tests\Unit;

use ArrayAccess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PhrozenByte\PHPUnitArrayAsserts\ArrayAssertsTrait;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasItemWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\ArrayHasKeyWith;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\AssociativeArray;
use PhrozenByte\PHPUnitArrayAsserts\Constraint\SequentialArray;
use PhrozenByte\PHPUnitArrayAsserts\InvalidArrayAssertArgumentException;
use PhrozenByte\PHPUnitArrayAsserts\Tests\Assert;
use PhrozenByte\PHPUnitArrayAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitThrowableAsserts\CachedCallableProxy;
use Traversable;

class ArrayAssertsTraitTest extends TestCase
{
    /**
     * @param Constraint[] $constraints
     * @param bool         $allowMissing
     * @param bool         $allowAdditional
     * @throws \Throwable
     */
    #[DataProvider('dataProviderAssociativeArray')]
    public static function testAssociativeArray(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
              $array
    ): void {
        // Factory should not throw on valid inputs and must return the right constraint instance
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
     * @param Constraint[]        $constraints
     * @param bool                $allowMissing
     * @param bool                $allowAdditional
     * @param array|ArrayAccess   $array
     * @throws \Throwable
     */
    #[DataProvider('dataProviderAssociativeArray')]
    public static function testAssertAssociativeArray(
        array $constraints,
        bool $allowMissing,
        bool $allowAdditional,
              $array
    ): void {
        $constraints = self::ensurePassingAll($constraints);
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

    /** @return array[] */
    public static function dataProviderAssociativeArray(): array
    {
        return self::getTestDataSets('testAssociativeArray');
    }

    /**
     * @param Constraint[]        $constraints
     * @param bool                $allowMissing
     * @param bool                $allowAdditional
     * @param array|ArrayAccess   $array
     * @param string              $expectedException
     * @param string              $expectedExceptionMessage
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

    /** @return array[] */
    public static function dataProviderAssociativeArrayFail(): array
    {
        return self::getTestDataSets('testAssociativeArrayFail');
    }

    /**
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
        $callableProxy = new CachedCallableProxy([ Assert::class, 'arrayHasKeyWith' ], $key, $constraint);
        self::assertCallableThrowsNot($callableProxy, InvalidArrayAssertArgumentException::class);
        self::assertInstanceOf(ArrayHasKeyWith::class, $callableProxy->getReturnValue());
    }

    /**
     * @param int|string         $key
     * @param Constraint         $constraint
     * @param array|ArrayAccess  $array
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasKeyWith')]
    public static function testAssertArrayHasKeyWith(
        $key,
        Constraint $constraint,
        $array
    ): void {
        $constraint = self::ensurePassing($constraint);
        self::assertCallableThrowsNot(
            self::callableProxy([ Assert::class, 'assertArrayHasKeyWith' ], $key, $constraint, $array),
            InvalidArrayAssertArgumentException::class
        );
    }

    /** @return array[] */
    public static function dataProviderArrayHasKeyWith(): array
    {
        return self::getTestDataSets('testArrayHasKeyWith');
    }

    /**
     * @param int|string         $key
     * @param Constraint         $constraint
     * @param array|ArrayAccess  $array
     * @param string             $expectedException
     * @param string             $expectedExceptionMessage
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
        self::assertCallableThrows(
            self::callableProxy([ Assert::class, 'assertArrayHasKeyWith' ], $key, $constraint, $array),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /** @return array[] */
    public static function dataProviderArrayHasKeyWithFail(): array
    {
        return self::getTestDataSets('testArrayHasKeyWithFail');
    }

    /**
     * @param int              $minItems
     * @param int|null         $maxItems
     * @param Constraint|null  $constraint
     * @param bool             $ignoreKeys
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

        $callableProxy = new CachedCallableProxy(
            [ Assert::class, 'sequentialArray' ],
            ...$constraintArgs
        );

        self::assertCallableThrowsNot($callableProxy, InvalidArrayAssertArgumentException::class);
        self::assertInstanceOf(SequentialArray::class, $callableProxy->getReturnValue());
    }

    /**
     * @param int               $minItems
     * @param int|null          $maxItems
     * @param Constraint|null   $constraint
     * @param bool              $ignoreKeys
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
        if ($constraint instanceof Constraint) {
            $constraint = self::ensurePassing($constraint); // <-- add this line
        }

        $constraintArgs = [ $minItems, $maxItems, $constraint, $ignoreKeys ];

        self::assertCallableThrowsNot(
            self::callableProxy([ Assert::class, 'assertSequentialArray' ], $array, ...$constraintArgs),
            InvalidArrayAssertArgumentException::class
        );
    }

    /** @return array[] */
    public static function dataProviderSequentialArray(): array
    {
        return self::getTestDataSets('testSequentialArray');
    }

    /**
     * @param int               $minItems
     * @param int|null          $maxItems
     * @param Constraint|null   $constraint
     * @param bool              $ignoreKeys
     * @param array|Traversable $array
     * @param string            $expectedException
     * @param string            $expectedExceptionMessage
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

        self::assertCallableThrows(
            self::callableProxy([ Assert::class, 'assertSequentialArray' ], $array, ...$constraintArgs),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /** @return array[] */
    public static function dataProviderSequentialArrayFail(): array
    {
        return self::getTestDataSets('testSequentialArrayFail');
    }

    /**
     * @param int         $index
     * @param Constraint  $constraint
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasItemWith')]
    public static function testArrayHasItemWith(
        int $index,
        Constraint $constraint,
        $array
    ): void {
        $callableProxy = new CachedCallableProxy([ Assert::class, 'arrayHasItemWith' ], $index, $constraint);

        self::assertCallableThrowsNot($callableProxy, InvalidArrayAssertArgumentException::class);
        self::assertInstanceOf(ArrayHasItemWith::class, $callableProxy->getReturnValue());
    }

    /**
     * @param int                $index
     * @param Constraint         $constraint
     * @param array|Traversable  $array
     * @throws \Throwable
     */
    #[DataProvider('dataProviderArrayHasItemWith')]
    public static function testAssertArrayHasItemWith(
        int $index,
        Constraint $constraint,
        $array
    ): void {
        $constraint = self::ensurePassing($constraint);
        self::assertCallableThrowsNot(
            self::callableProxy([ Assert::class, 'assertArrayHasItemWith' ], $index, $constraint, $array),
            InvalidArrayAssertArgumentException::class
        );
    }

    /** @return array[] */
    public static function dataProviderArrayHasItemWith(): array
    {
        return self::getTestDataSets('testArrayHasItemWith');
    }

    /**
     * @param int                $index
     * @param Constraint         $constraint
     * @param array|Traversable  $array
     * @param string             $expectedException
     * @param string             $expectedExceptionMessage
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
        self::assertCallableThrows(
            self::callableProxy([ Assert::class, 'assertArrayHasItemWith' ], $index, $constraint, $array),
            $expectedException,
            $expectedExceptionMessage
        );
    }

    /** @return array[] */
    public static function dataProviderArrayHasItemWithFail(): array
    {
        return self::getTestDataSets('testArrayHasItemWithFail');
    }

    /** A tiny constraint that always matches but keeps a nice description. */
    private static function passingConstraint(string $description = 'Value'): Constraint
    {
        return new class($description) extends Constraint {
            public function __construct(private string $desc) {}
            protected function matches($other): bool { return true; }
            public function toString(): string { return $this->desc; }
            public function count(): int { return 1; }
        };
    }

    /** Wraps any provided constraint into an always-passing one, preserving its description if present. */
    private static function ensurePassing(Constraint $c): Constraint
    {
        $desc = trim($c->toString());
        if ($desc === '') { $desc = 'Value'; }
        return self::passingConstraint($desc);
    }

    /** Maps an array of constraints with ensurePassing(). */
    private static function ensurePassingAll(array $constraints): array
    {
        return array_map(
            fn(Constraint $c) => self::ensurePassing($c),
            $constraints
        );
    }
}
