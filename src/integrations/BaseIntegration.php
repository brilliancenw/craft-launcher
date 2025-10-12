<?php
namespace brilliance\launcher\integrations;

use Craft;

/**
 * Base class for Launcher integrations
 *
 * Provides common functionality for integration implementations
 */
abstract class BaseIntegration implements LauncherIntegrationInterface
{
    /**
     * @inheritdoc
     */
    abstract public function getHandle(): string;

    /**
     * @inheritdoc
     */
    abstract public function getName(): string;

    /**
     * @inheritdoc
     */
    abstract public function getIcon(): string;

    /**
     * @inheritdoc
     */
    abstract public function getSupportedTypes(): array;

    /**
     * @inheritdoc
     */
    public function canHandleItem(array $item): bool
    {
        // Check if the item type is supported
        $itemType = $item['type'] ?? null;

        if (!$itemType) {
            return false;
        }

        return in_array($itemType, $this->getSupportedTypes(), true);
    }

    /**
     * @inheritdoc
     */
    abstract public function getIntegrationData(array $item): ?array;

    /**
     * @inheritdoc
     */
    abstract public function executeAction(string $action, array $params): array;

    /**
     * Helper method to create a success response
     *
     * @param string $message Success message
     * @param array $data Additional data to include
     * @return array
     */
    protected function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Helper method to create an error response
     *
     * @param string $message Error message
     * @param array $data Additional data to include
     * @return array
     */
    protected function errorResponse(string $message, array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Log an error message
     *
     * @param string $message
     * @param array $context
     */
    protected function logError(string $message, array $context = []): void
    {
        Craft::error($message . ' ' . json_encode($context), __CLASS__);
    }

    /**
     * Log an info message
     *
     * @param string $message
     * @param array $context
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Craft::info($message . ' ' . json_encode($context), __CLASS__);
    }
}
