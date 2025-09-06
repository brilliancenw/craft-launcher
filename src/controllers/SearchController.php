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
        $browseType = Craft::$app->getRequest()->getBodyParam('browseType', '');
        
        // Handle browse mode requests
        if (!empty($browseType)) {
            $results = Launcher::$plugin->search->browseContentType($browseType);
            $formattedResults = Launcher::$plugin->launcher->formatResults($results);
            
            return $this->asJson([
                'success' => true,
                'results' => $formattedResults,
                'isRecent' => false,
                'isBrowse' => true,
                'browseType' => $browseType,
            ]);
        }
        
        if (empty($query)) {
            $settings = Launcher::$plugin->getSettings();
            
            // Get popular items if history tracking is enabled
            if ($settings->enableLaunchHistory ?? true) {
                $popularItems = Launcher::$plugin->history->getPopularItems($settings->maxHistoryItems ?? 10);
                
                Craft::info('Popular items requested: Found ' . count($popularItems) . ' items', 'launcher');
                
                if (!empty($popularItems)) {
                    Craft::info('Returning popular items: ' . json_encode(array_map(function($item) { 
                        return $item['title'] . ' (' . $item['launchCount'] . ' launches)'; 
                    }, $popularItems)), 'launcher');
                    
                    return $this->asJson([
                        'success' => true,
                        'results' => $popularItems,
                        'isPopular' => true,
                    ]);
                }
            }
            
            // Fallback to recent items if no popular items or history disabled
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
        try {
            $this->requireAcceptsJson();
            $this->requirePostRequest();

            $item = Craft::$app->getRequest()->getBodyParam('item');
            
            Craft::info('Navigation request received. Item: ' . json_encode($item), 'launcher');
        
        if ($item && isset($item['url'])) {
            Launcher::$plugin->launcher->addRecentItem($item);
            
            // Record this launch in the user's history
            try {
                $success = Launcher::$plugin->history->recordLaunch($item);
                Craft::info('Launch history recorded: ' . ($success ? 'SUCCESS' : 'FAILED') . ' for item: ' . json_encode($item), 'launcher');
            } catch (\Exception $e) {
                Craft::error('Failed to record launch history: ' . $e->getMessage() . ' for item: ' . json_encode($item), 'launcher');
            }
        }

            return $this->asJson([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            Craft::error('Navigation request failed: ' . $e->getMessage(), 'launcher');
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function actionClearHistory(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $success = Launcher::$plugin->history->clearUserHistory();
        $stats = $success ? Launcher::$plugin->history->getUserStats() : null;

        return $this->asJson([
            'success' => $success,
            'message' => $success ? 'Launch history cleared successfully' : 'Failed to clear launch history',
            'stats' => $stats,
        ]);
    }

    public function actionGetHistoryStats(): Response
    {
        $this->requireAcceptsJson();

        $stats = Launcher::$plugin->history->getUserStats();

        return $this->asJson([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}