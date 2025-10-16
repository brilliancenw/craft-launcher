<?php

namespace brilliance\launcher\services;

use brilliance\launcher\Launcher;
use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\Json;

/**
 * AI Tool Service
 *
 * Provides tools/functions that AI agents can call to interact with Craft CMS.
 * Leverages existing SearchService and Craft APIs.
 */
class AIToolService extends Component
{
    /**
     * Get tool definitions for AI providers
     * Returns array of tool definitions in a provider-agnostic format
     */
    public function getToolDefinitions(): array
    {
        return [
            [
                'name' => 'listSections',
                'description' => 'Get a list of all available content sections/channels in Craft CMS. Returns basic info (name, handle, type) without field details.',
                'parameters' => [],
            ],
            [
                'name' => 'getSectionDetails',
                'description' => 'Get detailed information about a specific section including all fields, requirements, and configuration. Use this when you need to know what fields are available for creating content.',
                'parameters' => [
                    'handle' => [
                        'type' => 'string',
                        'description' => 'The section handle (e.g., "blog", "news", "products")',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'searchEntries',
                'description' => 'Search for existing entries across all sections or within a specific section. Useful for finding similar content or checking if something already exists.',
                'parameters' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search query',
                        'required' => true,
                    ],
                    'section' => [
                        'type' => 'string',
                        'description' => 'Optional section handle to limit search',
                        'required' => false,
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of results (default: 10)',
                        'required' => false,
                    ],
                ],
            ],
            [
                'name' => 'getFieldDetails',
                'description' => 'Get detailed information about a specific field including type, settings, and validation rules.',
                'parameters' => [
                    'handle' => [
                        'type' => 'string',
                        'description' => 'The field handle',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'createDraftEntry',
                'description' => 'Create a new draft entry with the provided content. The draft will need to be reviewed and published by the user.',
                'parameters' => [
                    'sectionHandle' => [
                        'type' => 'string',
                        'description' => 'Section handle where entry should be created',
                        'required' => true,
                    ],
                    'title' => [
                        'type' => 'string',
                        'description' => 'Entry title',
                        'required' => true,
                    ],
                    'fields' => [
                        'type' => 'object',
                        'description' => 'Field values as key-value pairs (fieldHandle => value)',
                        'required' => false,
                    ],
                ],
            ],
            [
                'name' => 'listFields',
                'description' => 'Get a list of all available fields in Craft CMS with basic information.',
                'parameters' => [],
            ],
            [
                'name' => 'listCategoryGroups',
                'description' => 'Get a list of all category groups (used for organizing content with tags, topics, etc.).',
                'parameters' => [],
            ],
            [
                'name' => 'getCategoryGroupDetails',
                'description' => 'Get detailed information about a specific category group including fields and available categories.',
                'parameters' => [
                    'handle' => [
                        'type' => 'string',
                        'description' => 'The category group handle (e.g., "topics", "tags", "blogCategories")',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'searchCategories',
                'description' => 'Search for categories/tags to use when creating content.',
                'parameters' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search query',
                        'required' => true,
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of results (default: 10)',
                        'required' => false,
                    ],
                ],
            ],
            [
                'name' => 'listAssetVolumes',
                'description' => 'Get a list of all asset volumes (media libraries for images, documents, videos, etc.).',
                'parameters' => [],
            ],
            [
                'name' => 'searchAssets',
                'description' => 'Search for assets/media files (images, documents, videos, etc.).',
                'parameters' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search query',
                        'required' => true,
                    ],
                    'volume' => [
                        'type' => 'string',
                        'description' => 'Optional volume handle to limit search',
                        'required' => false,
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of results (default: 10)',
                        'required' => false,
                    ],
                ],
            ],
            [
                'name' => 'listGlobals',
                'description' => 'Get a list of all global sets (site-wide content like contact info, social media, footer content, etc.).',
                'parameters' => [],
            ],
            [
                'name' => 'getGlobalDetails',
                'description' => 'Get detailed information about a specific global set including all fields and current values.',
                'parameters' => [
                    'handle' => [
                        'type' => 'string',
                        'description' => 'The global set handle (e.g., "siteInfo", "footer", "contactInfo")',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'searchGlobals',
                'description' => 'Search within global set content.',
                'parameters' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search query',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'listUtilities',
                'description' => 'Get a list of all available Craft CMS utilities (admin tools like cache clearing, backups, etc.).',
                'parameters' => [],
            ],
            [
                'name' => 'clearCaches',
                'description' => 'Clear all Craft CMS caches. Use this when the user asks to clear/flush/refresh caches.',
                'parameters' => [],
            ],
            [
                'name' => 'rebuildAssetIndexes',
                'description' => 'Rebuild asset indexes to sync the database with files on disk. Use when assets are missing or out of sync.',
                'parameters' => [],
            ],
            [
                'name' => 'getQueueStatus',
                'description' => 'Get the status of background queue jobs (pending, running, failed jobs).',
                'parameters' => [],
            ],
            [
                'name' => 'runQueueJobs',
                'description' => 'Process pending queue jobs in the background. Use when jobs are stuck or need to be processed.',
                'parameters' => [
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of jobs to process (default: 10)',
                        'required' => false,
                    ],
                ],
            ],
            [
                'name' => 'getSystemInfo',
                'description' => 'Get system information and diagnostics (PHP version, database, Craft version, environment, etc.).',
                'parameters' => [],
            ],
            [
                'name' => 'getCommerceStatus',
                'description' => 'Check if Craft Commerce is installed and get available product types.',
                'parameters' => [],
            ],
        ];
    }

    /**
     * Execute a tool/function call
     */
    public function executeTool(string $toolName, array $parameters): array
    {
        try {
            $result = match ($toolName) {
                'listSections' => $this->listSections(),
                'getSectionDetails' => $this->getSectionDetails($parameters['handle'] ?? ''),
                'searchEntries' => $this->searchEntries(
                    $parameters['query'] ?? '',
                    $parameters['section'] ?? null,
                    $parameters['limit'] ?? 10
                ),
                'getFieldDetails' => $this->getFieldDetails($parameters['handle'] ?? ''),
                'createDraftEntry' => $this->createDraftEntry(
                    $parameters['sectionHandle'] ?? '',
                    $parameters['title'] ?? '',
                    $parameters['fields'] ?? []
                ),
                'listFields' => $this->listFields(),
                'listCategoryGroups' => $this->listCategoryGroups(),
                'getCategoryGroupDetails' => $this->getCategoryGroupDetails($parameters['handle'] ?? ''),
                'searchCategories' => $this->searchCategories(
                    $parameters['query'] ?? '',
                    $parameters['limit'] ?? 10
                ),
                'listAssetVolumes' => $this->listAssetVolumes(),
                'searchAssets' => $this->searchAssets(
                    $parameters['query'] ?? '',
                    $parameters['volume'] ?? null,
                    $parameters['limit'] ?? 10
                ),
                'listGlobals' => $this->listGlobals(),
                'getGlobalDetails' => $this->getGlobalDetails($parameters['handle'] ?? ''),
                'searchGlobals' => $this->searchGlobals($parameters['query'] ?? ''),
                'listUtilities' => $this->listUtilities(),
                'clearCaches' => $this->clearCaches(),
                'rebuildAssetIndexes' => $this->rebuildAssetIndexes(),
                'getQueueStatus' => $this->getQueueStatus(),
                'runQueueJobs' => $this->runQueueJobs($parameters['limit'] ?? 10),
                'getSystemInfo' => $this->getSystemInfo(),
                'getCommerceStatus' => $this->getCommerceStatus(),
                default => ['error' => "Unknown tool: {$toolName}"],
            };

            // Ensure result is JSON-serializable
            // Convert any objects to arrays recursively
            return $this->ensureJsonSerializable($result);

        } catch (\Exception $e) {
            Craft::error("AI Tool execution error [{$toolName}]: " . $e->getMessage(), __METHOD__);
            return [
                'error' => $e->getMessage(),
                'tool' => $toolName,
            ];
        }
    }

    /**
     * Ensure data is JSON-serializable by converting objects to arrays
     */
    private function ensureJsonSerializable($data)
    {
        if (is_object($data)) {
            // Convert object to array
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->ensureJsonSerializable($value);
            }
        }

        return $data;
    }

