<?php
/**
 * @see       https://github.com/zendframework/zend-feed for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-feed/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Feed\Reader\TestAsset;

use Zend\Feed\Reader\Exception\InvalidArgumentException;
use Zend\Feed\Reader\Extension;
use Zend\Feed\Reader\ExtensionManagerInterface;

/**
 * Standalone extension manager that omits any extensions added after the 2.9 series.
 */
class CustomExtensionManager implements ExtensionManagerInterface
{
    private $extensions = [
        'Atom\Entry'            => Extension\Atom\Entry::class,
        'Atom\Feed'             => Extension\Atom\Feed::class,
        'Content\Entry'         => Extension\Content\Entry::class,
        'CreativeCommons\Entry' => Extension\CreativeCommons\Entry::class,
        'CreativeCommons\Feed'  => Extension\CreativeCommons\Feed::class,
        'DublinCore\Entry'      => Extension\DublinCore\Entry::class,
        'DublinCore\Feed'       => Extension\DublinCore\Feed::class,
        'Podcast\Entry'         => Extension\Podcast\Entry::class,
        'Podcast\Feed'          => Extension\Podcast\Feed::class,
        'Slash\Entry'           => Extension\Slash\Entry::class,
        'Syndication\Feed'      => Extension\Syndication\Feed::class,
        'Thread\Entry'          => Extension\Thread\Entry::class,
        'WellFormedWeb\Entry'   => Extension\WellFormedWeb\Entry::class,
    ];

    /**
     * Do we have the extension?
     *
     * @param  string $extension
     * @return bool
     */
    public function has($extension)
    {
        return array_key_exists($extension, $this->extensions);
    }

    /**
     * Retrieve the extension
     *
     * @param  string $extension
     * @return Extension\AbstractEntry|Extension\AbstractFeed
     */
    public function get($extension)
    {
        $class = $this->extensions[$extension];
        return new $class();
    }
}
