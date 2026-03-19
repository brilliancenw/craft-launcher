<?php

namespace brilliance\launcher\events;

use yii\base\Event;

/**
 * RegisterCpNavItemsEvent
 *
 * Event triggered to allow addon plugins to register their CP navigation items
 * under the Launcher section in the control panel.
 */
class RegisterCpNavItemsEvent extends Event
{
    /**
     * @var array CP navigation items
     *
     * Each item should follow Craft's CP nav item structure:
     * [
     *     'label' => 'API Configuration',
     *     'url' => 'launcher/api-config',
     *     'icon' => 'gear',  // optional
     * ]
     */
    public array $navItems = [];

    /**
     * Register a navigation item
     *
     * @param string $key Unique key for the nav item
     * @param array $item Nav item configuration
     */
    public function registerNavItem(string $key, array $item): void
    {
        $this->navItems[$key] = $item;
    }

    /**
     * Get all registered nav items
     *
     * @return array
     */
    public function getNavItems(): array
    {
        return $this->navItems;
    }
}
