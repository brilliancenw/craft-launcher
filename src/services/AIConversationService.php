<?php

namespace brilliance\launcher\services;

use brilliance\launcher\ai\AIProviderFactory;
use brilliance\launcher\ai\providers\AIResponse;
use brilliance\launcher\Launcher;
use brilliance\launcher\records\AIConversationRecord;
use brilliance\launcher\records\AIMessageRecord;
use Craft;
use craft\base\Component;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;

/**
 * AI Conversation Service
 *
 * Manages AI conversations including:
 * - Creating and retrieving conversations
 * - Storing messages to database
 * - Orchestrating AI provider interactions
 * - Executing tool calls
 * - Managing conversation history
 */
class AIConversationService extends Component
{
    /**
     * Get or create a conversation for the current user
     */
    public function getOrCreateConversation(?int $userId = null): ?AIConversationRecord
    {
        $userId = $userId ?? Craft::$app->user->id;

        if (!$userId) {
            return null;
        }

        $settings = Launcher::$plugin->getSettings();

        // Try to get the most recent conversation
        $conversation = AIConversationRecord::find()
            ->where(['userId' => $userId])
            ->orderBy(['lastMessageAt' => SORT_DESC])
            ->one();

        // Create new conversation if none exists
        if (!$conversation) {
            $conversation = new AIConversationRecord();
            $conversation->userId = $userId;
            $conversation->threadId = StringHelper::UUID();
            $conversation->provider = $settings->aiProvider;
            $conversation->title = 'New Conversation';
            $conversation->lastMessageAt = DateTimeHelper::currentUTCDateTime();
            $conversation->messageCount = 0;
            $conversation->save();
        }

        return $conversation;
    }

    /**
     * Get conversation by thread ID
     */
    public function getConversationByThreadId(string $threadId): ?AIConversationRecord
    {
        return AIConversationRecord::findOne(['threadId' => $threadId]);
    }

    /**
     * Get conversation messages
     */
    public function getMessages(int $conversationId, ?int $limit = null): array
    {
        $settings = Launcher::$plugin->getSettings();
        $limit = $limit ?? $settings->maxAIConversationHistory;

        $records = AIMessageRecord::find()
            ->where(['conversationId' => $conversationId])
            ->orderBy(['dateCreated' => SORT_DESC])
            ->limit($limit)
            ->all();

        // Reverse to get chronological order
        $records = array_reverse($records);

        $messages = [];
        foreach ($records as $record) {
            $messages[] = [
                'id' => $record->id,
                'role' => $record->role,
                'content' => $record->content,
                'toolCalls' => $record->toolCalls,
                'toolResults' => $record->toolResults,
                'metadata' => $record->metadata,
                'dateCreated' => $record->dateCreated,
            ];
        }

        return $messages;
    }

