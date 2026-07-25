<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

class IdpGoal extends ActiveRecord
{
    public static function tableName() { return '{{%idp_goal}}'; }
    public function rules()
    {
        return [
            [['plan_id', 'title'], 'required'],
            [['plan_id', 'sequence', 'created_by', 'updated_by'], 'integer'],
            [['gap_reason', 'expected_outcome', 'success_measure', 'due_date', 'created_at', 'updated_at'], 'safe'],
            [['weight_percent', 'progress_percent'], 'number', 'min' => 0, 'max' => 100],
            [['title'], 'string', 'max' => 255],
            [['source_type'], 'string', 'max' => 30],
            [['status'], 'string', 'max' => 20],
        ];
    }
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) return false;
        $now = date('Y-m-d H:i:s'); $uid = \Yii::$app->user->isGuest ? null : \Yii::$app->user->id;
        if ($insert) { $this->created_at = $now; $this->created_by = $uid; }
        $this->updated_at = $now; $this->updated_by = $uid; return true;
    }
    public static function sourceOptions()
    {
        return ['jd' => 'ช่องว่างจาก JD', 'appraisal' => 'ผลการประเมิน', 'new_role' => 'เตรียมรับหน้าที่ใหม่', 'career' => 'เป้าหมายอาชีพ', 'employee' => 'ความสนใจของพนักงาน', 'organization' => 'ความจำเป็นของหน่วยงาน'];
    }
    public function getPlan() { return $this->hasOne(IdpPlan::class, ['id' => 'plan_id']); }
    public function getActivities() { return $this->hasMany(IdpActivity::class, ['goal_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]); }
}
