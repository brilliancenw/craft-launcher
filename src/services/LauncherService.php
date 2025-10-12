<?php
namespace brilliance\launcher\services;

use brilliance\launcher\Launcher;

use Craft;
use craft\base\Component;

class LauncherService extends Component
{
    public function formatResults(array $results): array
    {
        $formatted = [];
        $index = 1;

        foreach ($results as $type => $items) {
            if (empty($items)) {
                continue;
            }

            foreach ($items as $item) {
                if ($index > 9) {
                    $item['shortcut'] = null;
                } else {
                    $item['shortcut'] = (string)$index;
                }

                $item['index'] = $index;

                // Add integration data
                $item['integrations'] = Launcher::$plugin->integration->getIntegrationsForItem($item);

                $formatted[] = $item;
                $index++;
            }
        }

        return $formatted;
    }

    public function getRecentItems(): array
    {
        $userId = Craft::$app->getUser()->getId();
        if (!$userId) {
            return [];
        }

        $cacheKey = 'launcher-recent-' . $userId;
        $recent = Craft::$app->getCache()->get($cacheKey);

        return $recent ?: [];
    }

    public function addRecentItem(array $item): void
    {
        $userId = Craft::$app->getUser()->getId();
        if (!$userId) {
            return;
        }

        $cacheKey = 'launcher-recent-' . $userId;
        $recent = $this->getRecentItems();

        $itemKey = md5($item['url']);
        
        unset($recent[$itemKey]);
        
        $recent = [$itemKey => $item] + $recent;
        
        $recent = array_slice($recent, 0, 10, true);

        Craft::$app->getCache()->set($cacheKey, $recent, 86400 * 7);
    }
}