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
class HOnlineComAtom10Test extends \PHPUnit_Framework_TestCase
{
    protected $feedSamplePath = null;

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files/h-online.com-atom10.xml';
    }

    public function testGetsTitle()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('The H - news feed', $feed->getTitle());
    }

    public function testGetsAuthors()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals([['name'=>'The H']], (array) $feed->getAuthors());
    }

    public function testGetsSingleAuthor()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals(['name'=>'The H'], $feed->getAuthor());
    }

    public function testGetsCopyright()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals(null, $feed->getCopyright());
    }

    public function testGetsDescription()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('Technology news', $feed->getDescription());
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
        $this->assertEquals('http://www.h-online.com', $feed->getLink());
    }

    public function testGetsEncoding()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('UTF-8', $feed->getEncoding());
    }

    public function testGetsEntryCount()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals(60, $feed->count());
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
        $this->assertEquals('http://www.h-online.com/security/McAfee-update-brings-systems-down-again--/news/113689/from/rss', $entry->getId());
    }

    public function testGetsEntryTitle()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('McAfee update brings systems down again', $entry->getTitle());
    }

    public function testGetsEntryAuthors()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals([['name'=>'The H']], (array) $entry->getAuthors());
    }

    public function testGetsEntrySingleAuthor()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals(['name'=>'The H'], $entry->getAuthor());
    }

    public function testGetsEntryDescription()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        /**
         * Note: "’" is not the same as "'" - don't replace in error
         */
        $this->assertEquals('A McAfee signature update is currently causing system failures and a lot of overtime for administrators', $entry->getDescription());
    }

    public function testGetsEntryContent()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('A McAfee signature update is currently causing system failures and a lot of overtime for administrators', $entry->getContent());
    }

    public function testGetsEntryLinks()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals(['http://www.h-online.com/security/McAfee-update-brings-systems-down-again--/news/113689/from/rss'], $entry->getLinks());
    }

    public function testGetsEntryLink()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.h-online.com/security/McAfee-update-brings-systems-down-again--/news/113689/from/rss', $entry->getLink());
    }

    public function testGetsEntryPermaLink()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.h-online.com/security/McAfee-update-brings-systems-down-again--/news/113689/from/rss',
            $entry->getPermaLink());
    }

    public function testGetsEntryEncoding()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('UTF-8', $entry->getEncoding());
    }
}
