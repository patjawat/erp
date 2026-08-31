<?php

namespace app\modules\task\models;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\db\ActiveQuery;

/**
 * งานมอบหมาย
 *
 * @property int         $id
 * @property string      $ref
 * @property string      $title              ชื่องาน
 * @property string|null $detail             รายละเอียด
 * @property int         $owner_unit_id      หน่วยงานเจ้าของงาน (tree.id)
 * @property int|null    $assignee_emp_id    ผู้รับผิดชอบ ว่างได้เมื่อรอจ่ายงาน
 * @property int|null    $assigner_emp_id    ผู้มอบหมาย
 * @property string|null $due_date           กำหนดเสร็จ
 * @property string|null $next_check_date    จุดตรวจถัดไป
 * @property string      $priority           normal | urgent
 * @property string      $status             pending | doing | done | cancelled
 * @property bool        $is_waiting         ติดรอผู้อื่น
 * @property int         $postpone_count     เลื่อนกำหนดมาแล้วกี่ครั้ง
 * @property string|null $source_module      dms | manual | pm
 * @property string|null $source_id
 * @property string|null $last_activity_at
 * @property string|null $completed_at
 * @property int|null    $completed_by
 *
 * @property Employees|null    $assignee
 * @property Employees|null    $assigner
 * @property Organization|null $ownerUnit
 * @property TaskActivity[]    $activities
 */
class Task extends TaskActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_DOING = 'doing';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_URGENT = 'urgent';

    public const SOURCE_DMS = 'dms';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_PM = 'pm';

    /** สถานะที่ยังนับว่าเป็นงานค้าง */
    public const OPEN_STATUSES = [self::STATUS_PENDING, self::STATUS_DOING];

    public static function tableName()
    {
        return '{{%task}}';
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'รอเริ่ม',
            self::STATUS_DOING => 'กำลังทำ',
            self::STATUS_DONE => 'เสร็จ',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function priorityLabels(): array
    {
        return [
            self::PRIORITY_NORMAL => 'ปกติ',
            self::PRIORITY_URGENT => 'ด่วน',
        ];
    }

    public function rules()
    {
        return [
            [['title', 'owner_unit_id'], 'required'],
            [['detail'], 'string'],
            [['owner_unit_id', 'assignee_emp_id', 'assigner_emp_id', 'postpone_count', 'completed_by'], 'integer'],
            [['due_date', 'next_check_date', 'last_activity_at', 'completed_at'], 'safe'],
            [['is_waiting'], 'boolean'],
            [['title'], 'string', 'max' => 255],
            [['ref', 'source_id'], 'string', 'max' => 64],
            [['priority', 'status'], 'string', 'max' => 20],
            [['source_module'], 'string', 'max' => 32],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['priority'], 'in', 'range' => array_keys(self::priorityLabels())],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['priority'], 'default', 'value' => self::PRIORITY_NORMAL],
            [['is_waiting'], 'default', 'value' => false],
            [['postpone_count'], 'default', 'value' => 0],
            [['assignee_emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employees::class, 'targetAttribute' => ['assignee_emp_id' => 'id']],
            [['assigner_emp_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employees::class, 'targetAttribute' => ['assigner_emp_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'ชื่องาน',
            'detail' => 'รายละเอียด',
            'owner_unit_id' => 'หน่วยงานเจ้าของ',
            'assignee_emp_id' => 'ผู้รับผิดชอบ',
            'assigner_emp_id' => 'ผู้มอบหมาย',
            'due_date' => 'กำหนดเสร็จ',
            'next_check_date' => 'จุดตรวจถัดไป',
            'priority' => 'ความสำคัญ',
            'status' => 'สถานะ',
            'is_waiting' => 'รอผู้อื่น',
        ];
    }

    public function getAssignee(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'assignee_emp_id']);
    }

    public function getAssigner(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'assigner_emp_id']);
    }

    public function getOwnerUnit(): ActiveQuery
    {
        return $this->hasOne(Organization::class, ['id' => 'owner_unit_id']);
    }

    public function getActivities(): ActiveQuery
    {
        return $this->hasMany(TaskActivity::class, ['task_id' => 'id'])->orderBy(['created_at' => SORT_DESC]);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    /** งานที่ส่งถึงหน่วยแล้วแต่ยังไม่มีใครรับผิดชอบ */
    public function isUnassigned(): bool
    {
        return $this->assignee_emp_id === null && $this->isOpen();
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /** เลยกำหนดแล้วกี่วัน (0 = ยังไม่เลย หรือไม่ได้กำหนดวัน) */
    public function overdueDays(): int
    {
        if (!$this->due_date || !$this->isOpen()) {
            return 0;
        }
        $diff = (strtotime(date('Y-m-d')) - strtotime($this->due_date)) / 86400;
        return $diff > 0 ? (int) $diff : 0;
    }

    /**
     * ข้อความอายุงานแบบที่คนอ่านเข้าใจทันที เช่น "7 สัปดาห์ที่ผ่านมา"
     * คืน null เมื่อยังไม่ถึงกำหนด เพื่อให้หน้าจอไม่ต้องแสดงอะไรเลย
     */
    public function ageText(): ?string
    {
        if (!$this->due_date || !$this->isOpen()) {
            return null;
        }

        $days = $this->overdueDays();
        if ($days === 0) {
            return $this->due_date === date('Y-m-d') ? 'ครบกำหนดวันนี้' : null;
        }
        if ($days === 1) {
            return 'เมื่อวาน';
        }
        if ($days < 7) {
            return $days . ' วันที่ผ่านมา';
        }
        if ($days < 30) {
            return intdiv($days, 7) . ' สัปดาห์ที่ผ่านมา';
        }
        if ($days < 365) {
            return intdiv($days, 30) . ' เดือนที่ผ่านมา';
        }
        return intdiv($days, 365) . ' ปีที่ผ่านมา';
    }

    public function priorityLabel(): string
    {
        return self::priorityLabels()[$this->priority] ?? $this->priority;
    }
}
