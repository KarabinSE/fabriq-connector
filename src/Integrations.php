<?php

namespace Karabin\FabriqConnector;

class Integrations
{
    public static function fabriq(?string $locale = null): FabriqRepository
    {
        return new FabriqRepository($locale);
    }
}
