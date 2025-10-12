<?php
namespace brilliance\launcher\integrations;

/**
 * Interface for Launcher integrations
 *
 * Allows plugins to provide contextual information and actions
 * for search results in the Launcher plugin.
 */
interface LauncherIntegrationInterface
{
    /**
     * Get the unique handle for this integration
     *
     * @return string
     */
    public function getHandle(): string;

    /**
     * Get the display name for this integration
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the integration icon
     *
     * @return string SVG markup, image URL, or emoji/text
     */
    public function getIcon(): string;

    /**
     * Get the item types this integration supports
     *
     * @return string[] Array of item types (Entry, Category, GlobalSet, Asset, Utility, etc.)
     */
    public function getSupportedTypes(): array;

    /**
     * Check if this integration can handle a specific item
     *
     * @param array $item The search result item data
     * @return bool
     */
    public function canHandleItem(array $item): bool;

    /**
     * Get integration data for a specific item
     *
     * Returns an array with status and actions:
     * [
     *     'handle' => 'integration-handle',
     *     'status' => [
     *         'label' => 'Status Label',
     *         'type' => 'success|warning|info|secondary',  // CSS class type
     *         'icon' => 'icon-name',  // Optional
     *     ],
     *     'actions' => [
     *         [
     *             'label' => 'Action Label',
     *             'action' => 'action-handle',
     *             'confirm' => true|false,  // Show confirmation?
     *             'confirmMessage' => 'Confirmation message',  // Optional custom message
     *         ],
     *         ...
     *     ]
     * ]
     *
     * @param array $item The search result item data
     * @return array|null Integration data array, or null if not applicable
     */
    public function getIntegrationData(array $item): ?array;

    /**
     * Execute an action for this integration
     *
     * @param string $action The action handle to execute
     * @param array $params Parameters for the action (typically includes item data)
     * @return array Response with success status and optional message/data
     *               ['success' => true|false, 'message' => 'Result message', 'data' => [...]]
     */
    public function executeAction(string $action, array $params): array;
}
