<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace ZendTest\Feed\Reader;

use PHPUnit\Framework\TestCase;
use Zend\Feed\Reader\Extension\Syndication\Feed;
use Zend\Feed\Reader\Extension\WellFormedWeb\Entry;
use Zend\Feed\Reader\ExtensionManagerInterface;
use Zend\Feed\Reader\StandaloneExtensionManager;

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
            'Atom\Entry'            => ['Atom\Entry', \Zend\Feed\Reader\Extension\Atom\Entry::class],
            'Atom\Feed'             => ['Atom\Feed', \Zend\Feed\Reader\Extension\Atom\Feed::class],
            'Content\Entry'         => ['Content\Entry', \Zend\Feed\Reader\Extension\Content\Entry::class],
            'CreativeCommons\Entry' => [
                'CreativeCommons\Entry',
                \Zend\Feed\Reader\Extension\CreativeCommons\Entry::class
            ],
            'CreativeCommons\Feed'  => [
                'CreativeCommons\Feed',
                \Zend\Feed\Reader\Extension\CreativeCommons\Feed::class
            ],
            'DublinCore\Entry'      => ['DublinCore\Entry', \Zend\Feed\Reader\Extension\DublinCore\Entry::class],
            'DublinCore\Feed'       => ['DublinCore\Feed', \Zend\Feed\Reader\Extension\DublinCore\Feed::class],
            'Podcast\Entry'         => ['Podcast\Entry', \Zend\Feed\Reader\Extension\Podcast\Entry::class],
            'Podcast\Feed'          => ['Podcast\Feed', \Zend\Feed\Reader\Extension\Podcast\Feed::class],
            'Slash\Entry'           => ['Slash\Entry', \Zend\Feed\Reader\Extension\Slash\Entry::class],
            'Syndication\Feed'      => ['Syndication\Feed', Feed::class],
            'Thread\Entry'          => ['Thread\Entry', \Zend\Feed\Reader\Extension\Thread\Entry::class],
            'WellFormedWeb\Entry'   => ['WellFormedWeb\Entry', Entry::class],
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

    public function testAddAcceptsValidExtensionClasses()
    {
        $ext = $this->createMock(\Zend\Feed\Reader\Extension\AbstractEntry::class);
        $this->extensions->add('Test/Entry', get_class($ext));
        $this->assertTrue($this->extensions->has('Test/Entry'));
        $ext = $this->createMock(\Zend\Feed\Reader\Extension\AbstractFeed::class);
        $this->extensions->add('Test/Feed', get_class($ext));
        $this->assertTrue($this->extensions->has('Test/Feed'));
    }

    public function testAddRejectsInvalidExtensions()
    {
        $this->expectException(\Zend\Feed\Reader\Exception\InvalidArgumentException::class);
        $this->extensions->add('Test/Entry', 'blah');
    }

    public function testExtensionRemoval()
    {
        $ext = $this->createMock(\Zend\Feed\Reader\Extension\AbstractEntry::class);
        $this->extensions->add('Test/Entry', get_class($ext));
        $this->assertTrue($this->extensions->has('Test/Entry'));
        $this->extensions->remove('Test/Entry');
        $this->assertFalse($this->extensions->has('Test/Entry'));
    }
}
