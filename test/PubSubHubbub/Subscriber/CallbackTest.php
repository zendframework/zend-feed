<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\PubSubHubbub\Subscriber;

use ArrayObject;
use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Feed\PubSubHubbub\Exception\ExceptionInterface;
use Zend\Feed\PubSubHubbub\HttpResponse;
use Zend\Feed\PubSubHubbub\Model;
use Zend\Feed\PubSubHubbub\Subscriber\Callback as CallbackSubscriber;

/**
 * @group      Zend_Feed
 * @group      Zend_Feed_Subsubhubbub
 */
class CallbackTest extends TestCase
{
    // @codingStandardsIgnoreStart
    /** @var CallbackSubscriber */
    public $_callback;
    /** @var \Zend\Db\Adapter\Adapter|\PHPUnit_Framework_MockObject_MockObject */
    public $_adapter;
    /** @var \Zend\Db\TableGateway\TableGateway|\PHPUnit_Framework_MockObject_MockObject */
    public $_tableGateway;
    /** @var \Zend\Db\ResultSet\ResultSet|\PHPUnit_Framework_MockObject_MockObject */
    public $_rowset;
    /** @var array */
    public $_get;
    // @codingStandardsIgnoreEnd
    /** @var DateTime */
    public $now;

    public function setUp()
    {
        $this->_callback = new CallbackSubscriber;

        $this->_adapter      = $this->_getCleanMock(
            Adapter::class
        );
        $this->_tableGateway = $this->_getCleanMock(
            TableGateway::class
        );
        $this->_rowset       = $this->_getCleanMock(
            ResultSet::class
        );

        $this->_tableGateway->expects($this->any())
            ->method('getAdapter')
            ->will($this->returnValue($this->_adapter));
        $storage = new Model\Subscription($this->_tableGateway);

        $this->now = new DateTime();
        $storage->setNow(clone $this->now);

        $this->_callback->setStorage($storage);

        $this->_get = [
            'hub_mode'          => 'subscribe',
            'hub_topic'         => 'http://www.example.com/topic',
            'hub_challenge'     => 'abc',
            'hub_verify_token'  => 'cba',
            'hub_lease_seconds' => '1234567'
        ];

        $_SERVER['REQUEST_METHOD'] = 'get';
        $_SERVER['QUERY_STRING']   = 'xhub.subscription=verifytokenkey';
    }

    public function testCanSetHttpResponseObject()
    {
        $this->_callback->setHttpResponse(new HttpResponse);
        $this->assertInstanceOf(HttpResponse::class, $this->_callback->getHttpResponse());
    }

    public function testCanUsesDefaultHttpResponseObject()
    {
        $this->assertInstanceOf(HttpResponse::class, $this->_callback->getHttpResponse());
    }

    public function testThrowsExceptionOnInvalidHttpResponseObjectSet()
    {
        $this->expectException(ExceptionInterface::class);
        $this->_callback->setHttpResponse(new \stdClass);
    }

    public function testThrowsExceptionIfNonObjectSetAsHttpResponseObject()
    {
        $this->expectException(ExceptionInterface::class);
        $this->_callback->setHttpResponse('');
    }

    public function testCanSetSubscriberCount()
    {
        $this->_callback->setSubscriberCount('10000');
        $this->assertEquals(10000, $this->_callback->getSubscriberCount());
    }

    public function testDefaultSubscriberCountIsOne()
    {
        $this->assertEquals(1, $this->_callback->getSubscriberCount());
    }

    public function testThrowsExceptionOnSettingZeroAsSubscriberCount()
    {
        $this->expectException(ExceptionInterface::class);
        $this->_callback->setSubscriberCount(0);
    }

    public function testThrowsExceptionOnSettingLessThanZeroAsSubscriberCount()
    {
        $this->expectException(ExceptionInterface::class);
        $this->_callback->setSubscriberCount(-1);
    }

