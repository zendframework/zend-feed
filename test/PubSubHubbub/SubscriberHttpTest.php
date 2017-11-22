<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\PubSubHubbub;

use PHPUnit\Framework\TestCase;
use Zend\Feed\PubSubHubbub\Model\Subscription;
use Zend\Feed\PubSubHubbub\PubSubHubbub;
use Zend\Feed\PubSubHubbub\Subscriber;
use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Socket;

/**
 * Note that $this->_baseuri must point to a directory on a web server
 * containing all the files under the files directory. You should symlink
 * or copy these files and set '_baseuri' properly using the constant in
 * phpunit.xml (based on phpunit.xml.dist)
 *
 * You can also set the proper constant in your test configuration file to
 * point to the right place.
 *
 * @group      Zend_Feed
 * @group      Zend_Feed_Subsubhubbub
 */
class SubscriberHttpTest extends TestCase
{
    /** @var Subscriber */
    protected $subscriber = null;

    /** @var string */
    protected $baseuri;

    /** @var HttpClient */
    protected $client = null;

    protected $storage;

    public function setUp()
    {
        $this->baseuri = getenv('TESTS_ZEND_FEED_PUBSUBHUBBUB_BASEURI');
        if ($this->baseuri) {
            if (substr($this->baseuri, -1) != '/') {
                $this->baseuri .= '/';
            }
            $name = $this->getName();
            if (($pos = strpos($name, ' ')) !== false) {
                $name = substr($name, 0, $pos);
            }
            $uri = $this->baseuri . $name . '.php';
            $this->client = new HttpClient($uri);
            $this->client->setAdapter(Socket::class);
            PubSubHubbub::setHttpClient($this->client);
            $this->subscriber = new Subscriber;

            $this->storage = $this->_getCleanMock(Subscription::class);
            $this->subscriber->setStorage($this->storage);
        } else {
            // Skip tests
            $this->markTestSkipped('Zend\Feed\PubSubHubbub\Subscriber dynamic tests are not enabled in phpunit.xml');
        }
    }

    public function testSubscriptionRequestSendsExpectedPostData()
    {
        $this->subscriber->setTopicUrl('http://www.example.com/topic');
        $this->subscriber->addHubUrl($this->baseuri . '/testRawPostData.php');
        $this->subscriber->setCallbackUrl('http://www.example.com/callback');
        $this->subscriber->setTestStaticToken('abc'); // override for testing
        $this->subscriber->subscribeAll();
        $this->assertEquals(
            'hub.callback=http%3A%2F%2Fwww.example.com%2Fcallback%3Fxhub.subscription%3D5536df06b5d'
            .'cb966edab3a4c4d56213c16a8184b&hub.lease_seconds=2592000&hub.mode='
            .'subscribe&hub.topic=http%3A%2F%2Fwww.example.com%2Ftopic&hub.veri'
            .'fy=sync&hub.verify=async&hub.verify_token=abc',
            $this->client->getResponse()->getBody()
        );
    }

    public function testUnsubscriptionRequestSendsExpectedPostData()
    {
        $this->subscriber->setTopicUrl('http://www.example.com/topic');
        $this->subscriber->addHubUrl($this->baseuri . '/testRawPostData.php');
        $this->subscriber->setCallbackUrl('http://www.example.com/callback');
        $this->subscriber->setTestStaticToken('abc'); //override for testing
        $this->subscriber->unsubscribeAll();
        $this->assertEquals(
            'hub.callback=http%3A%2F%2Fwww.example.com%2Fcallback%3Fxhub.subscription%3D5536df06b5d'
            .'cb966edab3a4c4d56213c16a8184b&hub.mode=unsubscribe&hub.topic=http'
            .'%3A%2F%2Fwww.example.com%2Ftopic&hub.verify=sync&hub.verify=async'
            .'&hub.verify_token=abc',
            $this->client->getResponse()->getBody()
        );

        $subscriptionRecord = $this->subscriber->getStorage()->getSubscription();
        $this->assertEquals($subscriptionRecord['subscription_state'], PubSubHubbub::SUBSCRIPTION_TODELETE);
    }

    // @codingStandardsIgnoreStart
    protected function _getCleanMock($className)
    {
        // @codingStandardsIgnoreEnd
        $class = new \ReflectionClass($className);
        $methods = $class->getMethods();
        $stubMethods = [];
        foreach ($methods as $method) {
            if ($method->isPublic() || ($method->isProtected()
            && $method->isAbstract())) {
                $stubMethods[] = $method->getName();
            }
        }
        $mocked = $this->getMockBuilder($className)->setMethods($stubMethods)->getMock();
        return $mocked;
    }
}
