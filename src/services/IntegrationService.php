<?php
namespace brilliance\launcher\services;

use brilliance\launcher\integrations\BlitzIntegration;
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

        foreach ($this->getIntegrations() as $integration) {
            try {
                if ($integration->canHandleItem($item)) {
                    $data = $integration->getIntegrationData($item);
                    if ($data !== null) {
                        $integrationData[] = $data;
                    }
                }
            } catch (\Exception $e) {
                Craft::error(
                    'Error getting integration data from ' . $integration->getHandle() . ': ' . $e->getMessage(),
                    __METHOD__
                );
            }
        }

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
