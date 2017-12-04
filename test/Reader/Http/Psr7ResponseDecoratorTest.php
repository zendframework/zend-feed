<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use Zend\Feed\Reader\Http\HeaderAwareResponseInterface;
use Zend\Feed\Reader\Http\Psr7ResponseDecorator;
use Zend\Feed\Reader\Http\ResponseInterface;
use ZendTest\Feed\Reader\TestAsset\Psr7Stream;

/**
 * @covers \Zend\Feed\Reader\Http\Psr7ResponseDecorator
 */
class Psr7ResponseDecoratorTest extends TestCase
{
    public function testDecoratorIsAFeedResponse()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertInstanceOf(ResponseInterface::class, $decorator);
    }

    public function testDecoratorIsAHeaderAwareResponse()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertInstanceOf(HeaderAwareResponseInterface::class, $decorator);
    }

    public function testDecoratorIsNotAPsr7Response()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertNotInstanceOf(Psr7ResponseInterface::class, $decorator);
    }

    public function testCanRetrieveDecoratedResponse()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertSame($originalResponse->reveal(), $decorator->getDecoratedResponse());
    }

    public function testProxiesToDecoratedResponseToRetrieveStatusCode()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $originalResponse->getStatusCode()->willReturn(301);
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertSame(301, $decorator->getStatusCode());
    }

    public function testProxiesToDecoratedResponseToRetrieveBody()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $originalResponse->getBody()->willReturn('BODY');
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertSame('BODY', $decorator->getBody());
    }

    public function testCastsStreamToStringWhenReturningPsr7Body()
    {
        $stream = new Psr7Stream('BODY');
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $originalResponse->getBody()->willReturn($stream);
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertSame('BODY', $decorator->getBody());
    }

    public function testProxiesToDecoratedResponseToRetrieveHeaderLine()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $originalResponse->hasHeader('E-Tag')->willReturn(true);
        $originalResponse->getHeaderLine('E-Tag')->willReturn('2015-11-17 12:32:00-06:00');
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertSame('2015-11-17 12:32:00-06:00', $decorator->getHeaderLine('E-Tag'));
    }

    public function testDecoratorReturnsDefaultValueWhenOriginalResponseDoesNotHaveHeader()
    {
        $originalResponse = $this->prophesize(Psr7ResponseInterface::class);
        $originalResponse->hasHeader('E-Tag')->willReturn(false);
        $originalResponse->getHeaderLine('E-Tag')->shouldNotBeCalled();
        $decorator = new Psr7ResponseDecorator($originalResponse->reveal());
        $this->assertSame('2015-11-17 12:32:00-06:00', $decorator->getHeaderLine('E-Tag', '2015-11-17 12:32:00-06:00'));
    }
}
