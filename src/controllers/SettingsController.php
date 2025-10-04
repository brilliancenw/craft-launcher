<?php

namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;
use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Settings Controller
 *
 * Handles AJAX requests for plugin settings updates
 */
class SettingsController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionCompleteFirstRun(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        try {
            $plugin = Launcher::getInstance();

            // Debug: Check if plugin and interface service are available
            if (!$plugin) {
                Craft::error('Plugin instance not available', __METHOD__);
                return $this->asJson([
                    'success' => false,
                    'error' => 'Plugin not initialized'
                ]);
            }

            if (!$plugin->interface) {
                Craft::error('Interface service not available', __METHOD__);
                return $this->asJson([
                    'success' => false,
                    'error' => 'Interface service not initialized'
                ]);
            }

            // Debug: Check table exists
            $tableExists = $plugin->interface->tableExists();
            Craft::info('Interface table exists: ' . ($tableExists ? 'YES' : 'NO'), __METHOD__);

            if (!$tableExists) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Interface settings table does not exist'
                ]);
            }

            // Use interface service instead of plugin settings
            $success = $plugin->interface->markFirstRunCompleted();

            if ($success) {
                Craft::info('First run completed and saved successfully', __METHOD__);
                return $this->asJson([
                    'success' => true,
                    'message' => 'Welcome screen completed'
                ]);
            } else {
                Craft::error('Failed to save first run completion', __METHOD__);
                return $this->asJson([
                    'success' => false,
                    'error' => 'Failed to save interface setting'
                ]);
            }

        } catch (\Exception $e) {
            Craft::error('Failed to complete first run: ' . $e->getMessage(), __METHOD__);

            return $this->asJson([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
}
