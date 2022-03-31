<?php

namespace Tpg\HeadlessBundle\Extension;

trait ExtensionContextBuilderTrait
{
    /** @var array<string,mixed>  */
    protected array $context = [];

    protected function with(string $key, $value):self
    {
        $instance = new static();
        $instance->context = array_merge($this->context,[$key=>$value]);
        return $instance;
    }

    public function withContext(array $context):self
    {
        $instance = new static();
        $instance->context = array_merge($this->context,$context);
        return $instance;
    }

    public function toArray():array
    {
        return $this->context;
    }
}