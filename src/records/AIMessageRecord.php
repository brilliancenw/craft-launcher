<?php

namespace brilliance\launcher\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * AI Message Record
 *
 * @property int $id
 * @property int $conversationId
 * @property string $role
 * @property string|null $content
 * @property array|null $toolCalls
 * @property array|null $toolResults
 * @property array|null $metadata
 * @property string $dateCreated
 * @property AIConversationRecord $conversation
 */
class AIMessageRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%launcher_ai_messages}}';
    }

    /**
     * Returns the message's conversation.
     */
    public function getConversation(): ActiveQueryInterface
    {
        return $this->hasOne(AIConversationRecord::class, ['id' => 'conversationId']);
    }
}
