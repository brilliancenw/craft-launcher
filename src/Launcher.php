<?php
namespace brilliance\launcher;

use brilliance\launcher\assetbundles\launcher\LauncherAsset;
use brilliance\launcher\assetbundles\launcher\LauncherFrontEndAsset;
use brilliance\launcher\models\Settings;
use brilliance\launcher\services\LauncherService;
use brilliance\launcher\services\SearchService;
use brilliance\launcher\services\HistoryService;
use brilliance\launcher\services\UserPreferenceService;
use brilliance\launcher\services\InterfaceService;
use brilliance\launcher\services\IntegrationService;
use brilliance\launcher\services\AddonService;
use brilliance\launcher\services\DrawerService;
use brilliance\launcher\utilities\LauncherTableUtility;
use brilliance\launcher\variables\LauncherVariable;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\controllers\UsersController;
use craft\events\ConfigEvent;
use craft\events\DefineEditUserScreensEvent;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\console\Application as ConsoleApplication;
use craft\services\Utilities;
use craft\services\ProjectConfig;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use craft\helpers\UrlHelper;
use yii\base\Event;
use yii\web\Response;

class Launcher extends Plugin
{
    public static ?Launcher $plugin = null;
    public string $schemaVersion = '1.2.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public static function config(): array
    {
        return [
            'components' => [
                'launcher' => LauncherService::class,
                'search' => SearchService::class,
                'history' => HistoryService::class,
                'userPreference' => UserPreferenceService::class,
                'interface' => InterfaceService::class,
                'integration' => IntegrationService::class,
                'addon' => AddonService::class,
                'drawer' => DrawerService::class,
            ],
        ];
    }

    public function getIconPath(): ?string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . 'icon.svg';
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Debug logging can be enabled by uncommenting the line below
        // Craft::info('[Launcher] Plugin init() called - request type: ' . (Craft::$app->getRequest()->getIsConsoleRequest() ? 'console' : 'web'), __METHOD__);

        $this->setComponents([
            'launcher' => LauncherService::class,
            'search' => SearchService::class,
            'history' => HistoryService::class,
            'userPreference' => UserPreferenceService::class,
            'interface' => InterfaceService::class,
            'integration' => IntegrationService::class,
            'addon' => AddonService::class,
            'drawer' => DrawerService::class,
        ]);

        // Handle project config changes
        $this->attachProjectConfigEventListeners();

        // Register default Brilliance drawer content provider
        $this->registerDefaultDrawerProvider();

