<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader\Entry;

use Zend\Feed\Reader;

/**
* @group Zend_Feed
* @group Zend_Feed_Reader
*/
class CommonTest extends \PHPUnit_Framework_TestCase
{
    protected $feedSamplePath = null;

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files/Common';
    }

    /**
     * Check DOM Retrieval and Information Methods
     */
    public function testGetsDomDocumentObject()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $this->assertInstanceOf('DOMDocument', $entry->getDomDocument());
    }

    public function testGetsDomXpathObject()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $this->assertInstanceOf('DOMXPath', $entry->getXpath());
    }

    public function testGetsXpathPrefixString()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('//atom:entry[1]', $entry->getXpathPrefix());
    }

    public function testGetsDomElementObject()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $this->assertInstanceOf('DOMElement', $entry->getElement());
    }

    public function testSaveXmlOutputsXmlStringForEntry()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $expected = file_get_contents($this->feedSamplePath.'/atom_rewrittenbydom.xml');
        $expected = str_replace("\r\n", "\n", $expected);
        $this->assertEquals($expected, $entry->saveXml());
    }

    public function testGetsNamedExtension()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $this->assertInstanceOf('Zend\Feed\Reader\Extension\Atom\Entry', $entry->getExtension('Atom'));
    }

    public function testReturnsNullIfExtensionDoesNotExist()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getExtension('Foo'));
    }

    /**
     * @group ZF-8213
     */
    public function testReturnsEncodingOfFeed()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('UTF-8', $entry->getEncoding());
    }

    /**
     * @group ZF-8213
     */
    public function testReturnsEncodingOfFeedAsUtf8IfUndefined()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom_noencodingdefined.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('UTF-8', $entry->getEncoding());
    }

    /**
    * When not passing the optional argument type
    */
    public function testFeedEntryCanDetectFeedType()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $stub = $this->getMockForAbstractClass(
            'Zend\Feed\Reader\Entry\AbstractEntry',
            [new Reader\Reader(), $entry->getElement(), $entry->getId()]
        );
        $this->assertEquals($entry->getType(), $stub->getType());
    }

    /**
    * When passing a newly created DOMElement without any DOMDocument assigned
    */
    public function testFeedEntryCanSetAnyType()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/atom.xml')
        );
        $entry = $feed->current();
        $domElement = new \DOMElement($entry->getElement()->tagName);
        $stub = $this->getMockForAbstractClass(
            'Zend\Feed\Reader\Entry\AbstractEntry',
            [new Reader\Reader(), $domElement, $entry->getId()]
        );
        $this->assertEquals($stub->getType(), Reader\Reader::TYPE_ANY);
    }
}
