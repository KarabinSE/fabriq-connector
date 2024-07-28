<?php

namespace Karabin\FabriqConnector;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class FabriqConnector extends Connector
{
    use AcceptsJson;

    protected readonly string $token;

    public function __construct(
        protected readonly string $locale,
    ) {
        $this->token = config('fabriq-connector.fabriq_connector_token');
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->token);
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
            'X-Locale' => $this->locale,
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
