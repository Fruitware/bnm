<?php

namespace Fruitware\Bnm;

use Fruitware\Bnm\Exception\BnmException;

class Curs
{
    /**
     * @var string
     */
    const CURRENCY = 'MDL';

    /**
	 * @var \DateTime Date of exchange rate
	 */
    protected $_date;

    /**
	 * @var string Language
	 */
    protected $_lang;

    /**
	 * @var Rate[]
	 */
    protected $_rates = array();

    /**
     * @param \DateTime $date
     * @param string $cachePath
     * @param string $lang
     *
     * @return Curs
     */
    public static function init(\DateTime $date = null, $cachePath = null, $lang = 'ru')
    {
        static $self;

        if (!$self instanceof Curs) {
            $self = new static($date, $cachePath, $lang);
        }

        return $self;
    }

    /**
	 * Load XML file with exchange rates by date from http://www.bnm.md/
	 *
	 * @param \DateTime $date
	 * @param string $cachePath
	 * @param string $lang
	 *
	 * @throws Exception\BnmException
	 */
    protected function __construct(\DateTime $date = null, $cachePath = null, $lang = 'ru')
    {
        $this->_lang = $lang;
        $this->cachePath = $cachePath;

        if ($date === null) {
            $date = new \DateTime();
        }

        $this->_date = $date;

        $this->_load($cachePath);
    }

    /**
     * Get concrete exchange rate by char code
     *
     * @param string $currencyCode
     *
     * @return Rate
     * @throws BnmException
     */
    public static function getRate($currencyCode)
    {
        $self = static::init();

        $currencyCode = strtoupper($currencyCode);
        if (isset($self->_rates[$currencyCode])) {
            return $self->_rates[$currencyCode];
        }

        throw new BnmException('Such currency does not exist');
    }

    /**
     * Converts one currency to another withing current rate
     *
     * @param string $fromCurrencyCode
     * @param float  $quantity
     * @param string $toCurrencyCode
     *
     * @return float
     */
    public static function exchange($fromCurrencyCode, $quantity, $toCurrencyCode = null)
    {
        $self = static::init();

        return $self->_exchange($fromCurrencyCode, $quantity, $toCurrencyCode);
    }

    /**
	 * Converts one currency to another withing current rate
	 *
	 * @param string $fromCurrencyCode
	 * @param float $quantity
	 * @param string $toCurrencyCode
	 *
	 * @return float
	 */
    protected function _exchange($fromCurrencyCode, $quantity, $toCurrencyCode = null)
    {
        $fromQuantity = strtoupper($fromCurrencyCode) == static::CURRENCY ? $quantity : $this->getRate($fromCurrencyCode)->exchangeFrom($quantity);
        if (empty($toCurrencyCode) || strtoupper($toCurrencyCode) == static::CURRENCY) {
            return $fromQuantity;
        }

        return $this->getRate($toCurrencyCode)->exchangeTo($fromQuantity);
    }

    /**
	 * Creating folder where we save XML file. Save XML currency array to object currency array
     *
	 * @throws BnmException
	 */
    protected function _load()
    {
        $dir = is_null($this->cachePath) ? dirname(__FILE__).'/files' : $this->cachePath;
        $dir = rtrim($dir, '/').'/'.$this->_lang;
        $file = $dir.'/'.$this->_date->format('Y-m-d').'.xml';
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755)) {
                throw new BnmException(sprintf('Cant create directory %s', $dir));
            }
        }

        $xml = file_exists($file) ? simplexml_load_file($file) : $this->_saveRates($file, $this->_date);

        if (!isset($xml, $xml->Valute)) {
            throw new BnmException(sprintf('Error loading exchange for %s date', $this->_date->format('Y-m-d')));
        }

        foreach ($xml->Valute as $xmlRate) {
            $rate = new Rate($xmlRate);
            $this->_rates[strtoupper($rate->getCharCode())] = $rate;
        }
    }

    /**
	 * Load XML file
	 *
	 * @param \DateTime $date
	 *
	 * @return \SimpleXMLElement
	 * @throws BnmException
	 */
    protected function _loadRates(\DateTime $date)
    {
        $xml = @file_get_contents(sprintf('http://www.bnm.md/%s/official_exchange_rates?get_xml=1&date=%s', $this->_lang, $date->format('d.m.Y')));

        if (!$xml) {
            throw new BnmException('Error curs loading.');
        }

        try {
            return new \SimpleXMLElement($xml);
        } catch (\Exception $e) {
            throw new BnmException('Error loading xml', $e->getCode());
        }
    }

    /**
	 * Save XML data to XML File
	 *
	 * @param string $file
	 * @param \DateTime $date
	 *
	 * @return \SimpleXMLElement
	 * @throws BnmException
	 */
    protected function _saveRates($file, \DateTime $date)
    {
        $ratesXmlArray = $this->_loadRates($date);
        if ($ratesXmlArray->asXML($file)) {
            return $ratesXmlArray;
        }

        throw new BnmException('Error saving xml');
    }
}

