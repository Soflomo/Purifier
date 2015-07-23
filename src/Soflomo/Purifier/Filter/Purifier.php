<?php

namespace Soflomo\Purifier\Filter;

use HTMLPurifier;
use Zend\Filter\FilterInterface;

class Purifier implements FilterInterface
{
    protected $purifier;

    /**
     * @var array
     */
    protected $purifierConfig;

    /**
     * @var bool
     */
    protected $persistentConf;



    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    /**
     * Sets config after initialization, that is used and passed to the
     * purify-method on filter() call
     * @param array $config
     * @param bool  $persistentConf
     */
    public function setConfig($config, $persistentConf = false)
    {
        $this->purifierConfig = $config;
        $this->persistentConf = (bool) $persistentConf;
    }



    /**
     * Retrieves the configuration set after initialization, resets config if
     * $this->persistentConf is set to FALSE (means in this case method returns
     * NULL on second call).
     * 
     * @return array|null
     */
    public function getConfig()
    {
        $config = $this->purifierConfig;
        if (!$this->persistentConf) {
            $this->purifierConfig = null;
        }
    
        return $config;
    }


    protected function getPurifier()
    {
        return $this->purifier;
    }

    /**
     * {@inheritdocs}
     */
    public function filter($value)
    {
        if ($this->purifierConfig) {
            return $this->getPurifier()->purify($value, $this->getConfig());
        }
        return $this->getPurifier()->purify($value);
    }
}
