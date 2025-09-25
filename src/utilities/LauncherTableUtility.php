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
        return 'Launcher Table Manager';
    }

    public static function id(): string
    {
        return 'launcher-table-manager';
    }

    public static function iconPath(): ?string
    {
        return '@brilliance/launcher/resources/icon-mask.svg';
    }

    public static function description(): string
    {
        return 'Manage the Launcher user history database table. Check status and create the table if needed.';
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

        return Craft::$app->getView()->renderTemplate('launcher/_utility', [
            'tableStatus' => $tableStatus,
            'stats' => $stats,
            'settings' => $settings,
            'pluginVersion' => Launcher::$plugin->getVersion(),
            'craftVersion' => Craft::$app->getVersion(),
        ]);
    }
}