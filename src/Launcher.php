<?php
namespace brilliance\launcher;

use brilliance\launcher\assetbundles\launcher\LauncherAsset;
use brilliance\launcher\models\Settings;
use brilliance\launcher\services\LauncherService;
use brilliance\launcher\services\SearchService;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterTemplateRootsEvent;
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
        return $this->getBasePath() . DIRECTORY_SEPARATOR . 'icon' . DIRECTORY_SEPARATOR . 'icon.svg';
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'launcher' => LauncherService::class,
            'search' => SearchService::class,
        ]);

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
                        if (window.LauncherPlugin) {
                            window.LauncherPlugin.init({
                                hotkey: '$hotkey',
                                searchUrl: Craft.getActionUrl('launcher/search'),
                                debounceDelay: {$settings->debounceDelay},
                                assetUrl: '$assetUrl'
                            });
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
}