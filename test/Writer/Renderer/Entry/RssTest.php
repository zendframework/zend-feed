<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Writer\Renderer\Entry;

use PHPUnit\Framework\TestCase;
use Zend\Feed\Reader;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Exception\ExceptionInterface;
use Zend\Feed\Writer\Renderer;

/**
 * @group      Zend_Feed
 * @group      Zend_Feed_Writer
 */
class RssTest extends TestCase
{
    protected $validWriter = null;
    protected $validEntry = null;

    public function setUp()
    {
        $this->validWriter = new Writer\Feed;

        $this->validWriter->setType('rss');

        $this->validWriter->setTitle('This is a test feed.');
        $this->validWriter->setDescription('This is a test description.');
        $this->validWriter->setLink('http://www.example.com');
        $this->validEntry = $this->validWriter->createEntry();
        $this->validEntry->setTitle('This is a test entry.');
        $this->validEntry->setDescription('This is a test entry description.');
        $this->validEntry->setLink('http://www.example.com/1');
        $this->validWriter->addEntry($this->validEntry);
    }

    public function tearDown()
    {
        $this->validWriter = null;
        $this->validEntry  = null;
    }

    public function testRenderMethodRunsMinimalWriterContainerProperlyBeforeICheckAtomCompliance()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $renderer->render();
    }

    public function testEntryEncodingHasBeenSet()
    {
        $this->validWriter->setEncoding('iso-8859-1');
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('iso-8859-1', $entry->getEncoding());
    }

    public function testEntryEncodingDefaultIsUsedIfEncodingNotSetByHand()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('UTF-8', $entry->getEncoding());
    }

    public function testEntryTitleHasBeenSet()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('This is a test entry.', $entry->getTitle());
    }

    public function testEntryTitleIfMissingThrowsExceptionIfDescriptionAlsoMissing()
    {
        $this->expectException(ExceptionInterface::class);
        $atomFeed = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->remove('title');
        $this->validEntry->remove('description');
        $atomFeed->render();
    }

    public function testEntryTitleCharDataEncoding()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setTitle('<>&\'"áéíóú');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('<>&\'"áéíóú', $entry->getTitle());
    }

    public function testEntrySummaryDescriptionHasBeenSet()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('This is a test entry description.', $entry->getDescription());
    }

    public function testEntryDescriptionIfMissingThrowsExceptionIfAlsoNoTitle()
    {
        $this->expectException(ExceptionInterface::class);
        $atomFeed = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->remove('description');
        $this->validEntry->remove('title');
        $atomFeed->render();
    }

    public function testEntryDescriptionCharDataEncoding()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setDescription('<>&\'"áéíóú');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('<>&\'"áéíóú', $entry->getDescription());
    }

    public function testEntryContentHasBeenSet()
    {
        $this->validEntry->setContent('This is test entry content.');
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('This is test entry content.', $entry->getContent());
    }

    public function testEntryContentCharDataEncoding()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setContent('<>&\'"áéíóú');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('<>&\'"áéíóú', $entry->getContent());
    }

    public function testEntryUpdatedDateHasBeenSet()
    {
        $this->validEntry->setDateModified(1234567890);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals(1234567890, $entry->getDateModified()->getTimestamp());
    }

    public function testEntryPublishedDateHasBeenSet()
    {
        $this->validEntry->setDateCreated(1234567000);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals(1234567000, $entry->getDateCreated()->getTimestamp());
    }

    public function testEntryIncludesLinkToHtmlVersionOfFeed()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getLink());
    }

    public function testEntryHoldsAnyAuthorAdded()
    {
        $this->validEntry->addAuthor(['name' => 'Jane',
                                            'email' => 'jane@example.com',
                                            'uri'  => 'http://www.example.com/jane']);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $author   = $entry->getAuthor();
        $this->assertEquals(['name' => 'Jane'], $entry->getAuthor());
    }

    public function testEntryAuthorCharDataEncoding()
    {
        $this->validEntry->addAuthor(['name' => '<>&\'"áéíóú',
                                            'email' => 'jane@example.com',
                                            'uri'  => 'http://www.example.com/jane']);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $author   = $entry->getAuthor();
        $this->assertEquals(['name' => '<>&\'"áéíóú'], $entry->getAuthor());
    }

    public function testEntryHoldsAnyEnclosureAdded()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'type'   => 'audio/mpeg',
                                              'length' => '1337',
                                              'uri'    => 'http://example.com/audio.mp3'
                                         ]);
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $enc   = $entry->getEnclosure();
        $this->assertEquals('audio/mpeg', $enc->type);
        $this->assertEquals('1337', $enc->length);
        $this->assertEquals('http://example.com/audio.mp3', $enc->url);
    }

    public function testAddsEnclosureThrowsExceptionOnMissingType()
    {
        $this->expectException(ExceptionInterface::class);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => '1337'
                                         ]);
        $renderer->render();
    }

    public function testAddsEnclosureThrowsExceptionOnMissingLength()
    {
        $this->expectException(ExceptionInterface::class);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'type' => 'audio/mpeg',
                                              'uri'  => 'http://example.com/audio.mp3'
                                         ]);
        $renderer->render();
    }

    public function testAddsEnclosureThrowsExceptionOnNonNumericLength()
    {
        $this->expectException(ExceptionInterface::class);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'type'   => 'audio/mpeg',
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => 'abc'
                                         ]);
        $renderer->render();
    }

    public function testAddsEnclosureThrowsExceptionOnNegativeLength()
    {
        $this->expectException(ExceptionInterface::class);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'type'   => 'audio/mpeg',
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => -23
                                         ]);
        $renderer->render();
    }

    public function testEnclosureWorksWithZeroLength()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'type'   => 'audio/mpeg',
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => 0
                                         ]);
        $renderer->render();
    }

    public function testEnclosureWorksWithPositiveLength()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'type'   => 'audio/mpeg',
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => 23
                                         ]);
        $renderer->render();
    }

    public function testEnclosureWorksWithPositiveLengthString()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setEnclosure([
                                              'type'   => 'audio/mpeg',
                                              'uri'    => 'http://example.com/audio.mp3',
                                              'length' => '23'
                                         ]);
        $renderer->render();
    }

    public function testEntryIdHasBeenSet()
    {
        $this->validEntry->setId('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6');
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6', $entry->getId());
    }

    public function testEntryIdHasBeenSetWithPermaLinkAsFalseWhenNotUri()
    {
        $this->markTestIncomplete('Untest due to ZFR potential bug');
    }

    public function testEntryIdDefaultIsUsedIfNotSetByHand()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $this->assertEquals($entry->getLink(), $entry->getId());
    }

    public function testCommentLinkRendered()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setCommentLink('http://www.example.com/id/1');
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/id/1', $entry->getCommentLink());
    }

    public function testCommentCountRendered()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setCommentCount(22);
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        $this->assertEquals(22, $entry->getCommentCount());
    }

    public function testCommentFeedLinksRendered()
    {
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $this->validEntry->setCommentFeedLinks([
                                                     ['uri' => 'http://www.example.com/atom/id/1',
                                                           'type' => 'atom'],
                                                     ['uri' => 'http://www.example.com/rss/id/1',
                                                           'type' => 'rss'],
                                                ]);
        $feed  = Reader\Reader::importString($renderer->render()->saveXml());
        $entry = $feed->current();
        // Skipped assertion is because RSS has no facility to show Atom feeds without an extension
        $this->assertEquals('http://www.example.com/rss/id/1', $entry->getCommentFeedLink('rss'));
        //$this->assertEquals('http://www.example.com/atom/id/1', $entry->getCommentFeedLink('atom'));
    }

    public function testCategoriesCanBeSet()
    {
        $this->validEntry->addCategories([
                                               ['term'   => 'cat_dog',
                                                     'label'  => 'Cats & Dogs',
                                                     'scheme' => 'http://example.com/schema1'],
                                               ['term' => 'cat_dog2']
                                          ]);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $expected = [
            ['term'   => 'cat_dog',
                  'label'  => 'cat_dog',
                  'scheme' => 'http://example.com/schema1'],
            ['term'   => 'cat_dog2',
                  'label'  => 'cat_dog2',
                  'scheme' => null]
        ];
        $this->assertEquals($expected, (array) $entry->getCategories());
    }

    /**
     * @group ZFWCHARDATA01
     */
    public function testCategoriesCharDataEncoding()
    {
        $this->validEntry->addCategories([
                                               ['term'   => '<>&\'"áéíóú',
                                                     'label'  => 'Cats & Dogs',
                                                     'scheme' => 'http://example.com/schema1'],
                                               ['term' => 'cat_dog2']
                                          ]);
        $renderer = new Renderer\Feed\Rss($this->validWriter);
        $feed     = Reader\Reader::importString($renderer->render()->saveXml());
        $entry    = $feed->current();
        $expected = [
            ['term'   => '<>&\'"áéíóú',
                  'label'  => '<>&\'"áéíóú',
                  'scheme' => 'http://example.com/schema1'],
            ['term'   => 'cat_dog2',
                  'label'  => 'cat_dog2',
                  'scheme' => null]
        ];
        $this->assertEquals($expected, (array) $entry->getCategories());
    }
}
