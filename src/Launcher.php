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
use craft\events\RegisterUserPermissionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\console\Application as ConsoleApplication;
use craft\services\Utilities;
use craft\services\ProjectConfig;
use craft\services\UserPermissions;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

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
                    if (Craft::$app->getUser()->checkPermission('accessLauncher')) {
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
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl',
                                            selectResultModifier: '{$settings->selectResultModifier}',
                                            searchableTypes: $searchableTypesJson,
                                            addons: $addonsJson,
                                            addonHotkeys: $addonHotkeysJson,
                                            modalTabs: $modalTabsJson
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
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl',
                                            selectResultModifier: '{$settings->selectResultModifier}',
                                            searchableTypes: $searchableTypesJson,
                                            addons: $addonsJson,
                                            addonHotkeys: $addonHotkeysJson,
                                            modalTabs: $modalTabsJson
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

        // Handle front-end launcher injection
        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function ($event) {
                    $this->injectFrontEndLauncher($event);
                }
            );
        }

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

        // Register our screen in the user edit screens
        Event::on(
            UsersController::class,
            UsersController::EVENT_DEFINE_EDIT_SCREENS,
            function (DefineEditUserScreensEvent $event) {
                $user = Craft::$app->getUser()->getIdentity();
                if ($user && Craft::$app->getUser()->checkPermission('accessLauncher')) {
                    $event->screens['launcher'] = [
                        'label' => 'Launcher',
                        'url' => 'myaccount/launcher',
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

                // User preferences
                $event->rules['myaccount/launcher'] = 'launcher/user-account/index';

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
                        <strong>🚀 Launcher Plugin:</strong>
                        <a href="/admin/myaccount/launcher" style="color: #1976d2; text-decoration: underline;">
                            Configure your Launcher preferences
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
     * Inject launcher assets on the front-end if user has enabled it
     */
    protected function injectFrontEndLauncher($event = null): void
    {
        // Security check: Only inject if user is authenticated
        if (!Craft::$app->getUser()->getIdentity()) {
            return;
        }

        // Security check: Verify user has launcher permissions
        if (!Craft::$app->getUser()->checkPermission('accessLauncher')) {
            return;
        }

        // Only inject if user is logged in and has preference enabled
        if (!$this->userPreference->isFrontEndEnabled()) {
            return;
        }

        // Skip in Live Preview mode to avoid conflicts
        if (Craft::$app->getRequest()->getIsLivePreview()) {
            return;
        }

        // Security check: Skip if request contains suspicious patterns
        $request = Craft::$app->getRequest();
        $userAgent = $request->getUserAgent();
        if (empty($userAgent) || $this->isSuspiciousRequest($request)) {
            return;
        }

        // Register the launcher assets (front-end version without CP dependencies)
        Craft::$app->getView()->registerAssetBundle(LauncherFrontEndAsset::class);

        $settings = $this->getSettings();
        $hotkey = $settings->hotkey;
        $assetUrl = Craft::$app->getAssetManager()->getPublishedUrl('@brilliance/launcher/assetbundles/launcher/dist');
        $searchUrl = Craft::$app->getUrlManager()->createUrl(['launcher/search']);
        $navigateUrl = Craft::$app->getUrlManager()->createUrl(['launcher/search/navigate']);
        $removeHistoryUrl = Craft::$app->getUrlManager()->createUrl(['launcher/search/remove-history-item']);
        $executeIntegrationUrl = Craft::$app->getUrlManager()->createUrl(['launcher/search/execute-integration']);

        // Get CSRF token details
        $request = Craft::$app->getRequest();
        $csrfTokenName = $request->csrfParam;
        $csrfTokenValue = $request->getCsrfToken();

        // Get user preferences
        $openInNewTab = $this->userPreference->isFrontEndNewTabEnabled();

        // Get current entry context if available
        $context = $this->getFrontEndContext($event);
        $contextJson = json_encode($context);

        // Convert boolean to string for JavaScript
        $openInNewTabJs = $openInNewTab ? 'true' : 'false';
        $searchableTypesJson = json_encode($settings->searchableTypes);

        $js = <<<JS
        // Front-end Launcher initialization
        document.addEventListener('DOMContentLoaded', function() {
            if (window.LauncherPlugin) {
                window.LauncherPlugin.init({
                    hotkey: '$hotkey',
                    searchUrl: '$searchUrl',
                    navigateUrl: '$navigateUrl',
                    removeHistoryUrl: '$removeHistoryUrl',
                    executeIntegrationUrl: '$executeIntegrationUrl',
                    csrfTokenName: '$csrfTokenName',
                    csrfTokenValue: '$csrfTokenValue',
                    debounceDelay: {$settings->debounceDelay},
                    assetUrl: '$assetUrl',
                    selectResultModifier: '{$settings->selectResultModifier}',
                    searchableTypes: $searchableTypesJson,
                    isFrontEnd: true,
                    openInNewTab: $openInNewTabJs,
                    frontEndContext: $contextJson
                });
            }
        });
        JS;

        Craft::$app->getView()->registerJs($js, View::POS_END);
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
        Craft::warning('Launcher plugin removal via project config is not supported.', __METHOD__);
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();

        if ($item === null) {
            return null;
        }

        $item['label'] = 'Launcher';
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

    public function getUserPermissions(): array
    {
        return [
            'accessLauncher' => ['label' => 'Access Launcher'],
        ];
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
                'The Launcher user history table is missing. Launch history and popular items features will not work. ' .
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

            Craft::info('Launcher user history table created successfully', __METHOD__);
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
                'Update Launcher plugin settings'
            );
        }
    }

    /**
     * Register the default Brilliance drawer content provider
     */
    private function registerDefaultDrawerProvider(): void
    {
        $this->drawer->registerProvider('brilliance', function($context) {
            $baseContent = [
                'title' => $context === 'assistant' ? 'AI Assistant Tips' : 'Launcher Tips',
                'sections' => [
                    [
                        'title' => 'Quick Tips',
                        'items' => $context === 'assistant' ? [
                            'Ask in natural language - the AI understands context',
                            'Request drafts - content is created for review, never auto-published',
                            'Use specific section names when creating content'
                        ] : [
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