<?php

namespace Karabin\FabriqConnector;

final class Integrations
{
    public static function fabriq(?string $locale = null): FabriqRepository
    {
        return new FabriqRepository($locale);
    }

    // add additional integrations here
}
