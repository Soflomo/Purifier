<?php
namespace PurifierTest\Filter;

class PurifierFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Soflomo\Purifier\Filter\Purifier
     */
	protected $filter;
	
	/**
	 * @var Zend\Filter\FilterPluginManager
	 */
	protected $filterManager;
	
	/**
	 * @var Zend\Form\FormElementManager
	 */
	protected $formManager;
	
	/**
	 * @var string
	 */
	protected $htmlTestString = '<p>Paragraph</p><script>DoEvil();</script><h1>Whitehat Header</h1><h1 onclick="MoreEvil()">Blackhat Header</h1>';
	
	/**
     * Setup the filter and services as close as posisble to 
     * during an application request
	 */
	public function setUp()
	{
		parent::setUp();
		$init = \Zend\Mvc\Application::init(\PurifierTest\Bootstrap::getConfig());
		
		$serviceManager = \PurifierTest\Bootstrap::getServiceManager();
		$filterChain = new \Zend\Filter\FilterChain();
		$this->filterManager = $filterChain->getPluginManager();
		$filterManager = $filterChain->getPluginManager();
		$this->filterManager->setServiceLocator($serviceManager);
		
		$filter = $serviceManager->get('htmlpurifier');

		$this->filterManager->setFactory('htmlpurifier', 'Soflomo\Purifier\Factory\PurifierFilterFactory');
		$this->filter = $this->filterManager->get('htmlpurifier');
		
		$this->formManager = $serviceManager->get('FormElementManager');
	}
	
	public function testCanAcceptAllowedElements()
	{
	    $this->filter->setOptions(['allowed_elements' => 'p']);
	    $result = $this->filter->filter($this->htmlTestString);
	    $this->assertEquals("<p>Paragraph</p>Whitehat HeaderBlackhat Header", $result);
	    
	    $result = $this->filter->filter($this->htmlTestString);
	}
	
	public function testConfigAndFilterState()
	{
	    $this->filter->setOptions(['allowed_elements' => 'p']);
	    $result = $this->filter->filter($this->htmlTestString);
	    $this->assertEquals("<p>Paragraph</p>Whitehat HeaderBlackhat Header", $result);
	     
	    $this->filterTwo = $this->filterManager->get('htmlpurifier');
	    
	    $result = $this->filterTwo->filter($this->htmlTestString);
	    $this->assertEquals("<p>Paragraph</p><h1>Whitehat Header</h1><h1>Blackhat Header</h1>", $result);
	}
	
	public function testFormManagerInit()
	{
	    $this->formManager->setInvokableClass('TestForm', '\Soflomo\Purifier\Test\Form\PurifierTestForm');
	    
	    $form = $this->formManager->get('TestForm');
        
	    $form->setData(['test' => $this->htmlTestString]);
	    $form->isValid();
	    
	    $this->assertEquals("<p>Paragraph</p>Whitehat HeaderBlackhat Header", $form->getData()['test']);
	}
}