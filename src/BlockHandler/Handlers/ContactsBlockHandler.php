<?php

namespace Karabin\FabriqConnector\BlockHandler\Handlers;

use Karabin\FabriqConnector\BlockHandler\BlockHandler;
use Karabin\FabriqConnector\Integrations;

class ContactsBlockHandler extends BlockHandler
{
    public function handle(array $block): array
    {
        $params = [
            'include' => 'content',
            'sort' => 'sortindex',
        ];

        $contacts = Integrations::fabriq()
            ->contacts($params)
            ->json('data', []);

        return [
            ...$block,
            'contacts' => $contacts,
        ];
    }
}
