<?php

namespace app\modules\appreciation\models;

use app\modules\hr\models\Employees;
use Yii;

/**
 * @property int $id
 * @property int $from_emp_id
 * @property int $to_emp_id
 * @property string $message
 * @property string|null $badge_type
 * @property int $points_given
 * @property string $created_at
 * @property Employees $fromEmp
 * @property Employees $toEmp
 */
class Appreciation extends \yii\db\ActiveRecord
{
    const BADGE_TEAM_PLAYER = 'team_player';
    const BADGE_PROBLEM_SOLVER = 'problem_solver';
    const BADGE_HELPFUL = 'helpful';
    const BADGE_LEADER = 'leader';
    const BADGE_OTHER = 'other';

    public static function badgeLabels()
    {
        return [
            self::BADGE_TEAM_PLAYER => 'Team Player',
            self::BADGE_PROBLEM_SOLVER => 'Problem Solver',
            self::BADGE_HELPFUL => 'ช่วยเหลือเก่ง',
            self::BADGE_LEADER => 'ผู้นำทีม',
            self::BADGE_OTHER => 'อื่นๆ',
        ];
    }

    /** Emoji ตาม Core Value / badge_type สำหรับแสดงในฟีด */
    public static function badgeEmojis()
    {
        return [
            self::BADGE_TEAM_PLAYER => '🤝',
            self::BADGE_PROBLEM_SOLVER => '💡',
            self::BADGE_HELPFUL => '🙌',
            self::BADGE_LEADER => '👑',
            self::BADGE_OTHER => '❤️',
        ];
    }

    public static function tableName()
    {
        return '{{%appreciation}}';
    }

    public function rules()
    {
        return [
            [['from_emp_id', 'to_emp_id', 'message'], 'required'],
            [['from_emp_id', 'to_emp_id', 'points_given'], 'integer'],
            [['message'], 'string'],
            [['created_at'], 'safe'],
            [['badge_type'], 'string', 'max' => 64],
            [['badge_type'], 'in', 'range' => array_keys(self::badgeLabels())],
            [['to_emp_id'], 'compare', 'compareAttribute' => 'from_emp_id', 'operator' => '!='],
            [['from_emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => 'id'],
            [['to_emp_id'], 'exist', 'targetClass' => Employees::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_emp_id' => 'ผู้ส่ง',
            'to_emp_id' => 'ผู้รับ',
            'message' => 'ข้อความคำขอบคุณ',
            'badge_type' => 'ประเภทคำขอบคุณ',
            'points_given' => 'คะแนน',
            'created_at' => 'เมื่อ',
        ];
    }

    public function getFromEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'from_emp_id']);
    }

    public function getToEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'to_emp_id']);
    }

    public function getLikes()
    {
        return $this->hasMany(AppreciationLike::class, ['appreciation_id' => 'id']);
    }

    public function getLikeCount()
    {
        return (int) $this->getLikes()->count();
    }

    /** ตรวจว่า emp_id นี้กด like แล้วหรือยัง */
    public function isLikedBy($empId)
    {
        return $this->getLikes()->andWhere(['emp_id' => $empId])->exists();
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if ($this->isNewRecord && empty($this->created_at)) {
                $this->created_at = date('Y-m-d H:i:s');
            }
            $mod = $this->getModule();
            if ($mod && $this->points_given <= 0) {
                $this->points_given = $mod->pointsPerThank;
            }
            return true;
        }
        return false;
    }

    protected function getModule()
    {
        return Yii::$app->getModule('appreciation');
    }
}
