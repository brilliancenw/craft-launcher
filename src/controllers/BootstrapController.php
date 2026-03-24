<?php
namespace brilliance\launcher\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\web\Response;
use brilliance\launcher\Launcher;

/**
 * Bootstrap controller for front-end launcher
 *
 * This endpoint is called by the bootstrap script to:
 * 1. Validate user authorization
 * 2. Return launcher configuration if authorized
 * 3. Return 204 No Content if not authorized (silent failure)
 *
 * Security: This endpoint is designed to be called from cached pages.
 * It performs full authorization checks on every request.
 */
class BootstrapController extends Controller
{
    /**
     * Allow anonymous access - we validate inside the action
     */
    protected array|int|bool $allowAnonymous = ['index'];

    /**
     * Disable CSRF for bootstrap request (cached pages won't have valid token)
     * A fresh CSRF token is returned in the response for subsequent requests
     */
    public $enableCsrfValidation = false;

    /**
     * Bootstrap endpoint
     *
     * Returns JSON config if authorized, 204 No Content if not.
     * Silent failure ensures no information leakage on cached pages.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $plugin = Launcher::getInstance();
        $settings = $plugin->getSettings();

        // Security: Check if front-end is enabled at admin level
        if ($settings->frontEndDeployment === 'disabled') {
            return $this->silentDeny();
        }

        // Security: User must be authenticated
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return $this->silentDeny();
        }

        // Security: User must have launcher permission
        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            return $this->silentDeny();
        }

        // Security: User must have front-end preference enabled
        if (!$plugin->userPreference->isFrontEndEnabled()) {
            return $this->silentDeny();
        }

        // Security: Skip suspicious requests (empty user agent, etc.)
        $request = Craft::$app->getRequest();
        if (empty($request->getUserAgent())) {
            return $this->silentDeny();
        }

        // Rate limiting: Prevent abuse (60 requests per minute per user)
        if (!$this->checkRateLimit($user->id)) {
            return $this->asJson([
                'success' => false,
                'error' => 'Rate limit exceeded',
            ])->setStatusCode(429);
        }

        // Build and return configuration
        return $this->asJson([
            'success' => true,
            'config' => $this->buildConfig($plugin, $settings),
            'assets' => $this->getAssetUrls(),
        ]);
    }

    /**
     * Return a silent denial (204 No Content)
     *
     * This response gives no indication that the launcher exists,
     * which is important for security on cached pages.
     *
     * @return Response
     */
    protected function silentDeny(): Response
    {
        $response = Craft::$app->getResponse();
        $response->setStatusCode(204);
        $response->content = '';
        return $response;
    }

    /**
     * Check rate limit for bootstrap requests
     *
     * @param int $userId
     * @return bool True if within limit, false if exceeded
     */
    protected function checkRateLimit(int $userId): bool
    {
        $cacheKey = 'launcher_bootstrap_rate_' . $userId;
        $cache = Craft::$app->getCache();

        $requests = $cache->get($cacheKey) ?: [];
        $now = time();

        // Remove requests older than 1 minute
        $requests = array_filter($requests, function($timestamp) use ($now) {
            return ($now - $timestamp) < 60;
        });

        // Check if user has exceeded limit (60 requests per minute)
        // This is generous since bootstrap only runs once per page load
        if (count($requests) >= 60) {
            return false;
        }

        // Add current request
        $requests[] = $now;
        $cache->set($cacheKey, $requests, 120); // Cache for 2 minutes

        return true;
    }

    /**
     * Build launcher configuration for front-end
     *
     * @param Launcher $plugin
     * @param mixed $settings
     * @return array
     */
    protected function buildConfig(Launcher $plugin, $settings): array
    {
        $request = Craft::$app->getRequest();

        return [
            'hotkey' => $settings->hotkey,
            'searchUrl' => UrlHelper::actionUrl('launcher/search'),
            'navigateUrl' => UrlHelper::actionUrl('launcher/search/navigate'),
            'removeHistoryUrl' => UrlHelper::actionUrl('launcher/search/remove-history-item'),
            'executeIntegrationUrl' => UrlHelper::actionUrl('launcher/search/execute-integration'),
            'setFiltersUrl' => UrlHelper::actionUrl('launcher/user-preference/set-search-filters'),
            'drawerContentUrl' => UrlHelper::actionUrl('launcher/search/drawer-content'),
            'csrfTokenName' => $request->csrfParam,
            'csrfTokenValue' => $request->getCsrfToken(),
            'debounceDelay' => $settings->debounceDelay,
            'selectResultModifier' => $settings->selectResultModifier,
            'searchableTypes' => $settings->searchableTypes,
            'isFrontEnd' => true,
            'openInNewTab' => $plugin->userPreference->isFrontEndNewTabEnabled(),
            'searchFilters' => $plugin->userPreference->getSearchFilters(),
            'availableFilterOptions' => $plugin->userPreference->getAvailableFilterOptions(),
            'allSections' => $plugin->getAllSectionsForFilter(),
            'allEntryTypes' => $plugin->getAllEntryTypesForFilter(),
        ];
    }

    /**
     * Get asset URLs for dynamic loading
     *
     * @return array
     */
    protected function getAssetUrls(): array
    {
        $assetUrl = Craft::$app->getAssetManager()->getPublishedUrl(
            '@brilliance/launcher/assetbundles/launcher/dist',
            true
        );

        return [
            'baseUrl' => $assetUrl,
            'js' => $assetUrl . '/js/launcher.js',
            'css' => $assetUrl . '/css/launcher.css',
        ];
    }
}
