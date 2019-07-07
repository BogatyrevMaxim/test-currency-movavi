# test-currency-movavi

```php
<?php
require_once '../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RateRUB\Provider\CbrProvider;
use RateRUB\Provider\RbcProvider;
use RateRUB\Provider\ProviderFactory;

// create logger
$stream = new StreamHandler(__DIR__ . '/your.log', Logger::DEBUG);
$logger = new Monolog\Logger('default', [$stream]);

// create client
$client = new RateRUB\Provider\Client\Client();
$factory = new ProviderFactory($client, $logger);

$service = new \RateRUB\RateService(...$factory->getProviders());
$rate = $service->getRate(\RateRUB\Entity\Currency::EUR, new \DateTimeImmutable('-1 week'));
var_dump($rate);

$rate = $service->getRate(\RateRUB\Entity\Currency::USD, new \DateTimeImmutable('-1 week'));
var_dump($rate);
```

Return string. Using BCMath.
```
string(7) "71.8179"
string(7) "63.0756"
```
