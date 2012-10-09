<?php
namespace Valu\Selector\SimpleSelector;

use Valu\Selector\SimpleSelector\Attribute,
    Valu\Selector\SimpleSelector\AbstractSelector;

class Role extends Attribute
{
    protected $name = AbstractSelector::SELECTOR_ROLE;
    
    public function __construct($value)
    {
        parent::__construct('role', Attribute::OPERATOR_EQUALS, $value);
    }
    
    public function getPattern(){
        $enclosure = self::getEnclosure();
        return array_pop($enclosure) . $this->getCondition();
    }
    
    public static function getEnclosure()
    {
        return array('$');
    }
}