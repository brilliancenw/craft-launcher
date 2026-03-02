<?php
namespace brilliance\launcher\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;

class UserPreferenceService extends Component
{
    private const PREFERENCE_KEY = 'launcher_frontend_enabled';
    private const NEW_TAB_KEY = 'launcher_frontend_new_tab';
    private const NESTED_ENTRIES_KEY = 'launcher_nested_entries';

    /**
     * Check if the current user has front-end launcher enabled
     */
    public function isFrontEndEnabled(): bool
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return false;
        }

        // Check if user has launcher permissions
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            return false;
        }

        // Get user preference
        $preferences = $user->getPreferences();
        return $preferences[self::PREFERENCE_KEY] ?? false;
    }

    /**
     * Set front-end launcher preference for the current user
     */
    public function setFrontEndEnabled(bool $enabled): bool
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return false;
        }

        // Check if user has launcher permissions
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            return false;
        }

        // Update user preferences using Craft's user service
        $preferences = [self::PREFERENCE_KEY => $enabled];

        try {
            Craft::$app->getUsers()->saveUserPreferences($user, $preferences);
            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to save launcher preference: ' . $e->getMessage(), 'launcher');
            return false;
        }
    }

    /**
     * Check if the current user has front-end links opening in new tab enabled
     */
    public function isFrontEndNewTabEnabled(): bool
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return false;
        }

        // Check if user has launcher permissions
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            return false;
        }

        // Get user preference
        $preferences = $user->getPreferences();
        return $preferences[self::NEW_TAB_KEY] ?? false;
    }

    /**
     * Set front-end new tab preference for the current user
     */
    public function setFrontEndNewTabEnabled(bool $enabled): bool
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return false;
        }

        // Check if user has launcher permissions
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            return false;
        }

        // Update user preferences using Craft's user service
        $preferences = [self::NEW_TAB_KEY => $enabled];

        try {
            Craft::$app->getUsers()->saveUserPreferences($user, $preferences);
            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to save launcher new tab preference: ' . $e->getMessage(), 'launcher');
            return false;
        }
    }

    /**
     * Get the preference key for use in forms
     */
    public function getPreferenceKey(): string
    {
        return self::PREFERENCE_KEY;
    }

    /**
     * Get the new tab preference key for use in forms
     */
    public function getNewTabPreferenceKey(): string
    {
        return self::NEW_TAB_KEY;
    }

    /**
     * Get the nested entries preference key for use in forms
     */
    public function getNestedEntriesPreferenceKey(): string
    {
        return self::NESTED_ENTRIES_KEY;
    }

    /**
     * Get the current user's nested entries preference
     * Returns: 'system' (use global setting), 'show' (always show), 'hide' (always hide)
     */
    public function getNestedEntriesPreference(): string
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return 'system';
        }

        $preferences = $user->getPreferences();
        return $preferences[self::NESTED_ENTRIES_KEY] ?? 'system';
    }

    /**
     * Set nested entries preference for the current user
     */
    public function setNestedEntriesPreference(string $value): bool
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return false;
        }

        // Check if user has launcher permissions
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            return false;
        }

        // Validate the value
        if (!in_array($value, ['system', 'show', 'hide'])) {
            $value = 'system';
        }

        $preferences = [self::NESTED_ENTRIES_KEY => $value];

        try {
            Craft::$app->getUsers()->saveUserPreferences($user, $preferences);
            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to save launcher nested entries preference: ' . $e->getMessage(), 'launcher');
            return false;
        }
    }

    /**
     * Determine if nested entries should be hidden based on global setting and user preference
     */
    public function shouldHideNestedEntries(): bool
    {
        $userPref = $this->getNestedEntriesPreference();

        // User override takes precedence
        if ($userPref === 'show') {
            return false;
        }
        if ($userPref === 'hide') {
            return true;
        }

        // Fall back to global setting
        $plugin = Craft::$app->getPlugins()->getPlugin('launcher');
        if ($plugin) {
            return $plugin->getSettings()->hideNestedEntries;
        }

        return true; // Default to hiding nested entries
    }
}