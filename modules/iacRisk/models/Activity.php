<?php

namespace app\modules\iacRisk\models;

use Yii;
use yii\db\ActiveRecord;

class Activity extends ActiveRecord
{
    public static function tableName(): string { return '{{%iac_activity}}'; }

    public function rules(): array
    {
        return [
            [['hospital_id', 'entity_type', 'action'], 'required'],
            [['hospital_id', 'fiscal_year_id', 'reporting_period_id', 'org_unit_id', 'entity_id', 'created_by', 'updated_by'], 'integer'],
            [['message'], 'string'],
            [['data_json', 'created_at', 'updated_at'], 'safe'],
            [['entity_type', 'action'], 'string', 'max' => 60],
            [['from_status', 'to_status'], 'string', 'max' => 30],
            [['ip_address'], 'string', 'max' => 45],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) return false;
        $now = date('Y-m-d H:i:s');
        $uid = !Yii::$app->has('user') || Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        if ($insert) {
            $this->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
            $this->created_at = $this->created_at ?: $now;
            $this->created_by = $this->created_by ?: $uid;
        }
        $this->updated_at = $now;
        $this->updated_by = $uid;
        return true;
    }
}
