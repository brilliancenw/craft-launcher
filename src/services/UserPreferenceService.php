<?php
namespace brilliance\launcher\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;

class UserPreferenceService extends Component
{
    private const PREFERENCE_KEY = 'launcher_frontend_enabled';
    private const NEW_TAB_KEY = 'launcher_frontend_new_tab';

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
        if (!Craft::$app->getUser()->checkPermission('accessLauncher')) {
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
        if (!Craft::$app->getUser()->checkPermission('accessLauncher')) {
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
        if (!Craft::$app->getUser()->checkPermission('accessLauncher')) {
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
        if (!Craft::$app->getUser()->checkPermission('accessLauncher')) {
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
}