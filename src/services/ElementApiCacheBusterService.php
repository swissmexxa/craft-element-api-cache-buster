<?php
/**
 * ElementApiCacheBuster plugin for Craft CMS 3.x
 *
 * Delete cache of element api if necessary .
 *
 * @link      http://knowhere.com
 * @copyright Copyright (c) 2021 Martin Lüpold
 */

namespace mexx\elementapicachebuster\services;

use mexx\elementapicachebuster\ElementApiCacheBuster;

use Craft;
use craft\base\Component;

/**
 * ElementApiCacheBusterService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Martin Lüpold
 * @package   ElementApiCacheBuster
 * @since     0.0.1
 */
class ElementApiCacheBusterService extends Component
{
    public $elementApiPrefix = 'elementapi:';
    public $elementApiAffix = ':';
    public $dependencies = array(
        'page' => array( 'url' => 'api/page/', 'lists' => []),
        'news' => array( 'url' => 'api/news/', 'lists' => ['api/news']),
        'pictureGallery' => array( 'url' => 'api/galleries/', 'lists' => ['api/galleries']),
        'event' => array( 'url' => null, 'lists' => ['api/events']),
        'navigation' => array( 'url' => null, 'lists' => ['api/navigation/header', 'api/navigation/footer']),
    );

    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     ElementApiCacheBuster::$plugin->elementApiCacheBusterService->exampleService()
     *
     * @return mixed
     */
    public function bustEntryCache($slug, $siteId, $type)
    {
        if (!$this->safetyChecks($type, false)) {
            return;
        }

        // Cash key example of element api: elementapi:1:api/news/turnplausch-mit-ariella-kaeslin:
        // => elementapi:siteid:url:
        $cacheKey = $this->getCacheKey($siteId, $this->dependencies['' . $type]['url']  . $slug);
        Craft::$app->getCache()->delete($cacheKey);
    }

    public function bustListsCache($siteId, $type)
    {
        if (!$this->safetyChecks($type, true)) {
            return;
        }

        foreach ($this->dependencies[''. $type]['lists'] as $url) {
            $cacheKey = $this->getCacheKey($siteId, $url);
            Craft::$app->getCache()->delete($cacheKey);
        }
    }

    // Private Methods
    // =========================================================================
    private function safetyChecks($type, $isList)
    {
        // check if key exists
        if (!array_key_exists('' . $type, $this->dependencies)) {
            return false;
        }

        // check if single entry cache should be cleared
        if (!$isList && $this->dependencies['' . $type]['url'] == null) {
            return false;
        }

        return true;
    }

    private function getCacheKey($siteId, $url)
    {
        return $this->elementApiPrefix . $siteId . ':' . $url . $this->elementApiAffix;
    }
}
