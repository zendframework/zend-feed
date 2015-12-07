<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Writer;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for feed writer extensions
 *
 * Validation checks that we have an Entry, Feed, or Extension\AbstractRenderer.
 */
class ExtensionPluginManager extends AbstractPluginManager
{
    /**
     * Default set of extension classes
     *
     * @var array
     */
    protected $invokables = [
        'atomrendererfeed'           => 'Zend\Feed\Writer\Extension\Atom\Renderer\Feed',
        'contentrendererentry'       => 'Zend\Feed\Writer\Extension\Content\Renderer\Entry',
        'dublincorerendererentry'    => 'Zend\Feed\Writer\Extension\DublinCore\Renderer\Entry',
        'dublincorerendererfeed'     => 'Zend\Feed\Writer\Extension\DublinCore\Renderer\Feed',
        'itunesentry'                => 'Zend\Feed\Writer\Extension\ITunes\Entry',
        'itunesfeed'                 => 'Zend\Feed\Writer\Extension\ITunes\Feed',
        'itunesrendererentry'        => 'Zend\Feed\Writer\Extension\ITunes\Renderer\Entry',
        'itunesrendererfeed'         => 'Zend\Feed\Writer\Extension\ITunes\Renderer\Feed',
        'slashrendererentry'         => 'Zend\Feed\Writer\Extension\Slash\Renderer\Entry',
        'threadingrendererentry'     => 'Zend\Feed\Writer\Extension\Threading\Renderer\Entry',
        'wellformedwebrendererentry' => 'Zend\Feed\Writer\Extension\WellFormedWeb\Renderer\Entry',
    ];

    /**
     * Do not share instances
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the extension loaded is of a valid type.
     *
     * @param  mixed $instance
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validate($instance)
    {
        if ($instance instanceof Extension\AbstractRenderer) {
            // we're okay
            return;
        }

        if ('Feed' == substr(get_class($instance), -4)) {
            // we're okay
            return;
        }

        if ('Entry' == substr(get_class($instance), -5)) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Extension\RendererInterface '
            . 'or the classname must end in "Feed" or "Entry"',
            (is_object($instance) ? get_class($instance) : gettype($instance)),
            __NAMESPACE__
        ));
    }
}
