<?php

declare(strict_types=1);

namespace app\modules\ai\models;

use yii\db\ActiveQuery;

/**
 * @property string $id
 * @property int|null $user_id
 * @property string $title
 * @property string $provider
 * @property string|null $model
 * @property string $status
 * @property string|null $metadata_json
 * @property string $created_at
 * @property string $updated_at
 */
class AiConversation extends AiActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ai_conversations}}';
    }

    public function rules(): array
    {
        return [
            [['id', 'title', 'provider', 'status'], 'required'],
            [['user_id'], 'integer'],
            [['metadata_json'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['title'], 'string', 'max' => 255],
            [['provider'], 'string', 'max' => 64],
            [['model'], 'string', 'max' => 128],
            [['status'], 'string', 'max' => 32],
        ];
    }

    public function getMessages(): ActiveQuery
    {
        return $this->hasMany(AiMessage::class, ['conversation_id' => 'id'])->orderBy(['created_at' => SORT_ASC]);
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
