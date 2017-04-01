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
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Feed\Reader\Exception\InvalidArgumentException;
use Zend\Feed\Reader\Http\Response as FeedResponse;
use Zend\Feed\Reader\Http\ZendHttpClientDecorator;
use Zend\Http\Client;
use Zend\Http\Headers;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;

/**
 * @covers \Zend\Feed\Reader\Http\ZendHttpClientDecorator
 */
class ZendHttpClientDecoratorTest extends TestCase
{
    public function setUp()
    {
        $this->client = $this->prophesize(Client::class);
    }

    public function prepareDefaultClientInteractions($uri, ObjectProphecy $response)
    {
        $this->client->resetParameters()->shouldBeCalled();
        $this->client->setMethod('GET')->shouldBeCalled();
        $this->client->setHeaders(Argument::type(Headers::class))->shouldBeCalled();
        $this->client->setUri($uri)->shouldBeCalled();
        $this->client->send()->will(function () use ($response) {
            return $response->reveal();
        });
    }

    public function createMockHttpResponse($statusCode, $body, Headers $headers = null)
    {
        $response = $this->prophesize(HttpResponse::class);
        $response->getStatusCode()->willReturn($statusCode);
        $response->getBody()->willReturn($body);
        $response->getHeaders()->willReturn($headers ?: new Headers());
        return $response;
    }

    public function createMockHttpHeaders(array $headers)
    {
        $mock = $this->prophesize(Headers::class);
        $mock->toArray()->willReturn($headers);
        return $mock;
    }

    public function testProvidesAccessToDecoratedClient()
    {
        $client = $this->prophesize(Client::class)->reveal();
        $decorator = new ZendHttpClientDecorator($client);
        $this->assertSame($client, $decorator->getDecoratedClient());
    }

    public function testDecoratorReturnsFeedResponse()
    {
        $headers = $this->createMockHttpHeaders(['Content-Type' => 'application/rss+xml']);
        $httpResponse = $this->createMockHttpResponse(200, '', $headers->reveal());
        $this->prepareDefaultClientInteractions('http://example.com', $httpResponse);

        $client = new ZendHttpClientDecorator($this->client->reveal());
        $response = $client->get('http://example.com');

        $this->assertInstanceOf(FeedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getBody());
        $this->assertEquals('application/rss+xml', $response->getHeaderLine('Content-Type'));
    }

    public function testDecoratorInjectsProvidedHeadersIntoClientWhenSending()
    {
        $responseHeaders = $this->createMockHttpHeaders([
            'Content-Type' => 'application/rss+xml',
            'Content-Length' => 1234,
            'X-Content-Length' => 1234.56,
        ]);
        $httpResponse = $this->createMockHttpResponse(200, '', $responseHeaders->reveal());
        $this->prepareDefaultClientInteractions('http://example.com', $httpResponse);

        $requestHeaders = $this->prophesize(Headers::class);
        $requestHeaders->addHeaderLine('Accept', 'application/rss+xml')->shouldBeCalled();
        $request = $this->prophesize(HttpRequest::class);
        $request->getHeaders()->willReturn($requestHeaders->reveal());
        $this->client->getRequest()->willReturn($request->reveal());

        $client = new ZendHttpClientDecorator($this->client->reveal());
        $response = $client->get('http://example.com', ['Accept' => [ 'application/rss+xml' ]]);

        $this->assertInstanceOf(FeedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', $response->getBody());
        $this->assertEquals('application/rss+xml', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(1234, $response->getHeaderLine('Content-Length'));
        $this->assertEquals(1234.56, $response->getHeaderLine('X-Content-Length'));
    }

    public function invalidHeaders()
    {
        $basicTests = [
            'zero-name' => [
                [[ 'value' ]],
                'Header names',
            ],
            'int-name' => [
                [1 => [ 'value' ]],
                'Header names',
            ],
            'numeric-name' => [
                ['1.1' => [ 'value' ]],
                'Header names',
            ],
            'empty-name' => [
                ['' => [ 'value' ]],
                'Header names',
            ],
            'null-value' => [
                ['X-Test' => null],
                'Header values',
            ],
            'true-value' => [
                ['X-Test' => true],
                'Header values',
            ],
            'false-value' => [
                ['X-Test' => false],
                'Header values',
            ],
            'zero-value' => [
                ['X-Test' => 0],
                'Header values',
            ],
            'int-value' => [
                ['X-Test' => 1],
                'Header values',
            ],
            'zero-float-value' => [
                ['X-Test' => 0.0],
                'Header values',
            ],
            'float-value' => [
                ['X-Test' => 1.1],
                'Header values',
            ],
            'string-value' => [
                ['X-Test' => 'value'],
                'Header values',
            ],
            'object-value' => [
                ['X-Test' => (object) [ 'value' ]],
                'Header values',
            ],
        ];

        foreach ($basicTests as $key => $arguments) {
            yield $key => $arguments;
        }

        $invalidIndividualValues = [
            'null-individual-value'   => null,
            'true-individual-value'   => true,
            'false-individual-value'  => false,
            'array-individual-value'  => ['string'],
            'object-individual-value' => (object) ['string'],
        ];

        foreach ($invalidIndividualValues as $key => $value) {
            yield $key => [['X-Test' => [ $value ]], 'strings or numbers'];
        }
    }

    /**
     * @dataProvider invalidHeaders
     */
    public function testDecoratorRaisesExceptionForInvalidHeaders($headers, $contains)
    {
        $httpResponse = $this->createMockHttpResponse(200, '');
        $this->client->resetParameters()->shouldBeCalled();
        $this->client->setMethod('GET')->shouldBeCalled();
        $this->client->setHeaders(Argument::type(Headers::class))->shouldBeCalled();
        $this->client->setUri('http://example.com')->shouldBeCalled();

        $requestHeaders = $this->prophesize(Headers::class);
        $request = $this->prophesize(HttpRequest::class);
        $request->getHeaders()->willReturn($requestHeaders->reveal());
        $this->client->getRequest()->willReturn($request->reveal());

        $client = new ZendHttpClientDecorator($this->client->reveal());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($contains);
        $client->get('http://example.com', $headers);
    }
}
