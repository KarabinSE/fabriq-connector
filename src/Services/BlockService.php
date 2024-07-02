<?php

namespace Karabin\FabriqConnector\Services;

use Karabin\FabriqConnector\FabriqConnector;

class BlockService
{
    protected array $shareArray = [];

    public array $blockMap = [];

    public function __construct()
    {
        $this->blockMap = config('fabriq-connector.block_map');
    }

    public function parseBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            if (array_key_exists($block['block_type']['component_name'], $this->blockMap)) {
                $this->share($this->blockMap[$block['block_type']['component_name']]);
            }
        }

        return $this;
    }

    public function share(array $block): array
    {
        $connector = new FabriqConnector();
        $request = new $block['request']($block['params']);
        if (config('app.env') !== 'production') {
            $request->disableCaching();
        }
        $response = $connector->send($request)->throw()->json();

        $this->shareArray[$block['key']] = $response;

        return [$block['key'] => $response];
    }

    public function getShareArray(): array
    {
        return $this->shareArray;
    }
}
