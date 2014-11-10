<?php

namespace Fruitware\Bnm;

use DateTime;
use GuzzleHttp\Client;
use Fruitware\Bnm\Exception\BnmException;

class Curs {
	/**
	 * @var DateTime Date of exchange rate
	 */
	private $_date;

	/**
	 * @var string
	 */
	private $_lang;

	/**
	 * @var string Rate array
	 */
	private $_ratesObjectArray = [ ];

	/**
	 * Load XML file with exchange rates by date from http://www.bnm.md/
	 *
	 * @param DateTime $date
	 * @param string NULL $customPath
	 * @param string $lang
	 *
	 * @throws Exception\BnmException
	 */
	public function __construct( DateTime $date = NULL, $customPath = NULL, $lang = 'ru' ) {
		$this->_lang = $lang;

		$currDate = new DateTime();
		if ($date === NULL || $date > $currDate) {
			$date = $currDate;
		}

		$this->_date = $date;

		$this->_load($customPath);
	}

	/**
	 * Converts one currency to another withing current rate
	 *
	 * @param $currencyFromCode
	 * @param $quantity
	 * @param NULL $currencyToCode
	 *
	 * @return float
	 */
	public static function exchange( $currencyFromCode, $quantity, $currencyToCode = NULL ) {
		$obj = new self();

		return $obj->_exchange( $currencyFromCode, $quantity, $currencyToCode );
	}

	/**
	 * Converts one currency to another withing current rate
	 *
	 * @param $currencyFromCode
	 * @param $quantity
	 * @param NULL $currencyToCode
	 *
	 * @return float
	 */
	protected function _exchange( $currencyFromCode, $quantity, $currencyToCode = NULL ) {
		$fromQuantity = strtolower( $currencyFromCode ) == 'mdl' ? $quantity : $this->getRate( $currencyFromCode )->exchangeFrom( $quantity );
		if ( empty( $currencyToCode ) || strtolower( $currencyToCode ) == 'mdl' ) {
			return $fromQuantity;
		}

		return $this->getRate( $currencyToCode )->exchangeTo( $fromQuantity );

	}

	/**
	 * Creating folder where we save XML file. Save XML currency array to object currency array
	 *
	 * @param string $customPath
	 *
	 * @throws BnmException
	 */
	protected function _load( $customPath ) {
		$dir    = (is_null($customPath) ? dirname( __FILE__ ) : $customPath) . '/files/';
		$source = $dir . '/' . $this->_date->format( 'Y-m-d' ) . '.xml';
		if ( ! is_dir( $dir ) ) {
			if ( ! mkdir( $dir, 0755 ) ) {
				throw new BnmException( 'Cant create directory for files' );
			}
		}

		$xml = file_exists( $source ) ? simplexml_load_file( $source ) : $this->saveRates( $source, $this->_date );

		if ( ! isset( $xml, $xml->Valute ) ) {
			throw new BnmException( 'Error loading' );
		}

		foreach ( $xml->Valute as $row ) {
			$bnmRate = new Rate( $row );
			$this->_ratesObjectArray[ strtolower( $bnmRate->getCharCode() ) ] = $bnmRate;
		}
	}

	/**
	 * Get concrete exchange rate by char code
	 *
	 * @param string $currCode
	 *
	 * @return Rate
	 * @throws BnmException
	 */
	public function getRate( $currCode ) {
		$currCode = strtolower( $currCode );
		if ( isset( $this->_ratesObjectArray[ $currCode ] ) ) {
			return $this->_ratesObjectArray[ $currCode ];
		}

		throw new BnmException( 'Such currency does not exist' );
	}

	/**
	 * Load XML file
	 *
	 * @param DateTime $date
	 *
	 * @return \SimpleXMLElement
	 * @throws BnmException
	 */
	private function loadRates( DateTime $date ) {
		/**
		 * @var \GuzzleHttp\Client $client
		 */
		$client = new Client();
		/**
		 * @var \GuzzleHttp\Message\Response $result
		 */
		$result = $client->get( 'http://www.bnm.md/' . $this->_lang . '/official_exchange_rates', [
			'query' => [ 'get_xml' => '1', 'date' => $date->format( 'd.m.Y' ) ]
		] );
		if ( $result->getStatusCode() !== 200 ) {
			throw new BnmException( 'Error loading.', $result->getStatusCode() );
		}
		try {
			return $result->xml();
		}
		catch ( Exception $e ) {
			throw new BnmException( 'Error loading xml' , $e->getCode());
		}
	}

	/**
	 * Save XML data to XML File
	 *
	 * @param string $filename
	 * @param DateTime $date
	 *
	 * @return \SimpleXMLElement
	 * @throws BnmException
	 */
	private function saveRates( $filename, DateTime $date ) {
		$ratesXmlArray = $this->loadRates( $date );
		if ( $ratesXmlArray->asXML( $filename ) ) {
			return $ratesXmlArray;
		}

		throw new BnmException( 'Error saving xml' );
	}
}
