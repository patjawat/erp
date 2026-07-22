<?php

declare(strict_types=1);

namespace app\modules\ai\models;

use app\modules\ai\Module;
use Yii;
use yii\db\ActiveRecord;

abstract class AiActiveRecord extends ActiveRecord
{
    public static function getDb()
    {
        $module = Yii::$app->getModule('ai');
        return $module instanceof Module ? $module->getDb() : Yii::$app->db;
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        if ($this->hasAttribute('id') && empty($this->id)) {
            $this->id = self::uuid();
        }

        return true;
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->hasAttribute('id') && empty($this->id)) {
            $this->id = self::uuid();
        }

        $now = date('Y-m-d H:i:s');
        if ($insert && $this->hasAttribute('created_at') && empty($this->created_at)) {
            $this->created_at = $now;
        }

        if ($this->hasAttribute('updated_at')) {
            $this->updated_at = $now;
        }

        return true;
    }

    public static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeJson(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $value
     */
    protected function encodeJson(array $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
