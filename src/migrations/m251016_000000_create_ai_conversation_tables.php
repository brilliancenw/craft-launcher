<?php

namespace brilliance\launcher\migrations;

use Craft;
use craft\db\Migration;

/**
 * m251016_000000_create_ai_conversation_tables migration.
 */
class m251016_000000_create_ai_conversation_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Create conversations table
        $this->createTable('{{%launcher_ai_conversations}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull()->comment('User who owns this conversation'),
            'threadId' => $this->string(255)->notNull()->comment('Unique thread identifier'),
            'provider' => $this->string(50)->notNull()->comment('AI provider (claude, openai, gemini)'),
            'title' => $this->string(255)->null()->comment('Conversation title'),
            'lastMessageAt' => $this->dateTime()->notNull()->comment('Timestamp of last message'),
            'messageCount' => $this->integer()->notNull()->defaultValue(0)->comment('Total messages in conversation'),
            'metadata' => $this->json()->null()->comment('Additional conversation metadata'),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
        ]);

        // Create indexes for conversations
        $this->createIndex(null, '{{%launcher_ai_conversations}}', ['userId'], false);
        $this->createIndex(null, '{{%launcher_ai_conversations}}', ['threadId'], true); // Unique thread ID
        $this->createIndex(null, '{{%launcher_ai_conversations}}', ['userId', 'lastMessageAt'], false);

        // Add foreign key for userId
        $this->addForeignKey(
            null,
            '{{%launcher_ai_conversations}}',
            ['userId'],
            '{{%users}}',
            ['id'],
            'CASCADE',
            null
        );

        // Create messages table
        $this->createTable('{{%launcher_ai_messages}}', [
            'id' => $this->primaryKey(),
            'conversationId' => $this->integer()->notNull()->comment('Reference to conversation'),
            'role' => $this->string(50)->notNull()->comment('Message role (user, assistant, system, tool)'),
            'content' => $this->mediumText()->null()->comment('Message content'),
            'toolCalls' => $this->json()->null()->comment('Tool/function calls made by assistant'),
            'toolResults' => $this->json()->null()->comment('Results from tool executions'),
            'metadata' => $this->json()->null()->comment('Additional message metadata'),
            'dateCreated' => $this->dateTime()->notNull(),
        ]);

        // Create indexes for messages
        $this->createIndex(null, '{{%launcher_ai_messages}}', ['conversationId'], false);
        $this->createIndex(null, '{{%launcher_ai_messages}}', ['conversationId', 'dateCreated'], false);

        // Add foreign key for conversationId
        $this->addForeignKey(
            null,
            '{{%launcher_ai_messages}}',
            ['conversationId'],
            '{{%launcher_ai_conversations}}',
            ['id'],
            'CASCADE',
            null
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%launcher_ai_messages}}');
        $this->dropTableIfExists('{{%launcher_ai_conversations}}');
        return true;
    }
}
