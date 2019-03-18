<?php

namespace BitbuyAT\Bitstamp\Facade;

use BitbuyAT\Bitstamp\Contracts\Client;
use Illuminate\Support\Facades\Facade;

class Bitstamp extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}