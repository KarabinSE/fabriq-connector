# Fabriq Saloon Connector

[![Latest Version on Packagist](https://img.shields.io/packagist/v/karabinse/fabriq-connector.svg)](https://packagist.org/packages/karabinse/fabriq-connector)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/karabinse/fabriq-connector/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/karabinse/fabriq-connector/actions?query=workflow%3Arun-tests+branch%3Amain)


## Installation

You can install the package via composer:

```bash
composer require karabinse/fabriq-connector
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="fabriq-connector-config"
```

This is the contents of the published config file:

```php
return [
    'base_url' => env('FABRIQ_CONNECTOR_BASE_URL', 'http://localhost'),
    'enable_cache' => env('FABRIQ_CONNECTOR_ENABLE_CACHE', false),
    'cache_expiry' => env('FABRIQ_CONNECTOR_CACHE_EXPIRY', (3600 * 24) * 7),
    'cache_driver' => env('FABRIQ_CONNECTOR_CACHE_STORE', 'file'),
    'fabriq_connector_token' => env('FABRIQ_CONNECTOR_TOKEN', ''),
];

```

## Usage

```php
$connector = new FabriqConnector(locale: 'en');
$request = new GetContactsRequest(params: ['include' => 'content']);

$response = $connector->send($request);

// Response
//    "data": [
//         {
//             "id": 1,
//             "locale": "sv",
//             "name": "Frans Rosander",
//             "email": "Frans@sten.com",
//             "phone": "+46 (44) 28 71 11",
//             "mobile": "+46 (0)702 - 99 09 41",
//             "published": 1,
//             "sortindex": 1,
//             "created_at": "2024-04-11T11:33:17.000000Z",
//             "updated_at": "2024-05-30T08:18:48.000000Z",
//             "content": {
//                 "data": {
//                     "body": "",
//                     "position": "",
//                     "image": {
//                         "id": 56,
//                         "file_name": "IMG_5966_FransR.jpg",
//                         "src": "",
//                         "thumb_src": "",
//                         "webp_src": "",
//                         "srcset": "",
//                         "alt_text": "",
//                         "caption": null,
//                         "mime_type": "image\/jpeg",
//                         "size": 23076,
//                         "width": 150,
//                         "height": 187,
//                         "custom_crop": false,
//                         "responsive": "",
//                         "x_position": "50%",
//                         "y_position": "50%",
//                         "meta_id": 13
//                     },
//                     "enabled_locales": null
//                 }
//             }
//         }
//     ]
```

## Testing

```bash
composer test
```

## Credits

- [Albin J Nilsson](https://github.com/KarabinSE)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
