<?php

namespace Fruitware\Bnm;

use Fruitware\Bnm\Exception\BnmException;

class Rates extends \ArrayIterator
{
    /**
     * @var string
     */
    const CURRENCY = 'MDL';

    /**
     * Get concrete rate by currency code
     *
     * @param string $currencyCode
     *
     * @return Rate
     * @throws BnmException
     */
    public function get($currencyCode)
    {
        $currencyCode = strtoupper($currencyCode);
        if ($this->offsetExists($currencyCode)) {
            return $this->offsetGet($currencyCode);
        }

        throw new BnmException(sprintf('%s currency not found', $currencyCode));
    }

    /**
     * Converts one currency to another within current rate
     *
     * @param string $fromCurrencyCode
     * @param float  $quantity
     * @param string $toCurrencyCode
     *
     * @return float
     */
    public function exchange($fromCurrencyCode, $quantity, $toCurrencyCode)
    {
        if ($fromCurrencyCode === $toCurrencyCode) {
            return $quantity;
        }

        $fromQuantity = strtoupper($fromCurrencyCode) === static::CURRENCY ? $quantity : $this->get($fromCurrencyCode)->exchangeFrom($quantity);
        if (empty($toCurrencyCode) || strtoupper($toCurrencyCode) === static::CURRENCY) {
            return $fromQuantity;
        }

        return $this->get($toCurrencyCode)->exchangeTo($fromQuantity);
    }
}
