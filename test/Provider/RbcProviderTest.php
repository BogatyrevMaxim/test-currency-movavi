<?php

namespace RateRUBTest\Provider;

use PHPUnit\Framework\TestCase;
use RateRUB\Entity\Currency;
use RateRUB\Provider\Exception\FailAccessException;
use RateRUB\Provider\Exception\InvalidDataException;
use RateRUB\Provider\RbcProvider;

class RbcProviderTest extends TestCase
{
    /**
     * @var RbcProvider
     */
    private $service;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\RateRUB\Provider\Client\ClientInterface
     */
    private $client;

    public function setUp(): void
    {
        $this->client = $this->createMock(\RateRUB\Provider\Client\ClientInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->service = new RbcProvider($this->client, $logger);
    }

    public function testGetRateException()
    {
        $this->expectException(FailAccessException::class);
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRateException2()
    {
        $this->client->method('get')->willReturn('incorrect json');
        $this->expectException(InvalidDataException::class);
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRateException3()
    {
        $this->expectException(InvalidDataException::class);
        $this->client->method('get')->willReturn('{}');
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRateException4()
    {
        $this->expectException(InvalidDataException::class);
        $this->client->method('get')->willReturn('{"status": 500}');
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRateException5()
    {
        $this->expectException(InvalidDataException::class);
        $this->client->method('get')->willReturn('{"status": 200, "data": {}}');
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRate()
    {
        $json = '{"status": 200, "meta": {"sum_deal": 1.0, "source": "cbrf", "currency_from": "USD", "date": null, "currency_to": "RUR"}, "data": {"date": "2019-07-07 20:09:55", "sum_result": 63.5841, "rate1": 63.5841, "rate2": 0.0157}}';
        $this->client->method('get')->willReturn($json);
        $date = new \DateTimeImmutable('2019-07-07');
        $rate = $this->service->getRate(Currency::USD, $date);
        $this->assertEquals($rate->getRateString(), '63.5841');
        $this->assertEquals($rate->getRateFloat(), 63.5841);
        $this->assertEquals($rate->getDate(), $date);
    }
}
