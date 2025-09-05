<?php
namespace brilliance\launcher\assetbundles\launcher;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class LauncherAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = '@brilliance/launcher/assetbundles/launcher/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/launcher.js',
        ];

        $this->css = [
            'css/launcher.css',
        ];

        parent::init();
    }
}