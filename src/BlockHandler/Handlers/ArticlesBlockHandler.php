<?php

namespace Karabin\FabriqConnector\BlockHandler\Handlers;

use Karabin\FabriqConnector\BlockHandler\BlockHandler;
use Karabin\FabriqConnector\Integrations;

class ArticlesBlockHandler extends BlockHandler
{
    public function handle(array $block): array
    {
        $params = [
            'number' => 3,
            'include' => 'content,slugs',
            'filter[type]' => 'activity',
        ];

        $articles = Integrations::fabriq()
            ->articles($params)
            ->json('data', []);

        return [
            ...$block,
            'articles' => $articles,
        ];
    }
}
