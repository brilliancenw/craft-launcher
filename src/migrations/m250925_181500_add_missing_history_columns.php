<?php

namespace brilliance\launcher\migrations;

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
        // Add the missing columns to the launcher_user_history table
        $this->addColumn('{{%launcher_user_history}}', 'itemId', $this->integer()->null()->after('itemType'));
        $this->addColumn('{{%launcher_user_history}}', 'firstLaunchedAt', $this->dateTime()->notNull()->after('launchCount'));
        $this->addColumn('{{%launcher_user_history}}', 'lastLaunchedAt', $this->dateTime()->notNull()->after('firstLaunchedAt'));

        // Update existing records to set the timestamp fields
        // Use dateCreated as firstLaunchedAt and dateUpdated as lastLaunchedAt for existing records
        $this->update('{{%launcher_user_history}}', [
            'firstLaunchedAt' => new \yii\db\Expression('dateCreated'),
            'lastLaunchedAt' => new \yii\db\Expression('dateUpdated')
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // Remove the added columns
        $this->dropColumn('{{%launcher_user_history}}', 'lastLaunchedAt');
        $this->dropColumn('{{%launcher_user_history}}', 'firstLaunchedAt');
        $this->dropColumn('{{%launcher_user_history}}', 'itemId');

        return true;
    }
}