    /**
     * List all sections
     * Leverages existing SearchService browse functionality
     */
    private function listSections(): array
    {
        $searchService = Launcher::$plugin->search;
        $browseResults = $searchService->browseContentType('sections');

        $sections = $browseResults['sections'] ?? [];
        $result = [];

        foreach ($sections as $section) {
            $result[] = [
                'name' => $section['title'],
                'handle' => $section['handle'],
                'type' => $section['type'] ?? 'section',
                'url' => $section['url'],
            ];
        }

        return [
            'sections' => $result,
            'totalCount' => count($result),
        ];
    }

    /**
     * Get detailed information about a section
     */
    private function getSectionDetails(string $handle): array
    {
        if (empty($handle)) {
            return ['error' => 'Section handle is required'];
        }

        $section = Craft::$app->getEntries()->getSectionByHandle($handle);
        if (!$section) {
            return ['error' => "Section not found: {$handle}"];
        }

        $entryTypes = $section->getEntryTypes();
        $entryTypeDetails = [];

        foreach ($entryTypes as $entryType) {
            $fieldLayout = $entryType->getFieldLayout();
            $fields = [];

            if ($fieldLayout) {
                foreach ($fieldLayout->getCustomFields() as $field) {
                    $fields[] = [
                        'name' => $field->name,
                        'handle' => $field->handle,
                        'type' => get_class($field),
                        'required' => $field->required ?? false,
                        'instructions' => $field->instructions ?? '',
                    ];
                }
            }

            $entryTypeDetails[] = [
                'name' => $entryType->name,
                'handle' => $entryType->handle,
                'hasTitleField' => $entryType->hasTitleField,
                'titleFormat' => $entryType->titleFormat ?? null,
                'fields' => $fields,
            ];
        }

        return [
            'section' => [
                'name' => $section->name,
                'handle' => $section->handle,
                'type' => $section->type,
                'enableVersioning' => $section->enableVersioning,
            ],
            'entryTypes' => $entryTypeDetails,
        ];
    }

