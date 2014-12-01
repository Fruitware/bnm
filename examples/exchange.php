<?php

require_once __DIR__.'/../vendor/autoload.php';

$cachePath =  __DIR__.'/cache';

$exchange = \Fruitware\Bnm\Curs::exchange('USD', 10, 'EUR');
var_dump($exchange);

$curs = \Fruitware\Bnm\Curs::init(null, $cachePath, 'en');

$exchange = $curs->exchange('USD', 100, 'MDL');
var_dump($exchange);

$exchange = $curs->exchange('MDL', 1000000, 'USD');
var_dump($exchange);

$exchange = $curs::exchange('EUR', 50000, 'MDL');
var_dump($exchange);