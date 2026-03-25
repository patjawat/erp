<?php
namespace app\modules\helpdesk2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * SLA settings for helpdesk2 (stored in `categorise`)
 *
 * - name: helpdesk2_sla
 * - code: default
 * - data_json: { urgency_hours: { "1": 72, ... } }
 */
class HelpdeskSlaSetting extends ActiveRecord
{
    public const SETTING_NAME = 'helpdesk2_sla';
    public const SETTING_CODE = 'default';

    public static function tableName(): string
    {
        return 'categorise';
    }

    public function rules(): array
    {
        return [
            [['name', 'code', 'title'], 'string', 'max' => 255],
            [['data_json'], 'safe'],
            [['active'], 'integer'],
        ];
    }

    public static function getRecord(): self
    {
        $record = static::findOne(['name' => self::SETTING_NAME, 'code' => self::SETTING_CODE]);
        if (!$record) {
            $record = new static();
            $record->name = self::SETTING_NAME;
            $record->code = self::SETTING_CODE;
            $record->title = 'SLA Helpdesk2';
            $record->data_json = json_encode([
                'urgency_hours' => [
                    // default mapping (backward compatible)
                    '1' => 72, // low
                    '2' => 24, // medium
                    '3' => 4,  // high
                    '4' => 1,  // critical
                    'low' => 72,
                    'medium' => 24,
                    'high' => 4,
                    'critical' => 1,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $record->active = 1;
            $record->save(false);
        }
        return $record;
    }

    public function getConfig(): array
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        return is_array($json) ? $json : [];
    }

    public function setConfig(array $config): bool
    {
        $this->data_json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $this->save(false);
    }
}

