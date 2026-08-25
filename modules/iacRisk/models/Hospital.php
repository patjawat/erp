<?php

namespace app\modules\iacRisk\models;

use Yii;
use yii\db\ActiveRecord;

class Hospital extends ActiveRecord
{
    public static function tableName(): string { return '{{%iac_hospital}}'; }

    public function rules(): array
    {
        return [
            [['code', 'name'], 'required'],
            [['active', 'is_current'], 'boolean'],
            [['code'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 255],
            [['province'], 'string', 'max' => 100],
            [['created_at', 'updated_at'], 'safe'],
            [['code'], 'unique'],
        ];
    }

    public function getFiscalYears() { return $this->hasMany(FiscalYear::class, ['hospital_id' => 'id']); }

    public function beforeSave($insert): bool
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
