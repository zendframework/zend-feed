<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Reader\TestAsset;

use Psr\Http\Message\StreamInterface;

class Psr7Stream implements StreamInterface
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

    public function close()
    {
    }

    public function detach()
    {
    }

    public function getSize()
    {
    }

    public function tell()
    {
    }

    public function eof()
    {
    }

    public function isSeekable()
    {
    }

    public function seek($offset, $whence = SEEK_SET)
    {
    }

    public function rewind()
    {
    }

    public function isWritable()
    {
    }

    public function write($string)
    {
    }

    public function isReadable()
    {
    }

    public function read($length)
    {
    }

    public function getContents()
    {
    }

    public function getMetadata($key = null)
    {
    }
}
