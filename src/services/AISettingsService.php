<?php

namespace brilliance\launcher\services;

use brilliance\launcher\records\AISettingsRecord;
use Craft;
use craft\base\Component;
use craft\helpers\App;

/**
 * AI Settings Service
 *
 * Manages environment-specific AI settings (API keys, brand info, guidelines)
 * These settings are stored in the database and are NOT part of project config
 */
class AISettingsService extends Component
{
    private ?AISettingsRecord $_settings = null;

    /**
     * Get AI settings
     */
    public function getSettings(): AISettingsRecord
    {
        if ($this->_settings === null) {
            $this->_settings = AISettingsRecord::getInstance();
        }

        return $this->_settings;
    }

    /**
     * Save AI settings
     */
    public function saveSettings(array $attributes): bool
    {
        $settings = $this->getSettings();

        // Set attributes
        $settings->setAttributes($attributes, false);

        // Encrypt API keys before saving
        if (isset($attributes['claudeApiKey'])) {
            $settings->claudeApiKey = $this->encryptApiKey($attributes['claudeApiKey']);
        }
        if (isset($attributes['openaiApiKey'])) {
            $settings->openaiApiKey = $this->encryptApiKey($attributes['openaiApiKey']);
        }
        if (isset($attributes['geminiApiKey'])) {
            $settings->geminiApiKey = $this->encryptApiKey($attributes['geminiApiKey']);
        }

        return $settings->save();
    }

    /**
     * Get decrypted API key for a provider
     */
    public function getApiKey(string $provider): ?string
    {
        $settings = $this->getSettings();

        $encrypted = match ($provider) {
            'claude' => $settings->claudeApiKey,
            'openai' => $settings->openaiApiKey,
            'gemini' => $settings->geminiApiKey,
            default => null,
        };

        if (!$encrypted) {
            return null;
        }

        return $this->decryptApiKey($encrypted);
    }

    /**
     * Get masked API key for display (show last 4 characters)
     */
    public function getMaskedApiKey(string $provider): ?string
    {
        $apiKey = $this->getApiKey($provider);

        if (!$apiKey) {
            return null;
        }

        $length = strlen($apiKey);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($apiKey, -4);
    }

    /**
     * Check if an API key is configured for a provider
     */
    public function hasApiKey(string $provider): bool
    {
        return !empty($this->getApiKey($provider));
    }

    /**
     * Encrypt an API key
     */
    private function encryptApiKey(?string $key): ?string
    {
        if (empty($key)) {
            return null;
        }

        // Use Craft's security helper to encrypt, then base64 encode for storage
        $encrypted = Craft::$app->getSecurity()->encryptByPassword($key, $this->getEncryptionKey());
        return base64_encode($encrypted);
    }

    /**
     * Decrypt an API key
     */
    private function decryptApiKey(?string $encrypted): ?string
    {
        if (empty($encrypted)) {
            return null;
        }

        try {
            // Base64 decode first, then decrypt
            $decoded = base64_decode($encrypted);
            return Craft::$app->getSecurity()->decryptByPassword($decoded, $this->getEncryptionKey());
        } catch (\Exception $e) {
            Craft::error('Failed to decrypt API key: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * Get encryption key from environment or generate one
     */
    private function getEncryptionKey(): string
    {
        // Use Craft's security key
        return App::env('CRAFT_SECURITY_KEY') ?? Craft::$app->getConfig()->getGeneral()->securityKey;
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return $this->getSettings()->aiProvider ?? 'claude';
    }

    /**
     * Get brand information
     */
    public function getBrandInfo(): array
    {
        $settings = $this->getSettings();

        return [
            'websiteName' => $settings->websiteName,
            'brandOwner' => $settings->brandOwner,
            'brandTagline' => $settings->brandTagline,
            'brandDescription' => $settings->brandDescription,
            'brandVoice' => $settings->brandVoice,
            'targetAudience' => $settings->targetAudience,
            'brandColors' => $settings->brandColors ?? [],
            'brandLogoUrl' => $settings->brandLogoUrl,
        ];
    }

    /**
     * Get content guidelines
     */
    public function getContentGuidelines(): array
    {
        $settings = $this->getSettings();

        return [
            'contentGuidelines' => $settings->contentGuidelines,
            'contentTone' => $settings->contentTone,
            'writingStyle' => $settings->writingStyle,
            'seoGuidelines' => $settings->seoGuidelines,
            'customGuidelines' => $settings->customGuidelines ?? [],
        ];
    }
}
