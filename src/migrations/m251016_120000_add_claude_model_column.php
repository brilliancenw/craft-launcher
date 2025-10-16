<?php

namespace brilliance\launcher\migrations;

use Craft;
use craft\db\Migration;

/**
 * m251016_120000_add_claude_model_column migration.
 *
 * Add claudeModel column to store selected Claude model
 */
class m251016_120000_add_claude_model_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Add claudeModel column
        $this->addColumn(
            '{{%launcher_ai_settings}}',
            'claudeModel',
            $this->string(100)->null()->after('geminiApiKey')->comment('Selected Claude model ID')
        );

        // Set default value for existing row
        $this->update(
            '{{%launcher_ai_settings}}',
            ['claudeModel' => 'claude-sonnet-4-20250514'],
            ['claudeModel' => null]
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn('{{%launcher_ai_settings}}', 'claudeModel');
        return true;
    }
}
