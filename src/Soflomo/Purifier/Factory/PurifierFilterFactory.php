<?php
namespace Soflomo\Purifier\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Soflomo\Purifier\Filter\Purifier;

class PurifierFilterFactory implements FactoryInterface
{
    /**
     * array of options for htmlpurify
     * 
     * @var array
     */
    protected $options;

    /**
     * @param mixed $options
     */
    public function __construct($options = null)
    {
        $this->options = $options;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $purifier = $serviceLocator->getServiceLocator()->get('HTMLPurifier');

        $filter = new Purifier($purifier);

        if ($this->options === null || empty($this->options)) {
            return $filter;
        } else {
            return $filter->setOptions($this->options);
        }
    }
}
