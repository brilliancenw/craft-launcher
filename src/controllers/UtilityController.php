<?php

namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;
use Craft;
use craft\web\Controller;
use craft\web\Response;
use craft\helpers\App;

/**
 * Utility Controller
 *
 * Handles AJAX requests from the utility interface
 */
class UtilityController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    /**
     * Generate and download a diagnostic report for troubleshooting
     */
    public function actionGenerateDiagnosticReport(): Response
    {
        $this->requirePostRequest();
        $this->requireAdmin();

        $report = $this->buildDiagnosticReport();

        // Generate filename with timestamp
        $filename = 'rocket-launcher-diagnostic-' . date('Y-m-d-His') . '.txt';

        // Return as downloadable text file
        $response = Craft::$app->getResponse();
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->content = $report;

        return $response;
    }

    /**
     * Build the diagnostic report content
     */
    private function buildDiagnosticReport(): string
    {
        $lines = [];
        $lines[] = '================================================================================';
        $lines[] = 'ROCKET LAUNCHER DIAGNOSTIC REPORT';
        $lines[] = 'Generated: ' . date('Y-m-d H:i:s T');
        $lines[] = '================================================================================';
        $lines[] = '';

        // System Information
        $lines[] = '--- SYSTEM INFORMATION ---';
        $lines[] = '';
        $lines[] = 'Rocket Launcher Version: ' . Launcher::$plugin->getVersion();
        $lines[] = 'Craft CMS Version: ' . Craft::$app->getVersion();
        $lines[] = 'Craft Edition: ' . Craft::$app->getEditionName();
        $lines[] = 'PHP Version: ' . PHP_VERSION;
        $lines[] = 'Database Driver: ' . Craft::$app->getDb()->getDriverName();
        $lines[] = 'Database Version: ' . Craft::$app->getDb()->getServerVersion();
        $lines[] = 'Dev Mode: ' . (App::devMode() ? 'Yes' : 'No');
        $lines[] = '';

        // User History Table Status
        $lines[] = '--- DATABASE STATUS ---';
        $lines[] = '';
        $historyService = Launcher::$plugin->history;
        $tableStatus = $historyService->getTableStatus();
        $lines[] = 'User History Table Exists: ' . ($tableStatus['exists'] ? 'Yes' : 'No');
        $lines[] = 'Table Status Message: ' . $tableStatus['message'];

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
                $lines[] = 'Total History Records: ' . $totalRecords;
                $lines[] = 'Users with History: ' . $totalUsers;
            } catch (\Exception $e) {
                $lines[] = 'Error reading table stats: ' . $e->getMessage();
            }
        }
        $lines[] = '';

        // Content Type Counts
        $lines[] = '--- CONTENT TYPE COUNTS ---';
        $lines[] = '';
        $lines[] = 'Entries: ' . \craft\elements\Entry::find()->status(null)->count();
        $lines[] = 'Users: ' . \craft\elements\User::find()->status(null)->count();
        $lines[] = 'Categories: ' . \craft\elements\Category::find()->status(null)->count();
        $lines[] = 'Assets: ' . \craft\elements\Asset::find()->status(null)->count();
        $lines[] = 'Sections: ' . count(Craft::$app->getEntries()->getAllSections());
        $lines[] = 'Entry Types: ' . count(Craft::$app->getEntries()->getAllEntryTypes());
        $lines[] = 'Category Groups: ' . count(Craft::$app->getCategories()->getAllGroups());
        $lines[] = 'Asset Volumes: ' . count(Craft::$app->getVolumes()->getAllVolumes());
        $lines[] = 'Global Sets: ' . count(Craft::$app->getGlobals()->getAllSets());
        $lines[] = 'Fields: ' . count(Craft::$app->getFields()->getAllFields());
        $lines[] = 'User Groups: ' . count(Craft::$app->getUserGroups()->getAllGroups());
        $lines[] = '';

        // Commerce Status
        $isCommerceInstalled = Craft::$app->getPlugins()->isPluginInstalled('commerce');
        $lines[] = '--- COMMERCE STATUS ---';
        $lines[] = '';
        $lines[] = 'Commerce Installed: ' . ($isCommerceInstalled ? 'Yes' : 'No');
        if ($isCommerceInstalled && Craft::$app->getPlugins()->isPluginEnabled('commerce')) {
            try {
                $commercePlugin = Craft::$app->getPlugins()->getPlugin('commerce');
                $lines[] = 'Commerce Version: ' . ($commercePlugin ? $commercePlugin->getVersion() : 'Unknown');
                $lines[] = 'Products: ' . \craft\commerce\elements\Product::find()->status(null)->count();
                $lines[] = 'Orders: ' . \craft\commerce\elements\Order::find()->isCompleted(true)->count();
            } catch (\Exception $e) {
                $lines[] = 'Error reading Commerce data: ' . $e->getMessage();
            }
        }
        $lines[] = '';

        // Plugin Settings
        $lines[] = '--- PLUGIN SETTINGS ---';
        $lines[] = '';
        $settings = Launcher::$plugin->getSettings();

        $lines[] = 'Hotkey: ' . $settings->hotkey;
        $lines[] = 'Debounce Delay: ' . $settings->debounceDelay . 'ms';
        $lines[] = 'Max Results: ' . $settings->maxResults;
        $lines[] = 'Enable Launch History: ' . ($settings->enableLaunchHistory ? 'Yes' : 'No');
        $lines[] = 'Max History Items: ' . $settings->maxHistoryItems;
        $lines[] = 'Select Result Modifier: ' . $settings->selectResultModifier;
        $lines[] = '';

        $lines[] = 'Search Options:';
        $lines[] = '  - Search Drafts: ' . ($settings->searchDrafts ? 'Yes' : 'No');
        $lines[] = '  - Search Revisions: ' . ($settings->searchRevisions ? 'Yes' : 'No');
        $lines[] = '  - Search Disabled: ' . ($settings->searchDisabled ? 'Yes' : 'No');
        $lines[] = '  - Search Entries By Author: ' . ($settings->searchEntriesByAuthor ? 'Yes' : 'No');
        $lines[] = '';

        $lines[] = 'Commerce Search Options:';
        $lines[] = '  - Search Commerce Customers: ' . ($settings->searchCommerceCustomers ? 'Yes' : 'No');
        $lines[] = '  - Search Commerce Products: ' . ($settings->searchCommerceProducts ? 'Yes' : 'No');
        $lines[] = '  - Search Commerce Orders: ' . ($settings->searchCommerceOrders ? 'Yes' : 'No');
        $lines[] = '';

        $lines[] = 'Searchable Content Types:';
        foreach ($settings->searchableTypes as $type => $enabled) {
            $lines[] = '  - ' . $type . ': ' . ($enabled ? 'Yes' : 'No');
        }
        $lines[] = '';

        $lines[] = 'Content Filters:';
        $lines[] = '  - Searchable Sections: ' . ($settings->searchableSections ? implode(', ', $settings->searchableSections) : '(all)');
        $lines[] = '  - Searchable Category Groups: ' . ($settings->searchableCategoryGroups ? implode(', ', $settings->searchableCategoryGroups) : '(all)');
        $lines[] = '  - Searchable Asset Volumes: ' . ($settings->searchableAssetVolumes ? implode(', ', $settings->searchableAssetVolumes) : '(all)');
        $lines[] = '';

        $lines[] = 'Enabled Integrations:';
        if (empty($settings->enabledIntegrations)) {
            $lines[] = '  (none configured)';
        } else {
            foreach ($settings->enabledIntegrations as $handle => $enabled) {
                $lines[] = '  - ' . $handle . ': ' . ($enabled ? 'Yes' : 'No');
            }
        }
        $lines[] = '';

        // Installed Plugins
        $lines[] = '--- INSTALLED PLUGINS ---';
        $lines[] = '';
        $plugins = Craft::$app->getPlugins()->getAllPlugins();
        foreach ($plugins as $plugin) {
            $enabled = Craft::$app->getPlugins()->isPluginEnabled($plugin->handle);
            $lines[] = '- ' . $plugin->name . ' (' . $plugin->handle . ') v' . $plugin->getVersion() . ' [' . ($enabled ? 'Enabled' : 'Disabled') . ']';
        }
        $lines[] = '';

        $lines[] = '================================================================================';
        $lines[] = 'END OF DIAGNOSTIC REPORT';
        $lines[] = '================================================================================';

        return implode("\n", $lines);
    }

    public function actionCreateTable(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Require admin permissions for table creation
        $this->requireAdmin();

        try {
            $historyService = Launcher::$plugin->history;

            // Check if table already exists
            if ($historyService->tableExists()) {
                Craft::info('Table creation requested but table already exists', __METHOD__);
                return $this->asJson([
                    'success' => false,
                    'error' => 'User history table already exists.'
                ]);
            }

            Craft::info('Starting table creation via utility', __METHOD__);

            // Create the table using the updated method with correct column sizes
            $success = Launcher::$plugin->createUserHistoryTable();

            if ($success) {
                // Clear the table existence cache
                $historyService->clearTableCache();

                Craft::info('Launcher user history table created successfully via utility', __METHOD__);

                return $this->asJson([
                    'success' => true,
                    'message' => 'User history table created successfully!'
                ]);
            } else {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Failed to create table. Check the logs for more details.'
                ]);
            }

        } catch (\Exception $e) {
            Craft::error('Failed to create launcher user history table via utility: ' . $e->getMessage(), __METHOD__);

            return $this->asJson([
                'success' => false,
                'error' => 'Failed to create table: ' . $e->getMessage()
            ]);
        }
    }
}