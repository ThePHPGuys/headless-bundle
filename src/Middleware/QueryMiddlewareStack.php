<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Closure;
use Doctrine\ORM\QueryBuilder;

final class QueryMiddlewareStack implements Stack,Middleware
{
    /**
     * @var \SplQueue<Middleware>
     */
    private \SplQueue $queue;
    /**
     * @var null|Closure(QueryBuilder,array):array
     */
    private ?Closure $default=null;

    public function __construct()
    {
        $this->queue = new \SplQueue();
    }

    public function pipe(Middleware $middleware):void
    {
        $this->queue->enqueue($middleware);
    }

    /**
     * @param  iterable<Middleware> $middlewares
     */
    public function pipes(iterable $middlewares):void
    {
        array_map(fn(Middleware $middleware)=>$this->pipe($middleware),$middlewares);
    }

    public function withDefault(\Closure $closure):self
    {
        $instance = clone $this;
        $instance->queue = clone $this->queue;
        $instance->default = $closure;
        return $instance;
    }

    public function handle(QueryBuilder $queryBuilder, array $context): array
    {
        return (new StackExecutor($this->queue,$this->default))->handle($queryBuilder,$context);
    }

    public function process(QueryBuilder $queryBuilder, array $context, Stack $stack): array
    {
        return $this
            ->withDefault(fn(QueryBuilder $qb, array $c)=>$stack->handle($qb,$c))
            ->handle($queryBuilder,$context);
    }

    /**
     * @param  Closure(QueryBuilder,array,Stack):array  $handler
     * @return Middleware
     */
    public static function createMiddlewareFromClosure(Closure $handler): Middleware
    {
        return new class($handler) implements Middleware {

            /**
             * @var Closure(QueryBuilder,array,Stack):array
             */
            private Closure $handler;

            public function __construct(Closure $handler)
            {
                $this->handler = $handler;
            }

            public function process(QueryBuilder $queryBuilder, array $context, Stack $stack): array
            {
                return ($this->handler)($queryBuilder, $context,$stack);
            }
        };
    }
}