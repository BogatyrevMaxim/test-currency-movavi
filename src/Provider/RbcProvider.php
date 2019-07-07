<?php

namespace RateRUB\Provider;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use RateRUB\Entity\Rate;
use RateRUB\Exception\ExtensionRequiredException;
use RateRUB\Provider\Client\ClientInterface;
use RateRUB\Provider\Exception\{FailAccessException, InvalidDataException};

class RbcProvider implements ProviderInterface
{
    private const DATE_URL_FORMAT = 'Y-m-d';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $urlTemplate = 'https://cash.rbc.ru/cash/json/converter_currency_rate/?currency_from=%s&currency_to=RUR&source=cbrf&sum=1&date=%s';
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * RbcProvider constructor.
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     */
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
        $url = sprintf($this->urlTemplate, $currencyFrom, $date->format(self::DATE_URL_FORMAT));

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

        $data = json_decode($content, true);
        if (json_last_error() > 0) {
            throw new InvalidDataException(json_last_error_msg());
        }

        if (!isset($data['status']) || $data['status'] !== 200) {
            throw new InvalidDataException(sprintf('Service incorrect response status: %d',
                $data['status'] ?? 'empty'));
        }

        if (empty($data['data']['rate1'])) {
            $this->logger->error('Currency rate not found', [
                'currencyFrom' => $currencyFrom,
                'url' => $url,
                'date' => $date->format(self::DATE_URL_FORMAT),
                'class' => self::class,
                'line' => __LINE__,
            ]);
            throw new InvalidDataException('Rate data not found');
        }

        $rate = $data['data']['rate1'];
        $this->logger->debug(sprintf('Find currency value: %s', $rate), [
            'currencyFrom' => $currencyFrom,
            'value' => $rate,
            'class' => self::class,
            'line' => __LINE__,
        ]);

        return new Rate($currencyFrom, $date, $rate);
    }
}