<?php
namespace Soflomo\Purifier\Factory;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

class PurifierFilterFactory implements ServiceLocatorAwareInterface
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    
	public function __invoke($options)
	{
	    exit(var_dump($options));
	    $purifier = $serviceLocator->getServiceLocator()->get('HTMLPurifier');
	    
	    return new Filter\Purifier($purifier);
	}
}