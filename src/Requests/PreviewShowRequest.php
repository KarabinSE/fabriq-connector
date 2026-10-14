<?php

namespace Karabin\FabriqConnector\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class PreviewShowRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(
        public string $uuid
    ) {}

    public function resolveEndpoint(): string
    {
        return '/previews/'.$this->uuid;
    }
}
