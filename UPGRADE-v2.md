# Upgrade guide for v2

## Overview

Version 2 replaces the old request-config style sharing flow with explicit block handlers. This gives you a cleaner, more testable way to turn incoming Fabriq blocks into the data your frontend expects.

| Area          | What changed in v2                                                                                                     | Required action                                                                                       |
| ------------- | ---------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| BlockService  | `ShareableArrayFromResponse` was removed and replaced with `BlockService` plus dedicated `BlockHandler` classes. | Replace any legacy shareable response code with`BlockService::processBlocks()` and custom handlers. |
| Configuration | The legacy`block_map` setting was replaced with `block_handlers`.                                                  | Update your config to map a block component name to a handler class.                                  |
| Registration  | Handlers are now resolved by component name and can be registered globally or at runtime.                              | Register handlers in the config or call`BlockService::configure()` before processing blocks.        |
| Front end     | BlockService `->shareInertia` method shares a `blocks` prop that contain the processed blocks                      | use `blocks` for rendering the blocks                                                              |

## Why this changed

The v1 flow relied on request configuration objects to resolve data for a block. In v2, each block type is handled by a dedicated class that implements the transformation logic itself.

This makes block processing:

- easier to test
- explicit and type-safe for each block component
- easier to extend without editing central request configuration

## Request class renames

The request classes were renamed to describe the resource and action. If your application refers to these request classes directly, update the imports and class names:

| v1 class | v2 class |
| --- | --- |
| `GetContactsRequest` | `ContactIndexRequest` |
| `GetMainMenuRequest` | `MenuShowRequest` |
| `GetNewsRequest` | `NewsIndexRequest` |
| `GetNewsItemRequest` | `NewsShowRequest` |
| `GetPageRequest` | `PageShowRequest` |

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

In this example, `SiloRequest` is a request class provided by your application or another dependency; it is not included in this package.

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

        $locations = Integrations::silo()
            ->locations()
            ->dto();

        return [
            ...$block,
            'contacts' => $contacts,
            'locations' => $locations,
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

## 5. Process blocks before sharing them

In v1, blocks were usually resolved through the old shareable response flow. In v2, call `BlockService` explicitly before sharing data with Inertia.

```php
use Karabin\FabriqConnector\Facades\BlockService;

$blocks = $page['blocks'] ?? [];

BlockService::processBlocks($blocks)
    ->shareInertia();
```

`processBlocks()` applies each matching handler to the incoming block payload and returns the transformed blocks. `shareInertia()` then shares the processed result with Inertia.

### Always include menus in Inertia responses

To make menus available on every Inertia page, fetch them in your application's `HandleInertiaRequests` middleware and add them to the shared props:

```php
use Illuminate\Http\Request;
use Inertia\Middleware;
use Karabin\FabriqConnector\Integrations;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        $locale = app()->getLocale();

		$menus = Integrations::fabriq()
            ->menus([
                'primary' => "main_menu_$locale",
                'secondary' => "secondary_menu_$locale",
                'footer' => "footer_menu_$locale",
            ]);

        return [
            ...parent::share($request),
            'menus' => $menus,
        ];
    }
}
```

The keys (`primary`, `secondary`, and `footer`) are the prop names your frontend can use. The corresponding values are the Fabriq menu identifiers. Since this runs for each Inertia request, these menus are included in shared page props throughout the application.

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

BlockService::processBlocks($blocks)
    ->shareInertia();
```

## 7. Migration checklist

- Replace `block_map` with `block_handlers`
- Create one `BlockHandler` class per block type
- Make sure each handler returns the original block plus any transformed data
- Register the handlers globally or via `BlockService::configure()`
- Call `processBlocks()` and `shareInertia()` before rendering your page

## 8. Notes

If you are upgrading from a large existing implementation, the easiest refactor is to move each block's request code into a dedicated handler class and keep the returned payload structure matching the previous frontend contract. This keeps the UI unchanged while moving the logic into the v2 architecture.
