<?php
namespace ValuCore;

use Zend\ModuleManager\Feature;
use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;

class Module
    implements Feature\AutoloaderProviderInterface,
               Feature\ConfigProviderInterface
{
    /**
     * getAutoloaderConfig() defined by AutoloaderProvider interface.
     *
     * @see AutoloaderProvider::getAutoloaderConfig()
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    'Valu' => __DIR__,
                ),
            ),
        );
    }
    
    /**
     * getConfig implementation for ConfigListener
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}