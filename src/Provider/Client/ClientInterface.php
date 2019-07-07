<?php

namespace RateRUB\Provider\Client;

use RateRUB\Exception\ExtensionRequiredException;

interface ClientInterface
{
    /**
     * @param string $url
     * @return mixed
     * @throws ExtensionRequiredException
     */
    public function get(string $url);
}