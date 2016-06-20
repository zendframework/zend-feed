<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Zend\Feed\Reader\FeedSet;

class FeedSetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FeedSet
     */
    protected $feedSet;

    protected function setUp()
    {
        $this->feedSet = new FeedSet();
    }

    /**
     * @dataProvider linkAndUriProvider
     */
    public function testAbsolutiseUri($link, $uri, $expected)
    {
        $method = new ReflectionMethod('Zend\Feed\Reader\FeedSet', 'absolutiseUri');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->feedSet, $link, $uri));
    }

    public function linkAndUriProvider()
    {
        return [
            'fully-qualified'   => ['feed', 'http://example.com', 'http://example.com/feed'],
            'fully-qualified-https' => ['feed', 'https://example.com', 'https://example.com/feed'],
            'scheme-relative'   => ['feed', '//example.com', 'http://example.com/feed'],
            'double-slash-path' => ['//feed','//example.com', 'http://example.com/feed'],
        ];
    }
}
