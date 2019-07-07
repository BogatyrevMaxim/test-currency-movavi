<?php

namespace RateRUB\Provider;

use Psr\Log\LoggerInterface;
use RateRUB\Provider\Client\ClientInterface;

class ProviderFactory
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @return ProviderInterface[]
     */
    public function getProviders()
    {
        return [
            new CbrProvider($this->client, $this->logger),
            new RbcProvider($this->client, $this->logger),
        ];

    }
}