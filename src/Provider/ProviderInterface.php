<?php

namespace RateRUB\Provider;

use RateRUB\Entity\Rate;

interface ProviderInterface
{
    public function getRate(string $currencyFrom, \DateTimeImmutable $date): Rate;
}