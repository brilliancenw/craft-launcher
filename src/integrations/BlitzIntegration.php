<?php
namespace brilliance\launcher\integrations;

use Craft;
use putyourlightson\blitz\Blitz;
use putyourlightson\blitz\models\SiteUriModel;
use putyourlightson\blitz\records\CacheRecord;

/**
 * Blitz caching integration for Launcher
 *
 * Provides cache status and clear cache actions for entries, categories, and global sets
 */
class BlitzIntegration extends BaseIntegration
{
    /**
     * @inheritdoc
     */
    public function getHandle(): string
    {
        return 'blitz';
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Blitz Cache';
    }

    /**
     * @inheritdoc
     */
    public function getIcon(): string
    {
        // Blitz lightning bolt icon
        return '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.5 1L3 9h5l-.5 6L13 7H8l.5-6z" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linejoin="round"/>
        </svg>';
    }

    /**
     * @inheritdoc
     */
    public function getSupportedTypes(): array
    {
        return ['Entry', 'Category', 'GlobalSet', 'Global'];
    }

    /**
     * Check if Blitz plugin is installed and enabled
     *
     * @return bool
     */
    private function isBlitzInstalled(): bool
    {
        return Craft::$app->getPlugins()->isPluginInstalled('blitz')
            && Craft::$app->getPlugins()->isPluginEnabled('blitz');
    }

