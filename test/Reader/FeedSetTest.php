<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Zend\Feed\Reader\FeedSet;

class FeedSetTest extends TestCase
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
    public function testAbsolutiseUri($link, $uri, $result)
    {
        $method = new ReflectionMethod(FeedSet::class, 'absolutiseUri');
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invoke($this->feedSet, $link, $uri));
    }

    public function linkAndUriProvider()
    {
        return [
            'fully-qualified' => ['feed', 'http://example.com', 'http://example.com/feed'],
            'default-scheme' => ['feed', '//example.com', 'http://example.com/feed'],
            'relative-path' => ['./feed', 'http://example.com/page', 'http://example.com/page/feed'],
            'relative-path-parent' => ['../feed', 'http://example.com/page', 'http://example.com/feed'],
            'scheme-relative' => ['//example.com/feed', 'https://example.org', 'https://example.com/feed'],
            'scheme-relative-default' => ['//example.com/feed', '//example.org', 'http://example.com/feed'],
            'invalid-absolute' => ['ftp://feed', 'http://example.com', null],
            'invalid' => ['', null, null],
        ];
    }
}
