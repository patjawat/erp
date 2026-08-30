<?php

namespace app\modules\task\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveQuery;

/**
 * ความเคลื่อนไหวของงาน
 *
 * ทำสองหน้าที่: เป็นหลักฐานว่าใครทำอะไรเมื่อไหร่
 * และเป็นตัวชี้ว่างานไม่ขยับมานานแค่ไหน (ใช้จับงานใกล้ร้อน)
 *
 * @property int         $id
 * @property string      $ref
 * @property int         $task_id
 * @property int|null    $emp_id
 * @property string      $action
 * @property string|null $note
 * @property string|null $created_at
 * @property int|null    $created_by
 *
 * @property Task            $task
 * @property Employees|null  $employee
 */
class TaskActivity extends TaskActiveRecord
{
    public const ACTION_CREATE = 'create';
    public const ACTION_ASSIGN = 'assign';
    public const ACTION_REASSIGN = 'reassign';
    public const ACTION_START = 'start';
    public const ACTION_NOTE = 'note';
    public const ACTION_POSTPONE = 'postpone';
    public const ACTION_COMPLETE = 'complete';
    public const ACTION_CANCEL = 'cancel';

    public static function tableName()
    {
        return '{{%task_activity}}';
    }

    public static function actionLabels(): array
    {
        return [
            self::ACTION_CREATE => 'สร้างงาน',
            self::ACTION_ASSIGN => 'มอบหมาย',
            self::ACTION_REASSIGN => 'เปลี่ยนผู้รับผิดชอบ',
            self::ACTION_START => 'เริ่มทำ',
            self::ACTION_NOTE => 'บันทึกเพิ่ม',
            self::ACTION_POSTPONE => 'เลื่อนกำหนด',
            self::ACTION_COMPLETE => 'ปิดงาน',
            self::ACTION_CANCEL => 'ยกเลิกงาน',
        ];
    }

    public function rules()
    {
        return [
            [['task_id', 'action'], 'required'],
            [['task_id', 'emp_id', 'created_by'], 'integer'],
            [['note'], 'string'],
            [['created_at'], 'safe'],
            [['ref'], 'string', 'max' => 64],
            [['action'], 'string', 'max' => 32],
            [['action'], 'in', 'range' => array_keys(self::actionLabels())],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'task_id' => 'งาน',
            'emp_id' => 'ผู้ทำรายการ',
            'action' => 'การกระทำ',
            'note' => 'บันทึก',
            'created_at' => 'เมื่อ',
        ];
    }

    public function getTask(): ActiveQuery
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    public function getEmployee(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function actionLabel(): string
    {
        return self::actionLabels()[$this->action] ?? $this->action;
    }
}
