<?php

namespace app\modules\roster\models;

use app\modules\hr\models\Employees;

/**
 * ใบเปลี่ยนตัวเวร — ทางเดียวที่แก้ตารางเวรได้หลังผู้อำนวยการประกาศแล้ว
 *
 * 3 ชนิด:
 *   swap     แลกเวรกันสองทาง — ผู้ขอยกเวรของตัวเองให้ และรับเวรของอีกฝ่ายมาแทน
 *   give     ยกเวรให้ทางเดียว — อีกฝ่ายรับเวรไปเฉยๆ ไม่มีเวรแลกกลับ
 *   replace  หัวหน้าหน่วยเปลี่ยนตัวเอง กรณีฉุกเฉิน (ป่วยกะทันหัน) ไม่ต้องรอคู่กรณีตอบรับ
 *            แต่บังคับกรอกเหตุผล เพราะเป็นการแก้เอกสารที่ ผอ. อนุมัติไปแล้ว
 *
 * @property int         $id
 * @property int         $period_id
 * @property int         $item_id
 * @property int|null    $counter_item_id
 * @property string      $type
 * @property int         $from_emp_id
 * @property int         $to_emp_id
 * @property string      $status
 * @property array|null  $warnings
 */
class Swap extends RosterActiveRecord
{
    public const TYPE_SWAP = 'swap';
    public const TYPE_GIVE = 'give';
    public const TYPE_REPLACE = 'replace';

    public const STATUS_PENDING = 'pending';     // รอคู่กรณีตอบรับ
    public const STATUS_ACCEPTED = 'accepted';   // คู่กรณีรับแล้ว รอหัวหน้าอนุมัติ
    public const STATUS_APPROVED = 'approved';   // หัวหน้าอนุมัติ เวรถูกสลับแล้ว
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName()
    {
        return '{{%roster_swap}}';
    }

    public function rules()
    {
        return [
            [['period_id', 'item_id', 'type', 'from_emp_id', 'to_emp_id'], 'required'],
            [['period_id', 'item_id', 'counter_item_id', 'from_emp_id', 'to_emp_id',
                'requested_by', 'responded_by', 'approved_by', 'created_by', 'updated_by'], 'integer'],
            [['warnings', 'data_json', 'responded_at', 'approved_at', 'created_at', 'updated_at'], 'safe'],
            [['reason', 'ref'], 'string', 'max' => 255],
            [['type'], 'in', 'range' => [self::TYPE_SWAP, self::TYPE_GIVE, self::TYPE_REPLACE]],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['to_emp_id'], 'compare', 'compareAttribute' => 'from_emp_id', 'operator' => '!=',
                'message' => 'เลือกคนอื่นที่ไม่ใช่คนเดิม'],
            // เปลี่ยนตัวฉุกเฉินคือการแก้เอกสารที่อนุมัติแล้ว ต้องมีเหตุผลกำกับเสมอ
            [['reason'], 'required', 'when' => fn($model) => $model->type === self::TYPE_REPLACE,
                'whenClient' => false, 'message' => 'กรุณาระบุเหตุผลในการเปลี่ยนตัว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => 'ชนิด',
            'from_emp_id' => 'คนเดิม',
            'to_emp_id' => 'คนใหม่',
            'reason' => 'เหตุผล',
            'status' => 'สถานะ',
        ];
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_SWAP => 'แลกเวร',
            self::TYPE_GIVE => 'ยกเวรให้',
            self::TYPE_REPLACE => 'เปลี่ยนตัวฉุกเฉิน',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'รอคู่กรณีตอบรับ',
            self::STATUS_ACCEPTED => 'รอหัวหน้าอนุมัติ',
            self::STATUS_APPROVED => 'เปลี่ยนตัวแล้ว',
            self::STATUS_REJECTED => 'ไม่รับ',
            self::STATUS_CANCELLED => 'ยกเลิก',
        ];
    }

    public static function statusColors(): array
    {
        return [
            self::STATUS_PENDING => 'warning',
            self::STATUS_ACCEPTED => 'info',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? (string) $this->type;
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    public function getStatusColor(): string
    {
        return self::statusColors()[$this->status] ?? 'secondary';
    }

    public function getPeriod()
    {
        return $this->hasOne(Period::class, ['id' => 'period_id']);
    }

    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'item_id']);
    }

    public function getCounterItem()
    {
        return $this->hasOne(Item::class, ['id' => 'counter_item_id']);
    }

    public function getFromEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'from_emp_id']);
    }

    public function getToEmployee()
    {
        return $this->hasOne(Employees::class, ['id' => 'to_emp_id']);
    }

    /** ยังรอการดำเนินการอยู่ไหม */
    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_ACCEPTED], true);
    }

    /** คำเตือนจากกฎที่บันทึกไว้ตอนอนุมัติ */
    public function warningList(): array
    {
        $raw = $this->warnings;
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        return is_array($raw) ? $raw : [];
    }

    /** ใบเปลี่ยนตัวที่อนุมัติแล้วของรอบนี้ จัดเป็น [item_id => Swap[]] ใช้ติดสัญลักษณ์บนกริด */
    public static function approvedByItem(int $periodId): array
    {
        $map = [];
        $rows = static::find()
            ->where(['period_id' => $periodId, 'status' => self::STATUS_APPROVED])
            ->orderBy(['approved_at' => SORT_ASC])
            ->all();
        foreach ($rows as $row) {
            $map[(int) $row->item_id][] = $row;
            if ($row->counter_item_id) {
                $map[(int) $row->counter_item_id][] = $row;
            }
        }
        return $map;
    }
}
