<?php
namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class UserPreferenceController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Craft::$app->getUser()->checkPermission('accessLauncher')) {
            throw new \yii\web\ForbiddenHttpException('User does not have permission to access launcher');
        }

        return true;
    }

    public function actionSetFrontEndEnabled(): Response
    {
        $this->requirePostRequest();

        // Handle both JSON (AJAX) and form data
        $request = Craft::$app->getRequest();
        if ($request->getIsAjax() && $request->getContentType() === 'application/json') {
            // AJAX JSON request
            $enabled = (bool) $request->getBodyParam('enabled', false);
            $newTabEnabled = (bool) $request->getBodyParam('newTabEnabled', false);
        } else {
            // Standard form submission
            $enabled = (bool) $request->getBodyParam('enabled', false);
            $newTabEnabled = (bool) $request->getBodyParam('newTabEnabled', false);
        }

        $success1 = Launcher::$plugin->userPreference->setFrontEndEnabled($enabled);
        $success2 = Launcher::$plugin->userPreference->setFrontEndNewTabEnabled($newTabEnabled);
        $success = $success1 && $success2;

        if ($request->getIsAjax()) {
            return $this->asJson([
                'success' => $success,
                'message' => $success
                    ? 'Launcher preferences updated successfully'
                    : 'Failed to update preferences'
            ]);
        } else {
            // Standard form submission - redirect back with flash message
            if ($success) {
                Craft::$app->getSession()->setNotice('Launcher preferences updated successfully');
            } else {
                Craft::$app->getSession()->setError('Failed to update preferences');
            }
            return $this->redirectToPostedUrl();
        }
    }

    public function actionGetFrontEndStatus(): Response
    {
        $this->requireAcceptsJson();

        $enabled = Launcher::$plugin->userPreference->isFrontEndEnabled();
        $newTabEnabled = Launcher::$plugin->userPreference->isFrontEndNewTabEnabled();

        return $this->asJson([
            'success' => true,
            'enabled' => $enabled,
            'newTabEnabled' => $newTabEnabled
        ]);
    }
}