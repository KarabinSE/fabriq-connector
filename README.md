# Fabriq Saloon Connector

[![Latest Version on Packagist](https://img.shields.io/packagist/v/karabinse/fabriq-connector.svg)](https://packagist.org/packages/karabinse/fabriq-connector)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/karabinse/fabriq-connector/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/karabinse/fabriq-connector/actions?query=workflow%3Arun-tests+branch%3Amain)

Laravel integration for the Fabriq API, built with Saloon.

## Installation

Install the package with Composer:
|  Fabriq Version |  Command |
|--|--|
|  `=< 4` | `composer require karabinse/fabriq-connector:"^2.0"`   |
| `> 5` | `composer require karabinse/fabriq-connector:"^3.0"`  |


Publish the configuration file:

```bash
php artisan vendor:publish --tag="fabriq-connector-config"
```

**Before extending the package's integrations or Fabriq repository, publish the extension classes into your application:**

```bash
php artisan fabriq-connector:publish
```

This creates `app/Http/Integrations/Integrations.php` and `app/Http/Integrations/Fabriq/FabriqRepository.php`. Extend these application classes to add integration factories or customize Fabriq repository methods. The command overwrites existing files at these paths, so back up or update them before running it again.

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

Generate a handler with Artisan by providing the block name; the command appends `Handler`:

```bash
php artisan fabriq:make-block-handler ContactsBlock
```

Omit the name to be prompted for it. The generated class is placed in `app/Support/BlockHandlers` and is ready to register in `config/fabriq-connector.php`.

## Add an integration

First of all its recommended to do `php artisan fabriq-connector:publish` if you want to extend the functionality. It will publish classes ready for you to extend.

For example, to connect to Silo and fetch locations, extend the package's `Integrations` class in your application with a factory for your Silo repository:

```php
<?php

namespace App\Http\Integrations;

use App\Http\Integrations\Silo\SiloRepository;
use Karabin\FabriqConnector\Integrations as BaseIntegrations;

class Integrations extends BaseIntegrations
{
    public static function silo(): SiloRepository
    {
        return new SiloRepository();
    }
}
```

The repository is where you add methods that send your Silo requests, such as `locations()`. Request classes and their endpoint details are specific to your application and are not included in this package.

To generate an integration repository, run:

```bash
php artisan fabriq:make-integration silo SiloConnector
```

The command creates `app/Http/Integrations/Silo/SiloRepository.php`. If `SiloConnector` does not exist, the command asks whether to create it with Saloon's `saloon:connector` command. You can also omit either argument to be prompted for it.

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
