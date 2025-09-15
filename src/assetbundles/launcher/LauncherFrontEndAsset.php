<?php
namespace brilliance\launcher\assetbundles\launcher;

use craft\web\AssetBundle;

class LauncherFrontEndAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = '@brilliance/launcher/assetbundles/launcher/dist';

        // No dependencies for front-end - avoid loading CP assets
        $this->depends = [];

        $this->js = [
            'js/launcher.js',
        ];

        $this->css = [
            'css/launcher.css',
        ];

        parent::init();
    }
}