        // Register console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'brilliance\\launcher\\console\\controllers';
        }

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function () {
                    if (Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
                        Craft::$app->getView()->registerAssetBundle(LauncherAsset::class);

                        $settings = $this->getSettings();
                        $hotkey = $settings->hotkey;
                        $assetUrl = Craft::$app->getAssetManager()->getPublishedUrl('@brilliance/launcher/assetbundles/launcher/dist');

                        $searchableTypesJson = json_encode($settings->searchableTypes);

                        // Get registered addons, hotkeys, and modal tabs
                        $addons = $this->addon->getRegisteredAddons();
                        $addonHotkeys = $this->addon->getRegisteredHotkeys();
                        $modalTabs = $this->addon->getModalTabs();
                        $addonsJson = json_encode($addons);
                        $addonHotkeysJson = json_encode($addonHotkeys);
                        $modalTabsJson = json_encode($modalTabs);

                        // Get filter configuration for frontend
                        $userFilters = $this->userPreference->getSearchFilters();
                        $availableFilterOptions = $this->userPreference->getAvailableFilterOptions();
                        $allSections = $this->getAllSectionsForFilter();
                        $allEntryTypes = $this->getAllEntryTypesForFilter();

                        $userFiltersJson = json_encode($userFilters);
                        $availableFilterOptionsJson = json_encode($availableFilterOptions);
                        $allSectionsJson = json_encode($allSections);
                        $allEntryTypesJson = json_encode($allEntryTypes);

                        $js = <<<JS
                        // Ensure LauncherPlugin initialization happens after DOM and Craft are ready
                        if (typeof Craft !== 'undefined') {
                            // Use Craft's ready handler if available
                            if (Craft.cp && Craft.cp.ready) {
                                Craft.cp.ready(function() {
                                    if (window.LauncherPlugin) {
                                        window.LauncherPlugin.init({
                                            hotkey: '$hotkey',
                                            searchUrl: Craft.getActionUrl('launcher/search'),
                                            drawerContentUrl: Craft.getActionUrl('launcher/search/drawer-content'),
                                            setFiltersUrl: Craft.getActionUrl('launcher/user-preference/set-search-filters'),
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl',
                                            selectResultModifier: '{$settings->selectResultModifier}',
                                            searchableTypes: $searchableTypesJson,
                                            addons: $addonsJson,
                                            addonHotkeys: $addonHotkeysJson,
                                            modalTabs: $modalTabsJson,
                                            searchFilters: $userFiltersJson,
                                            availableFilterOptions: $availableFilterOptionsJson,
                                            allSections: $allSectionsJson,
                                            allEntryTypes: $allEntryTypesJson
                                        });
                                    }
                                });
                            } else {
                                // Fallback for pages where Craft.cp might not exist
                                document.addEventListener('DOMContentLoaded', function() {
                                    if (window.LauncherPlugin) {
                                        window.LauncherPlugin.init({
                                            hotkey: '$hotkey',
                                            searchUrl: Craft.getActionUrl('launcher/search'),
                                            drawerContentUrl: Craft.getActionUrl('launcher/search/drawer-content'),
                                            setFiltersUrl: Craft.getActionUrl('launcher/user-preference/set-search-filters'),
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl',
                                            selectResultModifier: '{$settings->selectResultModifier}',
                                            searchableTypes: $searchableTypesJson,
                                            addons: $addonsJson,
                                            addonHotkeys: $addonHotkeysJson,
                                            modalTabs: $modalTabsJson,
                                            searchFilters: $userFiltersJson,
                                            availableFilterOptions: $availableFilterOptionsJson,
                                            allSections: $allSectionsJson,
                                            allEntryTypes: $allEntryTypesJson
                                        });
                                    }
                                });
                            }
                        }
                        JS;

                        Craft::$app->getView()->registerJs($js, View::POS_END);
                    }
                }
            );
        }

        // Handle front-end launcher injection using Response::EVENT_BEFORE_SEND
        // This event fires before the response is sent, reliable for front-end pages
        Event::on(
            Response::class,
            Response::EVENT_BEFORE_SEND,
            function ($event) {
                $request = Craft::$app->getRequest();
                $response = $event->sender;

                // Only inject on front-end HTML requests
                if ($request->getIsCpRequest() || $request->getIsConsoleRequest()) {
                    return;
                }

                // Check if this is an HTML response
                // Content-type may not be set yet, so also check response format and content
                $contentType = $response->getHeaders()->get('content-type');
                $isHtml = $contentType && strpos($contentType, 'text/html') !== false;

                // If content-type not set, check if response format is HTML or content contains HTML
                if (!$isHtml) {
                    $format = $response->format;
                    $isHtml = ($format === Response::FORMAT_HTML);

                    // If still unsure, check if content looks like HTML
                    if (!$isHtml && is_string($response->content)) {
                        $isHtml = (stripos($response->content, '<!DOCTYPE html') !== false ||
                                   stripos($response->content, '<html') !== false);
                    }
                }

                if (!$isHtml) {
                    return;
                }

                $this->injectFrontEndLauncherToResponse($response);
            }
        );

        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $e) {
                $e->roots[$this->id] = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates';
            }
        );

        // Register Twig variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('launcher', LauncherVariable::class);
            }
        );

        // Register utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LauncherTableUtility::class;
            }
        );

        // Note: User permission 'accessPlugin-launcher' is automatically registered by Craft
        // for plugins with hasCpSection = true. We use this permission for all access checks.

        // Register our screen in the user edit screens
        Event::on(
            UsersController::class,
            UsersController::EVENT_DEFINE_EDIT_SCREENS,
            function (DefineEditUserScreensEvent $event) {
                $currentUser = $event->currentUser;
                $editedUser = $event->editedUser;

                // Show the screen if:
                // 1. Viewing your own account AND you have launcher permission, OR
                // 2. You're an admin viewing any user who has launcher permission
                $isOwnAccount = $currentUser->id === $editedUser->id;
                $currentUserHasPermission = Craft::$app->getUser()->checkPermission('accessPlugin-launcher');
                $editedUserHasPermission = Craft::$app->getUserPermissions()->doesUserHavePermission($editedUser->id, 'accessPlugin-launcher');

                $shouldShowScreen = false;
                if ($isOwnAccount && $currentUserHasPermission) {
                    $shouldShowScreen = true;
                } elseif ($currentUser->admin && $editedUserHasPermission) {
                    $shouldShowScreen = true;
                }

                if ($shouldShowScreen) {
                    $url = $isOwnAccount ? 'myaccount/launcher' : 'users/' . $editedUser->id . '/launcher';

                    $event->screens['launcher'] = [
                        'label' => 'Rocket Launcher',
                        'url' => $url,
                    ];
                }
            }
        );

        // Register URL rules for user preferences, settings, and CP section
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                // CP section
                $event->rules['launcher'] = 'launcher/admin/index';

                // User preferences (own account)
                $event->rules['myaccount/launcher'] = 'launcher/user-account/index';

                // User preferences (viewing another user - admins only)
                $event->rules['users/<userId:\\d+>/launcher'] = 'launcher/user-account/view-user';

                // Settings
                $event->rules['launcher/settings/complete-first-run'] = 'launcher/settings/complete-first-run';
            }
        );

        // Add a link to launcher preferences on the main preferences page
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            function ($event) {
                $request = Craft::$app->getRequest();
                if ($request->getIsCpRequest() &&
                    $request->getSegment(1) === 'myaccount' &&
                    $request->getSegment(2) === 'preferences') {

                    $html = '<div style="background: #e3f2fd; border: 1px solid #2196f3; border-radius: 4px; padding: 12px; margin-bottom: 20px;">
                        <strong>Rocket Launcher Plugin:</strong>
                        <a href="/admin/myaccount/launcher" style="color: #1976d2; text-decoration: underline;">
                            Configure your Rocket Launcher preferences
                        </a>
                        - Enable the front-end launcher and customize your settings.
                    </div>';

                    Craft::$app->getView()->registerHtml($html);
                }
            }
        );

        Craft::info(
            Craft::t(
                'launcher',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * Inject launcher bootstrap script on the front-end
     *
     * This method injects a minimal bootstrap script that calls an action endpoint
     * to check authorization. This approach is compatible with full-page caching
     * solutions (Varnish, Blitz, Cloudflare, etc.) because no user-specific data
     * is embedded in the cached page.
     *
     * The bootstrap script:
     * 1. Calls /actions/launcher/bootstrap
     * 2. If authorized, loads the full launcher assets
     * 3. If not authorized, silently does nothing
     *
     * @param mixed $event The template render event (may contain template variables)
     */
    protected function injectFrontEndLauncher($event = null): void
    {
        $settings = $this->getSettings();

        // Only auto-inject if deployment method is 'auto'
        // 'disabled' = no front-end at all
        // 'twig' = only via {{ craft.launcher.bootstrap() }}
        // 'auto' = inject on all front-end pages
        if ($settings->frontEndDeployment !== 'auto') {
            return;
        }

        // Skip in Live Preview mode to avoid conflicts
        if (Craft::$app->getRequest()->getIsLivePreview()) {
            return;
        }

        // Inject the bootstrap script
        $this->injectBootstrapScript($event);
    }

    /**
     * Inject bootstrap script into Response body
     *
     * @param Response $response The response object
     */
    protected function injectFrontEndLauncherToResponse($response): void
    {
        $settings = $this->getSettings();

        // Only auto-inject if deployment method is 'auto'
        if ($settings->frontEndDeployment !== 'auto') {
            return;
        }

        // Skip in Live Preview mode
        if (Craft::$app->getRequest()->getIsLivePreview()) {
            return;
        }

        $content = $response->content;
        if (empty($content) || !is_string($content)) {
            return;
        }

        // Check if content has </body> tag
        if (stripos($content, '</body>') === false) {
            return;
        }

        // Build the bootstrap script
        $bootstrapUrl = UrlHelper::actionUrl('launcher/bootstrap');

        // Explicitly publish assets to ensure they exist
        $assetManager = Craft::$app->getAssetManager();
        $sourcePath = Craft::getAlias('@brilliance/launcher/assetbundles/launcher/dist');

        // Debug: Log the source path
        Craft::info('[Launcher] Source path: ' . $sourcePath, 'launcher');
        Craft::info('[Launcher] Source exists: ' . (is_dir($sourcePath) ? 'YES' : 'NO'), 'launcher');

        // Publish returns [publishedPath, publishedUrl]
        $published = $assetManager->publish($sourcePath);

        // Debug: Log publish result
        Craft::info('[Launcher] Publish result: ' . json_encode($published), 'launcher');

        if (!$published || empty($published[1])) {
            // If publishing fails, try getPublishedUrl as fallback
            Craft::warning('[Launcher] Primary publish failed, trying fallback', 'launcher');
            $assetUrl = $assetManager->getPublishedUrl($sourcePath, true);
            if (!$assetUrl) {
                Craft::error('[Launcher] All asset publishing methods failed', 'launcher');
                return;
            }
        } else {
            $assetUrl = $published[1];
        }

        $bootstrapScriptUrl = $assetUrl . '/js/launcher-bootstrap.js';

        // Debug: Log final URLs
        Craft::info('[Launcher] Bootstrap URL: ' . $bootstrapUrl, 'launcher');
        Craft::info('[Launcher] Script URL: ' . $bootstrapScriptUrl, 'launcher');

        // Verify the file actually exists
        $publishedPath = $published[0] ?? null;
        if ($publishedPath) {
            $scriptFile = $publishedPath . '/js/launcher-bootstrap.js';
            Craft::info('[Launcher] Script file exists: ' . (file_exists($scriptFile) ? 'YES' : 'NO'), 'launcher');
        }

        $script = <<<HTML
<!-- Rocket Launcher Bootstrap -->
<script src="{$bootstrapScriptUrl}" data-bootstrap-url="{$bootstrapUrl}" defer></script>
HTML;

        // Inject before </body>
        $content = str_ireplace('</body>', $script . '</body>', $content);
        $response->content = $content;
    }

    /**
     * Inject the bootstrap script tag
     *
     * This injects a minimal script that calls the bootstrap endpoint.
     * The endpoint performs all authorization checks and returns the
     * full launcher configuration if authorized.
     *
     * @param mixed $event The template render event
     */
    protected function injectBootstrapScript($event = null): void
    {
        $bootstrapUrl = UrlHelper::actionUrl('launcher/bootstrap');

        // Explicitly publish assets to ensure they exist
        $assetManager = Craft::$app->getAssetManager();
        $sourcePath = Craft::getAlias('@brilliance/launcher/assetbundles/launcher/dist');

        // Publish returns [publishedPath, publishedUrl]
        $published = $assetManager->publish($sourcePath);
        if (!$published || empty($published[1])) {
            // If publishing fails, try getPublishedUrl as fallback
            $assetUrl = $assetManager->getPublishedUrl($sourcePath, true);
            if (!$assetUrl) {
                return;
            }
        } else {
            $assetUrl = $published[1];
        }

        $bootstrapScriptUrl = $assetUrl . '/js/launcher-bootstrap.js';

        // Build context if available from template variables
        $contextJson = '{}';
        if ($event && !empty($event->variables)) {
            $context = $this->getFrontEndContext($event);
            if (!empty($context)) {
                $contextJson = json_encode($context);
            }
        }

        $view = Craft::$app->getView();

        // Register context as a JSON script tag (read by bootstrap script)
        $contextHtml = '<script type="application/json" id="launcher-context">' . $contextJson . '</script>';
        $view->registerHtml($contextHtml, View::POS_END);

        // Register the bootstrap script directly with defer for reliable loading
        $scriptHtml = '<script src="' . $bootstrapScriptUrl . '" data-bootstrap-url="' . $bootstrapUrl . '" defer></script>';
        $view->registerHtml($scriptHtml, View::POS_END);
    }

    /**
     * Get context data for front-end launcher
     */
    protected function getFrontEndContext($event = null): array
    {
        $context = [];

        // Try to get current element context from template variables first (more reliable)
        $templateVars = $event->variables ?? [];

        // Check for Entry/Single in template variables
        $entry = $templateVars['entry'] ?? null;
        if ($entry && isset($entry->id)) {
            $context['currentElement'] = [
                'id' => $entry->id,
                'title' => $entry->title,
                'type' => 'Entry',
                'section' => $entry->section->handle ?? null,
                'editUrl' => $entry->getCpEditUrl()
            ];
        }

        // Check for Category in template variables
        if (!isset($context['currentElement'])) {
            $category = $templateVars['category'] ?? null;
            if ($category && isset($category->id)) {
                $context['currentElement'] = [
                    'id' => $category->id,
                    'title' => $category->title,
                    'type' => 'Category',
                    'group' => $category->group->handle ?? null,
                    'editUrl' => $category->getCpEditUrl()
                ];
            }
        }

        // Check for Asset in template variables
        if (!isset($context['currentElement'])) {
            $asset = $templateVars['asset'] ?? null;
            if ($asset && isset($asset->id)) {
                $context['currentElement'] = [
                    'id' => $asset->id,
                    'title' => $asset->title ?: $asset->filename,
                    'type' => 'Asset',
                    'volume' => $asset->volume->handle ?? null,
                    'editUrl' => $asset->getCpEditUrl()
                ];
            }
        }

        // Check for User in template variables
        if (!isset($context['currentElement'])) {
            $user = $templateVars['user'] ?? null;
            if ($user && isset($user->id) && $user->id != Craft::$app->getUser()->getId()) {
                $context['currentElement'] = [
                    'id' => $user->id,
                    'title' => $user->fullName ?: $user->username,
                    'type' => 'User',
                    'editUrl' => $user->getCpEditUrl()
                ];
            }
        }

        // Check for Commerce Product in template variables (if Commerce is installed)
        if (!isset($context['currentElement']) && class_exists('craft\commerce\elements\Product')) {
            $product = $templateVars['product'] ?? null;
            if ($product && isset($product->id)) {
                $context['currentElement'] = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'type' => 'Product',
                    'editUrl' => $product->getCpEditUrl()
                ];
            }
        }

        // Check for Global Set in template variables
        if (!isset($context['currentElement'])) {
            $globalSet = $templateVars['globalSet'] ?? null;
            if ($globalSet && isset($globalSet->id)) {
                $context['currentElement'] = [
                    'id' => $globalSet->id,
                    'title' => $globalSet->name,
                    'type' => 'Global',
                    'editUrl' => $globalSet->getCpEditUrl()
                ];
            }
        }

        // Fallback: Try to get current element context from Twig globals (less reliable timing)
        if (!isset($context['currentElement']) && Craft::$app->has('elements')) {
            $twigGlobals = Craft::$app->getView()->getTwig()->getGlobals();

            // Check for Entry/Single
            $entry = $twigGlobals['entry'] ?? null;
            if ($entry && isset($entry->id)) {
                $context['currentElement'] = [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'type' => 'Entry',
                    'sectionHandle' => $entry->section->handle ?? null,
                    'typeHandle' => $entry->type->handle ?? null,
                    'editUrl' => $entry->getCpEditUrl()
                ];
            }

            // Check for Category
            if (!isset($context['currentElement'])) {
                $category = $twigGlobals['category'] ?? null;
                if ($category && isset($category->id)) {
                    $context['currentElement'] = [
                        'id' => $category->id,
                        'title' => $category->title,
                        'type' => 'Category',
                        'groupHandle' => $category->group->handle ?? null,
                        'editUrl' => $category->getCpEditUrl()
                    ];
                }
            }

            // Check for Asset
            if (!isset($context['currentElement'])) {
                $asset = $twigGlobals['asset'] ?? null;
                if ($asset && isset($asset->id)) {
                    $context['currentElement'] = [
                        'id' => $asset->id,
                        'title' => $asset->title ?: $asset->filename,
                        'type' => 'Asset',
                        'volumeHandle' => $asset->volume->handle ?? null,
                        'editUrl' => $asset->getCpEditUrl()
                    ];
                }
            }

            // Check for User
            if (!isset($context['currentElement'])) {
                $user = $twigGlobals['user'] ?? null;
                if ($user && isset($user->id) && $user->id != Craft::$app->getUser()->getId()) {
                    $context['currentElement'] = [
                        'id' => $user->id,
                        'title' => $user->fullName ?: $user->username,
                        'type' => 'User',
                        'editUrl' => $user->getCpEditUrl()
                    ];
                }
            }

            // Check for Commerce Product (if Commerce is installed)
            if (!isset($context['currentElement']) && class_exists('craft\commerce\elements\Product')) {
                $product = $twigGlobals['product'] ?? null;
                if ($product && isset($product->id)) {
                    $context['currentElement'] = [
                        'id' => $product->id,
                        'title' => $product->title,
                        'type' => 'Product',
                        'typeHandle' => $product->type->handle ?? null,
                        'editUrl' => $product->getCpEditUrl()
                    ];
                }
            }

            // Check for Global Set
            if (!isset($context['currentElement'])) {
                $globalSet = $twigGlobals['globalSet'] ?? null;
                if ($globalSet && isset($globalSet->id)) {
                    $context['currentElement'] = [
                        'id' => $globalSet->id,
                        'title' => $globalSet->name,
                        'type' => 'GlobalSet',
                        'handle' => $globalSet->handle,
                        'editUrl' => $globalSet->getCpEditUrl()
                    ];
                }
            }
        }

        return $context;
    }

    /**
     * Check if the request looks suspicious (basic security check)
     */
    protected function isSuspiciousRequest($request): bool
    {
        $userAgent = $request->getUserAgent();
        $suspiciousPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'wget', 'curl',
            'automated', 'test', 'monitor', 'scan'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        // Check for suspicious request patterns
        $uri = $request->getUrl();
        if (preg_match('/\.(xml|json|rss|feed)$/i', $uri)) {
            return true;
        }

        return false;
    }

    /**
     * Attaches event listeners for project config synchronization
     */
    protected function attachProjectConfigEventListeners(): void
    {
        // Listen to project config rebuild event
        Event::on(
            ProjectConfig::class,
            ProjectConfig::EVENT_REBUILD,
            function (RebuildConfigEvent $event) {
                $event->config['plugins']['launcher'] = [
                    'edition' => $this->edition,
                    'enabled' => $this->isInstalled,
                    'schemaVersion' => $this->schemaVersion,
                    'settings' => $this->getSettings()->toArray(),
                ];
            }
        );

        // Handle project config changes for this plugin
        Craft::$app->getProjectConfig()
            ->onAdd('plugins.launcher', [$this, 'handleProjectConfigChange'])
            ->onUpdate('plugins.launcher', [$this, 'handleProjectConfigChange'])
            ->onRemove('plugins.launcher', [$this, 'handleProjectConfigDelete']);
    }

    /**
     * Handles project config changes
     */
    public function handleProjectConfigChange(ConfigEvent $event): void
    {
        // Ensure the plugin is installed
        if (!$this->isInstalled) {
            return;
        }

        // Extract data from the event
        $data = $event->newValue ?? [];

        // Update the plugin settings internally without triggering project config writes
        // The project config system handles persistence, this handler is for side effects only
        if (isset($data['settings']) && is_array($data['settings'])) {
            $settings = new Settings($data['settings']);

            // Call parent setSettings to update internal state without writing to project config
            parent::setSettings($settings->toArray());
        }
    }

    /**
     * Handles project config deletion
     */
    public function handleProjectConfigDelete(): void
    {
        // This would handle plugin removal via project config
        // but typically plugins are managed separately from project config
        Craft::warning('Rocket Launcher plugin removal via project config is not supported.', __METHOD__);
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();

        if ($item === null) {
            return null;
        }

        $item['label'] = 'Rocket Launcher';
        $item['url'] = 'launcher';

        // Start with core launcher subnav items
        $item['subnav'] = [];

        // Add addon plugin subnav items
        $addonNavItems = $this->addon->getCpNavItems();
        foreach ($addonNavItems as $key => $navItem) {
            $item['subnav'][$key] = $navItem;
        }

        return $item;
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        // Check table status and show notification if needed
        $tableStatus = $this->history->getTableStatus();
        if (!$tableStatus['exists']) {
            Craft::$app->getSession()->setFlash('launcher-table-missing',
                'The Rocket Launcher user history table is missing. Launch history and popular items features will not work. ' .
                'Try reinstalling the plugin or contact your developer.'
            );
        }

        return Craft::$app->getView()->renderTemplate(
            'launcher/settings',
            [
                'settings' => $this->getSettings(),
                'firstRunCompleted' => $this->interface->isFirstRunCompleted()
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function install(): void
    {
        parent::install();
    }

    /**
     * @inheritdoc
     */
    public function afterInstall(): void
    {
        parent::afterInstall();

        // Ensure the user history table is created
        $this->createUserHistoryTable();
    }

    /**
     * Create the user history table if it doesn't exist (public method for manual table creation)
     */
    public function createUserHistoryTable(): bool
    {
        $db = Craft::$app->getDb();

        // Check if table already exists
        $tableSchema = $db->schema->getTableSchema('{{%launcher_user_history}}');
        if ($tableSchema !== null) {
            return true; // Table already exists
        }

        try {
            // Create the table manually
            $db->createCommand()->createTable('{{%launcher_user_history}}', [
                'id' => 'pk',
                'userId' => 'integer NOT NULL COMMENT "References craft users.id"',
                'itemType' => 'string(50) NOT NULL COMMENT "Type of item (entry, category, section, etc.)"',
                'itemId' => 'string(100) NULL COMMENT "Original item ID (if applicable)"',
                'itemTitle' => 'string(255) NOT NULL COMMENT "Display title of the item"',
                'itemUrl' => 'text NOT NULL COMMENT "URL that was launched"',
                'itemHash' => 'string(64) NOT NULL COMMENT "Hash of itemType+itemId+itemUrl for uniqueness"',
                'launchCount' => 'integer NOT NULL DEFAULT 1 COMMENT "Number of times launched"',
                'lastLaunchedAt' => 'datetime NOT NULL COMMENT "Last launch timestamp"',
                'firstLaunchedAt' => 'datetime NOT NULL COMMENT "First launch timestamp"',
                'dateCreated' => 'datetime NOT NULL',
                'dateUpdated' => 'datetime NOT NULL',
            ])->execute();

            // Create indexes
            $db->createCommand()->createIndex(
                'idx_user_launches',
                '{{%launcher_user_history}}',
                ['userId', 'launchCount', 'lastLaunchedAt'],
                false
            )->execute();

            // Create unique constraint
            $db->createCommand()->createIndex(
                'uk_user_item',
                '{{%launcher_user_history}}',
                ['userId', 'itemHash'],
                true
            )->execute();

            // Add foreign key constraint
            $db->createCommand()->addForeignKey(
                'fk_launcher_history_user',
                '{{%launcher_user_history}}',
                'userId',
                '{{%users}}',
                'id',
                'CASCADE',
                'CASCADE'
            )->execute();

            Craft::info('Rocket Launcher user history table created successfully', __METHOD__);
            return true;

        } catch (\Exception $e) {
            Craft::error('Failed to create launcher user history table: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function setSettings(array $settings): void
    {
        parent::setSettings($settings);

        // Save settings to project config when they change
        $projectConfig = Craft::$app->getProjectConfig();
        if (Craft::$app->getIsInstalled()
            && !$projectConfig->getIsApplyingExternalChanges()
            && !$projectConfig->readOnly) {
            $pluginHandle = $this->handle;

            // Update the project config with the new settings
            $projectConfig->set(
                "plugins.{$pluginHandle}.settings",
                $this->getSettings()->toArray(),
                'Update Rocket Launcher plugin settings'
            );
        }
    }

    /**
     * Get all sections for the filter panel
     */
    public function getAllSectionsForFilter(): array
    {
        $sections = Craft::$app->getEntries()->getAllSections();
        $result = [];

        foreach ($sections as $section) {
            // Check if user can view entries in this section
            if (!Craft::$app->getUser()->getIsAdmin()) {
                if (!Craft::$app->getUser()->checkPermission('viewEntries:' . $section->uid)) {
                    continue;
                }
            }

            $result[] = [
                'id' => $section->id,
                'name' => $section->name,
                'handle' => $section->handle,
            ];
        }

        return $result;
    }

    /**
     * Get all entry types for the filter panel
     */
    public function getAllEntryTypesForFilter(): array
    {
        $sections = Craft::$app->getEntries()->getAllSections();
        $result = [];

        foreach ($sections as $section) {
            // Check if user can view entries in this section
            if (!Craft::$app->getUser()->getIsAdmin()) {
                if (!Craft::$app->getUser()->checkPermission('viewEntries:' . $section->uid)) {
                    continue;
                }
            }

            foreach ($section->getEntryTypes() as $entryType) {
                $result[] = [
                    'id' => $entryType->id,
                    'name' => $entryType->name,
                    'handle' => $entryType->handle,
                    'sectionId' => $section->id,
                    'sectionName' => $section->name,
                ];
            }
        }

        return $result;
    }

    /**
     * Register the default Brilliance drawer content provider
     */
    private function registerDefaultDrawerProvider(): void
    {
        $this->drawer->registerProvider('brilliance', function($context) {
            // Don't provide default content for assistant context - let Astronaut handle it
            if ($context === 'assistant') {
                return null;
            }

            $baseContent = [
                'title' => 'Rocket Launcher Tips',
                'sections' => [
                    [
                        'title' => 'Quick Tips',
                        'items' => [
                            'Press * to browse content types',
                            'Use keyboard numbers (1-9) to quickly select results',
                            'Search works across entries, categories, assets, and more'
                        ]
                    ],
                    [
                        'title' => 'Resources',
                        'links' => [
                            [
                                'text' => 'Leave a Review',
                                'url' => 'https://plugins.craftcms.com/launcher',
                                'icon' => 'star'
                            ],
                            [
                                'text' => 'Feedback & Suggestions',
                                'url' => 'https://github.com/brilliancenw/craft-launcher/issues',
                                'icon' => 'message'
                            ],
                            [
                                'text' => 'Documentation',
                                'url' => 'https://github.com/brilliancenw/craft-launcher',
                                'icon' => 'book'
                            ]
                        ]
                    ]
                ]
            ];

            // Try to fetch from Brilliance feed first
            try {
                $feedUrl = "https://brilliancenw.com/launcher-feed/{$context}.json";
                $ch = curl_init($feedUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200 && $response) {
                    $feedData = json_decode($response, true);
                    if (is_array($feedData) && !empty($feedData)) {
                        // Merge feed data with base content
                        if (isset($feedData['title'])) {
                            $baseContent['title'] = $feedData['title'];
                        }
                        if (isset($feedData['sections']) && is_array($feedData['sections'])) {
                            $baseContent['sections'] = array_merge($feedData['sections'], $baseContent['sections']);
                        }
                    }
                }
            } catch (\Exception $e) {
                Craft::info("Could not fetch drawer feed: {$e->getMessage()}", __METHOD__);
            }

            return $baseContent;
        }, 100);
    }

    /**
     * Clean up user preferences on plugin uninstall
     */
    public function beforeUninstall(): void
    {
        parent::beforeUninstall();

        // Clear launcher preferences from all users
        $db = Craft::$app->getDb();

        try {
            // Remove launcher-related preferences from all users
            $db->createCommand()
                ->update(
                    '{{%users}}',
                    [
                        'preferences' => new \yii\db\Expression("JSON_REMOVE(preferences, '$.launcher_frontend_enabled', '$.launcher_frontend_new_tab')")
                    ],
                    [
                        'and',
                        ['not', ['preferences' => null]],
                        ['or',
                            'JSON_EXTRACT(preferences, "$.launcher_frontend_enabled") IS NOT NULL',
                            'JSON_EXTRACT(preferences, "$.launcher_frontend_new_tab") IS NOT NULL'
                        ]
                    ]
                )
                ->execute();

            Craft::info('Cleared launcher user preferences on uninstall', __METHOD__);
        } catch (\Exception $e) {
            Craft::warning('Failed to clear launcher user preferences on uninstall: ' . $e->getMessage(), __METHOD__);
        }
    }
}