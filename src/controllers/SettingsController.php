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
            $settings = $plugin->getSettings();

            // Get current settings as array
            $settingsArray = $settings->getAttributes();

            // Update the specific setting
            $settingsArray['firstRunCompleted'] = true;

            // Save the settings
            $success = Craft::$app->getPlugins()->savePluginSettings($plugin, $settingsArray);

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
                    'error' => 'Failed to save settings'
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
