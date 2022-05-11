<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Tpg\HeadlessBundle\Ast\Collection;

final class MiddlewareContextBuilder
{
    public const QUERY_TYPE = 'QUERY_TYPE';
    public const COLLECTION = 'QUERY_COLLECTION';
    public const COUNT = 'count';
    public const MANY = 'many';
    public const ONE = 'one';
    //When select joined relations
    public const JOINED = 'joined';

    use MiddlewareContextBuilderTrait;

    public function withQueryType(string $queryType):self
    {
        if(!in_array(
            $queryType,
            [
                self::COUNT,
                self::ONE,
                self::MANY,
                self::JOINED,
            ],
            true
        )){
            throw new \InvalidArgumentException(sprintf('Invalid query type "%s"',$queryType));
        }
        return $this->with(self::QUERY_TYPE,$queryType);
    }

    public function withCollection(Collection $collection):self
    {
        return $this->with(self::COLLECTION, $collection);
    }
}