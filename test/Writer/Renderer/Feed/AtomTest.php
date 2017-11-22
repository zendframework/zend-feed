<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Writer\Renderer\Feed;

use PHPUnit\Framework\TestCase;
use Zend\Feed\Reader;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Exception\ExceptionInterface;
use Zend\Feed\Writer\Feed;
use Zend\Feed\Writer\Renderer;

/**
 * @group      Zend_Feed
 * @group      Zend_Feed_Writer
 */
class AtomTest extends TestCase
{
    protected $validWriter = null;

    public function setUp()
    {
        $this->validWriter = new Writer\Feed;
        $this->validWriter->setTitle('This is a test feed.');
        $this->validWriter->setDescription('This is a test description.');
        $this->validWriter->setDateModified(1234567890);
        $this->validWriter->setLink('http://www.example.com');
        $this->validWriter->setFeedLink('http://www.example.com/atom', 'atom');
        $this->validWriter->addAuthor(['name' => 'Joe',
                                             'email' => 'joe@example.com',
                                             'uri'  => 'http://www.example.com/joe']);

        $this->validWriter->setType('atom');
    }

    public function tearDown()
    {
        $this->validWriter = null;
    }

    public function testSetsWriterInConstructor()
    {
        $writer = new Writer\Feed;
        $feed   = new Renderer\Feed\Atom($writer);
        $this->assertInstanceOf(Feed::class, $feed->getDataContainer());
    }

    public function testBuildMethodRunsMinimalWriterContainerProperlyBeforeICheckAtomCompliance()
    {
        $feed = new Renderer\Feed\Atom($this->validWriter);
        $feed->render();
    }

    public function testFeedEncodingHasBeenSet()
    {
        $this->validWriter->setEncoding('iso-8859-1');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('iso-8859-1', $feed->getEncoding());
    }

    /**
     * @group 6358
     * @group 6935
     */
    public function testDateModifiedHasTheCorrectFormat()
    {
        $date = new \DateTime();
        $date->setTimestamp(1071336602);
        $date->setTimezone(new \DateTimeZone('GMT'));
        $this->validWriter->setDateModified($date);
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $this->assertEquals(
            '2003-12-13T17:30:02+00:00',
            $atomFeed->getDomDocument()->getElementsByTagName('updated')->item(0)->textContent
        );
    }

