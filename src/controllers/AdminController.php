<?php

namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;
use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Admin Controller
 *
 * Handles the Launcher admin panel CP section
 */
class AdminController extends Controller
{
    /**
     * Dashboard/Index
     */
    public function actionIndex(): Response
    {
        $this->requirePermission('accessLauncher');

        $settings = Launcher::$plugin->getSettings();
        $aiSettings = Launcher::$plugin->aiSettingsService->getSettings();

        // Get conversation stats
        $conversationCount = \brilliance\launcher\records\AIConversationRecord::find()->count();
        $userConversationCount = \brilliance\launcher\records\AIConversationRecord::find()
            ->where(['userId' => Craft::$app->user->id])
            ->count();

        // Check provider configuration
        $hasApiKey = Launcher::$plugin->aiSettingsService->hasApiKey($aiSettings->aiProvider);

        return $this->renderTemplate('launcher/admin/index', [
            'settings' => $settings,
            'aiSettings' => $aiSettings,
            'conversationCount' => $conversationCount,
            'userConversationCount' => $userConversationCount,
            'hasApiKey' => $hasApiKey,
            'selectedTab' => 'dashboard',
        ]);
    }

    /**
     * API Configuration tab
     */
    public function actionApiConfig(): Response
    {
        $this->requirePermission('accessLauncher');

        $settings = Launcher::$plugin->getSettings();
        $aiSettings = Launcher::$plugin->aiSettingsService->getSettings();

        // Get masked API keys for display
        $maskedKeys = [
            'claude' => Launcher::$plugin->aiSettingsService->getMaskedApiKey('claude'),
            'openai' => Launcher::$plugin->aiSettingsService->getMaskedApiKey('openai'),
            'gemini' => Launcher::$plugin->aiSettingsService->getMaskedApiKey('gemini'),
        ];

        return $this->renderTemplate('launcher/admin/api-config', [
            'settings' => $settings,
            'aiSettings' => $aiSettings,
            'maskedKeys' => $maskedKeys,
            'selectedTab' => 'api-config',
        ]);
    }

    /**
     * Brand Information tab
     */
    public function actionBrandInfo(): Response
    {
        $this->requirePermission('accessLauncher');

        $settings = Launcher::$plugin->getSettings();
        $aiSettings = Launcher::$plugin->aiSettingsService->getSettings();

        return $this->renderTemplate('launcher/admin/brand-info', [
            'settings' => $settings,
            'aiSettings' => $aiSettings,
            'selectedTab' => 'brand-info',
        ]);
    }

    /**
     * Content Guidelines tab
     */
    public function actionGuidelines(): Response
    {
        $this->requirePermission('accessLauncher');

        $settings = Launcher::$plugin->getSettings();
        $aiSettings = Launcher::$plugin->aiSettingsService->getSettings();

        return $this->renderTemplate('launcher/admin/guidelines', [
            'settings' => $settings,
            'aiSettings' => $aiSettings,
            'selectedTab' => 'guidelines',
        ]);
    }

    /**
     * Save API configuration
     */
    public function actionSaveApiConfig(): Response
    {
        $this->requirePermission('accessLauncher');
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $attributes = [
            'aiProvider' => $request->getBodyParam('aiProvider'),
        ];

        // Only update API keys if they're provided (not empty)
        if ($claudeKey = $request->getBodyParam('claudeApiKey')) {
            $attributes['claudeApiKey'] = $claudeKey;
        }
        if ($openaiKey = $request->getBodyParam('openaiApiKey')) {
            $attributes['openaiApiKey'] = $openaiKey;
        }
        if ($geminiKey = $request->getBodyParam('geminiApiKey')) {
            $attributes['geminiApiKey'] = $geminiKey;
        }

        // Save selected Claude model
        if ($claudeModel = $request->getBodyParam('claudeModel')) {
            $attributes['claudeModel'] = $claudeModel;
        }

        if (Launcher::$plugin->aiSettingsService->saveSettings($attributes)) {
            Craft::$app->getSession()->setNotice('API configuration saved.');
            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setError('Could not save API configuration.');
        return $this->redirectToPostedUrl();
    }

    /**
     * Save brand information
     */
    public function actionSaveBrandInfo(): Response
    {
        $this->requirePermission('accessLauncher');
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $attributes = [
            'websiteName' => $request->getBodyParam('websiteName'),
            'brandOwner' => $request->getBodyParam('brandOwner'),
            'brandTagline' => $request->getBodyParam('brandTagline'),
            'brandDescription' => $request->getBodyParam('brandDescription'),
            'brandVoice' => $request->getBodyParam('brandVoice'),
            'targetAudience' => $request->getBodyParam('targetAudience'),
            'brandLogoUrl' => $request->getBodyParam('brandLogoUrl'),
            'brandColors' => $request->getBodyParam('brandColors', []),
        ];

        if (Launcher::$plugin->aiSettingsService->saveSettings($attributes)) {
            Craft::$app->getSession()->setNotice('Brand information saved.');
            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setError('Could not save brand information.');
        return $this->redirectToPostedUrl();
    }

    /**
     * Save content guidelines
     */
    public function actionSaveGuidelines(): Response
    {
        $this->requirePermission('accessLauncher');
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $attributes = [
            'contentGuidelines' => $request->getBodyParam('contentGuidelines'),
            'contentTone' => $request->getBodyParam('contentTone'),
            'writingStyle' => $request->getBodyParam('writingStyle'),
            'seoGuidelines' => $request->getBodyParam('seoGuidelines'),
            'customGuidelines' => $request->getBodyParam('customGuidelines', []),
        ];

        if (Launcher::$plugin->aiSettingsService->saveSettings($attributes)) {
            Craft::$app->getSession()->setNotice('Content guidelines saved.');
            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setError('Could not save content guidelines.');
        return $this->redirectToPostedUrl();
    }

    /**
     * Validate API key
     */
    public function actionValidateKey(): Response
    {
        $this->requirePermission('accessLauncher');
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $provider = Craft::$app->getRequest()->getBodyParam('provider');
        $apiKey = Craft::$app->getRequest()->getBodyParam('apiKey');

        if (empty($provider) || empty($apiKey)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Provider and API key are required',
            ]);
        }

        // Temporarily create provider with this key to validate
        $providerInstance = match ($provider) {
            'claude' => new \brilliance\launcher\ai\providers\ClaudeProvider($apiKey),
            default => null,
        };

        if (!$providerInstance) {
            return $this->asJson([
                'success' => false,
                'message' => 'Provider not yet implemented',
            ]);
        }

        $validation = $providerInstance->validate();

        return $this->asJson([
            'success' => $validation['valid'],
            'message' => $validation['message'],
        ]);
    }
}
