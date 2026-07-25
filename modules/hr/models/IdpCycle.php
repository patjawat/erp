<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

class IdpCycle extends ActiveRecord
{
    public static function tableName() { return '{{%idp_cycle}}'; }

    public function rules()
    {
        return [
            [['title', 'fiscal_year', 'start_date', 'end_date'], 'required'],
            [['fiscal_year', 'created_by', 'updated_by'], 'integer'],
            [['start_date', 'end_date', 'submission_due_date', 'review_due_date', 'description', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 150],
            [['status'], 'string', 'max' => 20],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;
        $now = date('Y-m-d H:i:s');
        $userId = \Yii::$app->user->isGuest ? null : \Yii::$app->user->id;
        if ($insert) { $this->created_at = $now; $this->created_by = $userId; }
        $this->updated_at = $now; $this->updated_by = $userId;
        return true;
    }

    public static function statusOptions()
    {
        return ['draft' => 'ฉบับร่าง', 'active' => 'กำลังใช้งาน', 'closed' => 'ปิดรอบ'];
    }

    public static function current()
    {
        return self::find()->where(['status' => 'active'])->orderBy(['start_date' => SORT_DESC, 'id' => SORT_DESC])->one();
    }

    public function getPlans() { return $this->hasMany(IdpPlan::class, ['cycle_id' => 'id']); }
    public function getStatusLabel() { return self::statusOptions()[$this->status] ?? $this->status; }
}
