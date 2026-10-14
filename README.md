# Fabriq Saloon Connector

[![Latest Version on Packagist](https://img.shields.io/packagist/v/karabinse/fabriq-connector.svg)](https://packagist.org/packages/karabinse/fabriq-connector)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/karabinse/fabriq-connector/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/karabinse/fabriq-connector/actions?query=workflow%3Arun-tests+branch%3Amain)

Laravel integration for the Fabriq API, built with Saloon.

## Installation

Install the package with Composer:

```bash
composer require karabinse/fabriq-connector
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="fabriq-connector-config"
```

Configure the Fabriq API URL and token in your environment:

```dotenv
FABRIQ_CONNECTOR_BASE_URL=https://your-fabriq-api.example
FABRIQ_CONNECTOR_TOKEN=your-token
FABRIQ_CONNECTOR_ENABLE_CACHE=false
FABRIQ_CONNECTOR_CACHE_EXPIRY=604800
FABRIQ_CONNECTOR_CACHE_STORE=file
```

The published `config/fabriq-connector.php` also lets you register block handlers:

```php
'block_handlers' => [
    'ContactsBlock' => ContactsBlockHandler::class,
    'ArticlesBlock' => ArticlesBlockHandler::class,
],
```

## Fetch Fabriq data

Use `Integrations::fabriq()` to make API requests. The locale defaults to the current Laravel application locale; pass a locale explicitly when needed.

```php
use Karabin\FabriqConnector\Integrations;

$page = Integrations::fabriq(locale: 'en')->page('about');

$contacts = Integrations::fabriq()
    ->contacts(['include' => 'content'])
    ->json('data', []);
```

## Process blocks

In v2, block-specific data is added by handlers. A handler extends `BlockHandler` and returns the original block with any additional props:

```php
<?php

namespace App\Fabriq\Blocks;

use Karabin\FabriqConnector\BlockHandler\BlockHandler;
use Karabin\FabriqConnector\Integrations;

final class ContactsBlockHandler extends BlockHandler
{
    public function handle(array $block): array
    {
        $contacts = Integrations::fabriq()
            ->contacts(['include' => 'content', 'sort' => 'sortindex'])
            ->json('data', []);

        return [
            ...$block,
            'contacts' => $contacts,
        ];
    }
}
```

Register handlers and retrieve the processed blocks:

```php
use Karabin\FabriqConnector\Facades\BlockService;

$blocks = BlockService::configure()
    ->processBlocks($page->blocks)
    ->blocks();

$pageData = [
    'page' => $page->page,
    'blocks' => $blocks,
];
```

`processBlocks()` matches each block's `block_type.component_name` to a registered handler. Blocks without a matching handler pass through unchanged. `blocks()` returns the processed blocks; include them in whichever response or rendering mechanism your application uses.

Block processing does not require Inertia. If your application uses Inertia, include `inertiajs/inertia-laravel` as a direct dependency of your application.

## Add an integration

To add an API such as Silo, extend the package's `Integrations` class in your application and add a factory for your request class:

```php
<?php

namespace App\Support;

use App\Integrations\SiloRequest;
use Karabin\FabriqConnector\Integrations as BaseIntegrations;

class Integrations extends BaseIntegrations
{
    public static function silo(): SiloRequest
    {
        return new SiloRequest();
    }
}
```

Your handlers can then call `App\Support\Integrations::silo()->locations()`. `SiloRequest` is application-provided and is not included in this package.

## Fetch menus

Fetch menus with `Integrations::fabriq()->menus()` and add them to the data your application returns or renders:

```php
use Karabin\FabriqConnector\Integrations;

$locale = app()->getLocale();

$nav = Integrations::fabriq()->menus([
    'primary' => "main_menu_$locale",
    'secondary' => "secondary_menu_$locale",
    'footer' => "footer_menu_$locale",
]);
```

The array keys become menu names (`primary`, `secondary`, and `footer`); the values are the corresponding Fabriq menu identifiers. The connector returns the menu data but does not automatically share it with a frontend.

## Testing

Run the test suite with:

```bash
composer test
```

## Credits

- [Albin J Nilsson](https://github.com/KarabinSE)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
