<?php

namespace app\modules\qms\models;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/**
 * รายการ checklist ในรอบปีหนึ่ง (instance ของ requirement; ตัวที่คัดลอกข้ามปี)
 *
 * @property int $id
 * @property int $cycle_id
 * @property int $requirement_id
 * @property string $title_snapshot
 * @property int|null $assignee_unit_id
 * @property int|null $assignee_emp_id
 * @property string|null $due_date
 * @property string $status
 * @property string|null $note
 * @property int $sort
 */
class CycleItem extends QmsActiveRecord
{
    public const STATUS_NONE = 'none';               // ยังไม่มีหลักฐาน
    public const STATUS_IN_PROGRESS = 'in_progress'; // มีบางส่วน
    public const STATUS_COMPLETE = 'complete';       // ครบ
    public const STATUS_NA = 'na';                   // ไม่เกี่ยวข้อง

    public static function tableName(): string
    {
        return '{{%qms_cycle_item}}';
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NONE => 'ยังขาด',
            self::STATUS_IN_PROGRESS => 'กำลังดำเนินการ',
            self::STATUS_COMPLETE => 'ครบถ้วน',
            self::STATUS_NA => 'ไม่เกี่ยวข้อง',
        ];
    }

    /** สี badge ต่อสถานะ (bootstrap tone) */
    public static function statusTones(): array
    {
        return [
            self::STATUS_NONE => 'danger',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETE => 'success',
            self::STATUS_NA => 'secondary',
        ];
    }

    public function rules(): array
    {
        return [
            [['cycle_id', 'requirement_id', 'title_snapshot'], 'required'],
            [['cycle_id', 'requirement_id', 'assignee_unit_id', 'assignee_emp_id', 'sort'], 'integer'],
            [['title_snapshot'], 'string', 'max' => 500],
            [['due_date'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['status'], 'default', 'value' => self::STATUS_NONE],
            [['note'], 'string'],
            [['sort'], 'default', 'value' => 0],
            [['cycle_id', 'requirement_id'], 'unique', 'targetAttribute' => ['cycle_id', 'requirement_id']],
            [['cycle_id'], 'exist', 'targetClass' => Cycle::class, 'targetAttribute' => 'id'],
            [['requirement_id'], 'exist', 'targetClass' => Requirement::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'requirement_id' => 'ข้อกำหนด',
            'title_snapshot' => 'ชื่อข้อกำหนด',
            'assignee_unit_id' => 'ผู้รับผิดชอบ (หน่วยงาน)',
            'assignee_emp_id' => 'ผู้รับผิดชอบ (บุคคล)',
            'due_date' => 'กำหนดส่ง',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public function getCycle()
    {
        return $this->hasOne(Cycle::class, ['id' => 'cycle_id']);
    }

    public function getRequirement()
    {
        return $this->hasOne(Requirement::class, ['id' => 'requirement_id']);
    }

    public function getEvidences()
    {
        return $this->hasMany(Evidence::class, ['cycle_item_id' => 'id']);
    }

    public function getAssigneeUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'assignee_unit_id']);
    }

    public function getAssigneeEmp()
    {
        return $this->hasOne(Employees::class, ['id' => 'assignee_emp_id']);
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function statusTone(): string
    {
        return self::statusTones()[$this->status] ?? 'secondary';
    }
}
