<?php

namespace Karabin\FabriqConnector\Services;

use Karabin\FabriqConnector\FabriqConnector;
use Karabin\FabriqConnector\Requests\GetContactsRequest;
use Karabin\FabriqConnector\Requests\GetNewsRequest;

class BlockService
{
    protected array $shareArray = [];

    public function __construct()
    {

    }

    const array BLOCK_MAP = [
        'ContactsBlock' => [
            'request' => GetContactsRequest::class,
            'key' => 'contacts',
            'params' => [
                'include' => 'content',
            ],
        ],
        'NewsBlock' => [
            'request' => GetNewsRequest::class,
            'key' => 'news',
            'params' => [
                'include' => 'content',
            ],
        ],
    ];

    public function parseBlocks(array $blocks)
    {
        foreach ($blocks as $block) {
            if (array_key_exists($block['block_type']['component_name'], self::BLOCK_MAP)) {
                $this->share(self::BLOCK_MAP[$block['block_type']['component_name']]);
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
