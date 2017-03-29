<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\PubSubHubbub;

use Zend\Http\Client as HttpClient;

class ClientNotReset extends HttpClient
{
    public function resetParameters($clearCookies = false, $clearAuth = true)
    {
        // Do nothing
    }
}
