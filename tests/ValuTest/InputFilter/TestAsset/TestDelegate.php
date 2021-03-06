<?php
namespace ValuTest\InputFilter\TestAsset;

use Valu\InputFilter\Configurator\Delegate\DelegateInterface;
use \ArrayObject;
use Valu\InputFilter\ConfiguratorInterface;
use Zend\InputFilter\InputFilterInterface;

class TestDelegate implements DelegateInterface
{
    
    public $getHandler;
    
    public $prepareHandler;
    
    public $finalizeHandler;
    
    public function getInputFilterSpecifications(ConfiguratorInterface $configurator, $name)
    {
        if($this->getHandler){
            return call_user_func($this->getHandler, $configurator, $name);
        }
    }
    
    public function prepareInputFilterSpecifications(ConfiguratorInterface $configurator, $name, ArrayObject $specifications)
    {
        if($this->prepareHandler){
            call_user_func($this->prepareHandler, $configurator, $name, $specifications);
        }
    }
    
    public function finalizeInputFilter(ConfiguratorInterface $configurator, $name, InputFilterInterface $inputFilter)
    {
        if($this->finalizeHandler){
            call_user_func($this->finalizeHandler, $configurator, $name, $inputFilter);
        }
    }
}