<?php
/**
 * PHPUnitArrayAssertions - Array-related PHPUnit assertions for API testing.
 *
 * @copyright Copyright (c) 2020, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-array-asserts/blob/master/src/Constraint/AssociativeArray.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitArrayAsserts\Constraint;

use ArrayAccess;
use LucidFrame\Console\ConsoleTable;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Util\Exporter;

/**
 * Constraint that asserts that a value is an associative array matching a
 * given structure and that the array's items pass other constraints.
 *
 * Any native array and ArrayAccess object is considered an associative array,
 * no matter which keys they use. However, the array's items are applied to
 * the matching constraint. By default, additional items will fail the
 * constraint. The same is true when items are missing.
 *
 * The expected keys and constraints to apply, as well as whether additional
 * and/or missing items should fail the constraint, are passed in the
 * constructor. Constraints can either be arbitrary `Constraint` instances
 * (e.g. `PHPUnit\Framework\Constraint\StringContains`), or any static value,
 * requiring exact matches of the values.
 * 
 * @psalm-suppress UnusedClass
 */
class AssociativeArray extends Constraint
{
    /** @var Constraint[] */
    protected $constraints = [];

    /** @var bool */
    protected $allowMissing;

    /** @var bool */
    protected $allowAdditional;

    /**
     * AssociativeArray constructor.
     *
     * @param Constraint[]|mixed[] $constraints     an array with the expected keys and constraints to apply
     * @param bool                 $allowMissing    whether missing items fail the constraint (defaults to FALSE)
     * @param bool                 $allowAdditional whether additional items fail the constraint (defaults to TRUE);
     *                                              this option works for native arrays only
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $constraints, bool $allowMissing = false, bool $allowAdditional = true)
    {
        foreach ($constraints as $key => $constraint) {
            if (!($constraint instanceof Constraint)) {
                $constraint = new IsEqual($constraint);
            }

            $this->constraints[$key] = $constraint;
        }

        $this->allowMissing = $allowMissing;
        $this->allowAdditional = $allowAdditional;
    }

    /**
     * Returns a human-readable string representation of this Constraint.
     *
     * @return string string representation of the Constraint
     */
    #[\Override]
    public function toString(): string
    {
        if (!$this->constraints) {
            return $this->allowAdditional ? 'is an associative array' : 'is an empty array';
        }

        $templateArguments = [];
        foreach ($this->constraints as $key => $constraint) {
            $templateArguments[] = Exporter::export($key);
            $templateArguments[] = $constraint->toString();
        }

        $itemConjunction = !$this->allowMissing ? 'and' : 'and/or';
        $itemCount = count($this->constraints);

        if ($this->allowAdditional) {
            $itemTemplate = sprintf(', %s has the key %%s whose value %%s', $itemConjunction);
            $template = 'is an associative array that has the key %s whose value %s'
                . str_repeat($itemTemplate, $itemCount - 1)
                . sprintf(', %s any other item', $itemConjunction);

            return vsprintf($template, $templateArguments);
        } else {
            $itemTemplate = sprintf(', %s the key %%s whose value %%s', $itemConjunction);
            $template = 'is an associative array that has just the key %s whose value %s'
                . str_repeat($itemTemplate, $itemCount - 1);

            return vsprintf($template, $templateArguments);
        }
    }

    /**
     * Returns whether the given value matches the Constraint.
     *
     * @param mixed $other the value to evaluate
     *
     * @return bool boolean indicating whether the value matches the Constraint
     */
    #[\Override]
    protected function matches($other): bool
    {
        foreach ($this->constraints as $key => $constraint) {
            $valueExists = false;
            if (is_array($other)) {
                $valueExists = array_key_exists($key, $other);
            } elseif ($other instanceof ArrayAccess) {
                $valueExists = $other->offsetExists($key);
            }

            if (!($valueExists ? $constraint->evaluate($other[$key], '', true) : $this->allowMissing)) {
                return false;
            }
        }

        if (is_array($other) && !$this->allowAdditional) {
            return !array_diff_key($other, $this->constraints);
        }

        return true;
    }

    /**
     * Returns the number of assertions performed by this Constraint.
     */
    #[\Override]
    public function count(): int
    {
        $count = 1;
        foreach ($this->constraints as $constraint) {
            $count += count($constraint);
        }

        return $count;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value
     *
     * @return string the failure description
     */
    #[\Override]
    protected function failureDescription($other): string
    {
        if (!(is_array($other) || ($other instanceof ArrayAccess))) {
            return Exporter::export($other) . ' is an associative array';
        }

        return 'associative array matches constraints';
    }

    /**
     * Returns additional failure description.
     *
     * @param mixed $other the evaluated value
     *
     * @return string the failure description
     */
    #[\Override]
    protected function additionalFailureDescription($other): string
    {
        if (!(is_array($other) || ($other instanceof ArrayAccess))) {
            return '';
        }

        $table = new ConsoleTable();
        $table->setHeaders([ 'Key', 'Value', 'Constraint' ]);

        foreach ($this->constraints as $key => $constraint) {
            $valueExists = is_array($other) ? array_key_exists($key, $other) : $other->offsetExists($key);

            $table->addRow([
                Exporter::export($key),
                $valueExists ? Exporter::shortenedExport($other[$key]) : '',
                'Value ' . $constraint->toString(),
            ]);
        }

        if (is_array($other)) {
            foreach (array_diff_key($other, $this->constraints) as $key => $value) {
                $table->addRow([
                    Exporter::export($key),
                    Exporter::shortenedExport($value),
                    ''
                ]);
            }
        }

        $output = $table->getTable();
        $output .= sprintf(
            "[%s] Allow missing; [%s] Allow additional\n",
            $this->allowMissing ? 'x' : ' ',
            $this->allowAdditional ? 'x' : ' '
        );

        return strtr($output, [ "\r\n" => "\n" ]);
    }
}