    /**
     * @inheritdoc
     */
    public function canHandleItem(array $item): bool
    {
        // Must have Blitz installed
        if (!$this->isBlitzInstalled()) {
            return false;
        }

        // Must be a supported type
        if (!parent::canHandleItem($item)) {
            return false;
        }

        // Must have either a URL/URI or an element ID we can look up
        if (empty($item['url']) && empty($item['uri']) && empty($item['id'])) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getIntegrationData(array $item): ?array
    {
        if (!$this->canHandleItem($item)) {
            return null;
        }

        try {
            // If item doesn't have an ID, try to look it up by URL
            if (empty($item['id']) && !empty($item['url']) && !empty($item['type'])) {
                $this->logInfo('Blitz: Item missing ID, attempting lookup by URL', [
                    'url' => $item['url'],
                    'type' => $item['type'],
                ]);
                $element = $this->getElementByUrl($item['url'], $item['type']);
                if ($element) {
                    $this->logInfo('Blitz: Element found by URL', [
                        'elementId' => $element->id,
                        'elementUri' => $element->uri ?? 'no-uri',
                    ]);
                    $item['id'] = $element->id;
                    $item['uri'] = $element->uri;
                } else {
                    $this->logInfo('Blitz: Element NOT found by URL', [
                        'url' => $item['url'],
                        'type' => $item['type'],
                    ]);
                }
            }

            // Get the item's URI
            $uri = $this->extractUri($item);
            if (!$uri) {
                // Element doesn't have a front-end URI (might be disabled, no URL, etc)
                $this->logInfo('Item has no URI for cache checking', [
                    'item_id' => $item['id'] ?? null,
                    'item_title' => $item['title'] ?? null,
                    'item_type' => $item['type'] ?? null,
                ]);
                return null;
            }

            // Get site ID
            $siteId = $item['siteId'] ?? Craft::$app->getSites()->getCurrentSite()->id;

            // Check if the page is cached
            $siteUri = new SiteUriModel([
                'siteId' => $siteId,
                'uri' => $uri,
            ]);

            // Check cache status
            $isCached = $this->isCached($siteUri);
            $isCacheable = $this->isCacheable($siteUri);

            $this->logInfo('Blitz integration data generated', [
                'uri' => $uri,
                'siteId' => $siteId,
                'isCached' => $isCached,
                'isCacheable' => $isCacheable,
            ]);

            // Determine status label and type
            if (!$isCacheable) {
                $statusLabel = 'Not Cacheable';
                $statusType = 'warning';
            } elseif ($isCached) {
                $statusLabel = 'Cached';
                $statusType = 'success';
            } else {
                $statusLabel = 'Uncached';
                $statusType = 'secondary';
            }

            // Build actions array - only show Clear Cache if actually cached
            $actions = [];
            if ($isCached) {
                $actions[] = [
                    'label' => 'Clear Cache',
                    'action' => 'clearCache',
                    'confirm' => true,
                    'confirmMessage' => 'Clear the Blitz cache for this page?',
                ];
            }

            return [
                'handle' => $this->getHandle(),
                'name' => $this->getName(),
                'icon' => $this->getIcon(),
                'status' => [
                    'label' => $statusLabel,
                    'type' => $statusType,
                ],
                'actions' => $actions,
            ];
        } catch (\Exception $e) {
            $this->logError('Error getting Blitz integration data', [
                'item' => $item,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function executeAction(string $action, array $params): array
    {
        if (!$this->isBlitzInstalled()) {
            return $this->errorResponse('Blitz plugin is not installed or enabled');
        }

        try {
            switch ($action) {
                case 'clearCache':
                    return $this->clearCache($params);

                default:
                    return $this->errorResponse('Unknown action: ' . $action);
            }
        } catch (\Exception $e) {
            $this->logError('Error executing Blitz action', [
                'action' => $action,
                'params' => $params,
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse('Error: ' . $e->getMessage());
        }
    }

    /**
     * Clear the Blitz cache for a specific item
     *
     * @param array $params
     * @return array
     */
    private function clearCache(array $params): array
    {
        $item = $params['item'] ?? $params;

        // Get the item's URI
        $uri = $this->extractUri($item);
        if (!$uri) {
            return $this->errorResponse('Could not determine page URI');
        }

        // Get site ID
        $siteId = $item['siteId'] ?? Craft::$app->getSites()->getCurrentSite()->id;

        // Create SiteUri model
        $siteUri = new SiteUriModel([
            'siteId' => $siteId,
            'uri' => $uri,
        ]);

        // Clear the cache
        Blitz::$plugin->clearCache->clearUris([$siteUri]);

        $this->logInfo('Cleared Blitz cache', [
            'uri' => $uri,
            'siteId' => $siteId,
        ]);

        return $this->successResponse('Cache cleared successfully', [
            'uri' => $uri,
            'siteId' => $siteId,
        ]);
    }

    /**
     * Check if a page is cached
     *
     * @param SiteUriModel $siteUri
     * @return bool
     */
    private function isCached(SiteUriModel $siteUri): bool
    {
        try {
            // Check if there's a cache record
            $cachedValue = Blitz::$plugin->cacheStorage->get($siteUri);
            return !empty($cachedValue);
        } catch (\Exception $e) {
            $this->logError('Error checking cache status', [
                'siteUri' => $siteUri->toArray(),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if a page is cacheable (not excluded from caching)
     *
     * @param SiteUriModel $siteUri
     * @return bool
     */
    private function isCacheable(SiteUriModel $siteUri): bool
    {
        try {
            // Check if caching is enabled globally
            if (!Blitz::$plugin->settings->cachingEnabled) {
                return false;
            }

            // Check if this URI should be cached
            $shouldCache = Blitz::$plugin->cacheRequest->getIsCacheableSiteUri($siteUri);

            return $shouldCache;
        } catch (\Exception $e) {
            $this->logError('Error checking if cacheable', [
                'siteUri' => $siteUri->toArray(),
                'error' => $e->getMessage(),
            ]);
            // If we can't determine, assume it's cacheable to avoid false warnings
            return true;
        }
    }

    /**
     * Extract URI from item data
     *
     * @param array $item
     * @return string|null
     */
    private function extractUri(array $item): ?string
    {
        // Check for direct URI
        if (!empty($item['uri'])) {
            return $item['uri'];
        }

        // Try to get element and its URI (do this first since URLs might be CP URLs)
        if (!empty($item['id']) && !empty($item['type'])) {
            $element = $this->getElementById($item['id'], $item['type']);
            if ($element && $element->uri) {
                return $element->uri;
            }
        }

        // Try to extract from URL (only if not a CP URL)
        if (!empty($item['url'])) {
            $cpTrigger = Craft::$app->getConfig()->getGeneral()->cpTrigger;

            // Skip CP URLs
            if (str_contains($item['url'], '/' . $cpTrigger . '/') || str_contains($item['url'], 'index.php?p=' . $cpTrigger)) {
                return null;
            }

            // Parse the URL to get the path
            $urlParts = parse_url($item['url']);
            if (isset($urlParts['path'])) {
                return ltrim($urlParts['path'], '/');
            }
        }

        return null;
    }

    /**
     * Get element by ID and type
     *
     * @param int $id
     * @param string $type
     * @return \craft\base\ElementInterface|null
     */
    private function getElementById(int $id, string $type)
    {
        try {
            switch ($type) {
                case 'Entry':
                    return Craft::$app->getEntries()->getEntryById($id);
                case 'Category':
                    return Craft::$app->getCategories()->getCategoryById($id);
                case 'Global':
                case 'GlobalSet':
                    return Craft::$app->getGlobals()->getSetById($id);
                default:
                    return null;
            }
        } catch (\Exception $e) {
            $this->logError('Error getting element', [
                'id' => $id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Try to get element by URL
     *
     * @param string $url
     * @param string $type
     * @return \craft\base\ElementInterface|null
     */
    private function getElementByUrl(string $url, string $type)
    {
        try {
            // First, try to extract element ID from CP URL
            $elementId = $this->extractElementIdFromCpUrl($url, $type);
            if ($elementId) {
                return $this->getElementById($elementId, $type);
            }

            // Otherwise, try to extract URI from URL and find by URI
            $uri = $this->extractUriFromUrl($url);
            if (!$uri) {
                return null;
            }

            // Try to find element by URI
            switch ($type) {
                case 'Entry':
                    return \craft\elements\Entry::find()->uri($uri)->one();
                case 'Category':
                    return \craft\elements\Category::find()->uri($uri)->one();
                case 'Global':
                case 'GlobalSet':
                    // Globals don't have URIs
                    return null;
                default:
                    return null;
            }
        } catch (\Exception $e) {
            $this->logError('Error getting element by URL', [
                'url' => $url,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract element ID from CP URL
     * CP URLs typically look like: /admin/entries/sectionHandle/123-slug
     *
     * @param string $url
     * @param string $type
     * @return int|null
     */
    private function extractElementIdFromCpUrl(string $url, string $type): ?int
    {
        try {
            $cpTrigger = Craft::$app->getConfig()->getGeneral()->cpTrigger;

            $this->logInfo('Blitz: Attempting to extract element ID from CP URL', [
                'url' => $url,
                'type' => $type,
                'cpTrigger' => $cpTrigger,
            ]);

            // Check if this is a CP URL
            if (!str_contains($url, '/' . $cpTrigger . '/') && !str_contains($url, 'index.php?p=' . $cpTrigger)) {
                $this->logInfo('Blitz: URL is not a CP URL', ['url' => $url]);
                return null;
            }

            // Parse different CP URL patterns based on element type
            switch ($type) {
                case 'Entry':
                    // Pattern: /admin/entries/sectionHandle/123-slug or /admin/entries/sectionHandle/123
                    if (preg_match('#/' . preg_quote($cpTrigger) . '/entries/[^/]+/(\d+)#', $url, $matches)) {
                        $this->logInfo('Blitz: Extracted entry ID from CP URL', ['id' => $matches[1]]);
                        return (int) $matches[1];
                    }
                    break;

                case 'Category':
                    // Pattern: /admin/categories/groupHandle/123-slug
                    if (preg_match('#/' . preg_quote($cpTrigger) . '/categories/[^/]+/(\d+)#', $url, $matches)) {
                        $this->logInfo('Blitz: Extracted category ID from CP URL', ['id' => $matches[1]]);
                        return (int) $matches[1];
                    }
                    break;

                case 'Global':
                case 'GlobalSet':
                    // Pattern: /admin/globals/setHandle
                    // Globals are accessed by handle, not ID in URLs, so this won't work
                    return null;
            }

            $this->logInfo('Blitz: Could not extract element ID from CP URL', [
                'url' => $url,
                'type' => $type,
            ]);

            return null;
        } catch (\Exception $e) {
            $this->logError('Error extracting element ID from CP URL', [
                'url' => $url,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract URI from URL
     *
     * @param string $url
     * @return string|null
     */
    private function extractUriFromUrl(string $url): ?string
    {
        // Get the CP trigger (default is 'admin' but can be customized)
        $cpTrigger = Craft::$app->getConfig()->getGeneral()->cpTrigger;

        // Skip CP URLs
        if (str_contains($url, '/' . $cpTrigger . '/') || str_contains($url, 'index.php?p=' . $cpTrigger)) {
            return null;
        }

        // Parse the URL to get the path
        $urlParts = parse_url($url);
        if (isset($urlParts['path'])) {
            return ltrim($urlParts['path'], '/');
        }

        return null;
    }
}
