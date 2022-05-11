<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Doctrine\ORM\QueryBuilder;

final class StackExecutor implements Stack
{
    private \SplQueue $queue;
    /**
     * @var callable|null
     */
    private $default;

    public function __construct(\SplQueue $queue, ?callable $default){
        $this->queue = clone $queue;
        $this->default = $default;
    }

    public function handle(QueryBuilder $queryBuilder, array $context): array
    {
        if($this->queue->isEmpty()){
            return $this->default?($this->default)($queryBuilder,$context):[];
        }
        return $this->queue->dequeue()->process($queryBuilder, $context,$this);
    }

}