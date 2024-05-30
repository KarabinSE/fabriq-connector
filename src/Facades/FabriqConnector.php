<?php

namespace Karabin\FabriqConnector\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Karabin\FabriqConnector\FabriqConnector
 */
class FabriqConnector extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Karabin\FabriqConnector\FabriqConnector::class;
    }
}
