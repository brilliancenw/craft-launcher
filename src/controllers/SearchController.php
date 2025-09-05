<?php
namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class SearchController extends Controller
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

    public function actionIndex(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $query = Craft::$app->getRequest()->getBodyParam('query', '');
        
        if (empty($query)) {
            $results = Launcher::$plugin->launcher->getRecentItems();
            return $this->asJson([
                'success' => true,
                'results' => array_values($results),
                'isRecent' => true,
            ]);
        }

        $searchResults = Launcher::$plugin->search->search($query);
        $formattedResults = Launcher::$plugin->launcher->formatResults($searchResults);

        return $this->asJson([
            'success' => true,
            'results' => $formattedResults,
            'isRecent' => false,
        ]);
    }

    public function actionNavigate(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $item = Craft::$app->getRequest()->getBodyParam('item');
        
        if ($item && isset($item['url'])) {
            Launcher::$plugin->launcher->addRecentItem($item);
        }

        return $this->asJson([
            'success' => true,
        ]);
    }
}