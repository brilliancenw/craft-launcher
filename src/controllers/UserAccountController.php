<?php
namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;

use Craft;
use craft\controllers\EditUserTrait;
use craft\web\Controller;
use craft\web\CpScreenResponseBehavior;
use yii\web\Response;

class UserAccountController extends Controller
{
    use EditUserTrait;

    public const SCREEN_LAUNCHER = 'launcher';

    protected array|int|bool $allowAnonymous = false;

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Basic check - user must be logged in
        if (!Craft::$app->getUser()->getIdentity()) {
            throw new \yii\web\ForbiddenHttpException('User must be logged in');
        }

        return true;
    }

    public function actionIndex(?int $userId = null): Response
    {
        // Get the current user (not another user - this is for myaccount area)
        $user = Craft::$app->getUser()->getIdentity();

        /** @var Response|CpScreenResponseBehavior $response */
        $response = $this->asEditUserScreen($user, self::SCREEN_LAUNCHER);

        $isEnabled = Launcher::$plugin->userPreference->isFrontEndEnabled();
        $isNewTabEnabled = Launcher::$plugin->userPreference->isFrontEndNewTabEnabled();
        $nestedEntriesPreference = Launcher::$plugin->userPreference->getNestedEntriesPreference();
        $globalHideNestedEntries = Launcher::$plugin->getSettings()->hideNestedEntries;

        // Set up the form action and save buttons
        $response->action('launcher/user-preference/set-front-end-enabled');
        $response->submitButtonLabel('Save');

        // Use contentTemplate instead of contentHtml to render our template
        $response->contentTemplate('launcher/_user-account-content', [
            'user' => $user,
            'isEnabled' => $isEnabled,
            'isNewTabEnabled' => $isNewTabEnabled,
            'nestedEntriesPreference' => $nestedEntriesPreference,
            'globalHideNestedEntries' => $globalHideNestedEntries,
        ]);

        return $response;
    }

    /**
     * View another user's launcher settings (admin only)
     */
    public function actionViewUser(int $userId): Response
    {
        // Only admins can view other users' settings
        if (!Craft::$app->getUser()->getIsAdmin()) {
            throw new \yii\web\ForbiddenHttpException('Only admins can view other users\' launcher settings');
        }

        // Get the user being viewed
        $user = Craft::$app->getUsers()->getUserById($userId);
        if (!$user) {
            throw new \yii\web\NotFoundHttpException('User not found');
        }

        /** @var Response|CpScreenResponseBehavior $response */
        $response = $this->asEditUserScreen($user, self::SCREEN_LAUNCHER);

        // Get the user's preferences
        $preferences = $user->getPreferences();
        $isEnabled = $preferences['launcher_frontend_enabled'] ?? false;
        $isNewTabEnabled = $preferences['launcher_frontend_new_tab'] ?? false;
        $nestedEntriesPreference = $preferences['launcher_nested_entries'] ?? 'system';
        $globalHideNestedEntries = Launcher::$plugin->getSettings()->hideNestedEntries;

        // Use contentTemplate to render the template (read-only view)
        $response->contentTemplate('launcher/_user-account-content', [
            'user' => $user,
            'isEnabled' => $isEnabled,
            'isNewTabEnabled' => $isNewTabEnabled,
            'nestedEntriesPreference' => $nestedEntriesPreference,
            'globalHideNestedEntries' => $globalHideNestedEntries,
            'readOnly' => true, // Indicate this is a read-only view
        ]);

        return $response;
    }
}