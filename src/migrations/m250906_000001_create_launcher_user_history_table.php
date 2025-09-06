<?php

namespace brilliance\launcher\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250906_000001_create_launcher_user_history_table migration.
 */
class m250906_000001_create_launcher_user_history_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%launcher_user_history}}');
        if ($tableSchema !== null) {
            return true; // Table already exists
        }

        $this->createTable('{{%launcher_user_history}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull()->comment('References craft users.id'),
            'itemType' => $this->string(50)->notNull()->comment('Type of item (entry, category, section, etc.)'),
            'itemId' => $this->string(100)->comment('Original item ID (if applicable)'),
            'itemTitle' => $this->string(255)->notNull()->comment('Display title of the item'),
            'itemUrl' => $this->text()->notNull()->comment('URL that was launched'),
            'itemHash' => $this->string(64)->notNull()->comment('Hash of itemType+itemId+itemUrl for uniqueness'),
            'launchCount' => $this->integer()->notNull()->defaultValue(1)->comment('Number of times launched'),
            'lastLaunchedAt' => $this->dateTime()->notNull()->comment('Last launch timestamp'),
            'firstLaunchedAt' => $this->dateTime()->notNull()->comment('First launch timestamp'),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
        ]);

        // Create indexes for performance
        $this->createIndex(
            'idx_user_launches',
            '{{%launcher_user_history}}',
            ['userId', 'launchCount', 'lastLaunchedAt'],
            false
        );

        // Create unique constraint to prevent duplicates
        $this->createIndex(
            'uk_user_item',
            '{{%launcher_user_history}}',
            ['userId', 'itemHash'],
            true
        );

        // Add foreign key constraint to users table
        $this->addForeignKey(
            'fk_launcher_history_user',
            '{{%launcher_user_history}}',
            'userId',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

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
}