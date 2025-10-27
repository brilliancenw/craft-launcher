<?php

namespace brilliance\launcher\events;

use yii\base\Event;

/**
 * RegisterAddonPluginsEvent
 *
 * Event triggered to allow other plugins to register as Launcher Suite addons.
 * This enables plugins like Launcher Assistant, Launcher Builder, etc. to
 * integrate seamlessly with the Launcher UI and navigation.
 */
class RegisterAddonPluginsEvent extends Event
{
    /**
     * @var array List of registered addon plugins
     *
     * Each addon should be an array with the following structure:
     * [
     *     'handle' => 'launcher-assistant',     // Plugin handle
     *     'name' => 'Assistant',                // Display name
     *     'hotkey' => 'cmd+j',                  // Keyboard shortcut (optional)
     *     'icon' => '<svg>...</svg>',           // Icon SVG (optional)
     *     'cpNavItems' => [],                   // CP navigation items to add
     *     'assetBundle' => AssetBundle::class,  // Asset bundle class (optional)
     *     'priority' => 10,                     // Sort priority (optional, default: 10)
     * ]
     */
    public array $addons = [];

    /**
     * Register an addon plugin
     *
     * @param array $addon Addon configuration
     */
    public function registerAddon(array $addon): void
    {
        $this->addons[] = $addon;
    }

    /**
     * Get all registered addons sorted by priority
     *
     * @return array
     */
    public function getAddons(): array
    {
        usort($this->addons, function ($a, $b) {
            $priorityA = $a['priority'] ?? 10;
            $priorityB = $b['priority'] ?? 10;
            return $priorityA <=> $priorityB;
        });

        return $this->addons;
    }
}
