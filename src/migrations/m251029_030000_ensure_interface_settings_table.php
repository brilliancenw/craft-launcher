<?php

namespace brilliance\launcher\migrations;

use Craft;
use craft\db\Migration;

/**
 * m251029_030000_ensure_interface_settings_table migration.
 *
 * This migration ensures the launcher_interface_settings table exists,
 * even if the original migration was marked as applied but table wasn't created.
 */
class m251029_030000_ensure_interface_settings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Check if table already exists
        if ($this->db->schema->getTableSchema('{{%launcher_interface_settings}}') !== null) {
            echo "    > launcher_interface_settings table already exists, skipping.\n";
            return true;
        }

        // Create the table
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

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%launcher_interface_settings}}');
        return true;
    }
}
