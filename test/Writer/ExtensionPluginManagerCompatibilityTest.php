<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Feed\Writer;

use PHPUnit\Framework\TestCase;
use Zend\Feed\Writer\Exception\InvalidArgumentException;
use Zend\Feed\Writer\ExtensionPluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

class ExtensionPluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new ExtensionPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf()
    {
        return;
    }

    public function testInstanceOfMatches()
    {
        $this->markTestSkipped(sprintf(
            'Skipping test; %s allows multiple extension types',
            ExtensionPluginManager::class
        ));
    }
}
