<?php

namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;
use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * AI Controller
 *
 * Handles AI assistant interactions
 */
class AiController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    /**
     * Check permissions before any action
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Check if user has launcher access
        if (!Craft::$app->getUser()->checkPermission('accessLauncher')) {
            throw new \yii\web\ForbiddenHttpException('User does not have permission to access launcher');
        }

        // Check if AI assistant is enabled
        $settings = Launcher::$plugin->getSettings();
        if (!($settings->enableAIAssistant ?? false)) {
            throw new \yii\web\ForbiddenHttpException('AI assistant is not enabled');
        }

        return true;
    }

    /**
     * Start or get existing conversation
     * POST /actions/launcher/ai/start
     */
    public function actionStart(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        try {
            $aiService = Launcher::$plugin->aiConversationService;
            $conversation = $aiService->getOrCreateConversation();

            if (!$conversation) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Failed to create conversation',
                ], 500);
            }

            // Get recent messages if conversation exists
            $messages = [];
            if ($conversation->messageCount > 0) {
                $messages = $aiService->getMessages($conversation->id, 10);
            }

            return $this->asJson([
                'success' => true,
                'conversation' => [
                    'threadId' => $conversation->threadId,
                    'title' => $conversation->title,
                    'messageCount' => $conversation->messageCount,
                    'provider' => $conversation->provider,
                ],
                'messages' => $messages,
            ]);
        } catch (\Exception $e) {
            Craft::error('AI start conversation error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a message to the AI
     * POST /actions/launcher/ai/send
     */
    public function actionSend(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $threadId = Craft::$app->getRequest()->getBodyParam('threadId');
        $message = Craft::$app->getRequest()->getBodyParam('message', '');

        if (empty($threadId)) {
            return $this->asJson([
                'success' => false,
                'error' => 'Thread ID is required',
            ], 400);
        }

        if (empty($message)) {
            return $this->asJson([
                'success' => false,
                'error' => 'Message is required',
            ], 400);
        }

        try {
            $aiService = Launcher::$plugin->aiConversationService;
            $result = $aiService->sendMessage($threadId, $message);

            if (isset($result['error'])) {
                return $this->asJson([
                    'success' => false,
                    'error' => $result['error'],
                ], 500);
            }

            // Return JSON with unescaped HTML (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            return $this->asJson($result, 200, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            Craft::error('AI send message error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversation history
     * GET /actions/launcher/ai/history
     */
    public function actionHistory(): Response
    {
        $this->requireAcceptsJson();

        $threadId = Craft::$app->getRequest()->getParam('threadId');

        if (empty($threadId)) {
            return $this->asJson([
                'success' => false,
                'error' => 'Thread ID is required',
            ], 400);
        }

        try {
            $aiService = Launcher::$plugin->aiConversationService;
            $conversation = $aiService->getConversationByThreadId($threadId);

            if (!$conversation) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Conversation not found',
                ], 404);
            }

            $messages = $aiService->getMessages($conversation->id);

            return $this->asJson([
                'success' => true,
                'messages' => $messages,
            ]);
        } catch (\Exception $e) {
            Craft::error('AI get history error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List user's conversations
     * GET /actions/launcher/ai/list
     */
    public function actionList(): Response
    {
        $this->requireAcceptsJson();

        try {
            $aiService = Launcher::$plugin->aiConversationService;
            $conversations = $aiService->getUserConversations();

            return $this->asJson([
                'success' => true,
                'conversations' => $conversations,
            ]);
        } catch (\Exception $e) {
            Craft::error('AI list conversations error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new conversation
     * POST /actions/launcher/ai/new
     */
    public function actionNew(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        try {
            $aiService = Launcher::$plugin->aiConversationService;
            $conversation = $aiService->createConversation();

            return $this->asJson([
                'success' => true,
                'conversation' => [
                    'threadId' => $conversation->threadId,
                    'title' => $conversation->title,
                    'messageCount' => 0,
                    'provider' => $conversation->provider,
                ],
            ]);
        } catch (\Exception $e) {
            Craft::error('AI create conversation error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a conversation
     * POST /actions/launcher/ai/delete
     */
    public function actionDelete(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $threadId = Craft::$app->getRequest()->getBodyParam('threadId');

        if (empty($threadId)) {
            return $this->asJson([
                'success' => false,
                'error' => 'Thread ID is required',
            ], 400);
        }

        try {
            $aiService = Launcher::$plugin->aiConversationService;
            $deleted = $aiService->deleteConversation($threadId);

            if (!$deleted) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Conversation not found or could not be deleted',
                ], 404);
            }

            return $this->asJson([
                'success' => true,
                'message' => 'Conversation deleted successfully',
            ]);
        } catch (\Exception $e) {
            Craft::error('AI delete conversation error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate AI provider configuration
     * GET /actions/launcher/ai/validate
     */
    public function actionValidate(): Response
    {
        $this->requireAcceptsJson();

        try {
            $settings = Launcher::$plugin->getSettings();
            $provider = \brilliance\launcher\ai\AIProviderFactory::create();

            if (!$provider) {
                return $this->asJson([
                    'success' => false,
                    'configured' => false,
                    'message' => 'No AI provider configured or API key missing',
                ]);
            }

            $validation = $provider->validate();

            return $this->asJson([
                'success' => true,
                'configured' => $validation['valid'],
                'provider' => $settings->aiProvider,
                'message' => $validation['message'],
            ]);
        } catch (\Exception $e) {
            Craft::error('AI validation error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'configured' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available models from the AI provider
     * GET /actions/launcher/ai/models
     */
    public function actionModels(): Response
    {
        $this->requireAcceptsJson();

        try {
            $provider = \brilliance\launcher\ai\AIProviderFactory::create();

            if (!$provider) {
                return $this->asJson([
                    'success' => false,
                    'message' => 'No AI provider configured or API key missing',
                    'models' => [],
                ]);
            }

            // Check if provider supports getting models
            if (!method_exists($provider, 'getAvailableModels')) {
                return $this->asJson([
                    'success' => false,
                    'message' => 'Provider does not support model listing',
                    'models' => [],
                ]);
            }

            $result = $provider->getAvailableModels();

            return $this->asJson($result);
        } catch (\Exception $e) {
            Craft::error('AI get models error: ' . $e->getMessage(), __METHOD__);
            return $this->asJson([
                'success' => false,
                'message' => $e->getMessage(),
                'models' => [],
            ], 500);
        }
    }
}