    /**
     * Search entries using existing SearchService
     */
    private function searchEntries(string $query, ?string $section, int $limit = 10): array
    {
        if (empty($query)) {
            return ['error' => 'Search query is required'];
        }

        // Leverage existing SearchService
        $searchService = Launcher::$plugin->search;
        $results = $searchService->search($query);

        // Filter and format results
        $entries = [];
        $count = 0;

        if (isset($results['entries'])) {
            foreach ($results['entries'] as $entry) {
                if ($count >= $limit) {
                    break;
                }

                // Filter by section if specified
                if ($section && isset($entry['sectionHandle']) && $entry['sectionHandle'] !== $section) {
                    continue;
                }

                $entries[] = [
                    'id' => $entry['id'] ?? null,
                    'title' => $entry['title'] ?? '',
                    'url' => $entry['url'] ?? '',
                    'section' => $entry['section'] ?? '',
                    'sectionHandle' => $entry['sectionHandle'] ?? '',
                    'status' => $entry['status'] ?? '',
                ];

                $count++;
            }
        }

        return [
            'entries' => $entries,
            'count' => count($entries),
            'query' => $query,
        ];
    }

    /**
     * Get field details
     */
    private function getFieldDetails(string $handle): array
    {
        if (empty($handle)) {
            return ['error' => 'Field handle is required'];
        }

        $field = Craft::$app->fields->getFieldByHandle($handle);
        if (!$field) {
            return ['error' => "Field not found: {$handle}"];
        }

        return [
            'name' => $field->name,
            'handle' => $field->handle,
            'type' => get_class($field),
            'instructions' => $field->instructions ?? '',
            'required' => $field->required ?? false,
            'settings' => $field->getSettings(),
        ];
    }

    /**
     * List all fields
     */
    private function listFields(): array
    {
        $fields = Craft::$app->fields->getAllFields();
        $result = [];

        foreach ($fields as $field) {
            $result[] = [
                'name' => $field->name,
                'handle' => $field->handle,
                'type' => get_class($field),
            ];
        }

        return [
            'fields' => $result,
            'totalCount' => count($result),
        ];
    }

    /**
     * Create a draft entry
     */
    private function createDraftEntry(string $sectionHandle, string $title, array $fields = []): array
    {
        if (empty($sectionHandle)) {
            return ['error' => 'Section handle is required'];
        }

        if (empty($title)) {
            return ['error' => 'Title is required'];
        }

        $section = Craft::$app->getEntries()->getSectionByHandle($sectionHandle);
        if (!$section) {
            return ['error' => "Section not found: {$sectionHandle}"];
        }

        $entryTypes = $section->getEntryTypes();
        if (empty($entryTypes)) {
            return ['error' => "No entry types found for section: {$sectionHandle}"];
        }

        // Use the first entry type
        $entryType = $entryTypes[0];

        // Create the entry
        $entry = new Entry();
        $entry->sectionId = $section->id;
        $entry->typeId = $entryType->id;
        $entry->title = $title;
        $entry->setFieldValues($fields);

        // Save as draft
        $entry->setScenario(Entry::SCENARIO_ESSENTIALS);

        if (!Craft::$app->drafts->saveElementAsDraft($entry, Craft::$app->user->id, null, null, false)) {
            return [
                'error' => 'Failed to create draft',
                'errors' => $entry->getErrors(),
            ];
        }

        return [
            'success' => true,
            'draftId' => $entry->draftId,
            'entryId' => $entry->id,
            'title' => $entry->title,
            'cpEditUrl' => $entry->getCpEditUrl(),
            'message' => 'Draft created successfully. You can review and publish it in the Craft control panel.',
        ];
    }

