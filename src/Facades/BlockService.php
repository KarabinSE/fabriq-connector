<?php

namespace Karabin\FabriqConnector\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Karabin\FabriqConnector\Services\BlockService
 */
class BlockService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Karabin\FabriqConnector\Services\BlockService::class;
    }
}
