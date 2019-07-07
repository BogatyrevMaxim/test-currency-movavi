<?php

namespace RateRUB\Entity;

use DateTimeImmutable;

class Rate
{
    /**
     * @var string
     */
    private $currencyFrom;

    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var float
     */
    private $rateFloat;

    /**
     * @var string
     */
    private $rateString;

    /**
     * Rate constructor.
     * @param string $currencyFrom
     * @param DateTimeImmutable $date
     * @param float|string $rate
     */
    public function __construct(string $currencyFrom, DateTimeImmutable $date, $rate)
    {
        $this->currencyFrom = $currencyFrom;
        $this->date = $date;
        $this->rateFloat = (float)$rate;
        $this->rateString = (string)$rate;
    }

    /**
     * @return string
     */
    public function getCurrencyFrom(): string
    {
        return $this->currencyFrom;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getRateFloat(): float
    {
        return (float)$this->rateFloat;
    }

    /**
     * @return string
     */
    public function getRateString(): string
    {
        return strval($this->rateString);
    }
}