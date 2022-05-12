<?php

namespace Tpg\HeadlessBundle\Middleware;


interface RestrictQueryTypeMiddleware
{
    /**
     * @return array<string>
     */
    public function restrictedToQueryTypes():array;
}