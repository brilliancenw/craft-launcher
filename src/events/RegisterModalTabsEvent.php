<?php

namespace brilliance\launcher\events;

use yii\base\Event;

class RegisterModalTabsEvent extends Event
{
    /**
     * @var array Registered modal tabs
     */
    public array $tabs = [];

    /**
     * Register a modal tab
     *
     * @param string $key Unique tab identifier
     * @param array $tab Tab configuration [
     *   'label' => string,      // Tab button label
     *   'hotkey' => string,     // Keyboard shortcut (e.g., 'cmd+j')
     *   'html' => string,       // Tab content HTML
     *   'priority' => int,      // Display order (lower = left, higher = right)
     * ]
     */
    public function registerTab(string $key, array $tab): void
    {
        $this->tabs[$key] = $tab;
    }

    /**
     * Get all registered tabs sorted by priority
     *
     * @return array
     */
    public function getTabs(): array
    {
        uasort($this->tabs, function($a, $b) {
            $priorityA = $a['priority'] ?? 50;
            $priorityB = $b['priority'] ?? 50;
            return $priorityA <=> $priorityB;
        });

        return $this->tabs;
    }
}
