<?php

namespace app\modules\appreciation\models;

use Yii;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $start_at
 * @property string $end_at
 * @property string $goal_type
 * @property int $goal_value
 * @property string|null $reward_name
 * @property string|null $reward_description
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 */
class AppreciationChallenge extends \yii\db\ActiveRecord
{
    const GOAL_SEND_COUNT = 'send_count';
    const GOAL_RECEIVE_COUNT = 'receive_count';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';

    public static function goalTypeLabels()
    {
        return [
            self::GOAL_SEND_COUNT => 'ส่งคำขอบคุณ',
            self::GOAL_RECEIVE_COUNT => 'ได้รับคำขอบคุณ',
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_DRAFT => 'แบบร่าง',
            self::STATUS_ACTIVE => 'กำลังดำเนินการ',
            self::STATUS_ENDED => 'สิ้นสุด',
        ];
    }

    public static function tableName()
    {
        return '{{%appreciation_challenge}}';
    }

    public function rules()
    {
        return [
            [['name', 'start_at', 'end_at', 'goal_type', 'goal_value'], 'required'],
            [['description', 'reward_description'], 'string'],
            [['start_at', 'end_at', 'created_at', 'updated_at'], 'safe'],
            [['goal_value'], 'integer', 'min' => 1],
            [['name', 'reward_name'], 'string', 'max' => 255],
            [['goal_type'], 'in', 'range' => array_keys(self::goalTypeLabels())],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['end_at'], 'compare', 'compareAttribute' => 'start_at', 'operator' => '>='],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อกิจกรรม',
            'description' => 'รายละเอียด',
            'start_at' => 'เริ่ม',
            'end_at' => 'สิ้นสุด',
            'goal_type' => 'ประเภทเป้าหมาย',
            'goal_value' => 'เป้าหมาย (ครั้ง)',
            'reward_name' => 'ของรางวัล',
            'reward_description' => 'รายละเอียดรางวัล',
            'status' => 'สถานะ',
            'created_at' => 'สร้างเมื่อ',
        ];
    }

    public function getProgresses()
    {
        return $this->hasMany(AppreciationChallengeProgress::class, ['challenge_id' => 'id']);
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE
            && strtotime($this->start_at) <= time()
            && strtotime($this->end_at) >= time();
    }
}
