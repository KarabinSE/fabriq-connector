<?php

namespace App\Http\Integrations;

use App\Http\Integrations\Fabriq\FabriqRepository;
use Karabin\FabriqConnector\Integrations as FabriqIntegrations;

class Integrations extends FabriqIntegrations
{
    public static function fabriq(?string $locale = null): FabriqRepository
    {
        return new FabriqRepository($locale);
    }

    /**
     * Here you can add your own integrations like example() or anotherIntegration()
     *
     * Create a new repository with php artisan fabriq:make-integration
     * then add a factory method for it here
     */
}
