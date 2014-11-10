<?php

namespace Fruitware\Bnm;

/**
 * Concrete exchange object
 * Class BnmRate
 * @package Fruitware\Bnm
 */
class Rate {
	/**
	 * @var \SimpleXMLElement
	 */
	private $_node;

	/**
	 * @param \SimpleXMLElement $node
	 */
	public function __construct( \SimpleXMLElement $node ) {
		$this->_node = $node;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return (int) $this->_node['ID'];
	}

	/**
	 * @return int
	 */
	public function getNumCode() {
		return (int) $this->_node->NumCode;
	}

	/**
	 * @return string
	 */
	public function getCharCode() {
		return (string) $this->_node->CharCode;
	}

	/**
	 * @return float
	 */
	public function getNominal() {
		return (int) $this->_node->Nominal;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return (string) $this->_node->Name;
	}

	/**
	 * @return float
	 */
	public function getValue() {
		return (double) $this->_node->Value;
	}

	/**
	 * @call exchangeTo
	 *
	 * @param $quantity
	 *
	 * @return float
	 */
	public function exchange( $quantity ) {
		return $this->exchangeTo( $quantity );
	}

	/**
	 * Convert MDL to current currency
	 *
	 * @param $quantity string
	 *
	 * @return float
	 */
	public function exchangeTo( $quantity ) {
		$rate    = $this->getValue();
		$nominal = $this->getNominal();

		$result = (double) ( $quantity / $nominal / $rate );

		return $result;
	}

	/**
	 * Convert current currency to MDL
	 *
	 * @param $quantity string
	 *
	 * @return float
	 */
	public function exchangeFrom( $quantity ) {
		$rate    = $this->getValue();
		$nominal = $this->getNominal();

		$result = (double) ( $quantity * $rate / $nominal );

		return $result;
	}
}
