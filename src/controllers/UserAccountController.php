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

        // Set up the form action and save buttons
        $response->action('launcher/user-preference/set-front-end-enabled');
        $response->submitButtonLabel('Save');

        // Use contentTemplate instead of contentHtml to render our template
        $response->contentTemplate('launcher/_user-account-content', [
            'user' => $user,
            'isEnabled' => $isEnabled,
            'isNewTabEnabled' => $isNewTabEnabled,
        ]);

        return $response;
    }
}