<?php

namespace brilliance\launcher\events;

use yii\base\Event;

/**
 * RegisterHotkeysEvent
 *
 * Event triggered to allow addon plugins to register custom keyboard shortcuts
 * that work alongside the main Launcher hotkey.
 */
class RegisterHotkeysEvent extends Event
{
    /**
     * @var array Registered hotkeys
     *
     * Each hotkey should be an array:
     * [
     *     'key' => 'cmd+j',
     *     'handler' => 'LauncherAssistant.open',  // JavaScript function to call
     *     'description' => 'Open Launcher Assistant',
     * ]
     */
    public array $hotkeys = [];

    /**
     * Register a hotkey
     *
     * @param string $key Keyboard shortcut (e.g., 'cmd+j')
     * @param string $handler JavaScript handler function name
     * @param string $description Human-readable description
     */
    public function registerHotkey(string $key, string $handler, string $description = ''): void
    {
        $this->hotkeys[] = [
            'key' => $key,
            'handler' => $handler,
            'description' => $description,
        ];
    }

    /**
     * Get all registered hotkeys
     *
     * @return array
     */
    public function getHotkeys(): array
    {
        return $this->hotkeys;
    }
}
