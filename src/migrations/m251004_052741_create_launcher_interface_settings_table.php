<?php

use craft\db\Migration;

/**
 * m251004_052741_create_launcher_interface_settings_table migration.
 */
class m251004_052741_create_launcher_interface_settings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%launcher_interface_settings}}', [
            'id' => $this->primaryKey(),
            'settingKey' => $this->string(255)->notNull()->comment('Setting identifier'),
            'userId' => $this->integer()->null()->comment('User ID (null for global settings)'),
            'interfaceData' => $this->json()->null()->comment('JSON data for interface preferences'),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
        ]);

        // Create indexes
        $this->createIndex(null, '{{%launcher_interface_settings}}', ['settingKey'], false);
        $this->createIndex(null, '{{%launcher_interface_settings}}', ['userId'], false);
        $this->createIndex(null, '{{%launcher_interface_settings}}', ['settingKey', 'userId'], true); // Unique combination

        // Add foreign key for userId (but allow null for global settings)
        $this->addForeignKey(null, '{{%launcher_interface_settings}}', ['userId'], '{{%users}}', ['id'], 'CASCADE', null);

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