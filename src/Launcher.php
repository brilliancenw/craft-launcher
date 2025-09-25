<?php
namespace brilliance\launcher;

use brilliance\launcher\assetbundles\launcher\LauncherAsset;
use brilliance\launcher\assetbundles\launcher\LauncherFrontEndAsset;
use brilliance\launcher\models\Settings;
use brilliance\launcher\services\LauncherService;
use brilliance\launcher\services\SearchService;
use brilliance\launcher\services\HistoryService;
use brilliance\launcher\services\UserPreferenceService;
use brilliance\launcher\utilities\LauncherTableUtility;

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
    public string $schemaVersion = '1.1.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = false;

    public static function config(): array
    {
        return [
            'components' => [
                'launcher' => LauncherService::class,
                'search' => SearchService::class,
                'history' => HistoryService::class,
                'userPreference' => UserPreferenceService::class,
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
        ]);

        // Handle project config changes
        $this->attachProjectConfigEventListeners();

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
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl',
                                            selectResultModifier: '{$settings->selectResultModifier}'
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
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl',
                                            selectResultModifier: '{$settings->selectResultModifier}'
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
                function () {
                    $this->injectFrontEndLauncher();
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

        // Register URL rules for user preferences
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['myaccount/launcher'] = 'launcher/user-account/index';
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
    protected function injectFrontEndLauncher(): void
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

        // Get CSRF token details
        $request = Craft::$app->getRequest();
        $csrfTokenName = $request->csrfParam;
        $csrfTokenValue = $request->getCsrfToken();

        // Get user preferences
        $openInNewTab = $this->userPreference->isFrontEndNewTabEnabled();

        // Get current entry context if available
        $contextScript = $this->getFrontEndContextScript();

        $js = <<<JS
        // Front-end Launcher initialization
        document.addEventListener('DOMContentLoaded', function() {
            if (window.LauncherPlugin) {
                window.LauncherPlugin.init({
                    hotkey: '$hotkey',
                    searchUrl: '$searchUrl',
                    navigateUrl: '$navigateUrl',
                    removeHistoryUrl: '$removeHistoryUrl',
                    csrfTokenName: '$csrfTokenName',
                    csrfTokenValue: '$csrfTokenValue',
                    debounceDelay: {$settings->debounceDelay},
                    assetUrl: '$assetUrl',
                    selectResultModifier: '{$settings->selectResultModifier}',
                    isFrontEnd: true,
                    openInNewTab: " . ($openInNewTab ? 'true' : 'false') . "
                });

                $contextScript
            }
        });
        JS;

        Craft::$app->getView()->registerJs($js, View::POS_END);
    }

    /**
     * Get context-aware script for front-end launcher
     */
    protected function getFrontEndContextScript(): string
    {
        $context = [];

        // Try to get current entry context
        if (Craft::$app->has('elements')) {
            $entry = Craft::$app->getView()->getTwig()->getGlobals()['entry'] ?? null;

            if ($entry && isset($entry->id)) {
                $context['currentEntry'] = [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'sectionHandle' => $entry->section->handle ?? null,
                    'typeHandle' => $entry->type->handle ?? null,
                    'editUrl' => $entry->getCpEditUrl()
                ];
            }
        }

        if (empty($context)) {
            return '';
        }

        $contextJson = json_encode($context);
        return "window.LauncherPlugin.setFrontEndContext($contextJson);";
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

        // Update the plugin settings
        if (isset($data['settings']) && is_array($data['settings'])) {
            $settings = new Settings($data['settings']);
            
            // Save the settings to the plugin
            Craft::$app->getPlugins()->savePluginSettings($this, $settings->toArray());
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
        return null;
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
                'settings' => $this->getSettings()
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
    public function createUserHistoryTable(): void
    {
        $db = Craft::$app->getDb();

        // Check if table already exists
        $tableSchema = $db->schema->getTableSchema('{{%launcher_user_history}}');
        if ($tableSchema !== null) {
            return; // Table already exists
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

        } catch (\Exception $e) {
            Craft::error('Failed to create launcher user history table: ' . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * @inheritdoc
     */
    public function setSettings(array $settings): void
    {
        parent::setSettings($settings);

        // Save settings to project config when they change
        if (Craft::$app->getIsInstalled() && !Craft::$app->getProjectConfig()->getIsApplyingExternalChanges()) {
            $projectConfig = Craft::$app->getProjectConfig();
            $pluginHandle = $this->handle;
            
            // Update the project config with the new settings
            $projectConfig->set(
                "plugins.{$pluginHandle}.settings",
                $this->getSettings()->toArray(),
                'Update Launcher plugin settings'
            );
        }
    }
}