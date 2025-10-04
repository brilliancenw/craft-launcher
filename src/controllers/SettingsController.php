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

            // Use interface service to mark first run as completed
            $success = $plugin->interface->markFirstRunCompleted();

            if ($success) {
                return $this->asJson([
                    'success' => true,
                    'message' => 'Welcome screen completed'
                ]);
            } else {
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
