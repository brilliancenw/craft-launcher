<?php

namespace brilliance\launcher\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * Install migration
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createLauncherUserHistoryTable();
        $this->createLauncherInterfaceSettingsTable();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%launcher_interface_settings}}');
        $this->dropTableIfExists('{{%launcher_user_history}}');
        return true;
    }

    /**
     * Creates the launcher_user_history table
     */
    private function createLauncherUserHistoryTable(): void
    {
        if ($this->db->schema->getTableSchema('{{%launcher_user_history}}') === null) {
            $this->createTable('{{%launcher_user_history}}', [
                'id' => $this->primaryKey(),
                'userId' => $this->integer()->notNull(),
                'itemType' => $this->string(50)->notNull(),
                'itemId' => $this->integer()->null(),
                'itemTitle' => $this->text()->notNull(),
                'itemUrl' => $this->text()->notNull(),
                'itemHash' => $this->string(32)->notNull(),
                'launchCount' => $this->integer()->defaultValue(1),
                'firstLaunchedAt' => $this->dateTime()->notNull(),
                'lastLaunchedAt' => $this->dateTime()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes for performance
            $this->createIndex(
                'idx_launcher_user_history_user_hash',
                '{{%launcher_user_history}}',
                ['userId', 'itemHash'],
                true
            );

            $this->createIndex(
                'idx_launcher_user_history_user_type',
                '{{%launcher_user_history}}',
                ['userId', 'itemType']
            );

            $this->createIndex(
                'idx_launcher_user_history_launch_count',
                '{{%launcher_user_history}}',
                'launchCount'
            );

            // Add foreign key constraint
            $this->addForeignKey(
                'fk_launcher_user_history_user',
                '{{%launcher_user_history}}',
                'userId',
                '{{%users}}',
                'id',
                'CASCADE'
            );
        }
    }

    /**
     * Creates the launcher_interface_settings table
     */
    private function createLauncherInterfaceSettingsTable(): void
    {
        if ($this->db->schema->getTableSchema('{{%launcher_interface_settings}}') === null) {
            $this->createTable('{{%launcher_interface_settings}}', [
                'id' => $this->primaryKey(),
                'settingKey' => $this->string(255)->notNull()->comment('Setting identifier'),
                'userId' => $this->integer()->null()->comment('User ID (null for global settings)'),
                'interfaceData' => $this->json()->null()->comment('JSON data for interface preferences'),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
            ]);

            // Create indexes
            $this->createIndex(
                'idx_launcher_interface_settings_key',
                '{{%launcher_interface_settings}}',
                ['settingKey'],
                false
            );

            $this->createIndex(
                'idx_launcher_interface_settings_user',
                '{{%launcher_interface_settings}}',
                ['userId'],
                false
            );

            $this->createIndex(
                'idx_launcher_interface_settings_key_user',
                '{{%launcher_interface_settings}}',
                ['settingKey', 'userId'],
                true
            );

            // Add foreign key for userId (allows null for global settings)
            $this->addForeignKey(
                'fk_launcher_interface_settings_user',
                '{{%launcher_interface_settings}}',
                'userId',
                '{{%users}}',
                'id',
                'CASCADE'
            );
        }
    }
}