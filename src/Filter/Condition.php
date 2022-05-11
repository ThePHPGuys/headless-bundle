<?php

namespace Tpg\HeadlessBundle\Filter;


use InvalidArgumentException;

final class Condition
{
    private const OPERATORS = [
        '=',
        '>',
        '>=',
        '<',
        '<=',
        '<>',
        'startsWith',
        'endsWith',
        'contains',
        'ncontains',
        'in',
        'nin',
        'nnull',
        'null'
    ];
    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string|int|float|string[]|int[]|float[]
     */
    private $value;

    public function __construct(string $property, string $operator, $value)
    {
        $this->validateOperator($operator);
        $this->validateValue($value);
        $this->property = $property;
        $this->operator = $operator;
        $this->value = $value;
    }

    private function validateOperator($operator): void
    {
        if (!in_array($operator, self::OPERATORS, true)) {
            throw new InvalidArgumentException('Incorrect value');
        }
    }

    private function validateValue($value): void
    {
        if (!is_string($value) && !is_numeric($value) && !is_array($value)) {
            throw new InvalidArgumentException('Incorrect value');
        }
    }

    public static function createFromArray(array $condition): self
    {
        if (!isset($condition['field'], $condition['operator'], $condition['value'])) {
            throw new InvalidArgumentException('Incorrect array condition struct');
        }

        return new self($condition['field'], $condition['operator'], $condition['value']);
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value()
    {
        return $this->value;
    }

    public function property(): string
    {
        return $this->property;
    }
}