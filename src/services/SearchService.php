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
    /**
     * Check if Craft Commerce is installed
     */
    private function isCommerceInstalled(): bool
    {
        return Craft::$app->getPlugins()->isPluginInstalled('commerce');
    }
    
    public function browseContentType(string $contentType): array
    {
        $settings = Launcher::$plugin->getSettings();

        switch ($contentType) {
            case 'entries':
                if ($settings->searchableTypes['entries'] ?? false) {
                    return ['entries' => $this->getAllEntries($settings)];
                }
                break;
            case 'categories':
                if ($settings->searchableTypes['categories'] ?? false) {
                    return ['categories' => $this->getAllCategories($settings)];
                }
                break;
            case 'assets':
                if ($settings->searchableTypes['assets'] ?? false) {
                    return ['assets' => $this->getAllAssets($settings)];
                }
                break;
            case 'users':
                if ($settings->searchableTypes['users'] ?? false) {
                    return ['users' => $this->getAllUsers($settings)];
                }
                break;
            case 'globals':
                if ($settings->searchableTypes['globals'] ?? false) {
                    return ['globals' => $this->getAllGlobals($settings)];
                }
                break;
            case 'sections':
                if ($settings->searchableTypes['sections'] ?? false) {
                    return ['sections' => $this->searchSections('')];
                }
                break;
            case 'entryTypes':
                if ($settings->searchableTypes['entryTypes'] ?? false) {
                    return ['entryTypes' => $this->searchEntryTypes('')];
                }
                break;
            case 'groups':
                if ($settings->searchableTypes['categoryGroups'] ?? false) {
                    return ['groups' => $this->searchCategoryGroups('')];
                }
                break;
            case 'volumes':
                if ($settings->searchableTypes['assetVolumes'] ?? false) {
                    return ['volumes' => $this->searchAssetVolumes('')];
                }
                break;
            case 'fields':
                if ($settings->searchableTypes['fields'] ?? false) {
                    return ['fields' => $this->searchFields('')];
                }
                break;
            case 'plugins':
                if ($settings->searchableTypes['plugins'] ?? false) {
                    return ['plugins' => $this->searchPlugins('')];
                }
                break;
        }

        return [];
    }

    public function search(string $query, array $context = []): array
    {

        if (empty($query)) {
            return [];
        }

        $settings = Launcher::$plugin->getSettings();
        $results = [];

        // Add front-end context actions first (if applicable)
        if (!empty($context)) {
            $contextResults = $this->getContextAwareResults($query, $context);
            if (!empty($contextResults)) {
                $results['context'] = $contextResults;
            }
        }

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

        if ($settings->searchableTypes['entryTypes'] ?? false) {
            $results['entryTypes'] = $this->searchEntryTypes($query);
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

        // Search static settings destinations
        $results['settings'] = $this->searchStaticSettings($query);

        // Commerce searches (if Commerce is installed)
        if ($this->isCommerceInstalled()) {
            if ($settings->searchCommerceCustomers ?? false) {
                $results['commerceCustomers'] = $this->searchCommerceCustomers($query, $settings);
            }

            if ($settings->searchCommerceProducts ?? false) {
                $results['commerceProducts'] = $this->searchCommerceProducts($query, $settings);
            }

            if ($settings->searchCommerceOrders ?? false) {
                $results['commerceOrders'] = $this->searchCommerceOrders($query, $settings);
            }
        }

        return array_filter($results);
    }

    /**
     * Get context-aware results for front-end launcher
     */
    private function getContextAwareResults(string $query, array $context): array
    {
        $results = [];
        $queryLower = strtolower($query);

        // Check if we have current entry context
        if (isset($context['currentEntry'])) {
            $entry = $context['currentEntry'];

            // Add "Edit this page" action for relevant search terms
            $editTerms = ['edit', 'edit this', 'edit page', 'edit this page', 'modify', 'change', 'update'];
            foreach ($editTerms as $term) {
                if (stripos($term, $queryLower) === 0 || stripos($queryLower, $term) === 0) {
                    $results[] = [
                        'title' => 'Edit this page',
                        'url' => $entry['editUrl'],
                        'type' => 'Context Action',
                        'entry' => $entry['title'],
                        'section' => $entry['sectionHandle'],
                        'icon' => 'newspaper',
                        'priority' => 100, // High priority to show first
                    ];
                    break;
                }
            }

            // Add "View entry details" action for relevant search terms
            $detailTerms = ['entry', 'details', 'info', 'view', 'current'];
            foreach ($detailTerms as $term) {
                if (stripos($term, $queryLower) === 0 || stripos($queryLower, $term) === 0) {
                    $results[] = [
                        'title' => 'View entry details',
                        'url' => $entry['editUrl'],
                        'type' => 'Context Action',
                        'entry' => $entry['title'],
                        'section' => $entry['sectionHandle'],
                        'icon' => 'newspaper',
                        'priority' => 90,
                    ];
                    break;
                }
            }
        }

        // Sort by priority (highest first)
        usort($results, function($a, $b) {
            return ($b['priority'] ?? 0) - ($a['priority'] ?? 0);
        });

        return $results;
    }

    private function searchEntries(string $query, $settings): array
    {
        // Search entries by content (existing functionality)
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
        $foundEntryIds = [];

        // Add entries found by content search
        foreach ($entries as $entry) {
            $foundEntryIds[] = $entry->id;
            $results[] = [
                'title' => $entry->title,
                'url' => $entry->getCpEditUrl(),
                'type' => 'Entry',
                'section' => $entry->getSection()->name,
                'status' => $entry->getStatus(),
                'icon' => 'newspaper',
            ];
        }

        // Also search for entries by author name (if enabled)
        if ($settings->searchEntriesByAuthor) {
            // First find users that match the query
            $matchingUsers = User::find()
                ->search($query)
                ->limit(5) // Limit user matches to avoid too many results
                ->all();

            if (!empty($matchingUsers)) {
            $userIds = [];
            foreach ($matchingUsers as $user) {
                $userIds[] = $user->id;
            }

            // Search for entries authored by these users
            $authorQuery = Entry::find()
                ->authorId($userIds)
                ->limit($settings->maxResults);

            // Apply the same filters as content search
            if (!$settings->searchDrafts) {
                $authorQuery->drafts(false);
            }

            if (!$settings->searchRevisions) {
                $authorQuery->revisions(false);
            }

            if (!$settings->searchDisabled) {
                $authorQuery->status(Entry::STATUS_ENABLED);
            }

            if (!empty($settings->searchableSections)) {
                $authorQuery->sectionId($settings->searchableSections);
            }

            if (!empty($settings->searchableEntryTypes)) {
                $authorQuery->typeId($settings->searchableEntryTypes);
            }

            $authoredEntries = $authorQuery->all();

            // Add entries by author (avoid duplicates)
            foreach ($authoredEntries as $entry) {
                if (!in_array($entry->id, $foundEntryIds)) {
                    $author = $entry->getAuthor();
                    $results[] = [
                        'title' => $entry->title,
                        'url' => $entry->getCpEditUrl(),
                        'type' => 'Entry',
                        'section' => $entry->getSection()->name,
                        'status' => $entry->getStatus(),
                        'icon' => 'newspaper',
                        'author' => $author ? $author->getFriendlyName() : null,
                    ];
                }
            }
            }
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
                'title' => $user->getFriendlyName() ?: '(No name provided)',
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

    private function searchEntryTypes(string $query): array
    {
        $sections = Craft::$app->getEntries()->getAllSections();
        $results = [];

        foreach ($sections as $section) {
            $entryTypes = $section->getEntryTypes();
            foreach ($entryTypes as $entryType) {
                if (stripos($entryType->name, $query) !== false || stripos($entryType->handle, $query) !== false) {
                    $results[] = [
                        'title' => $entryType->name,
                        'url' => UrlHelper::cpUrl('settings/entry-types/' . $entryType->id),
                        'type' => 'Entry Type',
                        'handle' => $entryType->handle,
                        'section' => $section->name,
                        'icon' => 'newspaper',
                    ];
                }
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

    private function searchAssetVolumes(string $query): array
    {
        // In Craft 5, use getVolumes() instead of getAssets()
        $volumes = Craft::$app->getVolumes()->getAllVolumes();
        $results = [];

        foreach ($volumes as $volume) {
            if (empty($query) || stripos($volume->name, $query) !== false || stripos($volume->handle, $query) !== false) {
                $results[] = [
                    'title' => $volume->name,
                    'url' => UrlHelper::cpUrl('settings/assets/volumes/' . $volume->id),
                    'type' => 'Asset Volume',
                    'handle' => $volume->handle,
                    'icon' => 'photo',
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
                'title' => $user->getFriendlyName() ?: '(No name provided)',
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

    private function searchStaticSettings(string $query): array
    {
        $queryLower = strtolower($query);
        $results = [];

        // Define static settings destinations with their search terms
        $staticDestinations = [
            [
                'title' => 'General',
                'url' => UrlHelper::cpUrl('settings/general'),
                'searchTerms' => ['general', 'settings', 'site', 'system'],
            ],
            [
                'title' => 'Sites',
                'url' => UrlHelper::cpUrl('settings/sites'),
                'searchTerms' => ['sites', 'site', 'multisite', 'locale'],
            ],
            [
                'title' => 'Routes',
                'url' => UrlHelper::cpUrl('settings/routes'),
                'searchTerms' => ['routes', 'routing', 'url', 'dynamic'],
            ],
            [
                'title' => 'Users',
                'url' => UrlHelper::cpUrl('settings/users'),
                'searchTerms' => ['users', 'user', 'accounts', 'permissions'],
            ],
            [
                'title' => 'Addresses',
                'url' => UrlHelper::cpUrl('addresses'),
                'searchTerms' => ['addresses', 'address', 'location', 'billing', 'shipping'],
            ],
            [
                'title' => 'Email',
                'url' => UrlHelper::cpUrl('settings/email'),
                'searchTerms' => ['email', 'mail', 'smtp', 'notifications'],
            ],
            [
                'title' => 'Plugins',
                'url' => UrlHelper::cpUrl('settings/plugins'),
                'searchTerms' => ['plugins', 'plugin', 'extensions', 'addons'],
            ],
            [
                'title' => 'Sections',
                'url' => UrlHelper::cpUrl('settings/sections'),
                'searchTerms' => ['sections', 'section', 'content structure'],
            ],
            [
                'title' => 'Entry Types',
                'url' => UrlHelper::cpUrl('settings/entry-types'),
                'searchTerms' => ['entry types', 'entry', 'types', 'content types'],
            ],
            [
                'title' => 'Fields',
                'url' => UrlHelper::cpUrl('settings/fields'),
                'searchTerms' => ['fields', 'field', 'custom fields'],
            ],
            [
                'title' => 'Globals',
                'url' => UrlHelper::cpUrl('settings/globals'),
                'searchTerms' => ['globals', 'global', 'global sets', 'site wide'],
            ],
            [
                'title' => 'Categories',
                'url' => UrlHelper::cpUrl('settings/categories'),
                'searchTerms' => ['categories', 'category', 'taxonomy', 'groups'],
            ],
            [
                'title' => 'Tags',
                'url' => UrlHelper::cpUrl('settings/tags'),
                'searchTerms' => ['tags', 'tag', 'tagging', 'labels'],
            ],
            [
                'title' => 'Assets',
                'url' => UrlHelper::cpUrl('settings/assets'),
                'searchTerms' => ['assets', 'asset', 'asset settings', 'volumes'],
            ],
            [
                'title' => 'Filesystems',
                'url' => UrlHelper::cpUrl('settings/fs'),
                'searchTerms' => ['filesystems', 'filesystem', 'storage', 'file storage'],
            ],
        ];

        // Search through static destinations
        foreach ($staticDestinations as $destination) {
            $found = false;

            // Check if query matches any search term
            foreach ($destination['searchTerms'] as $term) {
                if (stripos($term, $queryLower) !== false || stripos($queryLower, $term) !== false) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $results[] = [
                    'title' => $destination['title'],
                    'url' => $destination['url'],
                    'type' => 'Settings',
                    'icon' => 'settings',
                ];
            }
        }

        return $results;
    }

    // Commerce search methods
    
    private function searchCommerceCustomers(string $query, $settings): array
    {
        if (!$this->isCommerceInstalled()) {
            return [];
        }
        
        try {
            if (!class_exists('craft\commerce\elements\Customer')) {
                return [];
            }
            
            $customerClass = 'craft\commerce\elements\Customer';
            $customerQuery = $customerClass::find()
                ->search($query)
                ->limit($settings->maxResults);

            $customers = $customerQuery->all();
            $results = [];

            foreach ($customers as $customer) {
                $user = $customer->getUser();
                $title = $user ? $user->getFriendlyName() : ($customer->email ?: '(Name not available)');
                Craft::info("Customer found - Title: '{$title}', Email: '{$customer->email}'", __METHOD__);
                
                $results[] = [
                    'title' => $title,
                    'url' => UrlHelper::cpUrl('commerce/customers/' . $customer->id),
                    'type' => 'Commerce Customer',
                    'email' => $customer->email ?: '(Email not available)',
                    'icon' => 'users',
                ];
            }

            return $results;
        } catch (\Exception $e) {
            Craft::error('Commerce customer search error: ' . $e->getMessage(), __METHOD__);
            return [];
        }
    }

    private function searchCommerceProducts(string $query, $settings): array
    {
        if (!$this->isCommerceInstalled()) {
            return [];
        }
        
        try {
            if (!class_exists('craft\commerce\elements\Product') || !class_exists('craft\commerce\elements\Variant')) {
                return [];
            }
            
            $results = [];
            
            // Search products
            $productClass = 'craft\commerce\elements\Product';
            $productQuery = $productClass::find()
                ->search($query)
                ->limit($settings->maxResults);

            $products = $productQuery->all();
            foreach ($products as $product) {
                $results[] = [
                    'title' => $product->title,
                    'url' => $product->getCpEditUrl(),
                    'type' => 'Commerce Product',
                    'icon' => 'photo',
                ];
            }
            
            // Also search variants
            $variantClass = 'craft\commerce\elements\Variant';
            $variantQuery = $variantClass::find()
                ->search($query)
                ->limit($settings->maxResults);

            $variants = $variantQuery->all();
            foreach ($variants as $variant) {
                $product = $variant->getProduct();
                $results[] = [
                    'title' => $variant->title . ' (' . $product->title . ')',
                    'url' => $product->getCpEditUrl(),
                    'type' => 'Commerce Variant',
                    'product' => $product->title,
                    'icon' => 'photo',
                ];
            }

            return $results;
        } catch (\Exception $e) {
            Craft::error('Commerce product search error: ' . $e->getMessage(), __METHOD__);
            return [];
        }
    }

    private function searchCommerceOrders(string $query, $settings): array
    {
        if (!$this->isCommerceInstalled()) {
            return [];
        }
        
        try {
            // Import the Commerce classes directly
            if (!class_exists('craft\commerce\elements\Order')) {
                return [];
            }
            
            $orders = [];
            
            // Use the Order class directly
            $orderClass = 'craft\commerce\elements\Order';
            
            // First, try a full-text search on all searchable attributes
            $orderQuery = $orderClass::find()
                ->search($query)
                ->isCompleted(true)
                ->limit($settings->maxResults);

            $orders = $orderQuery->all();
            
            // If no results, try searching by partial order number match
            // This handles cases where user types part of the order number
            if (empty($orders)) {
                $orderQuery = $orderClass::find()
                    ->where(['like', 'commerce_orders.number', '%' . $query . '%'])
                    ->isCompleted(true)
                    ->limit($settings->maxResults);
                $orders = $orderQuery->all();
            }
            
            // If still no results and query looks like it might be a short number, try that
            if (empty($orders) && is_numeric($query)) {
                $orderQuery = $orderClass::find()
                    ->shortNumber($query)
                    ->isCompleted(true)
                    ->limit($settings->maxResults);
                $orders = $orderQuery->all();
            }
            $results = [];
            $foundOrderIds = [];

            // Add orders found by search
            foreach ($orders as $order) {
                $foundOrderIds[] = $order->id;
                
                // Get customer name safely
                $customerName = '(No name provided)';
                try {
                    $customer = $order->getCustomer();
                    if ($customer) {
                        $user = $customer->getUser();
                        if ($user) {
                            $customerName = $user->getFriendlyName() ?: '(No name provided)';
                        } elseif ($customer->email) {
                            $customerName = $customer->email;
                        }
                    } elseif ($order->email) {
                        $customerName = $order->email;
                    }
                } catch (\Exception $e) {
                    // Fallback to order email or default
                    $customerName = $order->email ?: '(No name provided)';
                }
                
                $orderStatus = 'Unknown';
                try {
                    $status = $order->getOrderStatus();
                    $orderStatus = $status ? $status->name : 'Unknown';
                } catch (\Exception $e) {
                    // Ignore status errors
                }
                
                $results[] = [
                    'title' => 'Order #' . $order->number,
                    'url' => UrlHelper::cpUrl('commerce/orders/' . $order->id),
                    'type' => 'Commerce Order',
                    'customer' => $customerName,
                    'status' => $orderStatus,
                    'icon' => 'newspaper',
                ];
            }
            
            // Also search by customer name/email
            // First try searching by email directly on orders
            if (filter_var($query, FILTER_VALIDATE_EMAIL) || strpos($query, '@') !== false) {
                $emailOrderQuery = $orderClass::find()
                    ->email($query)
                    ->isCompleted(true)
                    ->limit($settings->maxResults);
                    
                $emailOrders = $emailOrderQuery->all();
                foreach ($emailOrders as $order) {
                    if (!in_array($order->id, $foundOrderIds)) {
                        $foundOrderIds[] = $order->id;
                        
                        // Get customer name safely
                        $customerName = '(No name provided)';
                        try {
                            $customer = $order->getCustomer();
                            if ($customer) {
                                $user = $customer->getUser();
                                if ($user) {
                                    $customerName = $user->getFriendlyName() ?: '(No name provided)';
                                } elseif ($customer->email) {
                                    $customerName = $customer->email;
                                }
                            } elseif ($order->email) {
                                $customerName = $order->email;
                            }
                        } catch (\Exception $e) {
                            // Fallback to order email or default
                            $customerName = $order->email ?: '(No name provided)';
                        }
                        
                        $orderStatus = 'Unknown';
                        try {
                            $status = $order->getOrderStatus();
                            $orderStatus = $status ? $status->name : 'Unknown';
                        } catch (\Exception $e) {
                            // Ignore status errors
                        }
                        
                        $results[] = [
                            'title' => 'Order #' . $order->number,
                            'url' => UrlHelper::cpUrl('commerce/orders/' . $order->id),
                            'type' => 'Commerce Order',
                            'customer' => $customerName,
                            'status' => $orderStatus,
                            'icon' => 'newspaper',
                        ];
                    }
                }
            }
            
            // Search by customer records
            if (class_exists('craft\commerce\elements\Customer')) {
                $customerClass = 'craft\commerce\elements\Customer';
                $customerQuery = $customerClass::find()
                    ->search($query)
                    ->limit(5);
                    
                $customers = $customerQuery->all();
                foreach ($customers as $customer) {
                    $customerOrderQuery = $orderClass::find()
                        ->customer($customer)
                        ->isCompleted(true)
                        ->limit($settings->maxResults);
                    
                $customerOrders = $customerOrderQuery->all();
                foreach ($customerOrders as $order) {
                    if (!in_array($order->id, $foundOrderIds)) {
                        $foundOrderIds[] = $order->id;
                        
                        // Get customer name safely
                        $customerName = '(No name provided)';
                        try {
                            $user = $customer->getUser();
                            if ($user) {
                                $customerName = $user->getFriendlyName() ?: '(No name provided)';
                            } elseif ($customer->email) {
                                $customerName = $customer->email;
                            }
                        } catch (\Exception $e) {
                            $customerName = $customer->email ?: '(No name provided)';
                        }
                        
                        $orderStatus = 'Unknown';
                        try {
                            $status = $order->getOrderStatus();
                            $orderStatus = $status ? $status->name : 'Unknown';
                        } catch (\Exception $e) {
                            // Ignore status errors
                        }
                        
                        $results[] = [
                            'title' => 'Order #' . $order->number,
                            'url' => UrlHelper::cpUrl('commerce/orders/' . $order->id),
                            'type' => 'Commerce Order',
                            'customer' => $customerName,
                            'status' => $orderStatus,
                            'icon' => 'newspaper',
                        ];
                    }
                }
            }
            
            // Also search for orders by matching users directly
            if (class_exists('craft\elements\User')) {
                $userQuery = User::find()
                    ->search($query)
                    ->limit(5);
                    
                $users = $userQuery->all();
                foreach ($users as $user) {
                    // Find customer record for this user
                    if (class_exists('craft\commerce\elements\Customer')) {
                        $customer = $customerClass::find()
                            ->user($user)
                            ->one();
                            
                        if ($customer) {
                            $userOrderQuery = $orderClass::find()
                                ->customer($customer)
                                ->isCompleted(true)
                                ->limit($settings->maxResults);
                            
                            $userOrders = $userOrderQuery->all();
                            foreach ($userOrders as $order) {
                                if (!in_array($order->id, $foundOrderIds)) {
                                    $orderStatus = 'Unknown';
                                    try {
                                        $status = $order->getOrderStatus();
                                        $orderStatus = $status ? $status->name : 'Unknown';
                                    } catch (\Exception $e) {
                                        // Ignore status errors
                                    }
                                    
                                    $results[] = [
                                        'title' => 'Order #' . $order->number,
                                        'url' => UrlHelper::cpUrl('commerce/orders/' . $order->id),
                                        'type' => 'Commerce Order',
                                        'customer' => $user->getFriendlyName() ?: '(No name provided)',
                                        'status' => $orderStatus,
                                        'icon' => 'newspaper',
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

            return $results;
        } catch (\Exception $e) {
            Craft::error('Commerce order search error: ' . $e->getMessage(), __METHOD__);
            return [];
        }
    }
}