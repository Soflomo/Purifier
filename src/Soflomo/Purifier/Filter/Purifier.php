<?php

namespace Soflomo\Purifier\Filter;

use HTMLPurifier;
use Zend\Filter\FilterInterface;
use Zend\Filter\AbstractFilter;

class Purifier extends AbstractFilter implements FilterInterface
{
    protected $purifier;

    /**
     * @var array
     */
    protected $allowedClasses;
    
    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    protected function getPurifier()
    {
        if ($this->allowedClasses !== null) {
            $config = $this->purifier->config;
            $config->set('AllowedElements', $this->allowedClasses);
            
            //$this->purifier->config = $config;
        }
        
        return $this->purifier;
    }
    
    /**
     * {@inheritdocs}
     */
    public function filter($value)
    {
        return $this->getPurifier()->purify($value);
    }
    
    /**
     * Array of values to be provided to HTMLPurifier_Config Attr.AllowedClasses
     * 
     * @param array $allowedClasses
     */
    public function setAllowedClasses($allowedClasses)
    {
        $this->allowedClasses = $allowedClasses;
    }
}