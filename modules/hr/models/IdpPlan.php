<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

class IdpPlan extends ActiveRecord
{
    public static function tableName() { return '{{%idp_plan}}'; }

    public function rules()
    {
        return [
            [['cycle_id', 'emp_id'], 'required'],
            [['cycle_id', 'emp_id', 'supervisor_emp_id', 'created_by', 'updated_by'], 'integer'],
            [['progress_percent'], 'number', 'min' => 0, 'max' => 100],
            [['employee_summary', 'supervisor_comment', 'submitted_at', 'reviewed_at', 'completed_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 30],
            [['cycle_id', 'emp_id'], 'unique', 'targetAttribute' => ['cycle_id', 'emp_id']],
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
        return [
            'draft' => 'ฉบับร่าง',
            'submitted' => 'รอหัวหน้าพิจารณา',
            'revision' => 'ให้ปรับปรุง',
            'approved' => 'อนุมัติแล้ว',
            'in_progress' => 'กำลังดำเนินการ',
            'assessment' => 'รอประเมินผล',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
        ];
    }

    public function getCycle() { return $this->hasOne(IdpCycle::class, ['id' => 'cycle_id']); }
    public function getEmployee() { return $this->hasOne(Employees::class, ['id' => 'emp_id']); }
    public function getSupervisor() { return $this->hasOne(Employees::class, ['id' => 'supervisor_emp_id']); }
    public function getGoals() { return $this->hasMany(IdpGoal::class, ['plan_id' => 'id'])->orderBy(['sequence' => SORT_ASC, 'id' => SORT_ASC]); }
    public function getStatusLabel() { return self::statusOptions()[$this->status] ?? $this->status; }
    public function canEdit() { return in_array($this->status, ['draft', 'revision'], true); }
}
