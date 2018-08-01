<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Feed\PubSubHubbub;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Zend\Feed\PubSubHubbub\AbstractCallback;

class AbstractCallbackTest extends TestCase
{
    public function testDetectCallbackUrlIgnoresXOriginalUrlHeaderWhenXRewriteUrlHeaderIsNotPresent()
    {
        $_SERVER = array_merge($_SERVER, [
            'HTTP_X_ORIGINAL_URL' => '/hijack-attempt',
            'HTTPS' => 'on',
            'HTTP_HOST' => 'example.com',
            'REQUEST_URI' => '/requested/path',
        ]);

        $callback = new TestAsset\Callback();

        $r = new ReflectionMethod($callback, '_detectCallbackUrl');
        $r->setAccessible(true);

        $this->assertSame('/requested/path', $r->invoke($callback));
    }

    public function testDetectCallbackUrlRequiresCombinationOfIISWasUrlRewrittenAndUnencodedUrlToReturnEarly()
    {
        $_SERVER = array_merge($_SERVER, [
            'IIS_WasUrlRewritten' => '1',
            'UNENCODED_URL' => '/requested/path',
        ]);

        $callback = new TestAsset\Callback();

        $r = new ReflectionMethod($callback, '_detectCallbackUrl');
        $r->setAccessible(true);

        $this->assertSame('/requested/path', $r->invoke($callback));
    }

    public function testDetectCallbackUrlUsesRequestUriWhenNoOtherRewriteHeadersAreFound()
    {
        $_SERVER = array_merge($_SERVER, [
            'REQUEST_URI' => '/expected/path'
        ]);

        $callback = new TestAsset\Callback();

        $r = new ReflectionMethod($callback, '_detectCallbackUrl');
        $r->setAccessible(true);

        $this->assertSame('/expected/path', $r->invoke($callback));
    }

    public function testDetectCallbackUrlFallsBackToOrigPathInfoWhenAllElseFails()
    {
        $_SERVER = array_merge($_SERVER, [
            'ORIG_PATH_INFO' => '/expected/path',
        ]);

        $callback = new TestAsset\Callback();

        $r = new ReflectionMethod($callback, '_detectCallbackUrl');
        $r->setAccessible(true);

        $this->assertSame('/expected/path', $r->invoke($callback));
    }

    public function testDetectCallbackReturnsEmptyStringIfNoResourcesMatchedInServerSuperglobal()
    {
        $callback = new TestAsset\Callback();

        $r = new ReflectionMethod($callback, '_detectCallbackUrl');
        $r->setAccessible(true);

        $this->assertSame('', $r->invoke($callback));
    }
}
