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

    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    /**
     * Sets config after initialization, that is used and passed to the
     * purify-method on filter() call
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->purifierConfig = $config;
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
            return $this->getPurifier()->purify($value, $this->purifierConfig);
        }
        return $this->getPurifier()->purify($value);
    }
}
