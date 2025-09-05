<?php
namespace brilliance\launcher\services;

use brilliance\launcher\Launcher;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\elements\User;
use craft\helpers\UrlHelper;

class SearchService extends Component
{
    public function browseContentType(string $contentType): array
    {
        $settings = Launcher::$plugin->getSettings();
        $results = [];

        switch ($contentType) {
            case 'entries':
                if ($settings->searchableTypes['entries'] ?? false) {
                    $results['entries'] = $this->getAllEntries($settings);
                }
                break;
            case 'categories':
                if ($settings->searchableTypes['categories'] ?? false) {
                    $results['categories'] = $this->getAllCategories($settings);
                }
                break;
            case 'assets':
                if ($settings->searchableTypes['assets'] ?? false) {
                    $results['assets'] = $this->getAllAssets($settings);
                }
                break;
            case 'users':
                if ($settings->searchableTypes['users'] ?? false) {
                    $results['users'] = $this->getAllUsers($settings);
                }
                break;
            case 'globals':
                if ($settings->searchableTypes['globals'] ?? false) {
                    $results['globals'] = $this->getAllGlobals($settings);
                }
                break;
            case 'sections':
                if ($settings->searchableTypes['sections'] ?? false) {
                    $results['sections'] = $this->searchSections('');
                }
                break;
            case 'groups':
                if ($settings->searchableTypes['categoryGroups'] ?? false) {
                    $results['groups'] = $this->searchCategoryGroups('');
                }
                break;
            case 'volumes':
                if ($settings->searchableTypes['assetVolumes'] ?? false) {
                    $results['volumes'] = $this->searchAssetVolumes('');
                }
                break;
            case 'fields':
                if ($settings->searchableTypes['fields'] ?? false) {
                    $results['fields'] = $this->searchFields('');
                }
                break;
            case 'plugins':
                if ($settings->searchableTypes['plugins'] ?? false) {
                    $results['plugins'] = $this->searchPlugins('');
                }
                break;
        }

        return $results;
    }

    public function search(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        $settings = Launcher::$plugin->getSettings();
        $results = [];

        if ($settings->searchableTypes['entries'] ?? false) {
            $results['entries'] = $this->searchEntries($query, $settings);
        }

        if ($settings->searchableTypes['categories'] ?? false) {
            $results['categories'] = $this->searchCategories($query, $settings);
        }

        if ($settings->searchableTypes['assets'] ?? false) {
            $results['assets'] = $this->searchAssets($query, $settings);
        }

        if ($settings->searchableTypes['users'] ?? false) {
            $results['users'] = $this->searchUsers($query, $settings);
        }

        if ($settings->searchableTypes['globals'] ?? false) {
            $results['globals'] = $this->searchGlobals($query, $settings);
        }

        if ($settings->searchableTypes['sections'] ?? false) {
            $results['sections'] = $this->searchSections($query);
        }

        if ($settings->searchableTypes['categoryGroups'] ?? false) {
            $results['categoryGroups'] = $this->searchCategoryGroups($query);
        }

        if ($settings->searchableTypes['fields'] ?? false) {
            $results['fields'] = $this->searchFields($query);
        }

        if ($settings->searchableTypes['plugins'] ?? false) {
            $results['plugins'] = $this->searchPlugins($query);
        }

        return array_filter($results);
    }

    private function searchEntries(string $query, $settings): array
    {
        $entryQuery = Entry::find()
            ->search($query)
            ->limit($settings->maxResults);

        if (!$settings->searchDrafts) {
            $entryQuery->drafts(false);
        }

        if (!$settings->searchRevisions) {
            $entryQuery->revisions(false);
        }

        if (!$settings->searchDisabled) {
            $entryQuery->status(Entry::STATUS_ENABLED);
        }

        if (!empty($settings->searchableSections)) {
            $entryQuery->sectionId($settings->searchableSections);
        }

        if (!empty($settings->searchableEntryTypes)) {
            $entryQuery->typeId($settings->searchableEntryTypes);
        }

        $entries = $entryQuery->all();
        $results = [];

        foreach ($entries as $entry) {
            $results[] = [
                'title' => $entry->title,
                'url' => $entry->getCpEditUrl(),
                'type' => 'Entry',
                'section' => $entry->getSection()->name,
                'status' => $entry->getStatus(),
                'icon' => 'newspaper',
            ];
        }

        return $results;
    }

