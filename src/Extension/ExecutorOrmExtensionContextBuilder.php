<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


final class ExecutorOrmExtensionContextBuilder
{
    use ExtensionContextBuilderTrait;

    protected function withOperation(string $operation):self
    {
        if(!in_array(
            $operation,
            [
                ExecutorOrmExtension::OPERATION_COUNT,
                ExecutorOrmExtension::OPERATION_GET_ONE,
                ExecutorOrmExtension::OPERATION_GET_MANY,
                ExecutorOrmExtension::OPERATION_GET_JOINED,
            ],
            true
        )){
            throw new \InvalidArgumentException(sprintf('Incorrect extension operation "%s"',$operation));
        }
        return $this->with(ExecutorOrmExtension::OPERATION_CONTEXT_KEY,$operation);
    }

    public function withCount():self
    {
        return $this->withOperation(ExecutorOrmExtension::OPERATION_COUNT);
    }

    public function withGetOne():self
    {
        return $this->withOperation(ExecutorOrmExtension::OPERATION_GET_ONE);
    }

    public function withGetMany():self
    {
        return $this->withOperation(ExecutorOrmExtension::OPERATION_GET_MANY);
    }

    public function withGetJoined():self
    {
        return $this->withOperation(ExecutorOrmExtension::OPERATION_GET_JOINED);
    }
}