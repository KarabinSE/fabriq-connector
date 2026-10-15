# Upgrade guide for v2

## Overview

Version 2 replaces the old request-config style sharing flow with explicit block handlers. This gives you a cleaner, more testable way to turn incoming Fabriq blocks into the data your frontend expects.

| Area          | What changed in v2                                                                                                     | Required action                                                                                       |
| ------------- | ---------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| BlockService  | `ShareableArrayFromResponse` was removed and replaced with `BlockService` plus dedicated `BlockHandler` classes. | Replace any legacy shareable response code with`BlockService::processBlocks()` and custom handlers. |
| Configuration | The legacy`block_map` setting was replaced with `block_handlers`.                                                  | Update your config to map a block component name to a handler class.                                  |
| Registration  | Handlers are now resolved by component name and can be registered globally or at runtime.                              | Register handlers in the config or call`BlockService::configure()` before processing blocks.        |
| Front end     | `shareInertia()` and the connector's `inertiajs/inertia-laravel` dependency were removed. Block processing is now separate from frontend rendering or response handling. | Retrieve processed blocks with `blocks()` and pass them through your application's usual flow. Add `inertiajs/inertia-laravel` as a direct dependency if your application uses Inertia. |

## Why this changed

The v1 flow relied on request configuration objects to resolve data for a block. In v2, each block type is handled by a dedicated class that implements the transformation logic itself.

This makes block processing:

- easier to test
- explicit and type-safe for each block component
- easier to extend without editing central request configuration

## Request class renames

The request classes were renamed to describe the resource and action. If your application refers to these request classes directly, update the imports and class names:

| v1 class               | v2 class                |
| ---------------------- | ----------------------- |
| `GetContactsRequest` | `ContactIndexRequest` |
| `GetMainMenuRequest` | `MenuShowRequest`     |
| `GetNewsRequest`     | `NewsIndexRequest`    |
| `GetNewsItemRequest` | `NewsShowRequest`     |
| `GetPageRequest`     | `PageShowRequest`     |

For example, update `Karabin\FabriqConnector\Requests\GetContactsRequest` to `Karabin\FabriqConnector\Requests\ContactIndexRequest`. The supported methods on `Integrations::fabriq()`—such as `contacts()`, `articles()`, `article()`, `page()`, and `menus()`—can be used instead of constructing request classes directly.

## 1. Update your config

Replace the old `block_map` configuration with `block_handlers`.

### Old config

```php
'block_map' => [
    'ContactsBlock' => [
        'request' => GetContactsRequest::class,
        'params' => ['include' => 'content'],
    ],
],
```

### New config

```php
return [
    'base_url' => env('FABRIQ_CONNECTOR_BASE_URL', 'http://localhost'),
    'enable_cache' => env('FABRIQ_CONNECTOR_ENABLE_CACHE', false),
    'cache_expiry' => env('FABRIQ_CONNECTOR_CACHE_EXPIRY', (3600 * 24) * 7),
    'cache_store' => env('FABRIQ_CONNECTOR_CACHE_STORE', 'file'),
    'fabriq_connector_token' => env('FABRIQ_CONNECTOR_TOKEN', ''),
    'block_handlers' => [
        'ContactsBlock' => ContactsBlockHandler::class,
        'ArticlesBlock' => ArticlesBlockHandler::class,
    ],
];
```

The key is the block component name returned by Fabriq, and the value is the class that should handle that block.

## 2. Create a block handler

Every handler must extend `Karabin\FabriqConnector\BlockHandler\BlockHandler` and implement `handle(array $block): array`.

A handler can call `Integrations::fabriq()` directly to fetch the data needed for that block. This is the recommended pattern in v2 because the handler owns the block-specific request logic instead of pushing it into a shared request config layer.

## 3. Add additional integrations

The package's `Karabin\FabriqConnector\Integrations` class provides the connector's built-in integrations and can be extended by your application. Create an application class that extends it and add a static factory method for each additional API you want to expose. The inherited `fabriq()` factory remains available.

In this example, `SiloRepository` is the application repository for a fictional CMS integration. Its request classes and endpoint details are provided by your application, not this package.

```php
<?php

namespace App\Support;

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

Use your application's wrapper in your own handlers:

```php
<?php

namespace App\Fabriq\Blocks;

use Karabin\FabriqConnector\BlockHandler\BlockHandler;
use App\Support\Integrations;

final class ContactsBlockHandler extends BlockHandler
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

        $pages = Integrations::silo()
            ->pages()
            ->dto();

        return [
            ...$block,
            'contacts' => $contacts,
            'pages' => $pages,
        ];
    }
}
```

This keeps the request logic close to the block and makes it easy to change one block without affecting the rest of the connector. In most cases, the handler will fetch the data it needs, transform it, and merge it back into the original block structure.

## 4. Register handlers globally or per request

### Global registration

Register the handlers in `config/fabriq-connector.php` as shown above.

### Runtime registration

If you need to register additional handlers at runtime, use the facade or service directly:

```php
use Karabin\FabriqConnector\Facades\BlockService;

BlockService::configure([
    'ContactsBlock' => ContactsBlockHandler::class,
    'ArticlesBlock' => ArticlesBlockHandler::class,
]);
```

## 5. Process blocks and use the result in your application

In v1, blocks were usually resolved through the old shareable response flow. In v2, call `BlockService` explicitly to process the blocks, then pass the result through your application's normal response or rendering flow.

```php
use Karabin\FabriqConnector\Facades\BlockService;

$blocks = $page['blocks'] ?? [];

$processedBlocks = BlockService::configure()
    ->processBlocks($blocks)
    ->blocks();
```

`processBlocks()` applies each matching handler to the incoming block payload. `blocks()` returns the transformed blocks; the connector does not automatically share them with a frontend. The connector no longer requires `inertiajs/inertia-laravel`; if your application uses Inertia, declare that package as a direct application dependency.

### Fetch menus

Fetch menu data with `Integrations::fabriq()->menus()` and add it to your application's response or rendered data as needed:

```php
use Karabin\FabriqConnector\Integrations;

$locale = app()->getLocale();

$menus = Integrations::fabriq()->menus([
    'primary' => "main_menu_{$locale}",
    'secondary' => "secondary_menu_{$locale}",
    'footer' => "footer_menu_{$locale}",
]);
```

The keys (`primary`, `secondary`, and `footer`) are the menu names your frontend can use. The corresponding values are the Fabriq menu identifiers. The connector returns the menu data but leaves sharing or rendering it to your application.

## 6. Example migration

### Before

```php
// legacy request-config based flow
$shareable = ShareableArrayFromResponse::make(
    'ContactsBlock',
    GetContactsRequest::class,
    ['include' => 'content']
);
```

### After

```php
use Karabin\FabriqConnector\Facades\BlockService;

BlockService::configure([
    'ContactsBlock' => App\Fabriq\Blocks\ContactsBlockHandler::class,
]);

$processedBlocks = BlockService::configure()
    ->processBlocks($blocks)
    ->blocks();
```

## 7. Migration checklist

- Replace `block_map` with `block_handlers`
- Create one `BlockHandler` class per block type
- Make sure each handler returns the original block plus any transformed data
- Register the handlers globally or via `BlockService::configure()`
- Call `processBlocks()` and retrieve the processed blocks with `blocks()`

## 8. Notes

If you are upgrading from a large existing implementation, the easiest refactor is to move each block's request code into a dedicated handler class and keep the returned payload structure matching the previous frontend contract. This keeps the UI unchanged while moving the logic into the v2 architecture.
