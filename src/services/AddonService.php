<?php

namespace brilliance\launcher\services;

use brilliance\launcher\events\RegisterAddonPluginsEvent;
use brilliance\launcher\events\RegisterCpNavItemsEvent;
use brilliance\launcher\events\RegisterHotkeysEvent;
use Craft;
use craft\base\Component;
use yii\base\Event;

/**
 * Addon Service
 *
 * Manages integration with Launcher Suite addon plugins.
 * Provides event-based registration for plugins that extend Launcher functionality.
 */
class AddonService extends Component
{
    /**
     * Event triggered when addon plugins should register themselves
     */
    public const EVENT_REGISTER_ADDONS = 'registerAddons';

    /**
     * Event triggered when addon plugins should register CP nav items
     */
    public const EVENT_REGISTER_CP_NAV_ITEMS = 'registerCpNavItems';

    /**
     * Event triggered when addon plugins should register custom hotkeys
     */
    public const EVENT_REGISTER_HOTKEYS = 'registerHotkeys';

    /**
     * @var array Cached registered addons
     */
    private ?array $_addons = null;

    /**
     * @var array Cached CP nav items
     */
    private ?array $_cpNavItems = null;

    /**
     * @var array Cached hotkeys
     */
    private ?array $_hotkeys = null;

    /**
     * Get all registered addon plugins
     *
     * @return array
     */
    public function getRegisteredAddons(): array
    {
        if ($this->_addons !== null) {
            return $this->_addons;
        }

        $event = new RegisterAddonPluginsEvent();
        Event::trigger(static::class, self::EVENT_REGISTER_ADDONS, $event);

        $this->_addons = $event->getAddons();

        Craft::info(
            'Registered ' . count($this->_addons) . ' Launcher addon plugin(s)',
            __METHOD__
        );

        return $this->_addons;
    }

    /**
     * Get CP navigation items from all addon plugins
     *
     * @return array
     */
    public function getCpNavItems(): array
    {
        if ($this->_cpNavItems !== null) {
            return $this->_cpNavItems;
        }

        $event = new RegisterCpNavItemsEvent();
        Event::trigger(static::class, self::EVENT_REGISTER_CP_NAV_ITEMS, $event);

        $this->_cpNavItems = $event->getNavItems();

        return $this->_cpNavItems;
    }

    /**
     * Get registered hotkeys from all addon plugins
     *
     * @return array
     */
    public function getRegisteredHotkeys(): array
    {
        if ($this->_hotkeys !== null) {
            return $this->_hotkeys;
        }

        $event = new RegisterHotkeysEvent();
        Event::trigger(static::class, self::EVENT_REGISTER_HOTKEYS, $event);

        $this->_hotkeys = $event->getHotkeys();

        return $this->_hotkeys;
    }

    /**
     * Check if a specific addon is registered
     *
     * @param string $handle Addon plugin handle
     * @return bool
     */
    public function isAddonRegistered(string $handle): bool
    {
        $addons = $this->getRegisteredAddons();

        foreach ($addons as $addon) {
            if (($addon['handle'] ?? '') === $handle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a specific addon by handle
     *
     * @param string $handle Addon plugin handle
     * @return array|null
     */
    public function getAddon(string $handle): ?array
    {
        $addons = $this->getRegisteredAddons();

        foreach ($addons as $addon) {
            if (($addon['handle'] ?? '') === $handle) {
                return $addon;
            }
        }

        return null;
    }

    /**
     * Clear cached addon data (useful for testing)
     */
    public function clearCache(): void
    {
        $this->_addons = null;
        $this->_cpNavItems = null;
        $this->_hotkeys = null;
    }
}
