<?php

namespace Karabin\FabriqConnector;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class FabriqConnector extends Connector
{
    use AcceptsJson;

    public function __construct(protected readonly string $locale = 'sv')
    {
        $this->headers()->add('X-Locale', $locale);
    }

    public function resolveBaseUrl(): string
    {
        return config('fabriq-connector.base_url');
    }

    /**
     * Default headers for every request
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultQuery(): array
    {
        if (request()->has('preview')) {
            return [
                'preview' => request()->query('preview'),
            ];
        }

        return [];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