    /**
     * Get Commerce status
     */
    private function getCommerceStatus(): array
    {
        $isInstalled = Craft::$app->plugins->isPluginInstalled('commerce');

        if (!$isInstalled) {
            return [
                'installed' => false,
                'message' => 'Craft Commerce is not installed',
            ];
        }

        $productTypes = [];

        if (class_exists('craft\commerce\Plugin')) {
            $commerce = \craft\commerce\Plugin::getInstance();
            foreach ($commerce->getProductTypes()->getAllProductTypes() as $productType) {
                $productTypes[] = [
                    'name' => $productType->name,
                    'handle' => $productType->handle,
                ];
            }
        }

        return [
            'installed' => true,
            'productTypes' => $productTypes,
        ];
    }

    /**
     * List all category groups
     */
    private function listCategoryGroups(): array
    {
        $groups = Craft::$app->categories->getAllGroups();
        $result = [];

        foreach ($groups as $group) {
            $result[] = [
                'name' => $group->name,
                'handle' => $group->handle,
                'maxLevels' => $group->maxLevels,
            ];
        }

        return [
            'categoryGroups' => $result,
            'totalCount' => count($result),
        ];
    }

    /**
     * Get category group details
     */
    private function getCategoryGroupDetails(string $handle): array
    {
        if (empty($handle)) {
            return ['error' => 'Category group handle is required'];
        }

        $group = Craft::$app->categories->getGroupByHandle($handle);
        if (!$group) {
            return ['error' => "Category group not found: {$handle}"];
        }

        $fieldLayout = $group->getFieldLayout();
        $fields = [];

        if ($fieldLayout) {
            foreach ($fieldLayout->getCustomFields() as $field) {
                $fields[] = [
                    'name' => $field->name,
                    'handle' => $field->handle,
                    'type' => get_class($field),
                    'required' => $field->required ?? false,
                ];
            }
        }

        // Get some example categories
        $categories = \craft\elements\Category::find()
            ->groupId($group->id)
            ->limit(10)
            ->all();

        $exampleCategories = [];
        foreach ($categories as $category) {
            $exampleCategories[] = [
                'id' => $category->id,
                'title' => $category->title,
                'level' => $category->level,
            ];
        }

        return [
            'group' => [
                'name' => $group->name,
                'handle' => $group->handle,
                'maxLevels' => $group->maxLevels,
            ],
            'fields' => $fields,
            'exampleCategories' => $exampleCategories,
        ];
    }

    /**
     * Search categories
     */
    private function searchCategories(string $query, int $limit = 10): array
    {
        if (empty($query)) {
            return ['error' => 'Search query is required'];
        }

        $searchService = Launcher::$plugin->search;
        $results = $searchService->search($query);

        $categories = [];
        $count = 0;

        if (isset($results['categories'])) {
            foreach ($results['categories'] as $category) {
                if ($count >= $limit) {
                    break;
                }

                $categories[] = [
                    'id' => $category['id'] ?? null,
                    'title' => $category['title'] ?? '',
                    'group' => $category['group'] ?? '',
                    'groupHandle' => $category['groupHandle'] ?? '',
                ];

                $count++;
            }
        }

        return [
            'categories' => $categories,
            'count' => count($categories),
            'query' => $query,
        ];
    }

    /**
     * List all asset volumes
     */
    private function listAssetVolumes(): array
    {
        $volumes = Craft::$app->volumes->getAllVolumes();
        $result = [];

        foreach ($volumes as $volume) {
            $result[] = [
                'name' => $volume->name,
                'handle' => $volume->handle,
                'hasUrls' => $volume->hasUrls,
            ];
        }

        return [
            'volumes' => $result,
            'totalCount' => count($result),
        ];
    }

