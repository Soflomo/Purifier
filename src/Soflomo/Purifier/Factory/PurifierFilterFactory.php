<?php
namespace Soflomo\Purifier\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PurifierFilterFactory implements FactoryInterface
{
    public function __construct($options)
    {
        exit(var_dump($options));
    }
    
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
	    $purifier = $serviceLocator->getServiceLocator()->get('HTMLPurifier');
	    
	    return new Filter\Purifier($purifier);
	}
}