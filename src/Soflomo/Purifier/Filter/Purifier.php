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
        if ($this->allowedElements !== null) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.AllowedElements', $this->allowedElements);
            
            $this->purifier->config = $config;
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
    public function setAllowedElements($allowedElements)
    {
        $this->allowedElements = $allowedElements;
    }
}