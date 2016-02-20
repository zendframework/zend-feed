<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader;

use DOMDocument;
use DOMXPath;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\Http as ZendHttp;
use Zend\Stdlib\ErrorHandler;
use Zend\Feed\Reader\Exception\InvalidHttpClientException;

/**
*/
class Reader implements ReaderImportInterface
{
    /**
     * Namespace constants
     */
    const NAMESPACE_ATOM_03  = 'http://purl.org/atom/ns#';
    const NAMESPACE_ATOM_10  = 'http://www.w3.org/2005/Atom';
    const NAMESPACE_RDF      = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NAMESPACE_RSS_090  = 'http://my.netscape.com/rdf/simple/0.9/';
    const NAMESPACE_RSS_10   = 'http://purl.org/rss/1.0/';

    /**
     * Feed type constants
     */
    const TYPE_ANY              = 'any';
    const TYPE_ATOM_03          = 'atom-03';
    const TYPE_ATOM_10          = 'atom-10';
    const TYPE_ATOM_10_ENTRY    = 'atom-10-entry';
    const TYPE_ATOM_ANY         = 'atom';
    const TYPE_RSS_090          = 'rss-090';
    const TYPE_RSS_091          = 'rss-091';
    const TYPE_RSS_091_NETSCAPE = 'rss-091n';
    const TYPE_RSS_091_USERLAND = 'rss-091u';
    const TYPE_RSS_092          = 'rss-092';
    const TYPE_RSS_093          = 'rss-093';
    const TYPE_RSS_094          = 'rss-094';
    const TYPE_RSS_10           = 'rss-10';
    const TYPE_RSS_20           = 'rss-20';
    const TYPE_RSS_ANY          = 'rss';

    /**
     * Cache instance
     *
     * @var CacheStorage
     */
    protected $cache = null;

    /**
     * HTTP client object to use for retrieving feeds
     *
     * @var Http\ClientInterface
     */
    protected $httpClient = null;

    /**
     * Override HTTP PUT and DELETE request methods?
     *
     * @var bool
     */
    protected $httpMethodOverride = false;

    protected $httpConditionalGet = false;

    protected $extensionManager = null;

    protected $extensions = [
        'feed' => [
            'DublinCore\Feed',
            'Atom\Feed'
        ],
        'entry' => [
            'Content\Entry',
            'DublinCore\Entry',
            'Atom\Entry'
        ],
        'core' => [
            'DublinCore\Feed',
            'Atom\Feed',
            'Content\Entry',
            'DublinCore\Entry',
            'Atom\Entry'
        ]
    ];


    /**
     * Construct a new reader object
     *
     * @param  Http\ClientInterface $httpClient
     * @param  CacheStorage $cache
     * @return Zend\Feed\Reader\Reader
     */
    public function __construct(Http\ClientInterface $httpClient = null, CacheStorage $cache = null)
    {
        if (!is_null($httpClient)) {
            $this->setHttpClient($httpClient);
        }
        if (!is_null($cache)) {
            $this->setCache($cache);
        }
    }

