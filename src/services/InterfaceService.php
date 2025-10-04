<?php
namespace brilliance\launcher\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\Json;

/**
 * Interface Service for managing UI state and preferences
 * Stores non-configuration data that needs to persist but shouldn't be in plugin settings
 */
class InterfaceService extends Component
{
    private const TABLE_NAME = '{{%launcher_interface_settings}}';

    /**
     * Get interface setting value
     */
    public function getSetting(string $key, ?int $userId = null, $default = null)
    {
        $record = $this->getSettingRecord($key, $userId);

        if (!$record) {
            return $default;
        }

        $data = $record['interfaceData'] ? Json::decode($record['interfaceData']) : [];
        return $data['value'] ?? $default;
    }

    /**
     * Set interface setting value
     */
    public function setSetting(string $key, $value, ?int $userId = null): bool
    {
        try {
            Craft::info("Attempting to set interface setting: key=$key, userId=$userId, value=" . var_export($value, true), 'launcher');

            $data = ['value' => $value];
            $jsonData = Json::encode($data);
            Craft::info("JSON data to save: $jsonData", 'launcher');

            $record = $this->getSettingRecord($key, $userId);
            Craft::info("Existing record found: " . ($record ? 'YES' : 'NO'), 'launcher');

            if ($record) {
                // Update existing
                Craft::info("Updating existing record", 'launcher');
                $result = Craft::$app->getDb()->createCommand()
                    ->update(self::TABLE_NAME, [
                        'interfaceData' => $jsonData,
                        'dateUpdated' => Craft::$app->getDb()->getDateTimeValue(),
                    ], [
                        'settingKey' => $key,
                        'userId' => $userId,
                    ])
                    ->execute();
                Craft::info("Update result: $result rows affected", 'launcher');
            } else {
                // Create new
                Craft::info("Creating new record", 'launcher');
                $result = Craft::$app->getDb()->createCommand()
                    ->insert(self::TABLE_NAME, [
                        'settingKey' => $key,
                        'userId' => $userId,
                        'interfaceData' => $jsonData,
                        'dateCreated' => Craft::$app->getDb()->getDateTimeValue(),
                        'dateUpdated' => Craft::$app->getDb()->getDateTimeValue(),
                    ])
                    ->execute();
                Craft::info("Insert result: $result rows affected", 'launcher');
            }

            Craft::info("Interface setting saved successfully", 'launcher');
            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to save interface setting: ' . $e->getMessage(), 'launcher');
            Craft::error('Exception trace: ' . $e->getTraceAsString(), 'launcher');
            return false;
        }
    }

    /**
     * Check if the first run has been completed (global setting)
     */
    public function isFirstRunCompleted(): bool
    {
        return $this->getSetting('welcome_completed', null, false);
    }

    /**
     * Mark first run as completed (global setting)
     */
    public function markFirstRunCompleted(): bool
    {
        return $this->setSetting('welcome_completed', true, null);
    }

    /**
     * Get interface data for a specific key and user
     */
    public function getInterfaceData(string $key, ?int $userId = null): array
    {
        $record = $this->getSettingRecord($key, $userId);

        if (!$record || !$record['interfaceData']) {
            return [];
        }

        return Json::decode($record['interfaceData']);
    }

    /**
     * Set interface data for a specific key and user
     */
    public function setInterfaceData(string $key, array $data, ?int $userId = null): bool
    {
        try {
            $jsonData = Json::encode($data);

            $record = $this->getSettingRecord($key, $userId);

            if ($record) {
                // Update existing
                Craft::$app->getDb()->createCommand()
                    ->update(self::TABLE_NAME, [
                        'interfaceData' => $jsonData,
                        'dateUpdated' => Craft::$app->getDb()->getDateTimeValue(),
                    ], [
                        'settingKey' => $key,
                        'userId' => $userId,
                    ])
                    ->execute();
            } else {
                // Create new
                Craft::$app->getDb()->createCommand()
                    ->insert(self::TABLE_NAME, [
                        'settingKey' => $key,
                        'userId' => $userId,
                        'interfaceData' => $jsonData,
                        'dateCreated' => Craft::$app->getDb()->getDateTimeValue(),
                        'dateUpdated' => Craft::$app->getDb()->getDateTimeValue(),
                    ])
                    ->execute();
            }

            return true;
        } catch (\Exception $e) {
            Craft::error('Failed to save interface data: ' . $e->getMessage(), 'launcher');
            return false;
        }
    }

    /**
     * Check if table exists
     */
    public function tableExists(): bool
    {
        $tableName = Craft::$app->getDb()->getSchema()->getRawTableName(self::TABLE_NAME);
        return Craft::$app->getDb()->getSchema()->getTableSchema($tableName) !== null;
    }

    /**
     * Get setting record from database
     */
    private function getSettingRecord(string $key, ?int $userId = null): ?array
    {
        if (!$this->tableExists()) {
            return null;
        }

        $result = (new Query())
            ->select(['interfaceData', 'dateUpdated'])
            ->from(self::TABLE_NAME)
            ->where(['settingKey' => $key, 'userId' => $userId])
            ->one();

        return $result ?: null;
    }
}