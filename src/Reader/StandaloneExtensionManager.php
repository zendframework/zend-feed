<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader;

class StandaloneExtensionManager implements ExtensionManagerInterface
{
    private $extensions = [
        'Atom\Entry'            => Zend\Feed\Reader\Extension\Atom\Entry::class,
        'Atom\Feed'             => Zend\Feed\Reader\Extension\Atom\Feed::class,
        'Content\Entry'         => Zend\Feed\Reader\Extension\Content\Entry::class,
        'CreativeCommons\Entry' => Zend\Feed\Reader\Extension\CreativeCommons\Entry::class,
        'CreativeCommons\Feed'  => Zend\Feed\Reader\Extension\CreativeCommons\Feed::class,
        'DublinCore\Entry'      => Zend\Feed\Reader\Extension\DublinCore\Entry::class,
        'DublinCore\Feed'       => Zend\Feed\Reader\Extension\DublinCore\Feed::class,
        'Podcast\Entry'         => Zend\Feed\Reader\Extension\Podcast\Entry::class,
        'Podcast\Feed'          => Zend\Feed\Reader\Extension\Podcast\Feed::class,
        'Slash\Entry'           => Zend\Feed\Reader\Extension\Slash\Entry::class,
        'Syndication\Feed'      => Zend\Feed\Reader\Extension\Syndication\Feed::class,
        'Thread\Entry'          => Zend\Feed\Reader\Extension\Thread\Entry::class,
        'WellFormedWeb\Entry'   => Zend\Feed\Reader\Extension\WellFormedWeb\Entry::class,
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

    /**
     * Add an extension.
     *
     * @param string $name
     * @param string $class
     */
    public function add($name, $class)
    {
        $this->extensions[$name] = $class;
    }

    /**
     * Remove an extension.
     *
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->extensions[$name]);
    }
}
