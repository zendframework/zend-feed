<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Feed\PubSubHubbub\TestAsset;

use Zend\Feed\PubSubHubbub\AbstractCallback;

class Callback extends AbstractCallback
{
    /**
     * {@inheritDoc}
     */
    public function handle(array $httpData = null, $sendResponseNow = false)
    {
        return false;
    }
}
