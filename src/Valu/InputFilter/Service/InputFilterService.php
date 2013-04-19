<?php
namespace Valu\InputFilter\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Valu\InputFilter\InputFilterRepository;
use ValuSo\Annotation as ValuService;

class InputFilterService
    implements ServiceLocatorAwareInterface
{
    /**
     * Input filter repository
     * 
     * @var \Valu\InputFilter\InputFilterRepository
     */
    protected $repository;
    
    /**
     * Service locator
     *
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;
    
    public static function version()
    {
        return '0.1';
    }
    
    public function __construct(InputFilterRepository $inputFilterRepository)
    {
        $this->repository = $inputFilterRepository;
    }
    
    /**
     * Retrieve input filter by name
     * 
     * @param string $name
     * @return \Zend\InputFilter\InputFilterInterface|null
     */
    public function get($name)
    {
        $inputFilter = $this->repository->get($name);
        
        if ($inputFilter && 
            $inputFilter instanceof ServiceLocatorAwareInterface && 
            !$inputFilter->getServiceLocator() &&
            $this->getServiceLocator()) {
            
            $inputFilter->setServiceLocator($this->getServiceLocator());
        }
        
        return $inputFilter;
    }
    
    /**
     * Reload input filter
     * 
     * @param string $name
     * @return boolean
     */
    public function reload($name)
    {
        return $this->repository->reload($name);
    }
    
    /**
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::getServiceLocator()
     * @ValuService\Exclude
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    /**
     * @see \Zend\ServiceManager\ServiceLocatorAwareInterface::setServiceLocator()
     * @ValuService\Exclude
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}