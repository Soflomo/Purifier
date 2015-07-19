<?php
/**
 * @author Stefano Torresi (http://stefanotorresi.it)
 * @license See the file LICENSE for copying permission.
 * ************************************************
 */

namespace Soflomo\Purifier\Test\Factory;

use PHPUnit_Framework_TestCase as TestCase;
use Soflomo\Purifier\Factory\HtmlPurifierFactory;
use Zend\ServiceManager\ServiceManager;

class HtmlPurifierFactoryTest extends TestCase
{
    /**
     * @var HtmlPurifierFactory
     */
    protected $factory;

    /**
     * @var ServiceManager
     */
    protected $serviceLocator;

    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->factory        = new HtmlPurifierFactory();
        $this->serviceLocator = new ServiceManager();
        $this->cacheDir       = './tests/_files/cache';

        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function testStandaloneFileInclusion()
    {
        $this->setConfigService(array(
            'soflomo_purifier' => array(
                'standalone' => true,
                'standalone_path' => './tests/_files/standalone_mock.php',
            )
        ));

        $this->factory->createService($this->serviceLocator);

        $this->assertTrue(class_exists('StandaloneMock', false));
    }

    public function testFactoryThrowsExceptionIfStandaloneFileNotFound()
    {
        $this->setConfigService(array(
            'soflomo_purifier' => array(
                'standalone' => true,
                'standalone_path' => 'bogus',
            )
        ));

        $this->setExpectedException('RuntimeException', 'Could not find standalone purifier file');
        $this->factory->createService($this->serviceLocator);
    }

    public function testFactoryCanSetDefinitions()
    {
        $validAttributes= array('foo','bar','baz','bat');

        $this->setConfigService(array(
            'soflomo_purifier' => array(
                'standalone' => false,
                'config' => array(
                    'HTML.DefinitionID' => 'custom definitions',
                    'Cache.DefinitionImpl' => null,
                ),
                'definitions' => array(
                    'HTML' => array(
                        'addAttribute' => array('a', 'foo', new \HTMLPurifier_AttrDef_Enum($validAttributes)),
                    ),
                ),
            )
        ));

        /** @var \HTMLPurifier $purifier */
        $purifier = $this->factory->createService($this->serviceLocator);

        /** @var \HTMLPurifier_HTMLDefinition $definition */
        $definition = $purifier->config->getDefinition('HTML');
        $this->assertInstanceOf('HTMLPurifier_HTMLDefinition', $definition);

        /** @var \HTMLPurifier_ElementDef $elementDefinition */
        $elementDefinition = $definition->info['a'];
        $this->assertInstanceOf('HTMLPurifier_ElementDef', $elementDefinition);

        /** @var \HTMLPurifier_AttrDef_Enum $attributeDefinition */
        $attributeDefinition = $elementDefinition->attr['foo'];
        $this->assertInstanceOf('HTMLPurifier_AttrDef_Enum', $attributeDefinition);

        foreach($validAttributes as $value) {
            $this->assertArrayHasKey($value, $attributeDefinition->valid_values);
        }
    }

    public function testDefinitionCache()
    {
        $this->setConfigService(array(
            'soflomo_purifier' => array(
                'standalone' => false,
                'config' => array(
                    'HTML.DefinitionID' => 'custom definitions',
                    'Cache.SerializerPath' => $this->cacheDir
                ),
                'definitions' => array(
                    'HTML' => array(
                        'addAttribute' => array('a', 'foo', new \HTMLPurifier_AttrDef_Enum(array('asd'))),
                    ),
                ),
            )
        ));

        // create the purifier and get the definition a first time to warm up the cache
        $purifier = $this->factory->createService($this->serviceLocator);
        $purifier->config->getDefinition('HTML');

        $this->assertTrue(is_dir($this->cacheDir . '/HTML'));
        $cacheFiles = glob($this->cacheDir . '/HTML/*');
        $this->assertGreaterThan(0, count($cacheFiles));

        // now repeat
        $this->setConfigService(array(
            'soflomo_purifier' => array(
                'standalone' => false,
                'config' => array(
                    'HTML.DefinitionID' => 'custom definitions',
                    'Cache.SerializerPath' => $this->cacheDir
                ),
                'definitions' => array(
                    'HTML' => array(
                        'addAttribute' => array('a', 'foo', new \HTMLPurifier_AttrDef_Enum(array('asd'))),
                    ),
                ),
            )
        ), true);

        $purifier = $this->factory->createService($this->serviceLocator);

        /** @var \HTMLPurifier_HTMLDefinition $definition */
        $definition = $purifier->config->getDefinition('HTML');
        $this->assertInstanceOf('HTMLPurifier_HTMLDefinition', $definition);

        /** @var \HTMLPurifier_ElementDef $elementDefinition */
        $elementDefinition = $definition->info['a'];
        $this->assertInstanceOf('HTMLPurifier_ElementDef', $elementDefinition);

        /** @var \HTMLPurifier_AttrDef_Enum $attributeDefinition */
        $attributeDefinition = $elementDefinition->attr['foo'];
        $this->assertInstanceOf('HTMLPurifier_AttrDef_Enum', $attributeDefinition);
    }

    protected function tearDown()
    {
        $this->recursiveRmDir($this->cacheDir);
    }


    protected function setConfigService($array, $reset = false)
    {
        if ($reset) {
            $this->serviceLocator = new ServiceManager();
        }
        $this->serviceLocator->setService('config', $array);
    }

    protected function recursiveRmDir($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (! is_dir($path)) {
            return;
        }

        $children = glob($path . '*');

        foreach ($children as $child) {
            if (is_dir($child)) {
                $this->recursiveRmDir($child);
                continue;
            }
            unlink($child);
        }

        rmdir($path);
    }
}
