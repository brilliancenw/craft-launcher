<?php

namespace brilliance\launcher\controllers;

use Craft;
use craft\web\Controller;

/**
 * Admin Controller
 *
 * Handles the Launcher CP section dashboard
 */
class AdminController extends Controller
{
    /**
     * Launcher dashboard/index page
     */
    public function actionIndex()
    {
        return $this->renderTemplate('launcher/admin/index', [
            'title' => 'Launcher',
        ]);
    }
}
