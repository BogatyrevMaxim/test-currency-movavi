<?php

namespace RateRUBTest\Provider;

use PHPUnit\Framework\TestCase;
use RateRUB\Entity\Currency;
use RateRUB\Provider\CbrProvider;
use RateRUB\Provider\Exception\FailAccessException;
use RateRUB\Provider\Exception\InvalidDataException;

class CbrProviderTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\RateRUB\Provider\Client\ClientInterface
     */
    private $client;

    /**
     * @var CbrProvider
     */
    private $service;

    public function setUp(): void
    {
        $this->client = $this->createMock(\RateRUB\Provider\Client\ClientInterface::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->service = new CbrProvider($this->client, $logger);
    }

    public function testGetRateException()
    {
        $this->expectException(FailAccessException::class);
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRateException2()
    {
        $this->client->method('get')->willReturn('incorrect xml');
        $this->expectException(InvalidDataException::class);
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRateException3()
    {
        $this->client->method('get')->willReturn('<ValCurs></ValCurs>');
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage(sprintf('Currency not found: %s', Currency::USD));
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRateException4()
    {
        $this->client->method('get')->willReturn('<ValCurs Date="07.07.2019" name="Foreign Currency Market"><Valute ID="R01239">
<NumCode>978</NumCode>
<CharCode>EUR</CharCode>
<Nominal>1</Nominal>
<Name>Евро</Name>
<Value>71,6593</Value>
</Valute></ValCurs>');
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage(sprintf('Currency not found: %s', Currency::USD));
        $this->service->getRate(Currency::USD, new \DateTimeImmutable());
    }

    public function testGetRate()
    {
        $xml = '<ValCurs Date="07.07.2019" name="Foreign Currency Market"><Valute ID="R01235">
<NumCode>840</NumCode>
<CharCode>USD</CharCode>
<Nominal>1</Nominal>
<Name>Доллар США</Name>
<Value>63,5841</Value>
</Valute></ValCurs>';

        $date = new \DateTimeImmutable('2019-07-07');
        $this->client->method('get')->willReturn($xml);
        $rate = $this->service->getRate(Currency::USD, $date);
        $this->assertEquals($rate->getRateString(), '63.5841');
        $this->assertEquals($rate->getRateFloat(), 63.5841);
        $this->assertEquals($rate->getDate(), $date);
    }
}
