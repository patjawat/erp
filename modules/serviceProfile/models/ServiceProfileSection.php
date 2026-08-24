<?php

namespace app\modules\serviceProfile\models;

use Yii;
use yii\db\ActiveRecord;

class ServiceProfileSection extends ActiveRecord
{
    public static function tableName() { return '{{%service_profile_section}}'; }

    public function rules()
    {
        return [
            [['service_profile_id', 'section_code', 'title', 'block_type'], 'required'],
            [['service_profile_id', 'template_section_id', 'sort_order', 'created_by', 'updated_by'], 'integer'],
            [['is_required'], 'boolean'],
            [['content'], 'string'],
            [['data_json', 'config_snapshot_json', 'created_at', 'updated_at'], 'safe'],
            [['section_code'], 'string', 'max' => 80],
            [['title'], 'string', 'max' => 255],
            [['block_type'], 'string', 'max' => 50],
        ];
    }

    public function getProfile() { return $this->hasOne(ServiceProfile::class, ['id' => 'service_profile_id']); }
    public function getTemplateSection() { return $this->hasOne(ServiceProfileTemplateSection::class, ['id' => 'template_section_id']); }

    public function getData(): array
    {
        if (is_array($this->data_json)) return $this->data_json;
        $decoded = json_decode((string) $this->data_json, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setData(array $data): void { $this->data_json = $data; }

    public function isComplete(): bool
    {
        return trim(strip_tags((string) $this->content)) !== '' || $this->getData() !== [];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;
        $now = date('Y-m-d H:i:s');
        $uid = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        if ($insert) {
            $this->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $now;
            $this->created_by = $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }
}
