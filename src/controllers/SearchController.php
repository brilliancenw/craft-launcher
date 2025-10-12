<?php
namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;

use Craft;
use craft\elements\Entry;
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

        // Additional security check for front-end requests
        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            // Rate limiting for front-end requests
            if (!$this->checkRateLimit()) {
                throw new \yii\web\TooManyRequestsHttpException('Too many search requests. Please slow down.');
            }
        }

        $query = Craft::$app->getRequest()->getBodyParam('query', '');
        $browseType = Craft::$app->getRequest()->getBodyParam('browseType', '');
        
        // Handle browse mode requests
        if (!empty($browseType)) {
            try {
                $results = Launcher::$plugin->search->browseContentType($browseType);
                $formattedResults = Launcher::$plugin->launcher->formatResults($results);

                return $this->asJson([
                    'success' => true,
                    'results' => $formattedResults,
                    'isRecent' => false,
                    'isBrowse' => true,
                    'browseType' => $browseType,
                ]);
            } catch (\Exception $e) {
                Craft::error('Browse error for type ' . $browseType . ': ' . $e->getMessage(), 'launcher');
                return $this->asJson([
                    'success' => false,
                    'error' => 'Browse failed: ' . $e->getMessage(),
                    'browseType' => $browseType,
                ], 500);
            }
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

                    // Add integration data to popular items
                    foreach ($popularItems as &$item) {
                        $item['integrations'] = Launcher::$plugin->integration->getIntegrationsForItem($item);
                    }
                    unset($item);

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

        // Get context for front-end searches
        $context = Craft::$app->getRequest()->getBodyParam('context', []);

        // Validate context for security
        $context = $this->validateContext($context);

        $searchResults = Launcher::$plugin->search->search($query, $context);
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
        } else {
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

    public function actionRemoveHistoryItem(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $itemHash = Craft::$app->getRequest()->getBodyParam('itemHash');
        
        if (!$itemHash) {
            return $this->asJson([
                'success' => false,
                'error' => 'Item hash is required',
            ], 400);
        }

        try {
            $success = Launcher::$plugin->history->removeHistoryItem($itemHash);
            
            return $this->asJson([
                'success' => $success,
                'message' => $success ? 'Item removed from history' : 'Failed to remove item',
            ]);
        } catch (\Exception $e) {
            Craft::error('Failed to remove history item: ' . $e->getMessage(), 'launcher');
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check rate limit for front-end searches
     */
    private function checkRateLimit(): bool
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return false;
        }

        $cacheKey = 'launcher_rate_limit_' . $user->id;
        $cache = Craft::$app->getCache();

        $requests = $cache->get($cacheKey) ?: [];
        $now = time();

        // Remove requests older than 1 minute
        $requests = array_filter($requests, function($timestamp) use ($now) {
            return ($now - $timestamp) < 60;
        });

        // Check if user has exceeded limit (30 requests per minute)
        if (count($requests) >= 30) {
            return false;
        }

        // Add current request
        $requests[] = $now;
        $cache->set($cacheKey, $requests, 300); // Cache for 5 minutes

        return true;
    }

    /**
     * Validate context data for security
     */
    private function validateContext(array $context): array
    {
        $validatedContext = [];

        // Only allow specific context keys
        $allowedKeys = ['currentEntry'];

        foreach ($allowedKeys as $key) {
            if (isset($context[$key])) {
                if ($key === 'currentEntry') {
                    $validatedContext[$key] = $this->validateEntryContext($context[$key]);
                }
            }
        }

        return $validatedContext;
    }

    /**
     * Execute an integration action
     */
    public function actionExecuteIntegration(): Response
    {
        try {
            $this->requireAcceptsJson();
            $this->requirePostRequest();

            $integrationHandle = Craft::$app->getRequest()->getBodyParam('integration');
            $action = Craft::$app->getRequest()->getBodyParam('action');
            $params = Craft::$app->getRequest()->getBodyParam('params', []);

            if (!$integrationHandle || !$action) {
                return $this->asJson([
                    'success' => false,
                    'message' => 'Integration handle and action are required',
                ], 400);
            }

            Craft::info('Integration action request: ' . $integrationHandle . '::' . $action, 'launcher');

            // Execute the integration action
            $result = Launcher::$plugin->integration->executeAction($integrationHandle, $action, $params);

            return $this->asJson($result);

        } catch (\Exception $e) {
            Craft::error('Integration action failed: ' . $e->getMessage(), 'launcher');
            return $this->asJson([
                'success' => false,
                'message' => 'Action execution failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate entry context data
     */
    private function validateEntryContext($entryData): ?array
    {
        if (!is_array($entryData)) {
            return null;
        }

        // Required fields
        if (!isset($entryData['id']) || !is_numeric($entryData['id'])) {
            return null;
        }

        // Verify the entry actually exists and user has permission to edit it
        $entry = Entry::find()->id($entryData['id'])->one();
        if (!$entry) {
            return null;
        }

        // Check if user can edit this entry
        $section = $entry->getSection();
        if (!$section || !Craft::$app->getUser()->checkPermission('editEntries:' . $section->uid)) {
            return null;
        }

        // Return sanitized context
        return [
            'id' => (int) $entryData['id'],
            'title' => $entry->title,
            'sectionHandle' => $section->handle,
            'typeHandle' => $entry->getType() ? $entry->getType()->handle : 'unknown',
            'editUrl' => $entry->getCpEditUrl()
        ];
    }

}