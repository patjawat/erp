<?php

namespace app\modules\hr\models;

use yii\db\ActiveRecord;

class IdpActivity extends ActiveRecord
{
    public static function tableName() { return '{{%idp_activity}}'; }
    public function rules()
    {
        return [
            [['goal_id', 'title'], 'required'],
            [['goal_id', 'sequence', 'created_by', 'updated_by'], 'integer'],
            [['progress_percent'], 'number', 'min' => 0, 'max' => 100],
            [['due_date', 'evidence_note', 'reflection', 'completed_at', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['method_type'], 'string', 'max' => 30],
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
    public static function methodOptions()
    {
        return ['on_the_job' => 'เรียนรู้จากการปฏิบัติงาน', 'coaching' => 'Coaching / พี่เลี้ยง', 'course' => 'หลักสูตร / อบรม', 'project' => 'โครงการพิเศษ', 'self_learning' => 'เรียนรู้ด้วยตนเอง'];
    }
    public function getGoal() { return $this->hasOne(IdpGoal::class, ['id' => 'goal_id']); }
}
