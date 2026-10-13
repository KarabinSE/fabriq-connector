<?php

namespace Karabin\FabriqConnector\Facades;

use Illuminate\Support\Facades\Facade;

final class BlockService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Karabin\FabriqConnector\Services\BlockService::class;
    }
}
