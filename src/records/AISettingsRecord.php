<?php

namespace brilliance\launcher\records;

use craft\db\ActiveRecord;

/**
 * AI Settings Record
 *
 * Environment-specific AI settings (single row)
 *
 * @property int $id
 * @property string $aiProvider
 * @property string|null $claudeApiKey
 * @property string|null $openaiApiKey
 * @property string|null $geminiApiKey
 * @property string|null $claudeModel
 * @property string|null $websiteName
 * @property string|null $brandOwner
 * @property string|null $brandTagline
 * @property string|null $brandDescription
 * @property string|null $brandVoice
 * @property string|null $targetAudience
 * @property array|null $brandColors
 * @property string|null $brandLogoUrl
 * @property string|null $contentGuidelines
 * @property string|null $contentTone
 * @property string|null $writingStyle
 * @property string|null $seoGuidelines
 * @property array|null $customGuidelines
 * @property string $dateCreated
 * @property string $dateUpdated
 * @property string $uid
 */
class AISettingsRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%launcher_ai_settings}}';
    }

    /**
     * Get the singleton settings record
     */
    public static function getInstance(): ?self
    {
        $record = self::find()->one();

        // Create default if doesn't exist
        if (!$record) {
            $record = new self();
            $record->aiProvider = 'claude';
            $record->save();
        }

        return $record;
    }
}
