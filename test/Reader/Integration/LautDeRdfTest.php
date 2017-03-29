<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader\Integration;

use Zend\Feed\Reader;

/**
* @group Zend_Feed
* @group Zend_Feed_Reader
*/
class LautDeRdfTest extends \PHPUnit_Framework_TestCase
{
    protected $feedSamplePath = null;

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files/laut.de-rdf.xml';
    }

    /**
     * Feed level testing
     */

    public function testGetsTitle()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('laut.de - news', $feed->getTitle());
    }

    public function testGetsAuthors()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals([['name'=>'laut.de']], (array) $feed->getAuthors());
    }

    public function testGetsSingleAuthor()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals(['name'=>'laut.de'], $feed->getAuthor());
    }

    public function testGetsCopyright()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('Copyright © 2004 laut.de', $feed->getCopyright());
    }

    public function testGetsDescription()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('laut.de: aktuelle News', $feed->getDescription());
    }

    public function testGetsLanguage()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals(null, $feed->getLanguage());
    }

    public function testGetsLink()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('http://www.laut.de', $feed->getLink());
    }

    public function testGetsEncoding()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('ISO-8859-1', $feed->getEncoding());
    }



    /**
     * Entry level testing
     */

    public function testGetsEntryId()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.laut.de/vorlaut/news/2009/07/04/22426/index.htm', $entry->getId());
    }

    public function testGetsEntryTitle()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('Angelika Express: MySpace-Aus wegen Sido-Werbung', $entry->getTitle());
    }

    public function testGetsEntryAuthors()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals([['name'=>'laut.de']], (array) $entry->getAuthors());
    }

    public function testGetsEntrySingleAuthor()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals(['name'=>'laut.de'], $entry->getAuthor());
    }

    // Technically, the next two tests should not pass. However the source feed has an encoding
    // problem - it's stated as ISO-8859-1 but sent as UTF-8. The result is that a) it's
    // broken itself, or b) We should consider a fix in the future for similar feeds such
    // as using a more limited XML based decoding method (not html_entity_decode())

    public function testGetsEntryDescription()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('Schon lÃ¤nger haderten die KÃ¶lner mit der Plattform des "fiesen Rupert Murdoch". Das Fass zum Ãberlaufen brachte aber ein Werbebanner von Deutschrapper Sido.', $entry->getDescription());
    }

    public function testGetsEntryContent()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('Schon lÃ¤nger haderten die KÃ¶lner mit der Plattform des "fiesen Rupert Murdoch". Das Fass zum Ãberlaufen brachte aber ein Werbebanner von Deutschrapper Sido.', $entry->getContent());
    }

    public function testGetsEntryLinks()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals(['http://www.laut.de/vorlaut/news/2009/07/04/22426/index.htm'], $entry->getLinks());
    }

    public function testGetsEntryLink()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.laut.de/vorlaut/news/2009/07/04/22426/index.htm', $entry->getLink());
    }

    public function testGetsEntryPermaLink()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.laut.de/vorlaut/news/2009/07/04/22426/index.htm',
            $entry->getPermaLink());
    }

    public function testGetsEntryEncoding()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('ISO-8859-1', $entry->getEncoding());
    }
}
