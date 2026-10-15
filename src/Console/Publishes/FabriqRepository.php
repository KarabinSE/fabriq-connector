<?php

namespace App\Http\Integrations\Fabriq;

use Karabin\FabriqConnector\FabriqRepository as DefaultFabriqRepository;

class FabriqRepository extends DefaultFabriqRepository
{
    /**
     * Here you can add functions to extend the base FabriqRepository
     *
     * example below
     */

    // public function references()
    // {
    //     $referenceIndexRequest = new \App\Http\Integrations\Fabriq\Requests\ReferenceIndexRequest;

    //     return $this->connector->send($referenceIndexRequest);
    // }
}
