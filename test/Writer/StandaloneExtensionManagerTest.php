<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace ZendTest\Feed\Writer;

use PHPUnit\Framework\TestCase;
use Zend\Feed\Writer\StandaloneExtensionManager;
use Zend\Feed\Writer\ExtensionManagerInterface;

class StandaloneExtensionManagerTest extends TestCase
{
    /**
     * @var StandaloneExtensionManager
     */
    private $extensions;

    public function setUp()
    {
        $this->extensions = new StandaloneExtensionManager();
    }

    public function testIsAnExtensionManagerImplementation()
    {
        $this->assertInstanceOf(ExtensionManagerInterface::class, $this->extensions);
    }

    public function defaultPlugins()
    {
        return [
            'Atom\Renderer\Feed'           => [
                'Atom\Renderer\Feed' => \Zend\Feed\Writer\Extension\Atom\Renderer\Feed::class
            ],
            'Content\Renderer\Entry'       => [
                'Content\Renderer\Entry' => \Zend\Feed\Writer\Extension\Content\Renderer\Entry::class
            ],
            'DublinCore\Renderer\Entry'    => [
                'DublinCore\Renderer\Entry' => \Zend\Feed\Writer\Extension\DublinCore\Renderer\Entry::class
            ],
            'DublinCore\Renderer\Feed'     => [
                'DublinCore\Renderer\Feed' => \Zend\Feed\Writer\Extension\DublinCore\Renderer\Feed::class
            ],
            'ITunes\Entry'                 => ['ITunes\Entry' => \Zend\Feed\Writer\Extension\ITunes\Entry::class],
            'ITunes\Feed'                  => ['ITunes\Feed' => \Zend\Feed\Writer\Extension\ITunes\Feed::class],
            'ITunes\Renderer\Entry'        => [
                'ITunes\Renderer\Entry' => \Zend\Feed\Writer\Extension\ITunes\Renderer\Entry::class
            ],
            'ITunes\Renderer\Feed'         => [
                'ITunes\Renderer\Feed' => \Zend\Feed\Writer\Extension\ITunes\Renderer\Feed::class
            ],
            'Slash\Renderer\Entry'         => [
                'Slash\Renderer\Entry' => \Zend\Feed\Writer\Extension\Slash\Renderer\Entry::class
            ],
            'Threading\Renderer\Entry'     => [
                'Threading\Renderer\Entry' => \Zend\Feed\Writer\Extension\Threading\Renderer\Entry::class
            ],
            'WellFormedWeb\Renderer\Entry' => [
                'WellFormedWeb\Renderer\Entry' => \Zend\Feed\Writer\Extension\WellFormedWeb\Renderer\Entry::class
            ],
        ];
    }

    /**
     * @dataProvider defaultPlugins
     */
    public function testHasAllDefaultPlugins($pluginName, $pluginClass)
    {
        $this->assertTrue($this->extensions->has($pluginName));
    }

    /**
     * @dataProvider defaultPlugins
     */
    public function testCanRetrieveDefaultPluginInstances($pluginName, $pluginClass)
    {
        $extension = $this->extensions->get($pluginName);
        $this->assertInstanceOf($pluginClass, $extension);
    }

    /**
     * @dataProvider defaultPlugins
     */
    public function testEachPluginRetrievalReturnsNewInstance($pluginName, $pluginClass)
    {
        $extension = $this->extensions->get($pluginName);
        $this->assertInstanceOf($pluginClass, $extension);

        $test = $this->extensions->get($pluginName);
        $this->assertInstanceOf($pluginClass, $test);
        $this->assertNotSame($extension, $test);
    }

    public function testPluginAddRemove()
    {
        $this->extensions->add('Test/Test', 'mytestextension');
        $this->assertTrue($this->extensions->has('Test/Test'));
        $this->extensions->remove('Test/Test');
        $this->assertFalse($this->extensions->has('Test/Test'));
    }
}