    /**
     * Get the Feed cache
     *
     * @return CacheStorage
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set the feed cache
     *
     * @param  CacheStorage $cache
     * @return void
     */
    public function setCache(CacheStorage $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Set the HTTP client instance
     *
     * Sets the HTTP client object to use for retrieving the feeds.
     *
     * @param  ZendHttp\Client | Http\ClientInterface $httpClient
     * @return void
     */
    public function setHttpClient($httpClient)
    {
        if ($httpClient instanceof ZendHttp\Client) {
            $httpClient = new Http\ZendHttpClientDecorator($httpClient);
        }

        if (! $httpClient instanceof Http\ClientInterface) {
            throw new InvalidHttpClientException();
        }
        $this->httpClient = $httpClient;
    }

    /**
     * Gets the HTTP client object. If none is set, a new ZendHttp\Client will be used.
     *
     * @return Http\ClientInterface
     */
    public function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            return $this->httpClient = new Http\ZendHttpClientDecorator(new ZendHttp\Client());
        }
        return $this->httpClient;
    }

    /**
     * Toggle using POST instead of PUT and DELETE HTTP methods
     *
     * Some feed implementations do not accept PUT and DELETE HTTP
     * methods, or they can't be used because of proxies or other
     * measures. This allows turning on using POST where PUT and
     * DELETE would normally be used; in addition, an
     * X-Method-Override header will be sent with a value of PUT or
     * DELETE as appropriate.
     *
     * @param  bool $override Whether to override PUT and DELETE.
     * @return void
     */
    public function setHttpMethodOverride($override = true)
    {
        $this->httpMethodOverride = $override;
    }

    /**
     * Get the HTTP override state
     *
     * @return bool
     */
    public function getHttpMethodOverride()
    {
        return $this->httpMethodOverride;
    }

    /**
     * Set the flag indicating whether or not to use HTTP conditional GET
     *
     * @param  bool $bool
     * @return void
     */
    public function useHttpConditionalGet($bool = true)
    {
        $this->httpConditionalGet = $bool;
    }

    /**
     * Import a feed by providing a URI
     *
     * @param  string $uri The URI to the feed
     * @param  string $etag OPTIONAL Last received ETag for this resource
     * @param  string $lastModified OPTIONAL Last-Modified value for this resource
     * @return Feed\FeedInterface
     * @throws Exception\RuntimeException
     */
    public function import($uri, $etag = null, $lastModified = null)
    {
        $cache   = $this->getCache();
        $client  = $this->getHttpClient();
        $cacheId = 'Zend_Feed_Reader_' . md5($uri);

        if (static::$httpConditionalGet && $cache) {
            $headers = [];
            $data    = $cache->getItem($cacheId);
            if ($data && $client instanceof Http\HeaderAwareClientInterface) {
                // Only check for ETag and last modified values in the cache
                // if we have a client capable of emitting headers in the first place.
                if ($etag === null) {
                    $etag = $cache->getItem($cacheId . '_etag');
                }
                if ($lastModified === null) {
                    $lastModified = $cache->getItem($cacheId . '_lastmodified');
                }
                if ($etag) {
                    $headers['If-None-Match'] = [$etag];
                }
                if ($lastModified) {
                    $headers['If-Modified-Since'] = [$lastModified];
                }
            }
            $response = $client->get($uri, $headers);
            if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 304) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
            }
            if ($response->getStatusCode() == 304) {
                $responseXml = $data;
            } else {
                $responseXml = $response->getBody();
                $cache->setItem($cacheId, $responseXml);

                if ($response instanceof Http\HeaderAwareResponseInterface) {
                    if ($response->getHeaderLine('ETag', false)) {
                        $cache->setItem($cacheId . '_etag', $response->getHeaderLine('ETag'));
                    }
                    if ($response->getHeaderLine('Last-Modified', false)) {
                        $cache->setItem($cacheId . '_lastmodified', $response->getHeaderLine('Last-Modified'));
                    }
                }
            }
            return static::importString($responseXml);
        } elseif ($cache) {
            $data = $cache->getItem($cacheId);
            if ($data) {
                return static::importString($data);
            }
            $response = $client->get($uri);
            if ((int) $response->getStatusCode() !== 200) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
            }
            $responseXml = $response->getBody();
            $cache->setItem($cacheId, $responseXml);
            return static::importString($responseXml);
        } else {
            $response = $client->get($uri);
            if ((int) $response->getStatusCode() !== 200) {
                throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
            }
            $reader = static::importString($response->getBody());
            $reader->setOriginalSourceUri($uri);
            return $reader;
        }
    }

    /**
     * Import a feed from a remote URI
     *
     * Performs similarly to import(), except it uses the HTTP client passed to
     * the method, and does not take into account cached data.
     *
     * Primary purpose is to make it possible to use the Reader with alternate
     * HTTP client implementations.
     *
     * @param  string $uri
     * @param  Http\ClientInterface $client
     * @return self
     * @throws Exception\RuntimeException if response is not an Http\ResponseInterface
     */
    public function importRemoteFeed($uri, Http\ClientInterface $client)
    {
        $response = $client->get($uri);
        if (! $response instanceof Http\ResponseInterface) {
            throw new Exception\RuntimeException(sprintf(
                'Did not receive a %s\Http\ResponseInterface from the provided HTTP client; received "%s"',
                __NAMESPACE__,
                (is_object($response) ? get_class($response) : gettype($response))
            ));
        }

        if ((int) $response->getStatusCode() !== 200) {
            throw new Exception\RuntimeException('Feed failed to load, got response code ' . $response->getStatusCode());
        }
        $reader = static::importString($response->getBody());
        $reader->setOriginalSourceUri($uri);
        return $reader;
    }

    /**
     * Import a feed from a string
     *
     * @param  string $string
     * @return Feed\FeedInterface
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function importString($string)
    {
        $trimmed = trim($string);
        if (!is_string($string) || empty($trimmed)) {
            throw new Exception\InvalidArgumentException('Only non empty strings are allowed as input');
        }

        $libxmlErrflag = libxml_use_internal_errors(true);
        $oldValue = libxml_disable_entity_loader(true);
        $dom = new DOMDocument;
        $status = $dom->loadXML(trim($string));
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                throw new Exception\InvalidArgumentException(
                    'Invalid XML: Detected use of illegal DOCTYPE'
                );
            }
        }
        libxml_disable_entity_loader($oldValue);
        libxml_use_internal_errors($libxmlErrflag);

        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $error->message = trim($error->message);
                $errormsg = "DOMDocument cannot parse XML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse XML: Please check the XML document's validity";
            }
            throw new Exception\RuntimeException($errormsg);
        }

        $type = static::detectType($dom);

        static::registerCoreExtensions();

        if (substr($type, 0, 3) == 'rss') {
            $reader = new Feed\Rss($dom, $type);
        } elseif (substr($type, 8, 5) == 'entry') {
            $reader = new Entry\Atom($dom->documentElement, 0, self::TYPE_ATOM_10);
        } elseif (substr($type, 0, 4) == 'atom') {
            $reader = new Feed\Atom($dom, $type);
        } else {
            throw new Exception\RuntimeException('The URI used does not point to a '
            . 'valid Atom, RSS or RDF feed that Zend\Feed\Reader can parse.');
        }
        return $reader;
    }

    /**
     * Imports a feed from a file located at $filename.
     *
     * @param  string $filename
     * @throws Exception\RuntimeException
     * @return Feed\FeedInterface
     */
    public function importFile($filename)
    {
        ErrorHandler::start();
        $feed = file_get_contents($filename);
        $err  = ErrorHandler::stop();
        if ($feed === false) {
            throw new Exception\RuntimeException("File '{$filename}' could not be loaded", 0, $err);
        }
        return static::importString($feed);
    }

    /**
     * Find feed links
     *
     * @param $uri
     * @return FeedSet
     * @throws Exception\RuntimeException
     */
    public function findFeedLinks($uri)
    {
        $client   = $this->getHttpClient();
        $response = $client->get($uri);
        if ($response->getStatusCode() !== 200) {
            throw new Exception\RuntimeException("Failed to access $uri, got response code " . $response->getStatusCode());
        }
        $responseHtml = $response->getBody();
        $libxmlErrflag = libxml_use_internal_errors(true);
        $oldValue = libxml_disable_entity_loader(true);
        $dom = new DOMDocument;
        $status = $dom->loadHTML(trim($responseHtml));
        libxml_disable_entity_loader($oldValue);
        libxml_use_internal_errors($libxmlErrflag);
        if (!$status) {
            // Build error message
            $error = libxml_get_last_error();
            if ($error && $error->message) {
                $error->message = trim($error->message);
                $errormsg = "DOMDocument cannot parse HTML: {$error->message}";
            } else {
                $errormsg = "DOMDocument cannot parse HTML: Please check the XML document's validity";
            }
            throw new Exception\RuntimeException($errormsg);
        }
        $feedSet = new FeedSet;
        $links = $dom->getElementsByTagName('link');
        $feedSet->addLinks($links, $uri);
        return $feedSet;
    }

    /**
     * Detect the feed type of the provided feed
     *
     * @param  Feed\AbstractFeed|DOMDocument|string $feed
     * @param  bool $specOnly
     * @return string
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function detectType($feed, $specOnly = false)
    {
        if ($feed instanceof Feed\AbstractFeed) {
            $dom = $feed->getDomDocument();
        } elseif ($feed instanceof DOMDocument) {
            $dom = $feed;
        } elseif (is_string($feed) && !empty($feed)) {
            ErrorHandler::start(E_NOTICE|E_WARNING);
            ini_set('track_errors', 1);
            $oldValue = libxml_disable_entity_loader(true);
            $dom = new DOMDocument;
            $status = $dom->loadXML($feed);
            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    throw new Exception\InvalidArgumentException(
                        'Invalid XML: Detected use of illegal DOCTYPE'
                    );
                }
            }
            libxml_disable_entity_loader($oldValue);
            ini_restore('track_errors');
            ErrorHandler::stop();
            if (!$status) {
                if (!isset($phpErrormsg)) {
                    if (function_exists('xdebug_is_enabled')) {
                        $phpErrormsg = '(error message not available, when XDebug is running)';
                    } else {
                        $phpErrormsg = '(error message not available)';
                    }
                }
                throw new Exception\RuntimeException("DOMDocument cannot parse XML: $phpErrormsg");
            }
        } else {
            throw new Exception\InvalidArgumentException('Invalid object/scalar provided: must'
            . ' be of type Zend\Feed\Reader\Feed, DomDocument or string');
        }
        $xpath = new DOMXPath($dom);

        if ($xpath->query('/rss')->length) {
            $type = self::TYPE_RSS_ANY;
            $version = $xpath->evaluate('string(/rss/@version)');

            if (strlen($version) > 0) {
                switch ($version) {
                    case '2.0':
                        $type = self::TYPE_RSS_20;
                        break;

                    case '0.94':
                        $type = self::TYPE_RSS_094;
                        break;

                    case '0.93':
                        $type = self::TYPE_RSS_093;
                        break;

                    case '0.92':
                        $type = self::TYPE_RSS_092;
                        break;

                    case '0.91':
                        $type = self::TYPE_RSS_091;
                        break;
                }
            }

            return $type;
        }

        $xpath->registerNamespace('rdf', self::NAMESPACE_RDF);

        if ($xpath->query('/rdf:RDF')->length) {
            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_10);

            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length
            ) {
                return self::TYPE_RSS_10;
            }

            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_090);

            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length
            ) {
                return self::TYPE_RSS_090;
            }
        }

        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_10);

        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_10;
        }

        if ($xpath->query('//atom:entry')->length) {
            if ($specOnly == true) {
                return self::TYPE_ATOM_10;
            } else {
                return self::TYPE_ATOM_10_ENTRY;
            }
        }

        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_03);

        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_03;
        }

        return self::TYPE_ANY;
    }

    /**
     * Set plugin manager for use with Extensions
     *
     * @param ExtensionManagerInterface $extensionManager
     */
    public function setExtensionManager(ExtensionManagerInterface $extensionManager)
    {
        $this->extensionManager = $extensionManager;
    }

    /**
     * Get plugin manager for use with Extensions
     *
     * @return ExtensionManagerInterface
     */
    public function getExtensionManager()
    {
        if (!isset($this->extensionManager)) {
            $this->setExtensionManager(new StandaloneExtensionManager());
        }
        return $this->extensionManager;
    }

    /**
     * Register an Extension by name
     *
     * @param  string $name
     * @return void
     * @throws Exception\RuntimeException if unable to resolve Extension class
     */
    public function registerExtension($name)
    {
        $feedName  = $name . '\Feed';
        $entryName = $name . '\Entry';
        $manager   = $this->getExtensionManager();
        if (static::isRegistered($name)) {
            if ($manager->has($feedName) || $manager->has($entryName)) {
                return;
            }
        }

        if (!$manager->has($feedName) && !$manager->has($entryName)) {
            throw new Exception\RuntimeException('Could not load extension: ' . $name
                . ' using Plugin Loader. Check prefix paths are configured and extension exists.');
        }
        if ($manager->has($feedName)) {
            $this->extensions['feed'][] = $feedName;
        }
        if ($manager->has($entryName)) {
            $this->extensions['entry'][] = $entryName;
        }
    }

    /**
     * Is a given named Extension registered?
     *
     * @param  string $extensionName
     * @return bool
     */
    public function isRegistered($extensionName)
    {
        $feedName  = $extensionName . '\Feed';
        $entryName = $extensionName . '\Entry';
        if (in_array($feedName, $this->extensions['feed'])
            || in_array($entryName, $this->extensions['entry'])
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get a list of extensions
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Register core (default) extensions
     *
     * @return void
     */
    protected function registerCoreExtensions()
    {
        $this->registerExtension('DublinCore');
        $this->registerExtension('Content');
        $this->registerExtension('Atom');
        $this->registerExtension('Slash');
        $this->registerExtension('WellFormedWeb');
        $this->registerExtension('Thread');
        $this->registerExtension('Podcast');
    }

    /**
     * Utility method to apply array_unique operation to a multidimensional
     * array.
     *
     * @param array
     * @return array
     */
    public function arrayUnique(array $array)
    {
        foreach ($array as &$value) {
            $value = serialize($value);
        }
        $array = array_unique($array);
        foreach ($array as &$value) {
            $value = unserialize($value);
        }
        return $array;
    }
}
