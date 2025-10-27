<?php

namespace brilliance\launcher\services;

use Craft;
use yii\base\Component;

/**
 * Drawer Service
 *
 * Manages registration of drawer content providers from plugins.
 * Third-party plugins can register their own drawer content (tips, links, resources)
 * that will be displayed in the launcher drawer interface.
 */
class DrawerService extends Component
{
    /**
     * @var array Registered drawer content providers
     */
    private array $providers = [];

    /**
     * Register a drawer content provider
     *
     * @param string $handle Unique handle for the provider (usually plugin handle)
     * @param callable $callback Callback that returns drawer content array
     * @param int $priority Priority for ordering (higher = earlier in drawer)
     * @return void
     */
    public function registerProvider(string $handle, callable $callback, int $priority = 0): void
    {
        $this->providers[$handle] = [
            'callback' => $callback,
            'priority' => $priority,
        ];
    }

    /**
     * Get drawer content for a specific context
     *
     * @param string $context The context (e.g., 'search', 'assistant')
     * @return array Combined drawer content from all providers
     */
    public function getDrawerContent(string $context): array
    {
        // Sort providers by priority
        uasort($this->providers, function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        $content = [
            'title' => 'Tips & Resources',
            'sections' => [],
        ];

        // Collect content from all providers
        foreach ($this->providers as $handle => $provider) {
            try {
                $providerContent = call_user_func($provider['callback'], $context);

                if (is_array($providerContent)) {
                    // Merge sections
                    if (isset($providerContent['sections']) && is_array($providerContent['sections'])) {
                        $content['sections'] = array_merge($content['sections'], $providerContent['sections']);
                    }

                    // Use first provider's title if set
                    if (isset($providerContent['title']) && empty($content['title'])) {
                        $content['title'] = $providerContent['title'];
                    }
                }
            } catch (\Exception $e) {
                Craft::error("Error getting drawer content from provider '{$handle}': {$e->getMessage()}", __METHOD__);
            }
        }

        return $content;
    }

    /**
     * Check if any providers are registered
     *
     * @return bool
     */
    public function hasProviders(): bool
    {
        return !empty($this->providers);
    }

    /**
     * Get all registered provider handles
     *
     * @return array
     */
    public function getProviderHandles(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Remove a registered provider
     *
     * @param string $handle
     * @return void
     */
    public function unregisterProvider(string $handle): void
    {
        unset($this->providers[$handle]);
    }
}