    private function searchCategories(string $query, $settings): array
    {
        $categoryQuery = Category::find()
            ->search($query)
            ->limit($settings->maxResults);

        if (!$settings->searchDisabled) {
            $categoryQuery->status(Category::STATUS_ENABLED);
        }

        if (!empty($settings->searchableCategoryGroups)) {
            $categoryQuery->groupId($settings->searchableCategoryGroups);
        }

        $categories = $categoryQuery->all();
        $results = [];

        foreach ($categories as $category) {
            $results[] = [
                'title' => $category->title,
                'url' => $category->getCpEditUrl(),
                'type' => 'Category',
                'group' => $category->getGroup()->name,
                'status' => $category->getStatus(),
                'icon' => 'folder',
            ];
        }

        return $results;
    }

    private function searchAssets(string $query, $settings): array
    {
        $assetQuery = Asset::find()
            ->search($query)
            ->limit($settings->maxResults);

        if (!empty($settings->searchableAssetVolumes)) {
            $assetQuery->volumeId($settings->searchableAssetVolumes);
        }

        $assets = $assetQuery->all();
        $results = [];

        foreach ($assets as $asset) {
            $results[] = [
                'title' => $asset->title ?: $asset->filename,
                'url' => $asset->getCpEditUrl(),
                'type' => 'Asset',
                'volume' => $asset->getVolume()->name,
                'filename' => $asset->filename,
                'icon' => 'photo',
            ];
        }

        return $results;
    }

    private function searchUsers(string $query, $settings): array
    {
        $userQuery = User::find()
            ->search($query)
            ->limit($settings->maxResults);

        if (!$settings->searchDisabled) {
            $userQuery->status(User::STATUS_ACTIVE);
        }

        $users = $userQuery->all();
        $results = [];

        foreach ($users as $user) {
            $results[] = [
                'title' => $user->getFriendlyName(),
                'url' => $user->getCpEditUrl(),
                'type' => 'User',
                'email' => $user->email,
                'status' => $user->getStatus(),
                'icon' => 'users',
            ];
        }

        return $results;
    }

    private function searchGlobals(string $query, $settings): array
    {
        $globals = GlobalSet::find()->all();
        $results = [];
        $queryLower = strtolower($query);

        foreach ($globals as $global) {
            if (stripos($global->name, $query) !== false || stripos($global->handle, $query) !== false) {
                $results[] = [
                    'title' => $global->name,
                    'url' => $global->getCpEditUrl(),
                    'type' => 'Global Set',
                    'handle' => $global->handle,
                    'icon' => 'globe',
                ];
            }
        }

        return array_slice($results, 0, $settings->maxResults);
    }

    private function searchSections(string $query): array
    {
        $sections = Craft::$app->getEntries()->getAllSections();
        $results = [];
        $queryLower = strtolower($query);

        foreach ($sections as $section) {
            if (stripos($section->name, $query) !== false || stripos($section->handle, $query) !== false) {
                $results[] = [
                    'title' => $section->name,
                    'url' => UrlHelper::cpUrl('settings/sections/' . $section->id),
                    'type' => 'Section',
                    'handle' => $section->handle,
                    'icon' => 'newspaper',
                ];
            }
        }

        return $results;
    }

    private function searchCategoryGroups(string $query): array
    {
        $groups = Craft::$app->getCategories()->getAllGroups();
        $results = [];

        foreach ($groups as $group) {
            if (stripos($group->name, $query) !== false || stripos($group->handle, $query) !== false) {
                $results[] = [
                    'title' => $group->name,
                    'url' => UrlHelper::cpUrl('settings/categories/' . $group->id),
                    'type' => 'Category Group',
                    'handle' => $group->handle,
                    'icon' => 'folder',
                ];
            }
        }

        return $results;
    }

