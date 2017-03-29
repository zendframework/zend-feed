<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\PubSubHubbub;

use Zend\Feed\PubSubHubbub\PubSubHubbub;
use Zend\Feed\Reader\Reader as FeedReader;

/**
 * @group      Zend_Feed
 * @group      Zend_Feed_Subsubhubbub
 */
class PubSubHubbubTest extends \PHPUnit_Framework_TestCase
{
    public function testCanDetectHubs()
    {
        $feed = (new FeedReader())->importFile(__DIR__ . '/_files/rss20.xml');
        $this->assertEquals([
            'http://www.example.com/hub', 'http://www.example.com/hub2'
        ], PubSubHubbub::detectHubs($feed));
    }
}
