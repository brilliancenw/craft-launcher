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
}
