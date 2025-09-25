<?php

namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;
use Craft;
use craft\web\Controller;
use craft\web\Response;

/**
 * Utility Controller
 *
 * Handles AJAX requests from the utility interface
 */
class UtilityController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

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
                return $this->asJson([
                    'success' => false,
                    'error' => 'User history table already exists.'
                ]);
            }

            // Create the table using the migration logic
            $migration = new \brilliance\launcher\migrations\Install();
            $migration->db = Craft::$app->getDb();

            if ($migration->safeUp()) {

                Craft::info('Launcher user history table created successfully via utility', __METHOD__);

                return $this->asJson([
                    'success' => true,
                    'message' => 'User history table created successfully!'
                ]);
            } else {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Failed to create table using migration.'
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