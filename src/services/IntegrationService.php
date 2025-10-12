<?php
namespace brilliance\launcher\services;

use brilliance\launcher\integrations\BlitzIntegration;
use brilliance\launcher\integrations\ViewCountIntegration;
use brilliance\launcher\integrations\LauncherIntegrationInterface;
use Craft;
use craft\base\Component;
use yii\base\Event;

/**
 * Integration Service
 *
 * Manages third-party plugin integrations for the Launcher
 */
class IntegrationService extends Component
{
    /**
     * @event RegisterIntegrationsEvent
     */
    public const EVENT_REGISTER_INTEGRATIONS = 'registerIntegrations';

    /**
     * @var LauncherIntegrationInterface[]|null Registered integrations
     */
    private ?array $integrations = null;

    /**
     * Get all registered integrations
     *
     * @return LauncherIntegrationInterface[]
     */
    public function getIntegrations(): array
    {
        if ($this->integrations === null) {
            $this->integrations = [];

            // Register built-in integrations
            $this->registerBuiltInIntegrations();

            // Fire event to allow third-party integrations
            $event = new RegisterIntegrationsEvent([
                'integrations' => $this->integrations,
            ]);

            $this->trigger(self::EVENT_REGISTER_INTEGRATIONS, $event);

            $this->integrations = $event->integrations;
        }

        return $this->integrations;
    }

    /**
     * Register built-in integrations
     */
    private function registerBuiltInIntegrations(): void
    {
        // Register Blitz integration if available
        if (Craft::$app->getPlugins()->isPluginInstalled('blitz')
            && Craft::$app->getPlugins()->isPluginEnabled('blitz')) {
            $this->integrations[] = new BlitzIntegration();
        }

        // Register View Count integration if available
        if (Craft::$app->getPlugins()->isPluginInstalled('view-count')
            && Craft::$app->getPlugins()->isPluginEnabled('view-count')) {
            $this->integrations[] = new ViewCountIntegration();
        }
    }

    /**
     * Get integrations for a specific item
     *
     * @param array $item Search result item data
     * @return array Array of integration data
     */
    public function getIntegrationsForItem(array $item): array
    {
        $integrationData = [];
        $settings = \brilliance\launcher\Launcher::$plugin->getSettings();
        $enabledIntegrations = $settings->enabledIntegrations ?? [];

        Craft::info('Getting integrations for item: ' . json_encode([
            'id' => $item['id'] ?? 'NO_ID',
            'type' => $item['type'] ?? 'NO_TYPE',
            'title' => $item['title'] ?? 'NO_TITLE',
            'url' => $item['url'] ?? 'NO_URL',
        ]), 'launcher-integrations');

        foreach ($this->getIntegrations() as $integration) {
            try {
                $handle = $integration->getHandle();

                // Check if integration is enabled in settings (default to true if not set)
                $isEnabled = isset($enabledIntegrations[$handle])
                    ? ($enabledIntegrations[$handle] === true || $enabledIntegrations[$handle] === '1' || $enabledIntegrations[$handle] === 1)
                    : true; // Default to enabled if not in settings

                Craft::info("Integration '{$handle}' - enabled: " . ($isEnabled ? 'yes' : 'no'), 'launcher-integrations');

                if ($isEnabled) {
                    $canHandle = $integration->canHandleItem($item);
                    Craft::info("Integration '{$handle}' - canHandle: " . ($canHandle ? 'yes' : 'no'), 'launcher-integrations');

                    if ($canHandle) {
                        $data = $integration->getIntegrationData($item);
                        Craft::info("Integration '{$handle}' - data: " . json_encode($data), 'launcher-integrations');
                        if ($data !== null) {
                            $integrationData[] = $data;
                        }
                    }
                }
            } catch (\Exception $e) {
                Craft::error(
                    'Error getting integration data from ' . $integration->getHandle() . ': ' . $e->getMessage(),
                    __METHOD__
                );
            }
        }

        Craft::info('Total integrations returned: ' . count($integrationData), 'launcher-integrations');

        return $integrationData;
    }

    /**
     * Execute an integration action
     *
     * @param string $integrationHandle Integration handle
     * @param string $action Action to execute
     * @param array $params Action parameters
     * @return array Response from the integration
     */
    public function executeAction(string $integrationHandle, string $action, array $params): array
    {
        // Find the integration
        $integration = $this->getIntegrationByHandle($integrationHandle);

        if (!$integration) {
            return [
                'success' => false,
                'message' => 'Integration not found: ' . $integrationHandle,
            ];
        }

        try {
            return $integration->executeAction($action, $params);
        } catch (\Exception $e) {
            Craft::error(
                'Error executing integration action: ' . $e->getMessage(),
                __METHOD__
            );

            return [
                'success' => false,
                'message' => 'Error executing action: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get an integration by its handle
     *
     * @param string $handle
     * @return LauncherIntegrationInterface|null
     */
    private function getIntegrationByHandle(string $handle): ?LauncherIntegrationInterface
    {
        foreach ($this->getIntegrations() as $integration) {
            if ($integration->getHandle() === $handle) {
                return $integration;
            }
        }

        return null;
    }

    /**
     * Register a custom integration
     *
     * @param LauncherIntegrationInterface $integration
     */
    public function registerIntegration(LauncherIntegrationInterface $integration): void
    {
        if ($this->integrations === null) {
            $this->integrations = [];
        }

        $this->integrations[] = $integration;
    }
}

/**
 * Register Integrations Event
 */
class RegisterIntegrationsEvent extends Event
{
    /**
     * @var LauncherIntegrationInterface[] Registered integrations
     */
    public array $integrations = [];
}
