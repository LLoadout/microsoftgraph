<?php

namespace LLoadout\Microsoftgraph\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LLoadout\Microsoftgraph\Microsoftgraph
 */
class Microsoftgraph extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \LLoadout\Microsoftgraph\Microsoftgraph::class;
    }
}
