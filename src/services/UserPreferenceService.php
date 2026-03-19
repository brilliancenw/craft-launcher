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

    // Search filter preference keys
    private const FILTER_INCLUDE_DRAFTS = 'launcher_filter_drafts';
    private const FILTER_INCLUDE_DISABLED = 'launcher_filter_disabled';
    private const FILTER_SECTIONS = 'launcher_filter_sections';
    private const FILTER_ENTRY_TYPES = 'launcher_filter_entry_types';
    private const FILTER_INCLUDE_NESTED = 'launcher_filter_include_nested';

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
            return $plugin->getSettings()->includeNestedEntries;
        }

        return true; // Default to hiding nested entries
    }

    /**
     * Get all search filters as array
     */
    public function getSearchFilters(): array
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return $this->getDefaultSearchFilters();
        }

        $preferences = $user->getPreferences();

        return [
            'includeDrafts' => $preferences[self::FILTER_INCLUDE_DRAFTS] ?? false,
            'includeDisabled' => $preferences[self::FILTER_INCLUDE_DISABLED] ?? false,
            'includeNested' => $preferences[self::FILTER_INCLUDE_NESTED] ?? false,
            'sections' => $preferences[self::FILTER_SECTIONS] ?? [],
            'entryTypes' => $preferences[self::FILTER_ENTRY_TYPES] ?? [],
        ];
    }

    /**
     * Get default search filter values
     */
    public function getDefaultSearchFilters(): array
    {
        return [
            'includeDrafts' => false,
            'includeDisabled' => false,
            'includeNested' => false,
            'sections' => [],
            'entryTypes' => [],
        ];
    }

    /**
     * Set search filters from array
     */
    public function setSearchFilters(array $filters): bool
    {
        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return false;
        }

        // Check if user has launcher permissions
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            return false;
        }

        $preferences = [];

        // Validate and set each filter
        if (isset($filters['includeDrafts'])) {
            $preferences[self::FILTER_INCLUDE_DRAFTS] = (bool) $filters['includeDrafts'];
        }

        if (isset($filters['includeDisabled'])) {
            $preferences[self::FILTER_INCLUDE_DISABLED] = (bool) $filters['includeDisabled'];
        }

        if (isset($filters['includeNested'])) {
            $preferences[self::FILTER_INCLUDE_NESTED] = (bool) $filters['includeNested'];
        }

        if (isset($filters['sections'])) {
            // Ensure sections is an array of integers
            $preferences[self::FILTER_SECTIONS] = array_map('intval', (array) $filters['sections']);
        }

        if (isset($filters['entryTypes'])) {
            // Ensure entry types is an array of integers
            $preferences[self::FILTER_ENTRY_TYPES] = array_map('intval', (array) $filters['entryTypes']);
        }

        try {
            Craft::$app->getUsers()->saveUserPreferences($user, $preferences);
            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to save search filter preferences: ' . $e->getMessage(), 'launcher');
            return false;
        }
    }

    /**
     * Get effective filters respecting admin settings
     * Returns only filters that are allowed by admin settings
     */
    public function getEffectiveFilters(): array
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('launcher');
        if (!$plugin) {
            return $this->getDefaultSearchFilters();
        }

        $settings = $plugin->getSettings();
        $userFilters = $this->getSearchFilters();
        $effectiveFilters = [];

        // Apply admin restrictions
        $effectiveFilters['includeDrafts'] = $settings->allowUserFilterDrafts
            ? $userFilters['includeDrafts']
            : $settings->searchDrafts;

        $effectiveFilters['includeDisabled'] = $settings->allowUserFilterDisabled
            ? $userFilters['includeDisabled']
            : $settings->searchDisabled;

        // Note: Admin setting is hideNestedEntries, so we invert it for includeNested
        $effectiveFilters['includeNested'] = $settings->allowUserFilterNestedEntries
            ? $userFilters['includeNested']
            : !$settings->hideNestedEntries;

        $effectiveFilters['sections'] = $settings->allowUserFilterSections
            ? $userFilters['sections']
            : [];

        $effectiveFilters['entryTypes'] = $settings->allowUserFilterEntryTypes
            ? $userFilters['entryTypes']
            : [];

        return $effectiveFilters;
    }

    /**
     * Get available filter options for the filter panel
     * Returns which filters the admin allows users to customize
     */
    public function getAvailableFilterOptions(): array
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('launcher');
        if (!$plugin) {
            return [];
        }

        $settings = $plugin->getSettings();

        return [
            'allowDrafts' => $settings->allowUserFilterDrafts,
            'allowDisabled' => $settings->allowUserFilterDisabled,
            'allowSections' => $settings->allowUserFilterSections,
            'allowEntryTypes' => $settings->allowUserFilterEntryTypes,
            'allowNestedEntries' => $settings->allowUserFilterNestedEntries,
        ];
    }
}