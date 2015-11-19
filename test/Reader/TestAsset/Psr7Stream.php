<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader\TestAsset;

/**
 * This class may be used as a dummy for testing the return value from a
 * PSR-7 message's getBody() method. It does not implement the StreamInterface,
 * as PHP prior to version 7 does not do any return typehinting, making strict
 * adherence unnecessary.
 */
class Psr7Stream
{
    private $streamValue;

    public function __construct($streamValue)
    {
        $this->streamValue = $streamValue;
    }

    public function __toString()
    {
        return (string) $this->streamValue;
    }
}
