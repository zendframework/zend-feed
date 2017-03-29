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
class PodcastRss2Test extends \PHPUnit_Framework_TestCase
{
    protected $feedSamplePath = null;

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files/podcast.xml';
    }

    /**
     * Feed level testing
     */

    public function testGetsNewFeedUrl()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('http://newlocation.com/example.rss', $feed->getNewFeedUrl());
    }

    public function testGetsOwner()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('john.doe@example.com (John Doe)', $feed->getOwner());
    }

    public function testGetsCategories()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals([
            'Technology' => [
                'Gadgets' => null
            ],
            'TV & Film' => null
        ], $feed->getItunesCategories());
    }

    public function testGetsTitle()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('All About Everything', $feed->getTitle());
    }

    public function testGetsCastAuthor()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('John Doe', $feed->getCastAuthor());
    }

    public function testGetsFeedBlock()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('no', $feed->getBlock());
    }

    public function testGetsCopyright()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('℗ & © 2005 John Doe & Family', $feed->getCopyright());
    }

    public function testGetsDescription()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $expected = 'All About Everything is a show about everything.
            Each week we dive into any subject known to man and talk
            about it as much as we can. Look for our Podcast in the
            iTunes Store';
        $expected = str_replace("\r\n", "\n", $expected);
        $this->assertEquals($expected, $feed->getDescription());
    }

    public function testGetsLanguage()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('en-us', $feed->getLanguage());
    }

    public function testGetsLink()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('http://www.example.com/podcasts/everything/index.html', $feed->getLink());
    }

    public function testGetsEncoding()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('UTF-8', $feed->getEncoding());
    }

    public function testGetsFeedExplicit()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('yes', $feed->getExplicit());
    }

    public function testGetsEntryCount()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals(3, $feed->count());
    }

    public function testGetsImage()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $this->assertEquals('http://example.com/podcasts/everything/AllAboutEverything.jpg', $feed->getItunesImage());
    }

    /**
     * Entry level testing
     */

    public function testGetsEntryBlock()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('yes', $entry->getBlock());
    }

    public function testGetsEntryId()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('http://example.com/podcasts/archive/aae20050615.m4a', $entry->getId());
    }

    public function testGetsEntryTitle()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('Shake Shake Shake Your Spices', $entry->getTitle());
    }

    public function testGetsEntryCastAuthor()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('John Doe', $entry->getCastAuthor());
    }

    public function testGetsEntryExplicit()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('no', $entry->getExplicit());
    }

    public function testGetsSubtitle()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $expected = 'A short primer on table spices
            ';
        $expected = str_replace("\r\n", "\n", $expected);
        $this->assertEquals($expected, $entry->getSubtitle());
    }

    public function testGetsSummary()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $expected = 'This week we talk about salt and pepper
                shakers, comparing and contrasting pour rates,
                construction materials, and overall aesthetics. Come and
                join the party!';
        $expected = str_replace("\r\n", "\n", $expected);
        $this->assertEquals($expected, $entry->getSummary());
    }

    public function testGetsDuration()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('7:04', $entry->getDuration());
    }

    public function testGetsKeywords()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $expected = 'salt, pepper, shaker, exciting
            ';
        $expected = str_replace("\r\n", "\n", $expected);
        $this->assertEquals($expected, $entry->getKeywords());
    }

    public function testGetsEntryEncoding()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();
        $this->assertEquals('UTF-8', $entry->getEncoding());
    }

    public function testGetsEnclosure()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath)
        );
        $entry = $feed->current();

        $expected = new \stdClass();
        $expected->url    = 'http://example.com/podcasts/everything/AllAboutEverythingEpisode3.m4a';
        $expected->length = '8727310';
        $expected->type   = 'audio/x-m4a';

        $this->assertEquals($expected, $entry->getEnclosure());
    }
}