    /**
     * Search assets
     */
    private function searchAssets(string $query, ?string $volume, int $limit = 10): array
    {
        if (empty($query)) {
            return ['error' => 'Search query is required'];
        }

        $searchService = Launcher::$plugin->search;
        $results = $searchService->search($query);

        $assets = [];
        $count = 0;

        if (isset($results['assets'])) {
            foreach ($results['assets'] as $asset) {
                if ($count >= $limit) {
                    break;
                }

                // Filter by volume if specified
                if ($volume && isset($asset['volumeHandle']) && $asset['volumeHandle'] !== $volume) {
                    continue;
                }

                $assets[] = [
                    'id' => $asset['id'] ?? null,
                    'title' => $asset['title'] ?? '',
                    'filename' => $asset['filename'] ?? '',
                    'url' => $asset['url'] ?? '',
                    'volume' => $asset['volume'] ?? '',
                    'volumeHandle' => $asset['volumeHandle'] ?? '',
                    'kind' => $asset['kind'] ?? '',
                ];

                $count++;
            }
        }

        return [
            'assets' => $assets,
            'count' => count($assets),
            'query' => $query,
        ];
    }

    /**
     * List all global sets
     */
    private function listGlobals(): array
    {
        $globals = Craft::$app->globals->getAllSets();
        $result = [];

        foreach ($globals as $global) {
            $result[] = [
                'name' => $global->name,
                'handle' => $global->handle,
            ];
        }

        return [
            'globals' => $result,
            'totalCount' => count($result),
        ];
    }

    /**
     * Get global set details
     */
    private function getGlobalDetails(string $handle): array
    {
        if (empty($handle)) {
            return ['error' => 'Global set handle is required'];
        }

        $globalSet = Craft::$app->globals->getSetByHandle($handle);
        if (!$globalSet) {
            return ['error' => "Global set not found: {$handle}"];
        }

        $fieldLayout = $globalSet->getFieldLayout();
        $fields = [];
        $currentValues = [];

        if ($fieldLayout) {
            foreach ($fieldLayout->getCustomFields() as $field) {
                $fields[] = [
                    'name' => $field->name,
                    'handle' => $field->handle,
                    'type' => get_class($field),
                    'instructions' => $field->instructions ?? '',
                ];

                // Get current field value
                $value = $globalSet->getFieldValue($field->handle);
                $currentValues[$field->handle] = $this->formatFieldValue($value);
            }
        }

        return [
            'global' => [
                'name' => $globalSet->name,
                'handle' => $globalSet->handle,
            ],
            'fields' => $fields,
            'currentValues' => $currentValues,
        ];
    }

    /**
     * Search globals
     */
    private function searchGlobals(string $query): array
    {
        if (empty($query)) {
            return ['error' => 'Search query is required'];
        }

        $searchService = Launcher::$plugin->search;
        $results = $searchService->search($query);

        $globals = [];

        if (isset($results['globals'])) {
            foreach ($results['globals'] as $global) {
                $globals[] = [
                    'name' => $global['name'] ?? '',
                    'handle' => $global['handle'] ?? '',
                    'url' => $global['url'] ?? '',
                ];
            }
        }

        return [
            'globals' => $globals,
            'count' => count($globals),
            'query' => $query,
        ];
    }

    /**
     * Format field value for display
     */
    private function formatFieldValue(mixed $value): mixed
    {
        // Handle different field types
        if (is_object($value)) {
            if ($value instanceof \craft\elements\db\ElementQuery) {
                return 'Related elements (query)';
            }
            if (method_exists($value, '__toString')) {
                return (string)$value;
            }
            return get_class($value);
        }

        return $value;
    }

    /**
     * List all available utilities
     */
    private function listUtilities(): array
    {
        $searchService = Launcher::$plugin->search;

        // Use the existing getAllUtilities method
        $utilities = $searchService->browseContentType('utilities');

        if (isset($utilities['utilities'])) {
            return [
                'utilities' => $utilities['utilities'],
                'totalCount' => count($utilities['utilities']),
            ];
        }

        return [
            'utilities' => [],
            'totalCount' => 0,
        ];
    }

