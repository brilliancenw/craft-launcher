<?php
namespace brilliance\launcher;

use brilliance\launcher\assetbundles\launcher\LauncherAsset;
use brilliance\launcher\models\Settings;
use brilliance\launcher\services\LauncherService;
use brilliance\launcher\services\SearchService;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RebuildConfigEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\ProjectConfig;
use craft\web\View;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

class Launcher extends Plugin
{
    public static ?Launcher $plugin = null;
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = false;

    public static function config(): array
    {
        return [
            'components' => [
                'launcher' => LauncherService::class,
                'search' => SearchService::class,
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
        ]);

        // Handle project config changes
        $this->attachProjectConfigEventListeners();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            // Immediate test - inject a simple console log on EVERY CP page
            Craft::$app->getView()->registerJs("console.log('[LAUNCHER TEST] CP Page loaded at: ' + window.location.href);", View::POS_HEAD);
            
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
                        console.log('[Launcher PHP] Script injected on page:', window.location.pathname);
                        // Ensure LauncherPlugin initialization happens after DOM and Craft are ready
                        if (typeof Craft !== 'undefined') {
                            console.log('[Launcher PHP] Craft object found');
                            // Use Craft's ready handler if available
                            if (Craft.cp && Craft.cp.ready) {
                                console.log('[Launcher PHP] Using Craft.cp.ready');
                                Craft.cp.ready(function() {
                                    console.log('[Launcher PHP] Craft.cp.ready fired');
                                    if (window.LauncherPlugin) {
                                        window.LauncherPlugin.init({
                                            hotkey: '$hotkey',
                                            searchUrl: Craft.getActionUrl('launcher/search'),
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl'
                                        });
                                    } else {
                                        console.error('[Launcher PHP] LauncherPlugin not found!');
                                    }
                                });
                            } else {
                                console.log('[Launcher PHP] Craft.cp not available, using DOMContentLoaded');
                                // Fallback for pages where Craft.cp might not exist
                                document.addEventListener('DOMContentLoaded', function() {
                                    console.log('[Launcher PHP] DOMContentLoaded fired');
                                    if (window.LauncherPlugin) {
                                        window.LauncherPlugin.init({
                                            hotkey: '$hotkey',
                                            searchUrl: Craft.getActionUrl('launcher/search'),
                                            debounceDelay: {$settings->debounceDelay},
                                            assetUrl: '$assetUrl'
                                        });
                                    } else {
                                        console.error('[Launcher PHP] LauncherPlugin not found!');
                                    }
                                });
                            }
                        } else {
                            console.error('[Launcher PHP] Craft object not found!');
                        }
                        JS;
                        
                        Craft::$app->getView()->registerJs($js, View::POS_END);
                    }
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
    public function handleProjectConfigChange(array $data): void
    {
        // Ensure the plugin is installed
        if (!$this->isInstalled) {
            return;
        }

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