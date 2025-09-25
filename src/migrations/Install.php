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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
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
}