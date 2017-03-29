<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Writer\Extension\ITunes;

use PHPUnit\Framework\TestCase;
use Zend\Feed\Writer;
use Zend\Feed\Writer\Exception\ExceptionInterface;

/**
* @group Zend_Feed
* @group Zend_Feed_Writer
*/
class EntryTest extends TestCase
{
    public function testSetBlock()
    {
        $entry = new Writer\Entry;
        $entry->setItunesBlock('yes');
        $this->assertEquals('yes', $entry->getItunesBlock());
    }

    public function testSetBlockThrowsExceptionOnNonAlphaValue()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesBlock('123');
    }

    public function testSetBlockThrowsExceptionIfValueGreaterThan255CharsLength()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesBlock(str_repeat('a', 256));
    }

    public function testAddAuthors()
    {
        $entry = new Writer\Entry;
        $entry->addItunesAuthors(['joe', 'jane']);
        $this->assertEquals(['joe', 'jane'], $entry->getItunesAuthors());
    }

    public function testAddAuthor()
    {
        $entry = new Writer\Entry;
        $entry->addItunesAuthor('joe');
        $this->assertEquals(['joe'], $entry->getItunesAuthors());
    }

    public function testAddAuthorThrowsExceptionIfValueGreaterThan255CharsLength()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->addItunesAuthor(str_repeat('a', 256));
    }

    public function testSetDurationAsSeconds()
    {
        $entry = new Writer\Entry;
        $entry->setItunesDuration(23);
        $this->assertEquals(23, $entry->getItunesDuration());
    }

    public function testSetDurationAsMinutesAndSeconds()
    {
        $entry = new Writer\Entry;
        $entry->setItunesDuration('23:23');
        $this->assertEquals('23:23', $entry->getItunesDuration());
    }

    public function testSetDurationAsHoursMinutesAndSeconds()
    {
        $entry = new Writer\Entry;
        $entry->setItunesDuration('23:23:23');
        $this->assertEquals('23:23:23', $entry->getItunesDuration());
    }

    public function testSetDurationThrowsExceptionOnUnknownFormat()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesDuration('abc');
    }

    public function testSetDurationThrowsExceptionOnInvalidSeconds()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesDuration('23:456');
    }

    public function testSetDurationThrowsExceptionOnInvalidMinutes()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesDuration('23:234:45');
    }

    public function testSetExplicitToYes()
    {
        $entry = new Writer\Entry;
        $entry->setItunesExplicit('yes');
        $this->assertEquals('yes', $entry->getItunesExplicit());
    }

    public function testSetExplicitToNo()
    {
        $entry = new Writer\Entry;
        $entry->setItunesExplicit('no');
        $this->assertEquals('no', $entry->getItunesExplicit());
    }

    public function testSetExplicitToClean()
    {
        $entry = new Writer\Entry;
        $entry->setItunesExplicit('clean');
        $this->assertEquals('clean', $entry->getItunesExplicit());
    }

    public function testSetExplicitThrowsExceptionOnUnknownTerm()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesExplicit('abc');
    }

    public function testSetKeywords()
    {
        $entry = new Writer\Entry;
        $words = [
            'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10', 'a11', 'a12'
        ];
        $entry->setItunesKeywords($words);
        $this->assertEquals($words, $entry->getItunesKeywords());
    }

    public function testSetKeywordsThrowsExceptionIfMaxKeywordsExceeded()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $words = [
            'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7', 'a8', 'a9', 'a10', 'a11', 'a12', 'a13'
        ];
        $entry->setItunesKeywords($words);
    }

    public function testSetKeywordsThrowsExceptionIfFormattedKeywordsExceeds255CharLength()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $words = [
            str_repeat('a', 253), str_repeat('b', 2)
        ];
        $entry->setItunesKeywords($words);
    }

    public function testSetSubtitle()
    {
        $entry = new Writer\Entry;
        $entry->setItunesSubtitle('abc');
        $this->assertEquals('abc', $entry->getItunesSubtitle());
    }

    public function testSetSubtitleThrowsExceptionWhenValueExceeds255Chars()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesSubtitle(str_repeat('a', 256));
    }

    public function testSetSummary()
    {
        $entry = new Writer\Entry;
        $entry->setItunesSummary('abc');
        $this->assertEquals('abc', $entry->getItunesSummary());
    }

    public function testSetSummaryThrowsExceptionWhenValueExceeds255Chars()
    {
        $this->expectException(ExceptionInterface::class);
        $entry = new Writer\Entry;
        $entry->setItunesSummary(str_repeat('a', 4001));
    }
}
