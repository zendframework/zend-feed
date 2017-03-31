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
    public function testAbsolutiseUri($link, $uri, $result = 'http://example.com/feed')
    {
        $method = new ReflectionMethod('Zend\Feed\Reader\FeedSet', 'absolutiseUri');
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invoke($this->feedSet, $link, $uri));
    }

    public function linkAndUriProvider()
    {
        return [
            'fully-qualified'   => ['feed', 'http://example.com'],
            'scheme-relative'   => ['feed', '//example.com'],
            'protocol-relative' => ['//example.com/feed', 'https://example.org', 'https://example.com/feed'],
            'protocol-relative-default' => ['//example.com/feed', '//example.org'],
        ];
    }
}
