<?php
namespace brilliance\launcher\variables;

use brilliance\launcher\Launcher;

class LauncherVariable
{
    /**
     * Get the history service
     */
    public function getHistory()
    {
        return Launcher::$plugin->history;
    }

    /**
     * Get the search service
     */
    public function getSearch()
    {
        return Launcher::$plugin->search;
    }

    /**
     * Get the launcher service
     */
    public function getLauncher()
    {
        return Launcher::$plugin->launcher;
    }

    /**
     * Get the user preference service
     */
    public function getUserPreference()
    {
        return Launcher::$plugin->userPreference;
    }
}
