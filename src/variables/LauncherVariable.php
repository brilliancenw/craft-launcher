<?php
namespace brilliance\launcher\variables;

use Craft;
use craft\helpers\Html;
use brilliance\launcher\Launcher;
use Twig\Markup;

class LauncherVariable
{
    /**
     * Output the bootstrap script tag for front-end launcher
     *
     * Usage in templates:
     *   {{ craft.launcher.bootstrap() }}
     *   {{ craft.launcher.bootstrap({ context: entry }) }}
     *
     * @param array $options Optional configuration
     *   - context: Element to use for edit context (entry, category, etc.)
     * @return Markup|string Empty string if not available
     */
    public function bootstrap(array $options = []): Markup|string
    {
        $plugin = Launcher::getInstance();
        $settings = $plugin->getSettings();

        // Only output if deployment method allows it
        if ($settings->frontEndDeployment === 'disabled') {
            return '';
        }

        // Don't output in CP
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return '';
        }

        // Don't output in console requests
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return '';
        }

        // Build bootstrap URL
        $bootstrapUrl = Craft::$app->getUrlManager()->createUrl(['launcher/bootstrap']);

        // Get published asset URL for bootstrap script
        $assetUrl = Craft::$app->getAssetManager()->getPublishedUrl(
            '@brilliance/launcher/assetbundles/launcher/dist',
            true
        );
        $bootstrapScriptUrl = $assetUrl . '/js/launcher-bootstrap.js';

        // Build context data attribute if element provided
        $contextHtml = '';
        if (!empty($options['context'])) {
            $element = $options['context'];
            $contextData = [
                'currentElement' => [
                    'id' => $element->id ?? null,
                    'title' => $element->title ?? null,
                    'type' => basename(str_replace('\\', '/', get_class($element))),
                    'editUrl' => method_exists($element, 'getCpEditUrl') ? $element->getCpEditUrl() : null,
                ]
            ];
            $contextHtml = '<script type="application/json" data-launcher-context>' . Html::encode(json_encode($contextData)) . '</script>';
        }

        // Return script tag
        $html = '<script src="' . Html::encode($bootstrapScriptUrl) . '" data-bootstrap-url="' . Html::encode($bootstrapUrl) . '" defer></script>';

        if ($contextHtml) {
            $html = $contextHtml . "\n" . $html;
        }

        return new Markup($html, 'UTF-8');
    }

    /**
     * Check if front-end launcher is available (enabled by admin)
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $plugin = Launcher::getInstance();
        $settings = $plugin->getSettings();

        return $settings->frontEndDeployment !== 'disabled';
    }

    /**
     * Get the deployment method configured by admin
     *
     * @return string 'disabled', 'twig', or 'auto'
     */
    public function getDeploymentMethod(): string
    {
        $plugin = Launcher::getInstance();
        $settings = $plugin->getSettings();

        return $settings->frontEndDeployment;
    }

    /**
     * Get the history service
     */
    public function getHistory()
    {
        return Launcher::$plugin->history;
    }

    /**
     * Get the search service
     */
    public function getSearch()
    {
        return Launcher::$plugin->search;
    }

    /**
     * Get the launcher service
     */
    public function getLauncher()
    {
        return Launcher::$plugin->launcher;
    }

    /**
     * Get the user preference service
     */
    public function getUserPreference()
    {
        return Launcher::$plugin->userPreference;
    }

    /**
     * Get the integration service
     */
    public function getIntegration()
    {
        return Launcher::$plugin->integration;
    }

    /**
     * Get the addon service
     */
    public function getAddon()
    {
        return Launcher::$plugin->addon;
    }
}
