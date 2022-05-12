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
        return $this->processMiddleware($this->queue->dequeue(),$queryBuilder, $context);
    }

    private function processMiddleware(Middleware $middleware, QueryBuilder $queryBuilder, array $context):array
    {
        if($middleware instanceof CollectionAwareMiddleware){
            if(!isset($context[MiddlewareContextBuilder::COLLECTION])){
                return $this->handle($queryBuilder,$context);
            }
            $middleware->setCollection($context[MiddlewareContextBuilder::COLLECTION]);
        }

        if($middleware instanceof QueryTypeAwareMiddleware) {
            if(!isset($context[MiddlewareContextBuilder::QUERY_TYPE])){
                return $this->handle($queryBuilder,$context);
            }
            $middleware->setQueryType($context[MiddlewareContextBuilder::QUERY_TYPE]);
        }

        if($middleware instanceof RestrictQueryTypeMiddleware){
            if(
                !isset($context[MiddlewareContextBuilder::QUERY_TYPE])
                || !in_array($context[MiddlewareContextBuilder::QUERY_TYPE],$middleware->restrictedToQueryTypes(),true)
            ) {
                return $this->handle($queryBuilder, $context);
            }
        }

        return $middleware->process($queryBuilder, $context,$this);
    }
}