    private function searchFields(string $query): array
    {
        $fields = Craft::$app->getFields()->getAllFields();
        $results = [];

        foreach ($fields as $field) {
            if (stripos($field->name, $query) !== false || stripos($field->handle, $query) !== false) {
                $results[] = [
                    'title' => $field->name,
                    'url' => UrlHelper::cpUrl('settings/fields/edit/' . $field->id),
                    'type' => 'Field',
                    'handle' => $field->handle,
                    'fieldType' => $field::displayName(),
                    'icon' => 'field',
                ];
            }
        }

        return $results;
    }

    private function searchPlugins(string $query): array
    {
        $plugins = Craft::$app->getPlugins()->getAllPlugins();
        $results = [];

        foreach ($plugins as $plugin) {
            if (stripos($plugin->name, $query) !== false || stripos($plugin->handle, $query) !== false) {
                $results[] = [
                    'title' => $plugin->name,
                    'url' => UrlHelper::cpUrl('settings/plugins/' . $plugin->handle),
                    'type' => 'Plugin',
                    'handle' => $plugin->handle,
                    'version' => $plugin->version,
                    'icon' => 'plug',
                ];
            }
        }

        return $results;
    }

    private function getAllEntries($settings): array
    {
        $entryQuery = Entry::find()->limit($settings->maxResults);

        if (!$settings->searchDrafts) {
            $entryQuery->drafts(false);
        }

        if (!$settings->searchRevisions) {
            $entryQuery->revisions(false);
        }

        if (!$settings->searchDisabled) {
            $entryQuery->status(['live']);
        }

        if (!empty($settings->searchableSections)) {
            $entryQuery->sectionId($settings->searchableSections);
        }

        $entries = $entryQuery->all();
        $results = [];

        foreach ($entries as $entry) {
            $results[] = [
                'title' => $entry->title,
                'url' => $entry->getCpEditUrl(),
                'type' => 'Entry',
                'section' => $entry->getSection()->name,
                'status' => $entry->getStatus(),
                'icon' => 'newspaper',
            ];
        }

        return $results;
    }

    private function getAllCategories($settings): array
    {
        $categoryQuery = Category::find()->limit($settings->maxResults);

        if (!$settings->searchDisabled) {
            $categoryQuery->status(['enabled']);
        }

        if (!empty($settings->searchableCategoryGroups)) {
            $categoryQuery->groupId($settings->searchableCategoryGroups);
        }

        $categories = $categoryQuery->all();
        $results = [];

        foreach ($categories as $category) {
            $results[] = [
                'title' => $category->title,
                'url' => $category->getCpEditUrl(),
                'type' => 'Category',
                'group' => $category->getGroup()->name,
                'status' => $category->getStatus(),
                'icon' => 'folder',
            ];
        }

        return $results;
    }

    private function getAllAssets($settings): array
    {
        $assetQuery = Asset::find()->limit($settings->maxResults);

        if (!empty($settings->searchableAssetVolumes)) {
            $assetQuery->volumeId($settings->searchableAssetVolumes);
        }

        $assets = $assetQuery->all();
        $results = [];

        foreach ($assets as $asset) {
            $results[] = [
                'title' => $asset->title ?: $asset->filename,
                'url' => $asset->getCpEditUrl(),
                'type' => 'Asset',
                'volume' => $asset->getVolume()->name,
                'filename' => $asset->filename,
                'icon' => 'photo',
            ];
        }

        return $results;
    }

    private function getAllUsers($settings): array
    {
        $userQuery = User::find()->limit($settings->maxResults);

        if (!$settings->searchDisabled) {
            $userQuery->status(User::STATUS_ACTIVE);
        }

        $users = $userQuery->all();
        $results = [];

        foreach ($users as $user) {
            $results[] = [
                'title' => $user->getFriendlyName(),
                'url' => $user->getCpEditUrl(),
                'type' => 'User',
                'email' => $user->email,
                'status' => $user->getStatus(),
                'icon' => 'users',
            ];
        }

        return $results;
    }

    private function getAllGlobals($settings): array
    {
        $globals = GlobalSet::find()->limit($settings->maxResults)->all();
        $results = [];

        foreach ($globals as $global) {
            $results[] = [
                'title' => $global->name,
                'url' => $global->getCpEditUrl(),
                'type' => 'Global Set',
                'handle' => $global->handle,
                'icon' => 'globe',
            ];
        }

        return $results;
    }
}