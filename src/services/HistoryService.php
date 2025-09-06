<?php

namespace brilliance\launcher\services;

use brilliance\launcher\Launcher;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Db;

/**
 * History Service
 * 
 * Manages user launch history tracking and retrieval
 */
class HistoryService extends Component
{
    /**
     * Record a launch for the current user
     */
    public function recordLaunch(array $item): bool
    {
        $settings = Launcher::$plugin->getSettings();
        
        // Check if history tracking is enabled
        if (!($settings->enableLaunchHistory ?? true)) {
            return false;
        }

        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return false;
        }

        $userId = $user->id;
        $itemHash = $this->generateItemHash($item);
        $now = Db::prepareDateForDb(new \DateTime());

        try {
            // Check if this item already exists for this user
            $existingRecord = (new Query())
                ->select(['id', 'launchCount'])
                ->from('{{%launcher_user_history}}')
                ->where([
                    'userId' => $userId,
                    'itemHash' => $itemHash
                ])
                ->one();

            if ($existingRecord) {
                // Update existing record - increment count and update timestamp
                Craft::$app->db->createCommand()
                    ->update('{{%launcher_user_history}}', [
                        'launchCount' => $existingRecord['launchCount'] + 1,
                        'lastLaunchedAt' => $now,
                        'dateUpdated' => $now,
                        // Also update current item data in case title changed
                        'itemTitle' => $item['title'] ?? '',
                        'itemUrl' => $item['url'] ?? '',
                    ], [
                        'id' => $existingRecord['id']
                    ])
                    ->execute();
            } else {
                // Create new record
                Craft::$app->db->createCommand()
                    ->insert('{{%launcher_user_history}}', [
                        'userId' => $userId,
                        'itemType' => $item['type'] ?? 'unknown',
                        'itemId' => $item['id'] ?? null,
                        'itemTitle' => $item['title'] ?? '',
                        'itemUrl' => $item['url'] ?? '',
                        'itemHash' => $itemHash,
                        'launchCount' => 1,
                        'lastLaunchedAt' => $now,
                        'firstLaunchedAt' => $now,
                        'dateCreated' => $now,
                        'dateUpdated' => $now,
                    ])
                    ->execute();
            }

            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to record launch history: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Get the most popular items for the current user
     */
    public function getPopularItems(int $limit = 10): array
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return [];
        }

        $results = (new Query())
            ->select([
                'itemType',
                'itemId', 
                'itemTitle',
                'itemUrl',
                'itemHash',
                'launchCount',
                'lastLaunchedAt'
            ])
            ->from('{{%launcher_user_history}}')
            ->where(['userId' => $user->id])
            ->orderBy([
                'launchCount' => SORT_DESC,
                'lastLaunchedAt' => SORT_DESC
            ])
            ->limit($limit)
            ->all();

        // Format results to match the launcher's expected format
        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[] = [
                'title' => $result['itemTitle'],
                'url' => $result['itemUrl'],
                'type' => $result['itemType'],
                'id' => $result['itemId'],
                'itemHash' => $result['itemHash'],
                'launchCount' => (int)$result['launchCount'],
                'lastLaunched' => $result['lastLaunchedAt'],
                'isPopular' => true, // Flag to identify these as popular items
            ];
        }

        return $formattedResults;
    }

    /**
     * Clear all launch history for the current user
     */
    public function clearUserHistory(): bool
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return false;
        }

        try {
            Craft::$app->db->createCommand()
                ->delete('{{%launcher_user_history}}', [
                    'userId' => $user->id
                ])
                ->execute();

            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to clear user launch history: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Get launch history statistics for the current user
     */
    public function getUserStats(): array
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return [];
        }

        $stats = (new Query())
            ->select([
                'COUNT(*) as totalItems',
                'SUM(launchCount) as totalLaunches',
                'MAX(lastLaunchedAt) as lastActivity'
            ])
            ->from('{{%launcher_user_history}}')
            ->where(['userId' => $user->id])
            ->one();

        return [
            'totalItems' => (int)($stats['totalItems'] ?? 0),
            'totalLaunches' => (int)($stats['totalLaunches'] ?? 0),
            'lastActivity' => $stats['lastActivity'] ?? null,
        ];
    }

    /**
     * Remove a specific history item for the current user
     */
    public function removeHistoryItem(string $itemHash): bool
    {
        $user = Craft::$app->getUser()->getIdentity();
        if (!$user) {
            return false;
        }

        try {
            $deleted = Craft::$app->db->createCommand()
                ->delete('{{%launcher_user_history}}', [
                    'userId' => $user->id,
                    'itemHash' => $itemHash
                ])
                ->execute();

            return $deleted > 0;
        } catch (\Exception $e) {
            Craft::error('Failed to remove history item: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Generate a unique hash for an item
     */
    private function generateItemHash(array $item): string
    {
        $hashData = [
            $item['type'] ?? 'unknown',
            $item['id'] ?? '',
            $item['url'] ?? '',
        ];
        
        return hash('sha256', implode('|', $hashData));
    }
}