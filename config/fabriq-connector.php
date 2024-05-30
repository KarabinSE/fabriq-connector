<?php

// config for Karabin/FabriqConnector
return [
    'base_url' => env('FABRIQ_CONNECTOR_BASE_URL', 'http://localhost'),
    'cache_expiry' => env('FABRIQ_CONNECTOR_CACHE_EXPIRY', (3600 * 24) * 7),
];
