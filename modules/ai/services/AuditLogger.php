<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\models\AiAuditLog;
use Throwable;
use Yii;

class AuditLogger
{
    /**
     * @param array<string, mixed> $data
     */
    public function log(array $data): void
    {
        try {
            $model = new AiAuditLog();
            $model->user_id = $data['user_id'] ?? (isset(Yii::$app->user) && !Yii::$app->user->isGuest ? (int) Yii::$app->user->id : null);
            $model->conversation_id = $data['conversation_id'] ?? null;
            $model->message_id = $data['message_id'] ?? null;
            $model->provider = $data['provider'] ?? null;
            $model->dataset_code = $data['dataset_code'] ?? null;
            $model->tool_name = $data['tool_name'] ?? null;
            $model->action = (string) ($data['action'] ?? 'unknown');
            $model->status = (string) ($data['status'] ?? 'success');
            $model->row_count = (int) ($data['row_count'] ?? 0);
            $model->duration_ms = (int) ($data['duration_ms'] ?? 0);
            $model->error_message = $data['error_message'] ?? null;
            $model->request_json = $this->encode($data['request'] ?? null);
            $model->response_json = $this->encode($data['response'] ?? null);
            $model->ip_address = Yii::$app->request->userIP ?? null;
            $model->save(false);
        } catch (Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
        }
    }

    private function encode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }
}