    /**
     * Send a message and get AI response
     */
    public function sendMessage(string $threadId, string $userMessage): array
    {
        $conversation = $this->getConversationByThreadId($threadId);

        if (!$conversation) {
            return [
                'error' => 'Conversation not found',
            ];
        }

        // Store user message
        $this->storeMessage($conversation->id, 'user', $userMessage);

        // Get conversation history
        $messages = $this->getMessages($conversation->id);

        // Get AI provider
        $provider = AIProviderFactory::create($conversation->provider);

        if (!$provider) {
            return [
                'error' => 'AI provider not configured',
            ];
        }

        // Get system prompt and tools
        $contextService = Launcher::$plugin->craftContextService;
        $toolService = Launcher::$plugin->aiToolService;

        $systemPrompt = $contextService->buildSystemPrompt();
        $tools = $toolService->getToolDefinitions();

        // Convert messages to API format
        // Filter out "tool" role messages and empty assistant messages (except possibly the last one)
        $apiMessages = array_values(array_filter(array_map(function ($msg) {
            // Skip tool messages - they're only for our database history
            if ($msg['role'] === 'tool') {
                return null;
            }

            // Skip empty assistant messages (Claude only allows final assistant message to be empty)
            if ($msg['role'] === 'assistant' && empty($msg['content'])) {
                return null;
            }

            return [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }, $messages)));

        // Call AI provider
        $response = $provider->sendMessage($apiMessages, $tools, $systemPrompt);

        if ($response->hasError()) {
            return [
                'error' => $response->error,
            ];
        }

        // Handle tool calls if present
        if ($response->hasToolCalls()) {
            Craft::info('Tool calls detected, executing tools...', __METHOD__);
            $response = $this->handleToolCalls($conversation, $response, $provider, $apiMessages, $tools, $systemPrompt);
            Craft::info('Tool execution complete. Final response content: ' . substr($response->content, 0, 100), __METHOD__);
        } else {
            Craft::info('No tool calls detected', __METHOD__);
        }

        // Store assistant message
        $messageRecord = $this->storeMessage(
            $conversation->id,
            'assistant',
            $response->content,
            $response->toolCalls
        );

        // Update conversation
        $conversation->lastMessageAt = DateTimeHelper::currentUTCDateTime();
        $conversation->messageCount = AIMessageRecord::find()
            ->where(['conversationId' => $conversation->id])
            ->count();

        // Auto-generate title from first message
        if ($conversation->title === 'New Conversation' && $conversation->messageCount <= 2) {
            $conversation->title = $this->generateTitle($userMessage);
        }

        $conversation->save();

        return [
            'success' => true,
            'threadId' => $threadId,
            'message' => [
                'id' => $messageRecord->id,
                'role' => 'assistant',
                'content' => $response->content,
                'metadata' => $response->metadata,
            ],
        ];
    }

    /**
     * Handle tool/function calls from AI
     */
    private function handleToolCalls(
        AIConversationRecord $conversation,
        AIResponse $response,
        $provider,
        array $messages,
        array $tools,
        string $systemPrompt
    ): AIResponse {
        $toolService = Launcher::$plugin->aiToolService;
        $toolResults = [];

        // Store the assistant's tool call message
        $this->storeMessage(
            $conversation->id,
            'assistant',
            $response->content,
            $response->toolCalls
        );

        // Execute each tool call
        foreach ($response->toolCalls as $toolCall) {
            $result = $toolService->executeTool(
                $toolCall['name'],
                $toolCall['parameters']
            );

            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $toolCall['id'],
                'content' => json_encode($result),
            ];

            // Store tool result message for history
            $this->storeMessage(
                $conversation->id,
                'tool',
                json_encode($result),
                null,
                [[
                    'tool_call_id' => $toolCall['id'],
                    'tool_name' => $toolCall['name'],
                    'result' => $result,
                ]]
            );
        }

        // Add assistant's tool use message to conversation history
        $messages[] = [
            'role' => 'assistant',
            'content' => $response->content ?: '',
            'tool_use' => $response->toolCalls,
        ];

        // Add tool results as user message with tool_result content blocks
        $messages[] = [
            'role' => 'user',
            'content' => $toolResults,
        ];

        // Get final response with tool results
        return $provider->sendMessage($messages, $tools, $systemPrompt);
    }

    /**
     * Store a message to the database
     */
    private function storeMessage(
        int $conversationId,
        string $role,
        string $content,
        ?array $toolCalls = null,
        ?array $toolResults = null
    ): AIMessageRecord {
        $message = new AIMessageRecord();
        $message->conversationId = $conversationId;
        $message->role = $role;
        $message->content = $content;
        $message->toolCalls = $toolCalls;
        $message->toolResults = $toolResults;
        $message->save();

        return $message;
    }

    /**
     * Generate a conversation title from the first user message
     */
    private function generateTitle(string $message): string
    {
        // Take first 50 characters and add ellipsis if needed
        $title = StringHelper::safeTruncate($message, 50, '...');

        // Remove newlines
        $title = str_replace(["\r", "\n"], ' ', $title);

        return $title;
    }

    /**
     * Create a new conversation
     */
    public function createConversation(?int $userId = null): AIConversationRecord
    {
        $userId = $userId ?? Craft::$app->user->id;
        $settings = Launcher::$plugin->getSettings();

        $conversation = new AIConversationRecord();
        $conversation->userId = $userId;
        $conversation->threadId = StringHelper::UUID();
        $conversation->provider = $settings->aiProvider;
        $conversation->title = 'New Conversation';
        $conversation->lastMessageAt = DateTimeHelper::currentUTCDateTime();
        $conversation->messageCount = 0;
        $conversation->save();

        return $conversation;
    }

    /**
     * Get user's recent conversations
     */
    public function getUserConversations(?int $userId = null, int $limit = 10): array
    {
        $userId = $userId ?? Craft::$app->user->id;

        $records = AIConversationRecord::find()
            ->where(['userId' => $userId])
            ->orderBy(['lastMessageAt' => SORT_DESC])
            ->limit($limit)
            ->all();

        $conversations = [];
        foreach ($records as $record) {
            $conversations[] = [
                'threadId' => $record->threadId,
                'title' => $record->title,
                'provider' => $record->provider,
                'messageCount' => $record->messageCount,
                'lastMessageAt' => $record->lastMessageAt,
            ];
        }

        return $conversations;
    }

    /**
     * Delete a conversation
     */
    public function deleteConversation(string $threadId): bool
    {
        $conversation = $this->getConversationByThreadId($threadId);

        if (!$conversation) {
            return false;
        }

        // Messages will be cascade deleted via foreign key
        return (bool)$conversation->delete();
    }
}
