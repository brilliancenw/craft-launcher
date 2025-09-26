<?php

namespace brilliance\launcher\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250925_181500_add_missing_history_columns migration.
 */
class m250925_181500_add_missing_history_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // First, ensure the table exists - if not, create it with full schema
        $tableSchema = $this->db->schema->getTableSchema('{{%launcher_user_history}}');
        if ($tableSchema === null) {
            $this->createLauncherUserHistoryTable();
            return true;
        }

        // Check and add missing columns one by one
        $this->addColumnIfNotExists('itemId', $this->integer()->null(), 'itemType');
        $this->addColumnIfNotExists('firstLaunchedAt', $this->dateTime()->notNull(), 'launchCount');
        $this->addColumnIfNotExists('lastLaunchedAt', $this->dateTime()->notNull(), 'firstLaunchedAt');

        // Update existing records to set the timestamp fields if they have null values
        $this->updateTimestampFields();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%launcher_user_history}}');
        if ($tableSchema === null) {
            return true; // Table doesn't exist, nothing to do
        }

        // Remove the added columns if they exist
        $this->dropColumnIfExists('lastLaunchedAt');
        $this->dropColumnIfExists('firstLaunchedAt');
        $this->dropColumnIfExists('itemId');

        return true;
    }

    /**
     * Creates the launcher_user_history table with full schema
     */
    private function createLauncherUserHistoryTable(): void
    {
        $this->createTable('{{%launcher_user_history}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'itemType' => $this->string(50)->notNull(),
            'itemId' => $this->integer()->null(),
            'itemTitle' => $this->text()->notNull(),
            'itemUrl' => $this->text()->notNull(),
            'itemHash' => $this->string(64)->notNull(),
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

    /**
     * Add a column only if it doesn't exist
     */
    private function addColumnIfNotExists(string $column, $type, string $after = null): void
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%launcher_user_history}}');
        if ($tableSchema && !isset($tableSchema->columns[$column])) {
            if ($after) {
                $this->addColumn('{{%launcher_user_history}}', $column, $type->after($after));
            } else {
                $this->addColumn('{{%launcher_user_history}}', $column, $type);
            }
        }
    }

    /**
     * Drop a column only if it exists
     */
    private function dropColumnIfExists(string $column): void
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%launcher_user_history}}');
        if ($tableSchema && isset($tableSchema->columns[$column])) {
            $this->dropColumn('{{%launcher_user_history}}', $column);
        }
    }

    /**
     * Update existing records to set the timestamp fields
     */
    private function updateTimestampFields(): void
    {
        // Only update records where the timestamp fields are null
        $this->update('{{%launcher_user_history}}', [
            'firstLaunchedAt' => new \yii\db\Expression('COALESCE(firstLaunchedAt, dateCreated)'),
            'lastLaunchedAt' => new \yii\db\Expression('COALESCE(lastLaunchedAt, dateUpdated)')
        ], [
            'or',
            ['firstLaunchedAt' => null],
            ['lastLaunchedAt' => null]
        ]);
    }
}