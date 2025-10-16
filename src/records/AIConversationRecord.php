<?php

namespace brilliance\launcher\records;

use craft\db\ActiveRecord;
use craft\records\User;
use yii\db\ActiveQueryInterface;

/**
 * AI Conversation Record
 *
 * @property int $id
 * @property int $userId
 * @property string $threadId
 * @property string $provider
 * @property string|null $title
 * @property string $lastMessageAt
 * @property int $messageCount
 * @property array|null $metadata
 * @property string $dateCreated
 * @property string $dateUpdated
 * @property User $user
 * @property AIMessageRecord[] $messages
 */
class AIConversationRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%launcher_ai_conversations}}';
    }

    /**
     * Returns the conversation's user.
     */
    public function getUser(): ActiveQueryInterface
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    /**
     * Returns the conversation's messages.
     */
    public function getMessages(): ActiveQueryInterface
    {
        return $this->hasMany(AIMessageRecord::class, ['conversationId' => 'id'])
            ->orderBy(['dateCreated' => SORT_ASC]);
    }
}
