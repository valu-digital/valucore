<?php
namespace Valu\Selector\SimpleSelector;

use Valu\Selector\SimpleSelector\Attribute;
use Valu\Selector\SimpleSelector\AbstractSelector;

class Id extends Attribute
{

    protected $name = AbstractSelector::SELECTOR_ID;

    public function __construct($value)
    {
        parent::__construct('id', Attribute::OPERATOR_EQUALS, $value);
    }

    public function getPattern()
    {
        $enclosure = self::getEnclosure();
        return array_pop($enclosure) . $this->getCondition();
    }

    public static function getEnclosure()
    {
        return array(
            '#'
        );
    }
}