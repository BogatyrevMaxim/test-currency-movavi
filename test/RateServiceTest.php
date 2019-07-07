<?php

namespace RateRUBTest;

use PHPUnit\Framework\TestCase;
use RateRUB\RateService;

class RateServiceTest extends TestCase
{
    public function testGetAndSetScale()
    {
        $provider1 = $this->createMock(\RateRUB\Provider\ProviderInterface::class);

        $service = new RateService($provider1);
        $service->setScale(4);
        $this->assertEquals($service->getScale(), 4);

        $service->setScale(6);
        $this->assertEquals($service->getScale(), 6);
    }

    public function testGetRate()
    {
        $firstRate = '70.0500';
        $secondRate = '70.0700';
        $date = new \DateTimeImmutable();
        $rate1 = new \RateRUB\Entity\Rate(\RateRUB\Entity\Currency::USD, $date, $firstRate);
        $rate2 = new \RateRUB\Entity\Rate(\RateRUB\Entity\Currency::USD, $date, $secondRate);
        $provider1 = $this->createMock(\RateRUB\Provider\ProviderInterface::class);
        $provider1->method('getRate')->willReturn($rate1);

        $provider2 = $this->createMock(\RateRUB\Provider\ProviderInterface::class);
        $provider2->method('getRate')->willReturn($rate2);

        $service = new RateService($provider1, $provider2);
        $service->setScale(4);
        $value = $service->getRate(\RateRUB\Entity\Currency::USD, $date);
        $this->assertEquals($value, '70.0600');

        $service->setScale(2);
        $value = $service->getRate(\RateRUB\Entity\Currency::USD, $date);
        $this->assertEquals($value, '70.06');
    }

    public function testGetRateException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Providers not found');
        $service = new RateService();
        $service->getRate(\RateRUB\Entity\Currency::USD, new \DateTimeImmutable());
    }
}
