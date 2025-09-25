<?php

namespace brilliance\launcher\console\controllers;

use brilliance\launcher\Launcher;
use Craft;
use craft\console\Controller;
use craft\helpers\Console;
use yii\console\ExitCode;

/**
 * Launcher console commands
 */
class DefaultController extends Controller
{
    /**
     * Check the status of the user history table
     */
    public function actionStatus(): int
    {
        $this->stdout("Checking Launcher status...\n", Console::FG_CYAN);

        // Check table status
        $tableStatus = Launcher::$plugin->history->getTableStatus();

        $this->stdout("User History Table: ", Console::FG_YELLOW);
        if ($tableStatus['exists']) {
            $this->stdout("✓ " . $tableStatus['message'] . "\n", Console::FG_GREEN);
        } else {
            $this->stdout("✗ " . $tableStatus['message'] . "\n", Console::FG_RED);
        }

        // Check plugin status
        $plugin = Craft::$app->getPlugins()->getPlugin('launcher');
        $this->stdout("Plugin Version: " . $plugin->version . "\n");
        $this->stdout("Plugin Status: " . ($plugin->isEnabled ? "Enabled" : "Disabled") . "\n");

        // Check settings
        $settings = Launcher::$plugin->getSettings();
        $this->stdout("History Tracking: " . ($settings->enableLaunchHistory ? "Enabled" : "Disabled") . "\n");

        if (!$tableStatus['exists']) {
            $this->stdout("\nTo fix the missing table issue, run:\n", Console::FG_YELLOW);
            $this->stdout("  php craft launcher/create-table\n", Console::FG_CYAN);
        }

        return ExitCode::OK;
    }

    /**
     * Create the user history table
     */
    public function actionCreateTable(): int
    {
        $this->stdout("Creating Launcher user history table...\n", Console::FG_CYAN);

        // Check if table already exists
        if (Launcher::$plugin->history->tableExists()) {
            $this->stdout("✓ Table already exists, no action needed.\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        // Create the table
        try {
            Launcher::$plugin->createUserHistoryTable();

            // Verify it was created
            if (Launcher::$plugin->history->tableExists()) {
                $this->stdout("✓ User history table created successfully!\n", Console::FG_GREEN);
                $this->stdout("Launch history and popular items functionality is now available.\n");
            } else {
                $this->stderr("✗ Table creation appeared to succeed but table still not found.\n", Console::FG_RED);
                return ExitCode::SOFTWARE;
            }
        } catch (\Exception $e) {
            $this->stderr("✗ Failed to create table: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        return ExitCode::OK;
    }

    /**
     * Clear all user history data (admin only)
     */
    public function actionClearHistory(): int
    {
        if (!$this->confirm("Are you sure you want to clear ALL user history data? This cannot be undone.")) {
            $this->stdout("Operation cancelled.\n");
            return ExitCode::OK;
        }

        $this->stdout("Clearing all user history data...\n", Console::FG_CYAN);

        try {
            $deleted = Craft::$app->db->createCommand()
                ->delete('{{%launcher_user_history}}')
                ->execute();

            $this->stdout("✓ Cleared $deleted history records.\n", Console::FG_GREEN);
        } catch (\Exception $e) {
            $this->stderr("✗ Failed to clear history: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        return ExitCode::OK;
    }

    /**
     * Show user history statistics
     */
    public function actionStats(): int
    {
        $this->stdout("Launcher Usage Statistics\n", Console::FG_CYAN);
        $this->stdout("========================\n");

        if (!Launcher::$plugin->history->tableExists()) {
            $this->stderr("✗ User history table does not exist. Run 'php craft launcher/create-table' first.\n", Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        try {
            // Get overall stats
            $totalRecords = Craft::$app->db->createCommand()
                ->select('COUNT(*)')
                ->from('{{%launcher_user_history}}')
                ->queryScalar();

            $totalUsers = Craft::$app->db->createCommand()
                ->select('COUNT(DISTINCT userId)')
                ->from('{{%launcher_user_history}}')
                ->queryScalar();

            $totalLaunches = Craft::$app->db->createCommand()
                ->select('SUM(launchCount)')
                ->from('{{%launcher_user_history}}')
                ->queryScalar();

            $this->stdout("Total History Items: $totalRecords\n");
            $this->stdout("Active Users: $totalUsers\n");
            $this->stdout("Total Launches: $totalLaunches\n\n");

            // Top items
            $topItems = Craft::$app->db->createCommand()
                ->select(['itemTitle', 'itemType', 'SUM(launchCount) as total'])
                ->from('{{%launcher_user_history}}')
                ->groupBy(['itemTitle', 'itemType'])
                ->orderBy(['total' => SORT_DESC])
                ->limit(10)
                ->queryAll();

            if (!empty($topItems)) {
                $this->stdout("Top 10 Most Launched Items:\n", Console::FG_YELLOW);
                $this->stdout("---------------------------\n");
                foreach ($topItems as $item) {
                    $this->stdout(sprintf("  %3d launches - %s (%s)\n",
                        $item['total'],
                        $item['itemTitle'],
                        $item['itemType']
                    ));
                }
            }

        } catch (\Exception $e) {
            $this->stderr("✗ Failed to get statistics: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::SOFTWARE;
        }

        return ExitCode::OK;
    }
}