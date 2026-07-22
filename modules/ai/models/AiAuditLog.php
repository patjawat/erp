<?php

declare(strict_types=1);

namespace app\modules\ai\models;

/**
 * @property string $id
 * @property int|null $user_id
 * @property string|null $conversation_id
 * @property string|null $message_id
 * @property string|null $provider
 * @property string|null $dataset_code
 * @property string|null $tool_name
 * @property string $action
 * @property string $status
 * @property int $row_count
 * @property int $duration_ms
 * @property string|null $error_message
 * @property string|null $request_json
 * @property string|null $response_json
 * @property string|null $ip_address
 * @property string $created_at
 */
class AiAuditLog extends AiActiveRecord
{
    public static function tableName(): string
    {
        return '{{%ai_audit_logs}}';
    }

    public function rules(): array
    {
        return [
            [['id', 'action', 'status'], 'required'],
            [['user_id', 'row_count', 'duration_ms'], 'integer'],
            [['error_message', 'request_json', 'response_json'], 'string'],
            [['created_at'], 'safe'],
            [['id', 'conversation_id', 'message_id'], 'string', 'max' => 36],
            [['provider'], 'string', 'max' => 64],
            [['dataset_code', 'tool_name'], 'string', 'max' => 128],
            [['action', 'status'], 'string', 'max' => 64],
            [['ip_address'], 'string', 'max' => 45],
        ];
    }
}
