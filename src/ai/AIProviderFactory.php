<?php

namespace brilliance\launcher\ai;

use brilliance\launcher\ai\providers\BaseAIProvider;
use brilliance\launcher\ai\providers\ClaudeProvider;
use brilliance\launcher\Launcher;
use Craft;

/**
 * AI Provider Factory
 *
 * Creates the appropriate AI provider instance based on configuration
 */
class AIProviderFactory
{
    /**
     * Create an AI provider instance
     *
     * @param string|null $provider Provider name (claude, openai, gemini) or null to use settings
     * @return BaseAIProvider|null
     */
    public static function create(?string $provider = null): ?BaseAIProvider
    {
        // Get provider from environment-specific settings (database)
        $providerName = $provider ?? Launcher::$plugin->aiSettingsService->getProviderName();
        $apiKey = self::getApiKey($providerName);

        if (empty($apiKey)) {
            Craft::warning("No API key configured for provider: {$providerName}", __METHOD__);
            return null;
        }

        return match ($providerName) {
            'claude' => new ClaudeProvider($apiKey, [
                'model' => self::getClaudeModel(),
                'maxTokens' => 4096,
            ]),
            'openai' => self::createOpenAIProvider($apiKey),
            'gemini' => self::createGeminiProvider($apiKey),
            default => null,
        };
    }

    /**
     * Get API key for the specified provider
     */
    private static function getApiKey(string $provider): string
    {
        // Get API key from environment-specific settings (database)
        return Launcher::$plugin->aiSettingsService->getApiKey($provider) ?? '';
    }

    /**
     * Get Claude model from settings, with fallback to latest default
     */
    private static function getClaudeModel(): string
    {
        $settings = Launcher::$plugin->aiSettingsService->getSettings();
        return $settings->claudeModel ?? 'claude-sonnet-4-20250514';
    }

    /**
     * Create OpenAI provider (placeholder for future implementation)
     */
    private static function createOpenAIProvider(string $apiKey): ?BaseAIProvider
    {
        Craft::warning('OpenAI provider not yet implemented', __METHOD__);
        return null;

        // Future implementation:
        // return new OpenAIProvider($apiKey, [
        //     'model' => 'gpt-4',
        //     'maxTokens' => 4096,
        // ]);
    }

    /**
     * Create Gemini provider (placeholder for future implementation)
     */
    private static function createGeminiProvider(string $apiKey): ?BaseAIProvider
    {
        Craft::warning('Gemini provider not yet implemented', __METHOD__);
        return null;

        // Future implementation:
        // return new GeminiProvider($apiKey, [
        //     'model' => 'gemini-pro',
        //     'maxTokens' => 4096,
        // ]);
    }

    /**
     * Validate that a provider is properly configured
     */
    public static function isProviderConfigured(string $provider): bool
    {
        $apiKey = self::getApiKey($provider);
        return !empty($apiKey);
    }

    /**
     * Get list of available providers
     */
    public static function getAvailableProviders(): array
    {
        return [
            'claude' => [
                'name' => 'Claude (Anthropic)',
                'models' => ['claude-3-5-sonnet-20241022', 'claude-3-opus-20240229'],
                'configured' => self::isProviderConfigured('claude'),
            ],
            'openai' => [
                'name' => 'OpenAI',
                'models' => ['gpt-4', 'gpt-3.5-turbo'],
                'configured' => self::isProviderConfigured('openai'),
                'available' => false, // Not yet implemented
            ],
            'gemini' => [
                'name' => 'Gemini (Google)',
                'models' => ['gemini-pro', 'gemini-ultra'],
                'configured' => self::isProviderConfigured('gemini'),
                'available' => false, // Not yet implemented
            ],
        ];
    }
}