    /**
     * Clear all Craft caches
     */
    private function clearCaches(): array
    {
        try {
            // Clear Craft's internal caches
            Craft::$app->cache->flush();

            // Clear template caches
            Craft::$app->templateCaches->deleteAllCaches();

            // Clear data caches
            Craft::$app->getCache()->flush();

            // Clear asset transform indexes
            Craft::$app->assetTransforms->deleteAllTransformIndexes();

            // Clear compiled templates
            $compiledTemplatesPath = Craft::$app->path->getCompiledTemplatesPath();
            if (is_dir($compiledTemplatesPath)) {
                $this->deleteDirectory($compiledTemplatesPath);
            }

            Craft::info('Caches cleared via AI assistant', __METHOD__);

            return [
                'success' => true,
                'message' => 'All caches have been cleared successfully.',
                'cachesCleared' => [
                    'data',
                    'compiled templates',
                    'template caches',
                    'asset transforms',
                ],
            ];
        } catch (\Exception $e) {
            Craft::error('Failed to clear caches: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'error' => 'Failed to clear caches: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Rebuild asset indexes
     */
    private function rebuildAssetIndexes(): array
    {
        try {
            // Get all volumes
            $volumes = Craft::$app->volumes->getAllVolumes();

            if (empty($volumes)) {
                return [
                    'success' => true,
                    'message' => 'No asset volumes to index.',
                ];
            }

            $volumeIds = [];
            foreach ($volumes as $volume) {
                $volumeIds[] = $volume->id;
            }

            // Queue the asset indexing job
            Craft::$app->queue->push(new \craft\queue\jobs\UpdateAssetIndexes([
                'volumeIds' => $volumeIds,
            ]));

            return [
                'success' => true,
                'message' => 'Asset index rebuild has been queued. This will run in the background.',
                'volumesQueued' => count($volumeIds),
            ];
        } catch (\Exception $e) {
            Craft::error('Failed to queue asset index rebuild: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'error' => 'Failed to queue asset index rebuild: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue status
     */
    private function getQueueStatus(): array
    {
        try {
            $queue = Craft::$app->queue;

            // Get queue info using reflection if needed, or just count jobs
            $db = Craft::$app->getDb();

            $totalJobs = (int)$db->createCommand()
                ->from('{{%queue}}')
                ->count()
                ->queryScalar();

            $waitingJobs = (int)$db->createCommand()
                ->from('{{%queue}}')
                ->where(['timePushed' => null])
                ->orWhere(['and', ['not', ['timePushed' => null]], ['timeStarted' => null]])
                ->count()
                ->queryScalar();

            $failedJobs = (int)$db->createCommand()
                ->from('{{%queue}}')
                ->where(['not', ['fail' => null]])
                ->count()
                ->queryScalar();

            $runningJobs = (int)$db->createCommand()
                ->from('{{%queue}}')
                ->where(['not', ['timeStarted' => null]])
                ->andWhere(['timeDone' => null])
                ->count()
                ->queryScalar();

            return [
                'success' => true,
                'queue' => [
                    'totalJobs' => $totalJobs,
                    'waiting' => $waitingJobs,
                    'running' => $runningJobs,
                    'failed' => $failedJobs,
                ],
            ];
        } catch (\Exception $e) {
            Craft::error('Failed to get queue status: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'error' => 'Failed to get queue status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Run queue jobs
     */
    private function runQueueJobs(int $limit = 10): array
    {
        try {
            $queue = Craft::$app->queue;
            $processed = 0;

            // Run queue jobs
            for ($i = 0; $i < $limit; $i++) {
                if ($queue->run()) {
                    $processed++;
                } else {
                    break;
                }
            }

            return [
                'success' => true,
                'message' => "Processed {$processed} queue job(s).",
                'jobsProcessed' => $processed,
            ];
        } catch (\Exception $e) {
            Craft::error('Failed to run queue jobs: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'error' => 'Failed to run queue jobs: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get system information
     */
    private function getSystemInfo(): array
    {
        try {
            $info = Craft::$app->getInfo();

            return [
                'success' => true,
                'system' => [
                    'craftVersion' => Craft::$app->version,
                    'craftEdition' => Craft::$app->getEditionName(),
                    'phpVersion' => PHP_VERSION,
                    'databaseDriver' => Craft::$app->db->driverName,
                    'databaseVersion' => Craft::$app->db->getServerVersion(),
                    'environment' => Craft::$app->env,
                    'devMode' => Craft::$app->config->general->devMode,
                    'timezone' => Craft::$app->getTimeZone(),
                    'language' => Craft::$app->language,
                    'isMultiSite' => Craft::$app->getIsMultiSite(),
                ],
            ];
        } catch (\Exception $e) {
            Craft::error('Failed to get system info: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'error' => 'Failed to get system info: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Helper to delete directory recursively
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                @rmdir($item->getRealPath());
            } else {
                @unlink($item->getRealPath());
            }
        }

        @rmdir($dir);
    }
}
