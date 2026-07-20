<?php
namespace app\modules\appreciation\models;
use yii\db\ActiveRecord;
class AppreciationRedemption extends ActiveRecord
{
    const STATUS_PENDING='pending'; const STATUS_APPROVED='approved'; const STATUS_DELIVERED='delivered'; const STATUS_REJECTED='rejected';
    public static function tableName() { return '{{%appreciation_redemption}}'; }
    public static function statusLabels() { return [self::STATUS_PENDING=>'รอตรวจสอบ', self::STATUS_APPROVED=>'อนุมัติแล้ว', self::STATUS_DELIVERED=>'รับของแล้ว', self::STATUS_REJECTED=>'ไม่อนุมัติ']; }
    public function rules() { return [[['reward_id','program_year_id','emp_id','points_used','status','requested_at'], 'required'], [['reward_id','program_year_id','emp_id','points_used','processed_by'], 'integer'], [['requested_at','processed_at'], 'safe'], [['note'], 'string', 'max' => 500], [['status'], 'in', 'range' => array_keys(self::statusLabels())]]; }
    public function getReward() { return $this->hasOne(AppreciationReward::class, ['id' => 'reward_id']); }
}
