<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader\Entry;

use DateTime;
use Zend\Feed\Reader;

/**
* @group Zend_Feed
* @group Zend_Feed_Reader
*/
class AtomStandaloneEntryTest extends \PHPUnit_Framework_TestCase
{
    protected $feedSamplePath = null;

    protected $expectedCats = [];

    protected $expectedCatsDc = [];

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files/AtomStandaloneEntry';

        $this->expectedCats = [
            [
                'term' => 'topic1',
                'scheme' => 'http://example.com/schema1',
                'label' => 'topic1'
            ],
            [
                'term' => 'topic1',
                'scheme' => 'http://example.com/schema2',
                'label' => 'topic1'
            ],
            [
                'term' => 'cat_dog',
                'scheme' => 'http://example.com/schema1',
                'label' => 'Cat & Dog'
            ]
        ];
        $this->expectedCatsDc = [
            [
                'term' => 'topic1',
                'scheme' => null,
                'label' => 'topic1'
            ],
            [
                'term' => 'topic2',
                'scheme' => null,
                'label' => 'topic2'
            ]
        ];
    }

    public function testReaderImportOfAtomEntryDocumentReturnsEntryClass()
    {
        $object = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/id/atom10.xml')
        );
        $this->assertInstanceOf('Zend\Feed\Reader\Entry\Atom', $object);
    }

    /**
     * Get Id (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsIdFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/id/atom10.xml')
        );
        $this->assertEquals('1', $entry->getId());
    }

    /**
     * Get creation date (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsDateCreatedFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/datecreated/atom10.xml')
        );
        $edate = DateTime::createFromFormat(DateTime::ATOM, '2009-03-07T08:03:50Z');
        $this->assertEquals($edate, $entry->getDateCreated());
    }

    /**
     * Get modification date (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsDateModifiedFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/datemodified/atom10.xml')
        );
        $edate = DateTime::createFromFormat(DateTime::ATOM, '2009-03-07T08:03:50Z');
        $this->assertEquals($edate, $entry->getDateModified());
    }

    /**
     * Get Title (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsTitleFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/title/atom10.xml')
        );
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    /**
     * Get Authors (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsAuthorsFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/author/atom10.xml')
        );

        $authors = [
            ['email'=>'joe@example.com','name'=>'Joe Bloggs','uri'=>'http://www.example.com'],
            ['name'=>'Joe Bloggs','uri'=>'http://www.example.com'],
            ['name'=>'Joe Bloggs'],
            ['email'=>'joe@example.com','uri'=>'http://www.example.com'],
            ['uri'=>'http://www.example.com'],
            ['email'=>'joe@example.com']
        ];

        $this->assertEquals($authors, (array) $entry->getAuthors());
    }

    /**
     * Get Author (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsAuthorFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/author/atom10.xml')
        );
        $this->assertEquals(['name'=>'Joe Bloggs', 'email'=>'joe@example.com', 'uri'=>'http://www.example.com'], $entry->getAuthor());
    }

    /**
     * Get Description (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsDescriptionFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/description/atom10.xml')
        );
        $this->assertEquals('Entry Description', $entry->getDescription());
    }

    /**
     * Get enclosure
     * @group ZFR002
     */
    public function testGetsEnclosureFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/enclosure/atom10.xml')
        );

        $expected = new \stdClass();
        $expected->url    = 'http://www.example.org/myaudiofile.mp3';
        $expected->length = '1234';
        $expected->type   = 'audio/mpeg';

        $this->assertEquals($expected, $entry->getEnclosure());
    }

    /**
     * TEXT
     * @group ZFRATOMCONTENT
     */
    public function testGetsContentFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/content/atom10.xml')
        );
        $this->assertEquals('Entry Content &amp;', $entry->getContent());
    }

    /**
     * HTML Escaped
     * @group ZFRATOMCONTENT
     */
    public function testGetsContentFromAtom10Html()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/content/atom10_Html.xml')
        );
        $this->assertEquals('<p>Entry Content &amp;</p>', $entry->getContent());
    }

    /**
     * HTML CDATA Escaped
     * @group ZFRATOMCONTENT
     */
    public function testGetsContentFromAtom10HtmlCdata()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/content/atom10_HtmlCdata.xml')
        );
        $this->assertEquals('<p>Entry Content &amp;</p>', $entry->getContent());
    }

    /**
     * XHTML
     * @group ZFRATOMCONTENT
     */
    public function testGetsContentFromAtom10XhtmlNamespaced()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/content/atom10_Xhtml.xml')
        );
        $this->assertEquals('<p class="x:"><em>Entry Content &amp;x:</em></p>', $entry->getContent());
    }

    /**
     * Get Link (Unencoded Text)
     * @group ZFR002
     */
    public function testGetsLinkFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/link/atom10.xml')
        );
        $this->assertEquals('http://www.example.com/entry', $entry->getLink());
    }

    /**
     * Get Comment HTML Link
     * @group ZFR002
     */
    public function testGetsCommentLinkFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath . '/commentlink/atom10.xml')
        );
        $this->assertEquals('http://www.example.com/entry/comments', $entry->getCommentLink());
    }

    /**
     * Get category data
     * @group ZFR002
     */
    public function testGetsCategoriesFromAtom10()
    {
        $entry = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/category/atom10.xml')
        );
        $this->assertEquals($this->expectedCats, (array) $entry->getCategories());
        $this->assertEquals(['topic1', 'Cat & Dog'], array_values($entry->getCategories()->getValues()));
    }
}