    public function testThrowsExceptionOnSettingAnyScalarTypeCastToAZeroOrLessIntegerAsSubscriberCount()
    {
        $this->expectException(ExceptionInterface::class);
        $this->_callback->setSubscriberCount('0aa');
    }


    public function testCanSetStorageImplementation()
    {
        $storage = new Model\Subscription($this->_tableGateway);
        $this->_callback->setStorage($storage);
        $this->assertThat($this->_callback->getStorage(), $this->identicalTo($storage));
    }

    /**
     * @group ZF2_CONFLICT
     */
    public function testValidatesValidHttpGetData()
    {
        $mockReturnValue = $this->getMockBuilder('Result')->setMethods(['getArrayCopy'])->getMock();
        $mockReturnValue->expects($this->any())
            ->method('getArrayCopy')
            ->will($this->returnValue([
                'verify_token' => hash('sha256', 'cba')
            ]));

        $this->_tableGateway->expects($this->any())
            ->method('select')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue($this->_rowset));
        $this->_rowset->expects($this->any())
            ->method('current')
            ->will($this->returnValue($mockReturnValue));
        $this->_rowset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(1));

        $this->assertTrue($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfHubVerificationNotAGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfModeMissingFromHttpGetData()
    {
        unset($this->_get['hub_mode']);
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfTopicMissingFromHttpGetData()
    {
        unset($this->_get['hub_topic']);
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfChallengeMissingFromHttpGetData()
    {
        unset($this->_get['hub_challenge']);
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfVerifyTokenMissingFromHttpGetData()
    {
        unset($this->_get['hub_verify_token']);
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsTrueIfModeSetAsUnsubscribeFromHttpGetData()
    {
        $mockReturnValue = $this->getMockBuilder('Result')->setMethods(['getArrayCopy'])->getMock();
        $mockReturnValue->expects($this->any())
            ->method('getArrayCopy')
            ->will($this->returnValue([
                'verify_token' => hash('sha256', 'cba')
            ]));

        $this->_get['hub_mode'] = 'unsubscribe';
        $this->_tableGateway->expects($this->any())
            ->method('select')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue($this->_rowset));
        $this->_rowset->expects($this->any())
            ->method('current')
            ->will($this->returnValue($mockReturnValue));
        // require for the count call on the rowset in Model/Subscription
        $this->_rowset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(1));

        $this->assertTrue($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfModeNotRecognisedFromHttpGetData()
    {
        $this->_get['hub_mode'] = 'abc';
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfLeaseSecondsMissedWhenModeIsSubscribeFromHttpGetData()
    {
        unset($this->_get['hub_lease_seconds']);
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfHubTopicInvalidFromHttpGetData()
    {
        $this->_get['hub_topic'] = 'http://';
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfVerifyTokenRecordDoesNotExistForConfirmRequest()
    {
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testReturnsFalseIfVerifyTokenRecordDoesNotAgreeWithConfirmRequest()
    {
        $this->assertFalse($this->_callback->isValidHubVerification($this->_get));
    }

    public function testRespondsToInvalidConfirmationWith404Response()
    {
        unset($this->_get['hub_mode']);
        $this->_callback->handle($this->_get);
        $this->assertEquals(404, $this->_callback->getHttpResponse()->getStatusCode());
    }

    public function testRespondsToValidConfirmationWith200Response()
    {
        $this->_get['hub_mode'] = 'unsubscribe';
        $this->_tableGateway->expects($this->any())
            ->method('select')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue($this->_rowset));

        $t = clone $this->now;
        $rowdata = [
            'id'            => 'verifytokenkey',
            'verify_token'  => hash('sha256', 'cba'),
            'created_time'  => $t->getTimestamp(),
            'lease_seconds' => 10000
        ];

        $row = new ArrayObject($rowdata, ArrayObject::ARRAY_AS_PROPS);

        $this->_rowset->expects($this->any())
            ->method('current')
            ->will($this->returnValue($row));
        // require for the count call on the rowset in Model/Subscription
        $this->_rowset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(1));

        $this->_tableGateway->expects($this->once())
            ->method('delete')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue(true));

        $this->_callback->handle($this->_get);
        $this->assertEquals(200, $this->_callback->getHttpResponse()->getStatusCode());
    }

    public function testRespondsToValidConfirmationWithBodyContainingHubChallenge()
    {
        $this->_tableGateway->expects($this->any())
            ->method('select')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue($this->_rowset));

        $t = clone $this->now;
        $rowdata = [
            'id'            => 'verifytokenkey',
            'verify_token'  => hash('sha256', 'cba'),
            'created_time'  => $t->getTimestamp(),
            'lease_seconds' => 10000
        ];

        $row = new ArrayObject($rowdata, ArrayObject::ARRAY_AS_PROPS);

        $this->_rowset->expects($this->any())
            ->method('current')
            ->will($this->returnValue($row));
        // require for the count call on the rowset in Model/Subscription
        $this->_rowset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(1));

        $this->_tableGateway->expects($this->once())
            ->method('update')
            ->with(
                $this->equalTo([
                    'id'                => 'verifytokenkey',
                    'verify_token'      => hash('sha256', 'cba'),
                    'created_time'      => $t->getTimestamp(),
                    'lease_seconds'     => 1234567,
                    'subscription_state' => 'verified',
                    'expiration_time'   => $t->add(new DateInterval('PT1234567S'))->format('Y-m-d H:i:s')
                ]),
                $this->equalTo(['id' => 'verifytokenkey'])
            );

        $this->_callback->handle($this->_get);
        $this->assertEquals('abc', $this->_callback->getHttpResponse()->getContent());
    }

    public function testRespondsToValidFeedUpdateRequestWith200Response()
    {
        $_SERVER['REQUEST_METHOD']     = 'POST';
        $_SERVER['REQUEST_URI']        = '/some/path/callback/verifytokenkey';
        $_SERVER['CONTENT_TYPE']       = 'application/atom+xml';
        $feedXml                       = file_get_contents(__DIR__ . '/_files/atom10.xml');
        $GLOBALS['HTTP_RAW_POST_DATA'] = $feedXml; // dirty  alternative to php://input

        $this->_tableGateway->expects($this->any())
            ->method('select')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue($this->_rowset));

        $rowdata = [
            'id'           => 'verifytokenkey',
            'verify_token' => hash('sha256', 'cba'),
            'created_time' => time()
        ];

        $row = new ArrayObject($rowdata, ArrayObject::ARRAY_AS_PROPS);

        $this->_rowset->expects($this->any())
            ->method('current')
            ->will($this->returnValue($row));
        // require for the count call on the rowset in Model/Subscription
        $this->_rowset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(1));

        $this->_callback->handle([]);
        $this->assertEquals(200, $this->_callback->getHttpResponse()->getStatusCode());
    }

    public function testRespondsToInvalidFeedUpdateNotPostWith404Response()
    {
        // yes, this example makes no sense for GET - I know!!!
        $_SERVER['REQUEST_METHOD']     = 'GET';
        $_SERVER['REQUEST_URI']        = '/some/path/callback/verifytokenkey';
        $_SERVER['CONTENT_TYPE']       = 'application/atom+xml';
        $feedXml                       = file_get_contents(__DIR__ . '/_files/atom10.xml');
        $GLOBALS['HTTP_RAW_POST_DATA'] = $feedXml;

        $this->_callback->handle([]);
        $this->assertEquals(404, $this->_callback->getHttpResponse()->getStatusCode());
    }

    public function testRespondsToInvalidFeedUpdateWrongMimeWith404Response()
    {
        $_SERVER['REQUEST_METHOD']     = 'POST';
        $_SERVER['REQUEST_URI']        = '/some/path/callback/verifytokenkey';
        $_SERVER['CONTENT_TYPE']       = 'application/kml+xml';
        $feedXml                       = file_get_contents(__DIR__ . '/_files/atom10.xml');
        $GLOBALS['HTTP_RAW_POST_DATA'] = $feedXml;
        $this->_callback->handle([]);
        $this->assertEquals(404, $this->_callback->getHttpResponse()->getStatusCode());
    }

    /**
     * As a judgement call, we must respond to any successful request, regardless
     * of the wellformedness of any XML payload, by returning a 2xx response code.
     * The validation of feeds and their processing must occur outside the Hubbub
     * protocol.
     */
    public function testRespondsToInvalidFeedUpdateWrongFeedTypeForMimeWith200Response()
    {
        $_SERVER['REQUEST_METHOD']     = 'POST';
        $_SERVER['REQUEST_URI']        = '/some/path/callback/verifytokenkey';
        $_SERVER['CONTENT_TYPE']       = 'application/rss+xml';
        $feedXml                       = file_get_contents(__DIR__ . '/_files/atom10.xml');
        $GLOBALS['HTTP_RAW_POST_DATA'] = $feedXml;

        $this->_tableGateway->expects($this->any())
            ->method('select')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue($this->_rowset));

        $rowdata = [
            'id'            => 'verifytokenkey',
            'verify_token'  => hash('sha256', 'cba'),
            'created_time'  => time(),
            'lease_seconds' => 10000
        ];

        $row = new ArrayObject($rowdata, ArrayObject::ARRAY_AS_PROPS);


        $this->_rowset->expects($this->any())
            ->method('current')
            ->will($this->returnValue($row));
        // require for the count call on the rowset in Model/Subscription
        $this->_rowset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(1));

        $this->_callback->handle([]);
        $this->assertEquals(200, $this->_callback->getHttpResponse()->getStatusCode());
    }

    public function testRespondsToValidFeedUpdateWithXHubOnBehalfOfHeader()
    {
        $_SERVER['REQUEST_METHOD']     = 'POST';
        $_SERVER['REQUEST_URI']        = '/some/path/callback/verifytokenkey';
        $_SERVER['CONTENT_TYPE']       = 'application/atom+xml';
        $feedXml                       = file_get_contents(__DIR__ . '/_files/atom10.xml');
        $GLOBALS['HTTP_RAW_POST_DATA'] = $feedXml;

        $this->_tableGateway->expects($this->any())
            ->method('select')
            ->with($this->equalTo(['id' => 'verifytokenkey']))
            ->will($this->returnValue($this->_rowset));

        $rowdata = [
            'id'            => 'verifytokenkey',
            'verify_token'  => hash('sha256', 'cba'),
            'created_time'  => time(),
            'lease_seconds' => 10000
        ];

        $row = new ArrayObject($rowdata, ArrayObject::ARRAY_AS_PROPS);


        $this->_rowset->expects($this->any())
            ->method('current')
            ->will($this->returnValue($row));
        // require for the count call on the rowset in Model/Subscription
        $this->_rowset->expects($this->any())
            ->method('count')
            ->will($this->returnValue(1));

        $this->_callback->handle([]);
        $this->assertEquals(1, $this->_callback->getHttpResponse()->getHeader('X-Hub-On-Behalf-Of'));
    }

    // @codingStandardsIgnoreStart
    protected function _getCleanMock($className)
    {
        // @codingStandardsIgnoreEnd
        $class       = new \ReflectionClass($className);
        $methods     = $class->getMethods();
        $stubMethods = [];
        foreach ($methods as $method) {
            if ($method->isPublic() || ($method->isProtected()
                                        && $method->isAbstract())
            ) {
                $stubMethods[] = $method->getName();
            }
        }
        $mocked = $this->getMockBuilder($className)
            ->setMethods($stubMethods)
            ->setConstructorArgs([])
            ->setMockClassName(str_replace('\\', '_', ($className . '_PubsubSubscriberMock_' . uniqid())))
            ->disableOriginalConstructor()
            ->getMock();
        return $mocked;
    }
}
