<?php

namespace app\modules\pm\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ตั้งค่าโมดูล pm (key-value) — มิเรอร์ MedSopSetting
 *
 * @property string $setting_key
 * @property string|null $setting_value
 * @property int|null $updated_by
 * @property string $updated_at
 */
class PmSetting extends ActiveRecord
{
    public const CODE_PATTERN = 'code_pattern';

    public static function tableName()
    {
        return '{{%pm_setting}}';
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
        return $model === null || $model->setting_value === null ? $default : (string) $model->setting_value;
    }

    public static function setValue(string $key, string $value): bool
    {
        $model = static::findOne($key) ?: new static(['setting_key' => $key]);
        $model->setting_value = trim($value);
        $model->updated_by = Yii::$app->user->id;
        $model->updated_at = date('Y-m-d H:i:s');
        return $model->save();
    }
}
