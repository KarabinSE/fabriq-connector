<?php

// config for Karabin/FabriqConnector
return [
    'base_url' => env('FABRIQ_CONNECTOR_BASE_URL', 'http://localhost'),
    'enable_cache' => env('FABRIQ_CONNECTOR_ENABLE_CACHE', false),
    'cache_expiry' => env('FABRIQ_CONNECTOR_CACHE_EXPIRY', (3600 * 24) * 7),
    'cache_store' => env('FABRIQ_CONNECTOR_CACHE_STORE', 'file'),
];
