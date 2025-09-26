<?php

namespace brilliance\launcher\migrations;

use craft\db\Migration;

/**
 * m250926_171000_fix_itemhash_column_length migration.
 */
class m250926_171000_fix_itemhash_column_length extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Check if the table exists
        if ($this->db->schema->getTableSchema('{{%launcher_user_history}}') === null) {
            echo "Table launcher_user_history does not exist, skipping migration.\n";
            return true;
        }

        // Increase itemHash column from 32 to 64 characters to accommodate SHA-256 hashes
        $this->alterColumn('{{%launcher_user_history}}', 'itemHash', $this->string(64)->notNull());

        echo "Updated itemHash column length from 32 to 64 characters.\n";
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // Check if the table exists
        if ($this->db->schema->getTableSchema('{{%launcher_user_history}}') === null) {
            echo "Table launcher_user_history does not exist, skipping migration rollback.\n";
            return true;
        }

        // Revert back to 32 characters (this might truncate existing data)
        $this->alterColumn('{{%launcher_user_history}}', 'itemHash', $this->string(32)->notNull());

        echo "Reverted itemHash column length back to 32 characters.\n";
        return true;
    }
}