<?php

namespace Tpg\HeadlessBundle\Extension\Filter;


use InvalidArgumentException;

final class Filters
{
    public const AND = 'AND';
    public const OR = 'OR';

    private string $type;
    /**
     * @var Condition[]
     */
    private array $conditions=[];

    /**
     * @var self[]
     */
    private array $groups=[];

    public function __construct(string $type = self:: AND)
    {
        if (!in_array(strtoupper($type), [self:: AND, self:: OR], true)) {
            throw new InvalidArgumentException('Incorrect conditions group type');
        }
        $this->type = $type;
    }

    /**
     * @param array $group
     * @return static
     * [{type: "AND", conditions:}]
     */
    public static function createFromArray(array $group): self
    {
        $conditions = $group;
        if (isset($group['type'])) {
            $conditionGroup = new Filters($group['type']);
            if (!isset($group['conditions']) || count($group['conditions']) === 0) {
                throw new InvalidArgumentException('Incorrect array condition group struct');
            }
            $conditions = $group['conditions'];
        } else {
            $conditionGroup = new Filters();
        }

        foreach ($conditions as $condition) {
            if (!is_array($condition)) {
                continue;
            }
            if (isset($condition['type'], $condition['conditions'])) {
                $conditionGroup->addGroup(static::createFromArray($condition));
            }
            if (isset($condition['field'], $condition['operator'], $condition['value'])) {
                $conditionGroup->addCondition(Condition::createFromArray($condition));
            }
        }
        return $conditionGroup;
    }

    public function addGroup(self $group): void
    {
        $this->groups[] = $group;
    }

    public function addCondition(Condition $condition): void
    {
        $this->conditions[] = $condition;
    }

    public function isOr(): bool
    {
        return !$this->isAnd();
    }

    public function isAnd(): bool
    {
        return $this->type === self:: AND;
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function removeCondition(Condition $condition):void
    {
        $key = array_search($condition,$this->conditions,true);
        unset($this->conditions[$key]);
    }

    public function getGroups(): array
    {
        return $this->groups ?? [];
    }
}