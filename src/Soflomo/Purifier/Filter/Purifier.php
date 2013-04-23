<?php

namespace Soflomo\Purifier\Filter;

use HTMLPurifier;
use Zend\Filter\FilterInterface;

class Purifier implements FilterInterface
{
    protected $purifier;

    public function __construct(HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
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
        return $this->getPurifier()->purify($value);
    }
}