<?php
namespace brilliance\launcher\integrations;

use Craft;
use doublesecretagency\viewcount\ViewCount;

/**
 * View Count integration for Launcher
 *
 * Displays view counts for entries, categories, and other elements
 */
class ViewCountIntegration extends BaseIntegration
{
    /**
     * @inheritdoc
     */
    public function getHandle(): string
    {
        return 'view-count';
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'View Count';
    }

    /**
     * @inheritdoc
     */
    public function getIcon(): string
    {
        // Eye icon for view count
        return '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8 3C4.5 3 1.5 5.5 1 8c.5 2.5 3.5 5 7 5s6.5-2.5 7-5c-.5-2.5-3.5-5-7-5z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/>
            <circle cx="8" cy="8" r="2.5" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>';
    }

    /**
     * @inheritdoc
     */
    public function getSupportedTypes(): array
    {
        // View Count can track any element type
        return ['Entry', 'Category', 'Asset', 'GlobalSet', 'Global', 'User'];
    }

    /**
     * Check if View Count plugin is installed and enabled
     *
     * @return bool
     */
    private function isViewCountInstalled(): bool
    {
        return Craft::$app->getPlugins()->isPluginInstalled('view-count')
            && Craft::$app->getPlugins()->isPluginEnabled('view-count')
            && class_exists('doublesecretagency\\viewcount\\ViewCount');
    }

    /**
     * @inheritdoc
     */
    public function canHandleItem(array $item): bool
    {
        // Must have View Count installed
        if (!$this->isViewCountInstalled()) {
            return false;
        }

        // Must be a supported type
        if (!parent::canHandleItem($item)) {
            return false;
        }

        // Must have an element ID
        if (empty($item['id'])) {
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
                $this->logInfo('ViewCount: Item missing ID, attempting lookup by URL', [
                    'url' => $item['url'],
                    'type' => $item['type'],
                ]);
                $element = $this->getElementByUrl($item['url'], $item['type']);
                if ($element) {
                    $this->logInfo('ViewCount: Element found by URL', [
                        'elementId' => $element->id,
                    ]);
                    $item['id'] = $element->id;
                } else {
                    $this->logInfo('ViewCount: Element NOT found by URL', [
                        'url' => $item['url'],
                        'type' => $item['type'],
                    ]);
                }
            }

            // Still no ID? Can't proceed
            if (empty($item['id'])) {
                $this->logInfo('ViewCount: No element ID available, skipping', [
                    'item' => $item,
                ]);
                return null;
            }

            $elementId = (int) $item['id'];

            // Get the view count for this element
            $viewCount = ViewCount::$plugin->query->total($elementId);

            // Format the view count nicely
            $viewLabel = $this->formatViewCount($viewCount);

            $this->logInfo('View Count integration data generated', [
                'elementId' => $elementId,
                'viewCount' => $viewCount,
            ]);

            return [
                'handle' => $this->getHandle(),
                'name' => $this->getName(),
                'icon' => $this->getIcon(),
                'status' => [
                    'label' => $viewLabel,
                    'type' => 'default',
                ],
                'actions' => [], // No actions for view count (read-only)
            ];
        } catch (\Exception $e) {
            $this->logError('Error getting View Count integration data', [
                'item' => $item,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Format view count for display
     *
     * @param int $count
     * @return string
     */
    private function formatViewCount(int $count): string
    {
        if ($count === 0) {
            return 'No views';
        }

        if ($count === 1) {
            return '1 view';
        }

        // Format large numbers with K/M suffixes
        if ($count >= 1000000) {
            return number_format($count / 1000000, 1) . 'M views';
        }

        if ($count >= 1000) {
            return number_format($count / 1000, 1) . 'K views';
        }

        return number_format($count) . ' views';
    }

    /**
     * @inheritdoc
     */
    public function executeAction(string $action, array $params): array
    {
        // View Count integration has no actions (read-only)
        return $this->errorResponse('View Count has no actions');
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
                case 'Asset':
                    // Assets don't typically have URIs
                    return null;
                case 'Global':
                case 'GlobalSet':
                    // Globals don't have URIs
                    return null;
                case 'User':
                    // Users don't have URIs
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
                case 'Asset':
                    return Craft::$app->getAssets()->getAssetById($id);
                case 'Global':
                case 'GlobalSet':
                    return Craft::$app->getGlobals()->getSetById($id);
                case 'User':
                    return Craft::$app->getUsers()->getUserById($id);
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

            $this->logInfo('ViewCount: Attempting to extract element ID from CP URL', [
                'url' => $url,
                'type' => $type,
                'cpTrigger' => $cpTrigger,
            ]);

            // Check if this is a CP URL
            if (!str_contains($url, '/' . $cpTrigger . '/') && !str_contains($url, 'index.php?p=' . $cpTrigger)) {
                $this->logInfo('ViewCount: URL is not a CP URL', ['url' => $url]);
                return null;
            }

            // Parse different CP URL patterns based on element type
            switch ($type) {
                case 'Entry':
                    // Pattern: /admin/entries/sectionHandle/123-slug or /admin/entries/sectionHandle/123
                    if (preg_match('#/' . preg_quote($cpTrigger) . '/entries/[^/]+/(\d+)#', $url, $matches)) {
                        $this->logInfo('ViewCount: Extracted entry ID from CP URL', ['id' => $matches[1]]);
                        return (int) $matches[1];
                    }
                    break;

                case 'Category':
                    // Pattern: /admin/categories/groupHandle/123-slug
                    if (preg_match('#/' . preg_quote($cpTrigger) . '/categories/[^/]+/(\d+)#', $url, $matches)) {
                        $this->logInfo('ViewCount: Extracted category ID from CP URL', ['id' => $matches[1]]);
                        return (int) $matches[1];
                    }
                    break;

                case 'Asset':
                    // Pattern: /admin/assets/volumeHandle/123
                    if (preg_match('#/' . preg_quote($cpTrigger) . '/assets/[^/]+/(\d+)#', $url, $matches)) {
                        $this->logInfo('ViewCount: Extracted asset ID from CP URL', ['id' => $matches[1]]);
                        return (int) $matches[1];
                    }
                    break;

                case 'User':
                    // Pattern: /admin/users/123
                    if (preg_match('#/' . preg_quote($cpTrigger) . '/users/(\d+)#', $url, $matches)) {
                        $this->logInfo('ViewCount: Extracted user ID from CP URL', ['id' => $matches[1]]);
                        return (int) $matches[1];
                    }
                    break;

                case 'Global':
                case 'GlobalSet':
                    // Globals are accessed by handle, not ID in URLs
                    return null;
            }

            $this->logInfo('ViewCount: Could not extract element ID from CP URL', [
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
