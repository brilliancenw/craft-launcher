<?php

namespace brilliance\launcher\migrations;

use Craft;
use craft\db\Migration;

/**
 * m251016_100000_create_launcher_ai_settings_table migration.
 *
 * Environment-specific AI settings (API keys, brand info, guidelines)
 * These are NOT part of project config and are stored per-environment
 */
class m251016_100000_create_launcher_ai_settings_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Create AI settings table (single row)
        $this->createTable('{{%launcher_ai_settings}}', [
            'id' => $this->primaryKey(),

            // AI Provider Configuration
            'aiProvider' => $this->string(50)->defaultValue('claude')->comment('Active AI provider (claude, openai, gemini)'),
            'claudeApiKey' => $this->text()->null()->comment('Claude API key (encrypted)'),
            'openaiApiKey' => $this->text()->null()->comment('OpenAI API key (encrypted)'),
            'geminiApiKey' => $this->text()->null()->comment('Gemini API key (encrypted)'),

            // Brand Information
            'websiteName' => $this->string(255)->null()->comment('Website/brand name'),
            'brandOwner' => $this->string(255)->null()->comment('Brand owner/company name'),
            'brandTagline' => $this->string(255)->null()->comment('Brand tagline'),
            'brandDescription' => $this->text()->null()->comment('Brand description'),
            'brandVoice' => $this->text()->null()->comment('Brand voice guidelines'),
            'targetAudience' => $this->text()->null()->comment('Target audience description'),
            'brandColors' => $this->json()->null()->comment('Brand colors array'),
            'brandLogoUrl' => $this->string(500)->null()->comment('Brand logo URL'),

            // Content Guidelines
            'contentGuidelines' => $this->text()->null()->comment('General content guidelines'),
            'contentTone' => $this->text()->null()->comment('Content tone guidelines'),
            'writingStyle' => $this->text()->null()->comment('Writing style guidelines'),
            'seoGuidelines' => $this->text()->null()->comment('SEO guidelines'),
            'customGuidelines' => $this->json()->null()->comment('Custom guidelines array'),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Insert default row
        $this->insert('{{%launcher_ai_settings}}', [
            'aiProvider' => 'claude',
            'dateCreated' => date('Y-m-d H:i:s'),
            'dateUpdated' => date('Y-m-d H:i:s'),
            'uid' => \craft\helpers\StringHelper::UUID(),
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%launcher_ai_settings}}');
        return true;
    }
}
