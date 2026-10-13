<?php

namespace Karabin\FabriqConnector\BlockHandler;

abstract class BlockHandler
{
    abstract public function handle(array $block): array;
}
