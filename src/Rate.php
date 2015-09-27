<?php

namespace Fruitware\Bnm;

class Rate
{
    /**
	 * @var \SimpleXMLElement
	 */
    protected $node;

    /**
	 * @param \SimpleXMLElement $node
	 */
    public function __construct(\SimpleXMLElement $node)
    {
        $this->node = $node;
    }

    /**
	 * @return int
	 */
    public function getId()
    {
        return (int) $this->node['ID'];
    }

    /**
	 * @return int
	 */
    public function getNumCode()
    {
        return (int) $this->node->NumCode;
    }

    /**
	 * @return string
	 */
    public function getCharCode()
    {
        return (string) $this->node->CharCode;
    }

    /**
	 * @return float
	 */
    public function getNominal()
    {
        return (int) $this->node->Nominal;
    }

    /**
	 * @return string
	 */
    public function getName()
    {
        return (string) $this->node->Name;
    }

    /**
	 * @return float
	 */
    public function getValue()
    {
        return (double) $this->node->Value;
    }

    /**
	 * Convert MDL to current currency
	 *
	 * @param float $quantity
	 *
	 * @return float
	 */
    public function exchangeTo($quantity)
    {
        $rate    = $this->getValue();
        $nominal = $this->getNominal();

        return (double) ($quantity / $nominal / $rate);
    }

    /**
	 * Convert current currency to MDL
	 *
	 * @param float $quantity
	 *
	 * @return float
	 */
    public function exchangeFrom($quantity)
    {
        $rate    = $this->getValue();
        $nominal = $this->getNominal();

        return (double) ($quantity * $rate / $nominal);
    }
}
