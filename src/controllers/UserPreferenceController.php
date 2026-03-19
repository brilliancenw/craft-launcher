<?php
namespace brilliance\launcher\controllers;

use brilliance\launcher\Launcher;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class UserPreferenceController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Craft::$app->getUser()->checkPermission('accessPlugin-launcher')) {
            throw new \yii\web\ForbiddenHttpException('User does not have permission to access launcher');
        }

        return true;
    }

    public function actionSetFrontEndEnabled(): Response
    {
        $this->requirePostRequest();

        // Handle both JSON (AJAX) and form data
        $request = Craft::$app->getRequest();
        if ($request->getIsAjax() && $request->getContentType() === 'application/json') {
            // AJAX JSON request
            $enabled = (bool) $request->getBodyParam('enabled', false);
            $newTabEnabled = (bool) $request->getBodyParam('newTabEnabled', false);
            $nestedEntriesPreference = $request->getBodyParam('nestedEntriesPreference', 'system');
        } else {
            // Standard form submission
            $enabled = (bool) $request->getBodyParam('enabled', false);
            $newTabEnabled = (bool) $request->getBodyParam('newTabEnabled', false);
            $nestedEntriesPreference = $request->getBodyParam('nestedEntriesPreference', 'system');
        }

        $success1 = Launcher::$plugin->userPreference->setFrontEndEnabled($enabled);
        $success2 = Launcher::$plugin->userPreference->setFrontEndNewTabEnabled($newTabEnabled);
        $success3 = Launcher::$plugin->userPreference->setNestedEntriesPreference($nestedEntriesPreference);
        $success = $success1 && $success2 && $success3;

        if ($request->getIsAjax()) {
            return $this->asJson([
                'success' => $success,
                'message' => $success
                    ? 'Launcher preferences updated successfully'
                    : 'Failed to update preferences'
            ]);
        } else {
            // Standard form submission - redirect back with flash message
            if ($success) {
                Craft::$app->getSession()->setNotice('Launcher preferences updated successfully');
            } else {
                Craft::$app->getSession()->setError('Failed to update preferences');
            }
            return $this->redirectToPostedUrl();
        }
    }

    public function actionGetFrontEndStatus(): Response
    {
        $this->requireAcceptsJson();

        $enabled = Launcher::$plugin->userPreference->isFrontEndEnabled();
        $newTabEnabled = Launcher::$plugin->userPreference->isFrontEndNewTabEnabled();

        return $this->asJson([
            'success' => true,
            'enabled' => $enabled,
            'newTabEnabled' => $newTabEnabled
        ]);
    }

    /**
     * Set search filter preferences
     */
    public function actionSetSearchFilters(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // Get filters from request body
        $filters = [];

        if ($request->getIsAjax() && $request->getContentType() === 'application/json') {
            $filters = [
                'includeDrafts' => $request->getBodyParam('includeDrafts'),
                'includeDisabled' => $request->getBodyParam('includeDisabled'),
                'includeNested' => $request->getBodyParam('includeNested'),
                'sections' => $request->getBodyParam('sections', []),
                'entryTypes' => $request->getBodyParam('entryTypes', []),
            ];
        } else {
            $filters = [
                'includeDrafts' => $request->getBodyParam('includeDrafts'),
                'includeDisabled' => $request->getBodyParam('includeDisabled'),
                'includeNested' => $request->getBodyParam('includeNested'),
                'sections' => $request->getBodyParam('sections', []),
                'entryTypes' => $request->getBodyParam('entryTypes', []),
            ];
        }

        // Filter out null values (only update what was sent)
        $filters = array_filter($filters, function($value) {
            return $value !== null;
        });

        $success = Launcher::$plugin->userPreference->setSearchFilters($filters);

        if ($request->getIsAjax()) {
            return $this->asJson([
                'success' => $success,
                'message' => $success
                    ? 'Search filters updated successfully'
                    : 'Failed to update search filters',
                'filters' => Launcher::$plugin->userPreference->getSearchFilters()
            ]);
        } else {
            if ($success) {
                Craft::$app->getSession()->setNotice('Search filters updated successfully');
            } else {
                Craft::$app->getSession()->setError('Failed to update search filters');
            }
            return $this->redirectToPostedUrl();
        }
    }

    /**
     * Get current search filter preferences
     */
    public function actionGetSearchFilters(): Response
    {
        $this->requireAcceptsJson();

        $filters = Launcher::$plugin->userPreference->getSearchFilters();
        $availableOptions = Launcher::$plugin->userPreference->getAvailableFilterOptions();

        return $this->asJson([
            'success' => true,
            'filters' => $filters,
            'availableOptions' => $availableOptions
        ]);
    }
}