<?php

declare(strict_types=1);

namespace app\modules\ai\models;

use yii\db\ActiveQuery;

/**
 * @property string $id
 * @property string $conversation_id
 * @property string $role
 * @property string|null $content
 * @property string|null $tool_name
 * @property string|null $tool_call_id
 * @property string|null $provider
 * @property string|null $metadata_json
 * @property int|null $token_count
 * @property string $created_at
 */
class AiMessage extends AiActiveRecord
{
    public const ROLE_SYSTEM = 'system';
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_TOOL = 'tool';

    public static function tableName(): string
    {
        return '{{%ai_messages}}';
    }

    public function rules(): array
    {
        return [
            [['id', 'conversation_id', 'role'], 'required'],
            [['content', 'metadata_json'], 'string'],
            [['token_count'], 'integer'],
            [['created_at'], 'safe'],
            [['id', 'conversation_id'], 'string', 'max' => 36],
            [['role'], 'string', 'max' => 32],
            [['tool_name', 'tool_call_id'], 'string', 'max' => 128],
            [['provider'], 'string', 'max' => 64],
        ];
    }

    public function getConversation(): ActiveQuery
    {
        return $this->hasOne(AiConversation::class, ['id' => 'conversation_id']);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->decodeJson($this->metadata_json);
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata_json = $this->encodeJson($metadata);
    }
}
