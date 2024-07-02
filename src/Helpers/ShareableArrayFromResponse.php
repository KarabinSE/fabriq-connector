<?php

namespace Karabin\FabriqConnector\Helpers;

use Karabin\FabriqConnector\Services\BlockService;

class ShareableArrayFromResponse
{
    public static function parse(array $boxes = []): array
    {
        $blockService = new BlockService();

        $shareArray = $blockService
            ->parseBlocks($boxes)
            ->getShareArray();

        return $shareArray;
    }
}
