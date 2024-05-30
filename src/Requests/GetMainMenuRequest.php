<?php

namespace Karabin\FabriqConnector\Requests;

use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;

class GetMainMenuRequest extends Request implements Cacheable
{
    use HasCaching;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(
        public string $slug = 'main_menu_sv',
        public array $params = [],
    ) {
        if (! config('fabriq-connector.enable_cache')) {
            $this->disableCaching();
        }
    }

    protected function defaultQuery(): array
    {
        return $this->params;
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/menus/'.$this->slug;
    }

    protected function cacheKey(PendingRequest $pendingRequest): ?string
    {
        return 'fabriq_menu_'.$this->slug;
    }

    public function resolveCacheDriver(): Driver
    {
        return new LaravelCacheDriver(Cache::store(config('fabriq-connector.cache_store')));
    }

    public function cacheExpiryInSeconds(): int
    {
        return config('fabriq-connector.cache_expiry');
    }
}
