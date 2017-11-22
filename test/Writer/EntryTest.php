<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Writer;

use DateTime;
use PHPUnit\Framework\TestCase;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Exception\ExceptionInterface;
use Zend\Feed\Writer\Extension\ITunes\Entry;
use Zend\Feed\Writer\Source;

/**
 * @group      Zend_Feed
 * @group      Zend_Feed_Writer
 */
class EntryTest extends TestCase
{
    protected $feedSamplePath = null;

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files';
    }

    public function testAddsAuthorNameFromArray()
    {
        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => 'Joe']);
        $this->assertEquals([['name' => 'Joe']], $entry->getAuthors());
    }

    public function testAddsAuthorEmailFromArray()
    {
        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => 'Joe',
                                'email' => 'joe@example.com']);
        $this->assertEquals([['name'  => 'Joe',
                                        'email' => 'joe@example.com']], $entry->getAuthors());
    }

    public function testAddsAuthorUriFromArray()
    {
        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => 'Joe',
                                'uri' => 'http://www.example.com']);
        $this->assertEquals([['name' => 'Joe',
                                        'uri' => 'http://www.example.com']], $entry->getAuthors());
    }

    public function testAddAuthorThrowsExceptionOnInvalidNameFromArray()
    {
        $entry = new Writer\Entry;
        try {
            $entry->addAuthor(['name' => '']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    public function testAddAuthorThrowsExceptionOnInvalidEmailFromArray()
    {
        $entry = new Writer\Entry;
        try {
            $entry->addAuthor(['name' => 'Joe',
                                    'email' => '']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    public function testAddAuthorThrowsExceptionOnInvalidUriFromArray()
    {
        $entry = new Writer\Entry;
        try {
            $entry->addAuthor(['name' => 'Joe',
                                    'email' => 'joe@example.org',
                                    'uri' => '']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    public function testAddAuthorThrowsExceptionIfNameOmittedFromArray()
    {
        $entry = new Writer\Entry;
        try {
            $entry->addAuthor(['uri' => 'notauri']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    public function testAddsAuthorsFromArrayOfAuthors()
    {
        $entry = new Writer\Entry;
        $entry->addAuthors([
                                ['name' => 'Joe',
                                      'uri' => 'http://www.example.com'],
                                ['name' => 'Jane',
                                      'uri' => 'http://www.example.com']
                           ]);
        $expected = [
            ['name' => 'Joe',
                  'uri' => 'http://www.example.com'],
            ['name' => 'Jane',
                  'uri' => 'http://www.example.com']
        ];
        $this->assertEquals($expected, $entry->getAuthors());
    }

    public function testAddsEnclosure()
    {
        $entry = new Writer\Entry;
        $entry->setEnclosure([
                                  'type'   => 'audio/mpeg',
                                  'uri'    => 'http://example.com/audio.mp3',
                                  'length' => '1337'
                             ]);
        $expected = [
            'type'   => 'audio/mpeg',
            'uri'    => 'http://example.com/audio.mp3',
            'length' => '1337'
        ];
        $this->assertEquals($expected, $entry->getEnclosure());
    }

    public function testAddsEnclosureThrowsExceptionOnMissingUri()
    {
        $this->expectException(ExceptionInterface::class);
        $this->markTestIncomplete('Pending Zend\URI fix for validation');
        $entry = new Writer\Entry;
        $entry->setEnclosure([
                                  'type'   => 'audio/mpeg',
                                  'length' => '1337'
                             ]);
    }

    public function testAddsEnclosureThrowsExceptionWhenUriIsInvalid()
    {
        $this->expectException(ExceptionInterface::class);
        $this->markTestIncomplete('Pending Zend\URI fix for validation');
        $entry = new Writer\Entry;
        $entry->setEnclosure([
                                  'type'   => 'audio/mpeg',
                                  'uri'    => 'http://',
                                  'length' => '1337'
                             ]);
    }

    public function testSetsCopyright()
    {
        $entry = new Writer\Entry;
        $entry->setCopyright('Copyright (c) 2009 Paddy Brady');
        $this->assertEquals('Copyright (c) 2009 Paddy Brady', $entry->getCopyright());
    }

    public function testSetCopyrightThrowsExceptionOnInvalidParam()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setCopyright('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetsContent()
    {
        $entry = new Writer\Entry;
        $entry->setContent('I\'m content.');
        $this->assertEquals("I'm content.", $entry->getContent());
    }

    public function testSetContentThrowsExceptionOnInvalidParam()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setContent('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetDateCreatedDefaultsToCurrentTime()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated();
        $dateNow = new DateTime();
        $this->assertLessThanOrEqual($dateNow, $entry->getDateCreated());
    }

    public function testSetDateCreatedUsesGivenUnixTimestamp()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated(1234567890);
        $myDate = new DateTime('@' . 1234567890);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    /**
     * @group ZF-12070
     */
    public function testSetDateCreatedUsesGivenUnixTimestampWhenItIsLessThanTenDigits()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated(123456789);
        $myDate = new DateTime('@' . 123456789);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    /**
     * @group ZF-11610
     */
    public function testSetDateCreatedUsesGivenUnixTimestampWhenItIsAVerySmallInteger()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated(123);
        $myDate = new DateTime('@' . 123);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    public function testSetDateCreatedUsesDateTimeObject()
    {
        $myDate = new DateTime('@' . 1234567890);
        $entry = new Writer\Entry;
        $entry->setDateCreated($myDate);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    public function testSetDateModifiedDefaultsToCurrentTime()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified();
        $dateNow = new DateTime();
        $this->assertLessThanOrEqual($dateNow, $entry->getDateModified());
    }

    public function testSetDateModifiedUsesGivenUnixTimestamp()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified(1234567890);
        $myDate = new DateTime('@' . 1234567890);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    /**
     * @group ZF-12070
     */
    public function testSetDateModifiedUsesGivenUnixTimestampWhenItIsLessThanTenDigits()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified(123456789);
        $myDate = new DateTime('@' . 123456789);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    /**
     * @group ZF-11610
     */
    public function testSetDateModifiedUsesGivenUnixTimestampWhenItIsAVerySmallInteger()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified(123);
        $myDate = new DateTime('@' . 123);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    public function testSetDateModifiedUsesDateTimeObject()
    {
        $myDate = new DateTime('@' . 1234567890);
        $entry = new Writer\Entry;
        $entry->setDateModified($myDate);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    public function testSetDateCreatedThrowsExceptionOnInvalidParameter()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setDateCreated('abc');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetDateModifiedThrowsExceptionOnInvalidParameter()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setDateModified('abc');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetDateCreatedReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getDateCreated());
    }

    public function testGetDateModifiedReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getDateModified());
    }

    public function testGetCopyrightReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCopyright());
    }

    public function testGetContentReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getContent());
    }

    public function testSetsDescription()
    {
        $entry = new Writer\Entry;
        $entry->setDescription('abc');
        $this->assertEquals('abc', $entry->getDescription());
    }

    public function testSetDescriptionThrowsExceptionOnInvalidParameter()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setDescription('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetDescriptionReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getDescription());
    }

    public function testSetsId()
    {
        $entry = new Writer\Entry;
        $entry->setId('http://www.example.com/id');
        $this->assertEquals('http://www.example.com/id', $entry->getId());
    }

    public function testSetIdThrowsExceptionOnInvalidParameter()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setId('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetIdReturnsNullIfNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getId());
    }

    public function testSetsLink()
    {
        $entry = new Writer\Entry;
        $entry->setLink('http://www.example.com/id');
        $this->assertEquals('http://www.example.com/id', $entry->getLink());
    }

    public function testSetLinkThrowsExceptionOnEmptyString()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setLink('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetLinkThrowsExceptionOnInvalidUri()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setLink('http://');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetLinkReturnsNullIfNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getLink());
    }

    public function testGetLinksReturnsNullIfNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getLinks());
    }

    public function testSetsCommentLink()
    {
        $entry = new Writer\Entry;
        $entry->setCommentLink('http://www.example.com/id/comments');
        $this->assertEquals('http://www.example.com/id/comments', $entry->getCommentLink());
    }

    public function testSetCommentLinkThrowsExceptionOnEmptyString()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setCommentLink('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetCommentLinkThrowsExceptionOnInvalidUri()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setCommentLink('http://');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetCommentLinkReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCommentLink());
    }

    public function testSetsCommentFeedLink()
    {
        $entry = new Writer\Entry;

        $entry->setCommentFeedLink(['uri' => 'http://www.example.com/id/comments',
                                         'type' => 'rdf']);
        $this->assertEquals([['uri' => 'http://www.example.com/id/comments',
                                        'type' => 'rdf']], $entry->getCommentFeedLinks());
    }

    public function testSetCommentFeedLinkThrowsExceptionOnEmptyString()
    {
        $this->markTestIncomplete('Pending Zend\URI fix for validation');
        $entry = new Writer\Entry;
        try {
            $entry->setCommentFeedLink(['uri' => '',
                                             'type' => 'rdf']);
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetCommentFeedLinkThrowsExceptionOnInvalidUri()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setCommentFeedLink(['uri' => 'http://',
                                             'type' => 'rdf']);
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetCommentFeedLinkThrowsExceptionOnInvalidType()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setCommentFeedLink(['uri' => 'http://www.example.com/id/comments',
                                             'type' => 'foo']);
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetCommentFeedLinkReturnsNullIfNoneSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCommentFeedLinks());
    }

    public function testSetsTitle()
    {
        $entry = new Writer\Entry;
        $entry->setTitle('abc');
        $this->assertEquals('abc', $entry->getTitle());
    }

    public function testSetTitleThrowsExceptionOnInvalidParameter()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setTitle('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetTitleReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getTitle());
    }

    public function testSetsCommentCount()
    {
        $entry = new Writer\Entry;
        $entry->setCommentCount('10');
        $this->assertEquals(10, $entry->getCommentCount());
    }

    public function testSetsCommentCount0()
    {
        $entry = new Writer\Entry;
        $entry->setCommentCount(0);
        $this->assertEquals(0, $entry->getCommentCount());
    }

    public function allowedCommentCounts()
    {
        return [
            [0, 0],
            [0.0, 0],
            [1, 1],
            [PHP_INT_MAX, PHP_INT_MAX],
        ];
    }

    /**
     * @dataProvider allowedCommentCounts
     */
    public function testSetsCommentCountAllowed($count, $expected)
    {
        $entry = new Writer\Entry;
        $entry->setCommentCount($count);
        $this->assertSame($expected, $entry->getCommentCount());
    }

    public function disallowedCommentCounts()
    {
        return [
            [1.1],
            [-1],
            [-PHP_INT_MAX],
            [[]],
            [''],
            [false],
            [true],
            [new \stdClass],
            [null],
        ];
    }

    /**
     * @dataProvider disallowedCommentCounts
     */
    public function testSetsCommentCountDisallowed($count)
    {
        $entry = new Writer\Entry;
        $this->expectException(ExceptionInterface::class);
        $entry->setCommentCount($count);
    }

    public function testSetCommentCountThrowsExceptionOnInvalidEmptyParameter()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setCommentCount('');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testSetCommentCountThrowsExceptionOnInvalidNonIntegerParameter()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setCommentCount('a');
            $this->fail();
        } catch (Writer\Exception\ExceptionInterface $e) {
        }
    }

    public function testGetCommentCountReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCommentCount());
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::setEncoding
     */
    public function testSetEncodingThrowsExceptionIfNull()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setEncoding(null);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::addCategory
     */
    public function testAddCategoryThrowsExceptionIfNotSetTerm()
    {
        $entry = new Writer\Entry;
        try {
            $entry->addCategory(['scheme' => 'http://www.example.com/schema1']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::addCategory
     */
    public function testAddCategoryThrowsExceptionIfSchemeNull()
    {
        $entry = new Writer\Entry;
        try {
            $entry->addCategory(['term' => 'cat_dog', 'scheme' => '']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::setEnclosure
     */
    public function testSetEnclosureThrowsExceptionIfNotSetUri()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setEnclosure(['length' => '2']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::setEnclosure
     */
    public function testSetEnclosureThrowsExceptionIfNotValidUri()
    {
        $entry = new Writer\Entry;
        try {
            $entry->setEnclosure(['uri' => '']);
            $this->fail();
        } catch (Writer\Exception\InvalidArgumentException $e) {
        }
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::getExtension
     */
    public function testGetExtension()
    {
        $entry = new Writer\Entry;
        $foo = $entry->getExtension('foo');
        $this->assertNull($foo);

        $this->assertInstanceOf(Entry::class, $entry->getExtension('ITunes'));
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::getExtensions
     */
    public function testGetExtensions()
    {
        $entry = new Writer\Entry;

        $extensions = $entry->getExtensions();
        $this->assertInstanceOf(Entry::class, $extensions['ITunes\Entry']);
    }

    /**
     * @covers \Zend\Feed\Writer\Entry::getSource
     * @covers \Zend\Feed\Writer\Entry::createSource
     */
    public function testGetSource()
    {
        $entry = new Writer\Entry;

        $source = $entry->getSource();
        $this->assertNull($source);

        $entry->setSource($entry->createSource());
        $this->assertInstanceOf(Source::class, $entry->getSource());
    }

    public function testFluentInterface()
    {
        $entry = new Writer\Entry;

        $result = $entry->addAuthor(['name' => 'foo'])
                        ->addAuthors([['name' => 'foo']])
                        ->setEncoding('utf-8')
                        ->setCopyright('copyright')
                        ->setContent('content')
                        ->setDateCreated(null)
                        ->setDateModified(null)
                        ->setDescription('description')
                        ->setId('1')
                        ->setLink('http://www.example.com')
                        ->setCommentCount(1)
                        ->setCommentLink('http://www.example.com')
                        ->setCommentFeedLink(['uri' => 'http://www.example.com', 'type' => 'rss'])
                        ->setCommentFeedLinks([['uri' => 'http://www.example.com', 'type' => 'rss']])
                        ->setTitle('title')
                        ->addCategory(['term' => 'category'])
                        ->addCategories([['term' => 'category']])
                        ->setEnclosure(['uri' => 'http://www.example.com'])
                        ->setType('type')
                        ->setSource(new \Zend\Feed\Writer\Source());

        $this->assertSame($result, $entry);
    }
}
