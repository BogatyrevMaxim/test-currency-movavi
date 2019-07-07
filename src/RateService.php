<?php

namespace RateRUB;

use DateTimeImmutable;
use RateRUB\Exception\ExtensionRequiredException;
use RateRUB\Provider\ProviderInterface;

class RateService
{
    /**
     * @var ProviderInterface[]
     */
    private $providers;

    /**
     * @var int
     */
    private $scale = 4;

    public function __construct(ProviderInterface ...$providers)
    {
        if (!extension_loaded('bcmath')) {
            throw new ExtensionRequiredException('BCMath extension required');
        }

        $this->providers = $providers;
    }

    public function addProvider(ProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * uses bcmath for correct float operation
     * @param string $currencyFrom
     * @param DateTimeImmutable $date
     * @return string|null
     * @throws \Exception
     */
    public function getRate(string $currencyFrom, DateTimeImmutable $date)
    {
        if (count($this->providers) == 0) {
            throw new \Exception('Providers not found');
        }

        $sum = '0';
        foreach ($this->providers as $provider) {
            $rate = $provider->getRate($currencyFrom, $date);
            $sum = bcadd($sum, $rate->getRateString(), $this->scale);
        }

        return bcdiv($sum, count($this->providers), $this->scale);
    }

    /**
     * @return int
     */
    public function getScale(): int
    {
        return $this->scale;
    }

    /**
     * @param int $scale
     */
    public function setScale(int $scale): void
    {
        $this->scale = $scale;
    }
}