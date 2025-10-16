<?php

namespace brilliance\launcher\ai\providers;

/**
 * Base AI Provider
 *
 * Abstract class for AI provider implementations.
 * Each provider (Claude, OpenAI, Gemini) extends this to provide
 * a consistent interface for the application.
 */
abstract class BaseAIProvider
{
    protected string $apiKey;
    protected array $config;

    public function __construct(string $apiKey, array $config = [])
    {
        $this->apiKey = $apiKey;
        $this->config = $config;
    }

    /**
     * Send a message to the AI and get a complete response
     *
     * @param array $messages Array of message objects with 'role' and 'content'
     * @param array $tools Array of available tools/functions
     * @param array $systemPrompt System prompt/instructions
     * @return AIResponse
     */
    abstract public function sendMessage(
        array $messages,
        array $tools = [],
        string $systemPrompt = ''
    ): AIResponse;

    /**
     * Stream a message response (for real-time UI updates)
     *
     * @param array $messages Array of message objects
     * @param array $tools Array of available tools/functions
     * @param string $systemPrompt System prompt/instructions
     * @return \Generator Yields chunks of the response
     */
    abstract public function streamMessage(
        array $messages,
        array $tools = [],
        string $systemPrompt = ''
    ): \Generator;

    /**
     * Get the provider name
     */
    abstract public function getProviderName(): string;

    /**
     * Get the model being used
     */
    abstract public function getModel(): string;

    /**
     * Validate API key and configuration
     */
    abstract public function validate(): array;

    /**
     * Convert tool definitions to provider-specific format
     */
    abstract protected function formatTools(array $tools): array;

    /**
     * Parse tool calls from provider response
     */
    abstract protected function parseToolCalls(mixed $response): array;
}
