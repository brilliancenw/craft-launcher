<?php

namespace brilliance\launcher\ai\providers;

use Craft;
use craft\helpers\Json;

/**
 * Claude AI Provider
 *
 * Implementation for Anthropic's Claude API
 */
class ClaudeProvider extends BaseAIProvider
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';
    private string $model = 'claude-sonnet-4-20250514';

    /**
     * Send a message to Claude
     */
    public function sendMessage(
        array $messages,
        array $tools = [],
        string $systemPrompt = ''
    ): AIResponse {
        try {
            $payload = $this->buildPayload($messages, $tools, $systemPrompt, false);

            $response = $this->makeRequest($payload);

            if (isset($response['error'])) {
                return new AIResponse(
                    error: $response['error']['message'] ?? 'Unknown error'
                );
            }

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            Craft::error('Claude API error: ' . $e->getMessage(), __METHOD__);
            return new AIResponse(error: $e->getMessage());
        }
    }

    /**
     * Stream a message response
     */
    public function streamMessage(
        array $messages,
        array $tools = [],
        string $systemPrompt = ''
    ): \Generator {
        try {
            $payload = $this->buildPayload($messages, $tools, $systemPrompt, true);

            // For streaming, we'll need to implement SSE handling
            // This is a simplified version - full implementation would use curl with callbacks
            $response = $this->makeRequest($payload);

            yield Json::encode($this->parseResponse($response)->toArray());
        } catch (\Exception $e) {
            Craft::error('Claude streaming error: ' . $e->getMessage(), __METHOD__);
            yield Json::encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'claude';
    }

    /**
     * Get model name
     */
    public function getModel(): string
    {
        return $this->config['model'] ?? $this->model;
    }

    /**
     * Validate API key
     */
    public function validate(): array
    {
        if (empty($this->apiKey)) {
            return [
                'valid' => false,
                'message' => 'API key is required',
            ];
        }

        // Try a minimal API call to validate
        try {
            $response = $this->makeRequest([
                'model' => $this->getModel(),
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'Hi'],
                ],
            ]);

            if (isset($response['error'])) {
                return [
                    'valid' => false,
                    'message' => $response['error']['message'] ?? 'Invalid API key',
                ];
            }

            return [
                'valid' => true,
                'message' => 'API key is valid',
                'model' => $this->getModel(),
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available models from Claude API
     */
    public function getAvailableModels(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'API key is required',
                'models' => [],
            ];
        }

        try {
            $ch = curl_init('https://api.anthropic.com/v1/models');

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-key: ' . $this->apiKey,
                    'anthropic-version: ' . self::API_VERSION,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new \Exception("cURL error: {$error}");
            }

            curl_close($ch);

            if ($httpCode !== 200) {
                Craft::error("Claude Models API returned HTTP {$httpCode}: {$response}", __METHOD__);
                return [
                    'success' => false,
                    'message' => "API returned HTTP {$httpCode}",
                    'models' => [],
                ];
            }

            $data = Json::decode($response);

            if (isset($data['error'])) {
                return [
                    'success' => false,
                    'message' => $data['error']['message'] ?? 'Unknown error',
                    'models' => [],
                ];
            }

            return [
                'success' => true,
                'models' => $data['data'] ?? [],
            ];
        } catch (\Exception $e) {
            Craft::error('Claude get models error: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'models' => [],
            ];
        }
    }

    /**
     * Build API request payload
     */
    private function buildPayload(
        array $messages,
        array $tools,
        string $systemPrompt,
        bool $stream
    ): array {
        $formattedMessages = $this->formatMessages($messages);

        $payload = [
            'model' => $this->getModel(),
            'max_tokens' => $this->config['maxTokens'] ?? 4096,
            'messages' => $formattedMessages,
        ];

        if (!empty($systemPrompt)) {
            $payload['system'] = $systemPrompt;
        }

        if (!empty($tools)) {
            $formattedTools = $this->formatTools($tools);
            $payload['tools'] = $formattedTools;

            // Log the formatted tools for debugging
            Craft::info('Formatted tools being sent to Claude: ' . json_encode($formattedTools, JSON_PRETTY_PRINT), __METHOD__);
        }

        if ($stream) {
            $payload['stream'] = true;
        }

        // Log the formatted messages for debugging
        Craft::info('Formatted messages being sent to Claude (count: ' . count($formattedMessages) . '): ' . json_encode($formattedMessages, JSON_PRETTY_PRINT), __METHOD__);

        return $payload;
    }

    /**
     * Format messages for Claude API
     */
    private function formatMessages(array $messages): array
    {
        $formatted = [];

        foreach ($messages as $message) {
            // Skip system messages (they go in system parameter)
            if (($message['role'] ?? '') === 'system') {
                continue;
            }

            $formattedMessage = [
                'role' => $message['role'] ?? 'user',
            ];

            // Handle content - can be string or array of content blocks
            $content = $message['content'] ?? '';

            // If content is already an array (e.g., tool_result blocks), use as-is
            if (is_array($content)) {
                $formattedMessage['content'] = $content;
            }
            // If this is an assistant message with tool_use, format as content blocks
            elseif (isset($message['tool_use']) && !empty($message['tool_use'])) {
                $contentBlocks = [];

                // Add text content if present
                if (!empty($content)) {
                    $contentBlocks[] = [
                        'type' => 'text',
                        'text' => $content,
                    ];
                }

                // Add tool_use blocks
                foreach ($message['tool_use'] as $toolCall) {
                    // Ensure input is an object, not an array
                    $input = $toolCall['parameters'] ?? [];
                    if (is_array($input) && empty($input)) {
                        $input = (object)[];
                    }

                    $toolUseBlock = [
                        'type' => 'tool_use',
                        'id' => $toolCall['id'],
                        'name' => $toolCall['name'],
                        'input' => $input,
                    ];

                    // Log the tool_use block being echoed back
                    Craft::info('Echoing tool_use block to Claude: ' . json_encode($toolUseBlock), __METHOD__);

                    $contentBlocks[] = $toolUseBlock;
                }

                $formattedMessage['content'] = $contentBlocks;
            }
            // Simple string content
            else {
                $formattedMessage['content'] = $content;
            }

            $formatted[] = $formattedMessage;
        }

        return $formatted;
    }

    /**
     * Format tools for Claude API
     */
    protected function formatTools(array $tools): array
    {
        $formatted = [];

        foreach ($tools as $tool) {
            $formatted[] = [
                'name' => $tool['name'],
                'description' => $tool['description'] ?? '',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => $this->formatToolParameters($tool['parameters'] ?? []),
                    'required' => $this->getRequiredParameters($tool['parameters'] ?? []),
                ],
            ];
        }

        return $formatted;
    }

    /**
     * Format tool parameters for Claude
     */
    private function formatToolParameters(array $parameters)
    {
        $formatted = [];

        foreach ($parameters as $name => $param) {
            $formatted[$name] = [
                'type' => $param['type'] ?? 'string',
                'description' => $param['description'] ?? '',
            ];
        }

        // Return empty object (not array) for tools with no parameters
        // This ensures JSON encoding produces {} instead of []
        return empty($formatted) ? (object)[] : $formatted;
    }

    /**
     * Get required parameters
     */
    private function getRequiredParameters(array $parameters): array
    {
        $required = [];

        foreach ($parameters as $name => $param) {
            if ($param['required'] ?? false) {
                $required[] = $name;
            }
        }

        return $required;
    }

    /**
     * Parse Claude API response
     */
    private function parseResponse(array $response): AIResponse
    {
        $content = '';
        $toolCalls = [];

        // Log the raw response for debugging
        Craft::info('Claude API Response - stop_reason: ' . ($response['stop_reason'] ?? 'unknown') . ', content blocks: ' . count($response['content'] ?? []), __METHOD__);

        // Extract content blocks
        foreach ($response['content'] ?? [] as $block) {
            Craft::info('Processing content block type: ' . ($block['type'] ?? 'unknown'), __METHOD__);

            if ($block['type'] === 'text') {
                $content .= $block['text'];
                Craft::info('Text block content length: ' . strlen($block['text']), __METHOD__);
            } elseif ($block['type'] === 'tool_use') {
                $toolCall = [
                    'id' => $block['id'],
                    'name' => $block['name'],
                    'parameters' => $block['input'],
                ];
                $toolCalls[] = $toolCall;

                // Log each tool call for debugging
                Craft::info('Parsed tool call from Claude: ' . json_encode($toolCall), __METHOD__);
            }
        }

        Craft::info('Final parsed content length: ' . strlen($content) . ', tool calls: ' . count($toolCalls), __METHOD__);

        return new AIResponse(
            content: $content,
            toolCalls: $toolCalls,
            role: $response['role'] ?? 'assistant',
            metadata: [
                'id' => $response['id'] ?? null,
                'model' => $response['model'] ?? null,
                'usage' => $response['usage'] ?? null,
                'stop_reason' => $response['stop_reason'] ?? null,
            ]
        );
    }

    /**
     * Parse tool calls from response
     */
    protected function parseToolCalls(mixed $response): array
    {
        return $this->parseResponse($response)->toolCalls;
    }

    /**
     * Make HTTP request to Claude API
     */
    private function makeRequest(array $payload): array
    {
        // Log the request payload for debugging
        Craft::info('Claude API Request: ' . json_encode($payload, JSON_PRETTY_PRINT), __METHOD__);

        $ch = curl_init(self::API_URL);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => Json::encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: ' . self::API_VERSION,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("cURL error: {$error}");
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            Craft::error("Claude API returned HTTP {$httpCode}: {$response}", __METHOD__);
        }

        $decoded = Json::decode($response);

        // Log the full response for debugging
        Craft::info('Claude API Response: ' . json_encode($decoded), __METHOD__);

        return $decoded;
    }
}
