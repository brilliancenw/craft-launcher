<?php

namespace brilliance\launcher\utilities;

use brilliance\launcher\Launcher;
use Craft;
use craft\base\Utility;
use craft\helpers\UrlHelper;

/**
 * Launcher Table Management Utility
 *
 * Provides table diagnostics and management tools that work even in production
 * environments where admin settings changes are disabled.
 */
class LauncherTableUtility extends Utility
{
    public static function displayName(): string
    {
        return 'Rocket Launcher';
    }

    public static function id(): string
    {
        return 'launcher-table-manager';
    }

    public static function icon(): ?string
    {
        return dirname(__DIR__) . '/resources/icon-mask.svg';
    }

    public static function description(): string
    {
        return 'Manage the Rocket Launcher user history database table. Check status and create the table if needed.';
    }

    public static function contentHtml(): string
    {
        $historyService = Launcher::$plugin->history;
        $tableStatus = $historyService->getTableStatus();

        // Get some basic statistics if table exists
        $stats = null;
        if ($tableStatus['exists']) {
            try {
                $totalRecords = (new \craft\db\Query())
                    ->from('{{%launcher_user_history}}')
                    ->count();

                $totalUsers = (new \craft\db\Query())
                    ->from('{{%launcher_user_history}}')
                    ->select('userId')
                    ->distinct()
                    ->count();

                $stats = [
                    'totalRecords' => $totalRecords,
                    'totalUsers' => $totalUsers,
                ];
            } catch (\Exception $e) {
                Craft::warning('Could not fetch launcher table statistics: ' . $e->getMessage(), __METHOD__);
            }
        }

        $settings = Launcher::$plugin->getSettings();

        // Get search index status
        $searchIndexStatus = self::getSearchIndexStatus();

        return Craft::$app->getView()->renderTemplate('launcher/_utility', [
            'tableStatus' => $tableStatus,
            'stats' => $stats,
            'settings' => $settings,
            'pluginVersion' => Launcher::$plugin->getVersion(),
            'craftVersion' => Craft::$app->getVersion(),
            'searchIndexStatus' => $searchIndexStatus,
        ]);
    }

    /**
     * Get search index status for all sections
     */
    private static function getSearchIndexStatus(): array
    {
        $status = [
            'sections' => [],
            'searchableFields' => [],
            'totalIndexedKeywords' => 0,
        ];

        try {
            // Get all sections
            $sections = Craft::$app->getEntries()->getAllSections();

            foreach ($sections as $section) {
                // Count entries in this section
                $entryCount = \craft\elements\Entry::find()
                    ->section($section->handle)
                    ->status(null)
                    ->count();

                // Count indexed entries for this section
                $indexedCount = (new \craft\db\Query())
                    ->from('{{%searchindex}} si')
                    ->innerJoin('{{%entries}} e', 'si.elementId = e.id')
                    ->where(['e.sectionId' => $section->id])
                    ->select('si.elementId')
                    ->distinct()
                    ->count();

                $status['sections'][] = [
                    'name' => $section->name,
                    'handle' => $section->handle,
                    'id' => $section->id,
                    'entryCount' => $entryCount,
                    'indexedCount' => $indexedCount,
                    'isFullyIndexed' => $entryCount === $indexedCount,
                ];
            }

            // Get searchable fields
            $fields = Craft::$app->getFields()->getAllFields();
            foreach ($fields as $field) {
                if ($field->searchable) {
                    $status['searchableFields'][] = [
                        'name' => $field->name,
                        'handle' => $field->handle,
                        'type' => $field::displayName(),
                    ];
                }
            }

            // Get total indexed keywords count
            $status['totalIndexedKeywords'] = (new \craft\db\Query())
                ->from('{{%searchindex}}')
                ->count();

        } catch (\Exception $e) {
            Craft::warning('Could not fetch search index status: ' . $e->getMessage(), __METHOD__);
            $status['error'] = $e->getMessage();
        }

        return $status;
    }
}