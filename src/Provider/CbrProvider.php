<?php

namespace RateRUB\Provider;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use RateRUB\Entity\Rate;
use RateRUB\Exception\ExtensionRequiredException;
use RateRUB\Provider\Client\ClientInterface;
use RateRUB\Provider\Exception\{FailAccessException, InvalidDataException};

class CbrProvider implements ProviderInterface
{
    private const DATE_URL_FORMAT = 'd/m/Y';
    private $urlTemplate = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=%s';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * @param string $currencyFrom
     * @param DateTimeImmutable $date
     * @return Rate
     * @throws ExtensionRequiredException
     * @throws FailAccessException
     * @throws InvalidDataException
     */
    public function getRate(string $currencyFrom, DateTimeImmutable $date): Rate
    {
        $currencyFrom = mb_strtoupper($currencyFrom);
        $url = sprintf($this->urlTemplate, $date->format(self::DATE_URL_FORMAT));

        $this->logger->info(sprintf('Prepare url %s', $url), [
            'url' => $url,
            'currencyFrom' => $currencyFrom,
            'date' => $date->format(self::DATE_URL_FORMAT),
            'class' => self::class,
            'line' => __LINE__,
        ]);

        $content = $this->client->get($url);

        $this->logger->debug(sprintf('Get content %s', $url), [
            'url' => $url,
            'content' => $content,
            'currencyFrom' => $currencyFrom,
            'date' => $date->format(self::DATE_URL_FORMAT),
            'class' => self::class,
            'line' => __LINE__,
        ]);

        if (!$content) {
            throw new FailAccessException('Fail access for cash.rbc.ru');
        }

        try {
            $object = simplexml_load_string($content);
        } catch (\Throwable $exception) {
            throw new InvalidDataException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if ($errors = libxml_get_errors()) {
            throw new InvalidDataException(implode(PHP_EOL, $errors));
        }

        $rate = 0;
        $isFound = false;
        foreach ($object->Valute as $item) {
            if ((string)$item->CharCode === $currencyFrom) {
                $this->logger->debug(sprintf('Find currency value: %s', (string)$item->Value), [
                    'currencyFrom' => $currencyFrom,
                    'CharCode' => (string)$item->CharCode,
                    'value' => (string)$item->Value,
                    'class' => self::class,
                    'line' => __LINE__,
                ]);

                $rate = (float)str_replace(',', '.', (string)$item->Value);
                $isFound = true;
                break;
            }
        }

        if (!$isFound) {
            $this->logger->error('Currency not found', [
                'currencyFrom' => $currencyFrom,
                'url' => $url,
                'date' => $date->format(self::DATE_URL_FORMAT),
                'class' => self::class,
                'line' => __LINE__,
            ]);
            throw new InvalidDataException(sprintf('Currency not found: %s', $currencyFrom));
        }

        return new Rate($currencyFrom, $date, $rate);
    }
}