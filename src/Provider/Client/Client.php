<?php

namespace RateRUB\Provider\Client;

use RateRUB\Exception\ExtensionRequiredException;

/**
 * Simple get content
 * Class Client
 * @package RateRUR\Provider
 */
class Client implements ClientInterface
{
    /**
     * @param $url
     * @return false|string
     * @throws ExtensionRequiredException
     */
    public function get(string $url)
    {
        if (ini_get('allow_url_fopen') === false) {
            throw new ExtensionRequiredException('Need allow_url_fopen');
        }

        return file_get_contents($url);
    }
}