    public function testFeedEncodingDefaultIsUsedIfEncodingNotSetByHand()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('UTF-8', $feed->getEncoding());
    }

    public function testFeedTitleHasBeenSet()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('This is a test feed.', $feed->getTitle());
    }

    public function testFeedTitleIfMissingThrowsException()
    {
        $this->expectException(ExceptionInterface::class);
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('title');
        $atomFeed->render();
    }

    /**
     * @group ZFWCHARDATA01
     */
    public function testFeedTitleCharDataEncoding()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->setTitle('<>&\'"áéíóú');
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('<>&\'"áéíóú', $feed->getTitle());
    }

    public function testFeedSubtitleHasBeenSet()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('This is a test description.', $feed->getDescription());
    }

    public function testFeedSubtitleThrowsNoExceptionIfMissing()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('description');
        $atomFeed->render();
    }

    /**
     * @group ZFWCHARDATA01
     */
    public function testFeedSubtitleCharDataEncoding()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->setDescription('<>&\'"áéíóú');
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('<>&\'"áéíóú', $feed->getDescription());
    }

    public function testFeedUpdatedDateHasBeenSet()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals(1234567890, $feed->getDateModified()->getTimestamp());
    }

    public function testFeedUpdatedDateIfMissingThrowsException()
    {
        $this->expectException(ExceptionInterface::class);
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('dateModified');
        $atomFeed->render();
    }

    public function testFeedGeneratorHasBeenSet()
    {
        $this->validWriter->setGenerator('FooFeedBuilder', '1.00', 'http://www.example.com');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('FooFeedBuilder', $feed->getGenerator());
    }

    public function testFeedGeneratorIfMissingThrowsNoException()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('generator');
        $atomFeed->render();
    }

    public function testFeedGeneratorDefaultIsUsedIfGeneratorNotSetByHand()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('Zend_Feed_Writer', $feed->getGenerator());
    }

    /**
     * @group ZFWCHARDATA01
     */
    public function testFeedGeneratorCharDataEncoding()
    {
        $this->validWriter->setGenerator('<>&\'"áéíóú', '1.00', 'http://www.example.com');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('<>&\'"áéíóú', $feed->getGenerator());
    }

    public function testFeedLanguageHasBeenSet()
    {
        $this->validWriter->setLanguage('fr');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('fr', $feed->getLanguage());
    }

    public function testFeedLanguageIfMissingThrowsNoException()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('language');
        $atomFeed->render();
    }

    public function testFeedLanguageDefaultIsUsedIfGeneratorNotSetByHand()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals(null, $feed->getLanguage());
    }

    public function testFeedIncludesLinkToHtmlVersionOfFeed()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('http://www.example.com', $feed->getLink());
    }

    public function testFeedLinkToHtmlVersionOfFeedIfMissingThrowsNoExceptionIfIdSet()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->setId('http://www.example.com');
        $this->validWriter->remove('link');
        $atomFeed->render();
    }

    public function testFeedLinkToHtmlVersionOfFeedIfMissingThrowsExceptionIfIdMissing()
    {
        $this->expectException(ExceptionInterface::class);
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('link');
        $atomFeed->render();
    }

    public function testFeedIncludesLinkToXmlAtomWhereTheFeedWillBeAvailable()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('http://www.example.com/atom', $feed->getFeedLink());
    }

    public function testFeedLinkToXmlAtomWhereTheFeedWillBeAvailableIfMissingThrowsException()
    {
        $this->expectException(ExceptionInterface::class);
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('feedLinks');
        $atomFeed->render();
    }

    public function testFeedHoldsAnyAuthorAdded()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed   = Reader\Reader::importString($atomFeed->saveXml());
        $author = $feed->getAuthor();
        $this->assertEquals([
                                 'email' => 'joe@example.com',
                                 'name' => 'Joe',
                                 'uri'  => 'http://www.example.com/joe'], $feed->getAuthor());
    }

    /**
     * @group ZFWCHARDATA01
     */
    public function testFeedAuthorCharDataEncoding()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $this->validWriter->remove('authors');
        $this->validWriter->addAuthor([
                                            'email' => '<>&\'"áéíóú',
                                            'name' => '<>&\'"áéíóú',
                                            'uri'  => 'http://www.example.com/joe']);
        $atomFeed->render();
        $feed   = Reader\Reader::importString($atomFeed->saveXml());
        $author = $feed->getAuthor();
        $this->assertEquals([
                                 'email' => '<>&\'"áéíóú',
                                 'name' => '<>&\'"áéíóú',
                                 'uri'  => 'http://www.example.com/joe'], $feed->getAuthor());
    }

    public function testFeedAuthorIfNotSetThrowsExceptionIfAnyEntriesAlsoAreMissingAuthors()
    {
        $this->markTestIncomplete('Not yet implemented...');
    }

    public function testFeedAuthorIfNotSetThrowsNoExceptionIfAllEntriesIncludeAtLeastOneAuthor()
    {
        $this->markTestIncomplete('Not yet implemented...');
    }

    public function testFeedIdHasBeenSet()
    {
        $this->validWriter->setId('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6', $feed->getId());
    }

    public function testFeedIdDefaultOfHtmlLinkIsUsedIfNotSetByHand()
    {
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals($feed->getLink(), $feed->getId());
    }

    public function testBaseUrlCanBeSet()
    {
        $this->validWriter->setBaseUrl('http://www.example.com/base');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('http://www.example.com/base', $feed->getBaseUrl());
    }

    public function testCopyrightCanBeSet()
    {
        $this->validWriter->setCopyright('Copyright © 2009 Paddy');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('Copyright © 2009 Paddy', $feed->getCopyright());
    }

    public function testCopyrightCharDataEncoding()
    {
        $this->validWriter->setCopyright('<>&\'"áéíóú');
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed = Reader\Reader::importString($atomFeed->saveXml());
        $this->assertEquals('<>&\'"áéíóú', $feed->getCopyright());
    }

    public function testCategoriesCanBeSet()
    {
        $this->validWriter->addCategories([
                                                ['term'   => 'cat_dog',
                                                      'label'  => 'Cats & Dogs',
                                                      'scheme' => 'http://example.com/schema1'],
                                                ['term' => 'cat_dog2']
                                           ]);
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed     = Reader\Reader::importString($atomFeed->saveXml());
        $expected = [
            ['term'   => 'cat_dog',
                  'label'  => 'Cats & Dogs',
                  'scheme' => 'http://example.com/schema1'],
            ['term'   => 'cat_dog2',
                  'label'  => 'cat_dog2',
                  'scheme' => null]
        ];
        $this->assertEquals($expected, (array) $feed->getCategories());
    }

    public function testCategoriesCharDataEncoding()
    {
        $this->validWriter->addCategories([
                                                ['term'   => 'cat_dog',
                                                      'label'  => '<>&\'"áéíóú',
                                                      'scheme' => 'http://example.com/schema1'],
                                                ['term' => 'cat_dog2']
                                           ]);
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed     = Reader\Reader::importString($atomFeed->saveXml());
        $expected = [
            ['term'   => 'cat_dog',
                  'label'  => '<>&\'"áéíóú',
                  'scheme' => 'http://example.com/schema1'],
            ['term'   => 'cat_dog2',
                  'label'  => 'cat_dog2',
                  'scheme' => null]
        ];
        $this->assertEquals($expected, (array) $feed->getCategories());
    }

    public function testHubsCanBeSet()
    {
        $this->validWriter->addHubs(
            ['http://www.example.com/hub', 'http://www.example.com/hub2']
        );
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed     = Reader\Reader::importString($atomFeed->saveXml());
        $expected = [
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ];
        $this->assertEquals($expected, (array) $feed->getHubs());
    }

    public function testImageCanBeSet()
    {
        $this->validWriter->setImage(
            ['uri' => 'http://www.example.com/logo.gif']
        );
        $atomFeed = new Renderer\Feed\Atom($this->validWriter);
        $atomFeed->render();
        $feed     = Reader\Reader::importString($atomFeed->saveXml());
        $expected = [
            'uri' => 'http://www.example.com/logo.gif'
        ];
        $this->assertEquals($expected, $feed->getImage());
    }
}
