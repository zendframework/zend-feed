<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader;

use stdClass;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Test as TestAdapter;
use Zend\Http\Response as HttpResponse;
use Zend\Feed\Reader;
use Zend\Feed\Reader\Http\ClientInterface;

/**
* @group Zend_Feed
* @group Zend_Feed_Reader
*/
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    protected $feedSamplePath = null;

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files';
    }

    public function testStringImportTrimsContentToAllowSlightlyInvalidXml()
    {
        $feed = (new Reader\Reader())->importString(
            '   ' . file_get_contents($this->feedSamplePath.'/Reader/rss20.xml')
        );
    }

    public function testDetectsFeedIsRss20()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/rss20.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_20, $type);
    }

    public function testDetectsFeedIsRss094()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/rss094.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_094, $type);
    }

    public function testDetectsFeedIsRss093()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/rss093.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_093, $type);
    }

    public function testDetectsFeedIsRss092()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/rss092.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_092, $type);
    }

    public function testDetectsFeedIsRss091()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/rss091.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_091, $type);
    }

    public function testDetectsFeedIsRss10()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/rss10.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_10, $type);
    }

    public function testDetectsFeedIsRss090()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/rss090.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_090, $type);
    }

    public function testDetectsFeedIsAtom10()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/atom10.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_ATOM_10, $type);
    }

    public function testDetectsFeedIsAtom03()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents($this->feedSamplePath.'/Reader/atom03.xml')
        );
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_ATOM_03, $type);
    }

    /**
     * @group ZF-9723
     */
    public function testDetectsTypeFromStringOrToRemindPaddyAboutForgettingATestWhichLetsAStupidTypoSurviveUnnoticedForMonths()
    {
        $feed = '<?xml version="1.0" encoding="utf-8" ?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/"><channel></channel></rdf:RDF>';
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_10, $type);
    }

    public function testGetEncoding()
    {
        $feed = (new Reader\Reader())->importString(
            file_get_contents(dirname(__FILE__) . '/Entry/_files/Atom/title/plain/atom10.xml')
        );

        $this->assertEquals('utf-8', $feed->getEncoding());
        $this->assertEquals('utf-8', $feed->current()->getEncoding());
    }

    public function testImportsFile()
    {
        $feed = (new Reader\Reader())->importFile(
            dirname(__FILE__) . '/Entry/_files/Atom/title/plain/atom10.xml'
        );
        $this->assertInstanceOf('Zend\Feed\Reader\Feed\FeedInterface', $feed);
    }

    public function testImportsUri()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testImportsUri() requires a network connection');
        }

        (new Reader\Reader())->import('http://www.planet-php.net/rdf/');
    }

    /**
     * @group ZF-8328
     * @expectedException Zend\Feed\Reader\Exception\RuntimeException
     */
    public function testImportsUriAndThrowsExceptionIfNotAFeed()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testImportsUri() requires a network connection');
        }

        (new Reader\Reader())->import('http://example.com');
    }

    public function testGetsFeedLinksAsValueObject()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testGetsFeedLinksAsValueObject() requires a network connection');
        }

        $links = (new Reader\Reader())->findFeedLinks('http://www.planet-php.net');

        $this->assertEquals('http://www.planet-php.org/rss/', $links->rss);
    }

    public function testCompilesLinksAsArrayObject()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testGetsFeedLinksAsValueObject() requires a network connection');
        }
        $links = (new Reader\Reader())->findFeedLinks('http://www.planet-php.net');
        $this->assertInstanceOf('Zend\Feed\Reader\FeedSet', $links);
        $this->assertEquals([
            'rel' => 'alternate', 'type' => 'application/rss+xml', 'href' => 'http://www.planet-php.org/rss/'
        ], (array) $links->getIterator()->current());
    }

    public function testFeedSetLoadsFeedObjectWhenFeedArrayKeyAccessed()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testGetsFeedLinksAsValueObject() requires a network connection');
        }
        $links = (new Reader\Reader())->findFeedLinks('http://www.planet-php.net');
        $link = $links->getIterator()->current();
        $this->assertInstanceOf('Zend\Feed\Reader\Feed\Rss', $link['feed']);
    }

    public function testZeroCountFeedSetReturnedFromEmptyList()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testGetsFeedLinksAsValueObject() requires a network connection');
        }
        $links = (new Reader\Reader())->findFeedLinks('http://www.example.com');
        $this->assertEquals(0, count($links));
    }

    /**
     * @group ZF-8327
     */
    public function testGetsFeedLinksAndTrimsNewlines()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testGetsFeedLinksAsValueObject() requires a network connection');
        }

        $links = (new Reader\Reader())->findFeedLinks('http://www.infopod.com.br');
        $this->assertEquals('http://www.infopod.com.br/feed/', $links->rss);
    }

    /**
     * @group ZF-8330
     */
    public function testGetsFeedLinksAndNormalisesRelativeUrls()
    {
        if (!getenv('TESTS_ZEND_FEED_READER_ONLINE_ENABLED')) {
            $this->markTestSkipped('testGetsFeedLinksAsValueObject() requires a network connection');
        }

        $links = (new Reader\Reader())->findFeedLinks('http://meiobit.com');
        $this->assertEquals('http://meiobit.com/feed/', $links->rss);
    }

    /**
     * @group ZF-8330
     */
    public function testGetsFeedLinksAndNormalisesRelativeUrlsOnUriWithPath()
    {
        $currClient = (new Reader\Reader())->getHttpClient();

        $testAdapter = new TestAdapter();
        $response = new HttpResponse();
        $response->setStatusCode(200);
        $response->setContent('<!DOCTYPE html><html><head><link rel="alternate" type="application/rss+xml" href="../test.rss"><link rel="alternate" type="application/atom+xml" href="/test.atom"></head><body></body></html>');
        $testAdapter->setResponse($response);
        (new Reader\Reader())->setHttpClient(new HttpClient(null, ['adapter' => $testAdapter]));

        $links = (new Reader\Reader())->findFeedLinks('http://foo/bar');

        (new Reader\Reader())->setHttpClient($currClient);

        $this->assertEquals('http://foo/test.rss', $links->rss);
        $this->assertEquals('http://foo/test.atom', $links->atom);
    }

    public function testRegistersUserExtension()
    {
        require_once __DIR__ . '/_files/My/Extension/JungleBooks/Entry.php';
        require_once __DIR__ . '/_files/My/Extension/JungleBooks/Feed.php';
        $manager = new Reader\ExtensionManager(new Reader\ExtensionPluginManager(
            $this->getMockBuilder('Interop\Container\ContainerInterface')->getMock()
        ));
        $manager->setInvokableClass('JungleBooks\Entry', 'My\Extension\JungleBooks\Entry');
        $manager->setInvokableClass('JungleBooks\Feed', 'My\Extension\JungleBooks\Feed');
        (new Reader\Reader())->setExtensionManager($manager);
        (new Reader\Reader())->registerExtension('JungleBooks');

        $this->assertTrue((new Reader\Reader())->isRegistered('JungleBooks'));
    }

    /**
     * This test is failing on windows:
     * Failed asserting that exception of type "Zend\Feed\Reader\Exception\RuntimeException" matches expected exception "Zend\Feed\Reader\Exception\InvalidArgumentException". Message was: "DOMDocument cannot parse XML: Entity 'discloseInfo' failed to parse".
     * @todo why is the assertEquals commented out?
     */
    public function testXxePreventionOnFeedParsing()
    {
        $this->setExpectedException('Zend\Feed\Reader\Exception\InvalidArgumentException');
        $string = file_get_contents($this->feedSamplePath.'/Reader/xxe-atom10.xml');
        $string = str_replace('XXE_URI', $this->feedSamplePath.'/Reader/xxe-info.txt', $string);
        $feed = (new Reader\Reader())->importString($string);
        //$this->assertEquals('info:', $feed->getTitle());
    }

    public function testImportRemoteFeedMethodPerformsAsExpected()
    {
        $uri = 'http://example.com/feeds/reader.xml';
        $feedContents = file_get_contents($this->feedSamplePath . '/Reader/rss20.xml');
        $response = $this->getMock('Zend\Feed\Reader\Http\ResponseInterface', ['getStatusCode', 'getBody']);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->will($this->returnValue(200));
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($feedContents));

        $client = $this->getMock('Zend\Feed\Reader\Http\ClientInterface', ['get']);
        $client->expects($this->once())
            ->method('get')
            ->with($this->equalTo($uri))
            ->will($this->returnValue($response));

        $feed = (new Reader\Reader())->importRemoteFeed($uri, $client);
        $this->assertInstanceOf('Zend\Feed\Reader\Feed\FeedInterface', $feed);
        $type = (new Reader\Reader())->detectType($feed);
        $this->assertEquals(Reader\Reader::TYPE_RSS_20, $type);
    }

    public function testImportStringMethodThrowProperExceptionOnEmptyString()
    {
        $this->setExpectedException('Zend\Feed\Reader\Exception\InvalidArgumentException');
        $string = ' ';
        $feed = (new Reader\Reader())->importString($string);
    }

    public function testSetHttpFeedClient()
    {
        $client = $this->getMock('Zend\Feed\Reader\Http\ClientInterface');
        (new Reader\Reader())->setHttpClient($client);
        $this->assertEquals($client, (new Reader\Reader())->getHttpClient());
    }

    public function testSetHttpClientWillDecorateAZendHttpClientInstance()
    {
        $client = new HttpClient();
        (new Reader\Reader())->setHttpClient($client);
        $cached = (new Reader\Reader())->getHttpClient();
        $this->assertInstanceOf(ClientInterface::class, $cached);
        $this->assertAttributeSame($client, 'client', $cached);
    }

    public function testSetHttpClientThrowsException()
    {
        $this->setExpectedException(Reader\Exception\InvalidHttpClientException::class);
        (new Reader\Reader())->setHttpClient(new stdClass);
    }

    protected function _getTempDirectory()
    {
        $tmpdir = [];
        foreach ([$_ENV, $_SERVER] as $tab) {
            foreach (['TMPDIR', 'TEMP', 'TMP', 'windir', 'SystemRoot'] as $key) {
                if (isset($tab[$key])) {
                    if (($key == 'windir') or ($key == 'SystemRoot')) {
                        $dir = realpath($tab[$key] . '\\temp');
                    } else {
                        $dir = realpath($tab[$key]);
                    }
                    if ($this->_isGoodTmpDir($dir)) {
                        return $dir;
                    }
                }
            }
        }
        if (function_exists('sys_get_temp_dir')) {
            $dir = sys_get_temp_dir();
            if ($this->_isGoodTmpDir($dir)) {
                return $dir;
            }
        }
        $tempFile = tempnam(md5(uniqid(rand(), true)), '');
        if ($tempFile) {
            $dir = realpath(dirname($tempFile));
            unlink($tempFile);
            if ($this->_isGoodTmpDir($dir)) {
                return $dir;
            }
        }
        if ($this->_isGoodTmpDir('/tmp')) {
            return '/tmp';
        }
        if ($this->_isGoodTmpDir('\\temp')) {
            return '\\temp';
        }
    }

    protected function _isGoodTmpDir($dir)
    {
        return (is_readable($dir) && is_writable($dir));
    }
}
