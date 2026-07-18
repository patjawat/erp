<?php

namespace app\modules\medsop\models;

use yii\db\ActiveRecord;

class MedSopSetting extends ActiveRecord
{
    public const SOP_PREFIX = 'sop_prefix';
    public const WI_PREFIX = 'wi_prefix';
    public const CODE_PATTERN = 'code_pattern';
    public const DOCUMENT_CATEGORIES = 'document_categories';
    public const ANNOUNCEMENT_STATUSES = 'announcement_statuses';

    public static function tableName()
    {
        return '{{%medsop_setting}}';
    }

    public static function primaryKey()
    {
        return ['setting_key'];
    }

    public function rules()
    {
        return [
            [['setting_key'], 'required'],
            [['setting_value'], 'string'],
            [['updated_by'], 'integer'],
            [['updated_at'], 'safe'],
            [['setting_key'], 'string', 'max' => 100],
        ];
    }

    public static function value(string $key, string $default = ''): string
    {
        $model = static::findOne($key);
        return $model === null ? $default : (string) $model->setting_value;
    }

    public static function setValue(string $key, string $value): bool
    {
        $model = static::findOne($key) ?: new static(['setting_key' => $key]);
        $model->setting_value = trim($value);
        $model->updated_by = \Yii::$app->user->id;
        $model->updated_at = date('Y-m-d H:i:s');
        return $model->save();
    }

    public static function listValue(string $key, array $default = []): array
    {
        $decoded = json_decode(static::value($key, ''), true);
        return is_array($decoded) && $decoded !== [] ? $decoded : $default;
    }
}
