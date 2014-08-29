<?php

namespace Soflomo\Purifier\Filter;

use HTMLPurifier;
use Zend\Filter\FilterInterface;
use Zend\Filter\AbstractFilter;

class Purifier extends AbstractFilter implements FilterInterface
{
    protected $purifier;

    /**
     * Comma seperated values as string
     * 
     * @var string
     */
    protected $allowedElements;
    
    /**
     * @param HTMLPurifier $purifier
     */
    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    /**
     * Returns the purifier with config allowed elements added if specified
     * 
     * @return HTMLPurifier
     */
    protected function getPurifier()
    {
        return $this->purifier;
    }
    
    /**
     * {@inheritdocs}
     */
    public function filter($value)
    {
        $purifier = $this->getPurifier();
        
        if ($this->allowedElements !== null) {
            $config = clone $purifier->config;
            $config->set('HTML.AllowedElements', $this->allowedElements);
        
            return $purifier->purify($value, $config);
        }
        
        return $purifier->purify($value);
    }
    
    /**
     * Array of values to be provided to HTMLPurifier_Config Attr.AllowedClasses
     * 
     * @param array $allowedClasses
     */
    public function setAllowedElements($allowedElements)
    {
        $this->allowedElements = $allowedElements;
    }
}