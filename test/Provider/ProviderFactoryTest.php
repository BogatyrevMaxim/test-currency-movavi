<?php

namespace RateRUBTest\Provider;

use RateRUB\Provider\ProviderFactory;
use PHPUnit\Framework\TestCase;

class ProviderFactoryTest extends TestCase
{
    public function testGetProviders()
    {
        $client = $this->createMock(\RateRUB\Provider\Client\ClientInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $factory = new ProviderFactory($client, $logger);
        $this->assertNotEmpty($factory);
        $this->assertIsObject($factory);
        $providers = $factory->getProviders();
        $this->assertNotEmpty($providers);
        $this->assertIsArray($providers);
        $this->assertGreaterThanOrEqual(2, count($providers));
    }
}
