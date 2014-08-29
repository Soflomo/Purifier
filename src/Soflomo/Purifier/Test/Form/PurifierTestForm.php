<?php
namespace Soflomo\Purifier\Test\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class PurifierTestForm extends Form implements InputFilterProviderInterface
{
    /**
     * @var string
     */
    protected $name;
    
    public function __construct()
    {
        $this->name = 'test';
        parent::__construct($this->name);
    }    
    
    /**
     * Provide default input rules for this element
     *
     * Attaches strip tags filter
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'test' => [
                'required' => false,
                'filters' => [
                    array('name' => 'htmlpurifier',
                          'options' => [
        	                   'allowed_elements' => 'p'
                           ]
                    ),
                ],
            ]
        ];
    }

    
    
    public function init()
    {
        $this->add(array(
                'name' => 'test',
                'type' => 'Textarea',
        
        ));
    }
}