##BNM converter

Library uses National Bank of Moldova official gate to get currency rate within given date.

## Installation via Composer

```composer require "fruitware/bnm" : "2.*"```

## Examples

## Basic usage with current date

```php
// init client
$cacheDir = '/tmp/bnm'; // not required
$client = new \Fruitware\Bnm\Client($cacheDir);

// get rates on a specific date
$rates = $client->get(new DateTime());

// exchange 100 USD to MDL
$exchange = $rates->exchange('USD', 100, 'MDL');

// exchange 1000000 MDL to USD
$exchange = $rates->exchange('MDL', 1000000, 'USD');

// exchange 50000 EUR to MDL
$exchange = $rates->exchange('EUR', 50000, 'MDL');
```