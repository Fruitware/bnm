<?php

namespace Fruitware\Bnm;
;
use DateTime;
use Fruitware\Bnm\Exception\BnmException;
use SimpleXMLElement;

class Client
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
	 * @param string $cacheDir
	 *
	 * @throws Exception\BnmException
	 */
    public function __construct($cacheDir = null)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param DateTime $date
     * @param string   $lang
     *
     * @return Rates
     * @throws BnmException
     */
    public function get(DateTime $date, $lang = 'en')
    {
        $lang = $this->getValidLang($lang);

        if (!$data = $this->loadFromCache($date, $lang)) {
            $data = $this->load($date, $lang);
            $this->save($date, $lang, $data);
        }

        return $this->parse($data);
    }

    /**
     * @param string $lang
     *
     * @return string
     * @throws BnmException
     */
    protected function getValidLang($lang)
    {
        $lang = strtolower($lang);

        if(!in_array($lang, array('en', 'ru', 'md'))) {
            throw new BnmException('Invalid lang');
        }

        return $lang;
    }

    /**
     * Load XML file
     *
     * @param DateTime $date
     * @param string   $lang
     *
     * @throws BnmException
     * @return string
     */
    protected function load(DateTime $date, $lang)
    {
        $data = @file_get_contents(sprintf('http://www.bnm.md/%s/official_exchange_rates?get_xml=1&date=%s', $lang, $date->format('d.m.Y')));

        if (!$data) {
            throw new BnmException('Error loading data');
        }

        return $data;
    }

    /**
     * Load XML file
     *
     * @param DateTime $date
     * @param string   $lang
     *
     * @return string|false
     */
    protected function loadFromCache(DateTime $date, $lang)
    {
        $file = $this->getCacheFileName($date, $lang);

        return file_exists($file) ? file_get_contents($file) : false;
    }

    /**
     * @param string $data
     *
     * @return Rates
     * @throws BnmException
     */
    protected function parse($data)
    {
        try {
            $xml = new SimpleXMLElement($data);
        }
        catch (\Exception $e) {
            throw new BnmException('Error loading xml', $e->getCode());
        }

        if (!isset($xml, $xml->Valute)) {
            throw new BnmException('Error parse data. Wrong xml structure');
        }

        $rates = array();
        foreach ($xml->Valute as $xmlRate) {
            $rate = new Rate($xmlRate);
            $rates[strtoupper($rate->getCharCode())] = $rate;
        }

        return new Rates($rates);
    }

    /**
	 * Save data to XML File
	 *
	 * @param DateTime $date
     * @param string   $lang
     * @param string   $data
	 *
	 * @return bool
	 * @throws BnmException
	 */
    protected function save(dateTime $date, $lang, $data)
    {
        if (!$this->cacheDir) {
            return false;
        }

        $dir = $this->getCacheDirWithLang($lang);
        $file = $dir.'/'.$date->format('Y-m-d').'.xml';

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new BnmException(sprintf('Can not create cache directory %s', $dir));
            }
        }

        if (false === file_put_contents($file, $data, LOCK_EX)) {
            throw new BnmException('Error saving data');
        }

        return true;
    }

    /**
     * @param string $lang
     *
     * @return string
     */
    protected function getCacheDirWithLang($lang)
    {
        return rtrim($this->cacheDir, '/').'/'.$lang;
    }

    /**
     * @param DateTime $date
     * @param string   $lang
     *
     * @return string
     */
    protected function getCacheFileName(DateTime $date, $lang)
    {
        return $this->getCacheDirWithLang($lang).'/'.$date->format('Y-m-d').'.xml';
    }
}

