<?php

namespace app\modules\roster\models;

use app\modules\hr\models\Organization;
use Yii;

/**
 * รอบเวร = 1 หน่วยงาน × 1 เดือน — เป็นหน่วยของการตรวจสอบ อนุมัติ และประกาศ
 *
 * draft      หัวหน้าหน่วยกำลังจัด แก้ได้อิสระ
 * submitted  ส่งให้หัวหน้ากลุ่มงานตรวจสอบ ล็อกไม่ให้แก้
 * reviewed   หัวหน้ากลุ่มงานตรวจแล้ว รอผู้อำนวยการอนุมัติ
 * published  ผอ. อนุมัติและประกาศ เจ้าหน้าที่เห็นเวรตัวเองที่ /me/roster
 *            หลังจากนี้แก้กริดตรงๆ ไม่ได้ ต้องผ่านใบเปลี่ยนตัวเวร (roster_swap)
 * closed     สิ้นเดือนแล้ว ล็อกถาวร (ใช้เป็นฐานคิดค่าตอบแทนในเฟสถัดไป)
 *
 * ยุบ "อนุมัติ" กับ "ประกาศ" เป็นขั้นเดียว เพราะเมื่อ ผอ. อนุมัติแล้วต้องแจ้งทีมทันที
 * ไม่มีเหตุให้ค้างไว้ — คอลัมน์ approved_at/approved_by ยังบันทึกว่า ผอ. อนุมัติเมื่อไร
 *
 * @property int         $id
 * @property string|null $ref
 * @property int         $unit_id
 * @property int         $month
 * @property int         $year_ce
 * @property int|null    $thai_year
 * @property string      $status
 */
class Period extends RosterActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';

    /** สถานะที่ยังแก้กริดได้ */
    public const EDITABLE_STATUSES = [self::STATUS_DRAFT];

    /** สถานะที่เจ้าหน้าที่เห็นเวรตัวเอง และเปลี่ยนตัวได้ผ่านใบขอเท่านั้น */
    public const LIVE_STATUSES = [self::STATUS_PUBLISHED, self::STATUS_CLOSED];

    public static function tableName()
    {
        return '{{%roster_period}}';
    }

    public function rules()
    {
        return [
            [['unit_id', 'month', 'year_ce', 'title'], 'required'],
            [['unit_id', 'month', 'year_ce', 'thai_year', 'submitted_by', 'reviewed_by', 'approved_by',
                'published_by', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['month'], 'integer', 'min' => 1, 'max' => 12],
            [['note'], 'string'],
            [['data_json', 'submitted_at', 'reviewed_at', 'approved_at', 'published_at',
                'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title', 'ref'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusLabels())],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['title'], 'unique', 'targetAttribute' => ['unit_id', 'year_ce', 'month', 'title'],
                'message' => 'หน่วยงานนี้มีแผ่นชื่อนี้ในเดือนดังกล่าวอยู่แล้ว'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'unit_id' => 'หน่วยงาน',
            'month' => 'เดือน',
            'year_ce' => 'ปี (ค.ศ.)',
            'thai_year' => 'ปี (พ.ศ.)',
            'title' => 'ชื่อรอบ',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'ร่าง',
            self::STATUS_SUBMITTED => 'รอตรวจสอบ',
            self::STATUS_REVIEWED => 'รอ ผอ. อนุมัติ',
            self::STATUS_PUBLISHED => 'ประกาศแล้ว',
            self::STATUS_CLOSED => 'ปิดรอบ',
        ];
    }

    public static function statusColors(): array
    {
        return [
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_SUBMITTED => 'warning',
            self::STATUS_REVIEWED => 'info',
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_CLOSED => 'dark',
        ];
    }

    /** ประกาศแล้ว — แก้กริดไม่ได้ ต้องเปลี่ยนตัวผ่านใบขอ */
    public function isLive(): bool
    {
        return in_array($this->status, self::LIVE_STATUSES, true);
    }

    /** ยังเปลี่ยนตัวเวรได้ไหม — ปิดรอบแล้วห้ามแตะ */
    public function allowsSwap(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function getStatusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) $this->status;
    }

    public function getStatusColor(): string
    {
        return self::statusColors()[$this->status] ?? 'secondary';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, self::EDITABLE_STATUSES, true);
    }

    public function getUnit()
    {
        return $this->hasOne(Organization::class, ['id' => 'unit_id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['period_id' => 'id']);
    }

    public function unitName(): string
    {
        $unit = $this->unit;
        return $unit ? $unit->name : ('หน่วยงาน #' . $this->unit_id);
    }

    public static function monthNames(): array
    {
        return [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
    }

    public function monthLabel(): string
    {
        return (self::monthNames()[(int) $this->month] ?? '?') . ' ' . $this->thaiYear();
    }

    public function thaiYear(): int
    {
        return (int) ($this->thai_year ?: ($this->year_ce + 543));
    }

    public function daysInMonth(): int
    {
        return (int) date('t', mktime(0, 0, 0, (int) $this->month, 1, (int) $this->year_ce));
    }

    public function dateOfDay(int $day): string
    {
        return sprintf('%04d-%02d-%02d', $this->year_ce, $this->month, $day);
    }

    public function firstDate(): string
    {
        return $this->dateOfDay(1);
    }

    public function lastDate(): string
    {
        return $this->dateOfDay($this->daysInMonth());
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (!$this->thai_year && $this->year_ce) {
            $this->thai_year = (int) $this->year_ce + 543;
        }
        if (!$this->title) {
            $this->title = 'ตารางเวร ' . $this->monthLabel();
        }
        return true;
    }

    // ── ขอบเขตเวรของแผ่นนี้ ─────────────────────────────────────────────────
    //
    // 1 หน่วยงาน 1 เดือน มีได้หลายแผ่น (ตารางหลัก / Refer / On call / บ่ายดึก)
    // แต่ละแผ่นครอบเฉพาะเวรที่เลือกไว้ กริดและตัวนับจึงไม่ปนกัน

    /** @return int[] รหัสเวรของหน่วยที่แผ่นนี้ครอบ — ว่าง = ครอบทุกเวรของหน่วย */
    public function shiftIds(): array
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        $ids = is_array($json) ? ($json['unit_shift_ids'] ?? []) : [];
        return array_values(array_filter(array_map('intval', (array) $ids)));
    }

    /** @param int[] $ids */
    public function setShiftIds(array $ids): void
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        $json = is_array($json) ? $json : [];
        $json['unit_shift_ids'] = array_values(array_unique(array_map('intval', $ids)));
        $this->data_json = $json;
    }

    /** @return int[] เจ้าหน้าที่ต่างหน่วยที่เพิ่มมาช่วยขึ้นเวรในแผ่นนี้ */
    public function externalEmployeeIds(): array
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        $ids = is_array($json) ? ($json['external_employee_ids'] ?? []) : [];
        return array_values(array_unique(array_filter(array_map('intval', (array) $ids))));
    }

    /** @param int[] $ids */
    public function setExternalEmployeeIds(array $ids): void
    {
        $json = $this->data_json;
        if (is_string($json)) {
            $json = json_decode($json, true);
        }
        $json = is_array($json) ? $json : [];
        $json['external_employee_ids'] = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $this->data_json = $json;
    }

    /**
     * เวรที่แผ่นนี้ใช้ index ด้วย id — ถ้าไม่ได้ระบุขอบเขต ใช้ทุกเวรของหน่วย
     * @return UnitShift[]
     */
    public function sheetShifts(): array
    {
        $all = UnitShift::mapForUnit((int) $this->unit_id);
        $ids = $this->shiftIds();
        if (empty($ids)) {
            return $all;
        }
        $picked = [];
        foreach ($ids as $id) {
            if (isset($all[$id])) {
                $picked[$id] = $all[$id];
            }
        }
        // ถ้าเวรที่เลือกไว้ถูกปิดใช้งานไปหมด ให้ถอยไปใช้ทั้งหมด ดีกว่าโชว์กริดเปล่า
        return $picked ?: $all;
    }

    public function coversShift(int $unitShiftId): bool
    {
        return isset($this->sheetShifts()[$unitShiftId]);
    }

    /** เส้นทางสถานะที่อนุญาต — ดึงกลับมาแก้ (draft) ได้จากทุกขั้นก่อนปิดรอบ */
    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_DRAFT => [self::STATUS_SUBMITTED],
            self::STATUS_SUBMITTED => [self::STATUS_REVIEWED, self::STATUS_DRAFT],
            self::STATUS_REVIEWED => [self::STATUS_PUBLISHED, self::STATUS_DRAFT],
            self::STATUS_PUBLISHED => [self::STATUS_CLOSED, self::STATUS_DRAFT],
            self::STATUS_CLOSED => [],
        ];
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, static::allowedTransitions()[$this->status] ?? [], true);
    }

    /** เปลี่ยนสถานะพร้อมประทับเวลา/ผู้ทำ — คืน false ถ้าเปลี่ยนจากสถานะปัจจุบันไม่ได้ */
    public function transitionTo(string $status): bool
    {
        if (!$this->canTransitionTo($status)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $userId = static::currentUserId();
        if ($status === self::STATUS_SUBMITTED) {
            $this->submitted_at = $now;
            $this->submitted_by = $userId;
        } elseif ($status === self::STATUS_REVIEWED) {
            $this->reviewed_at = $now;
            $this->reviewed_by = $userId;
        } elseif ($status === self::STATUS_PUBLISHED) {
            // ผอ. อนุมัติและประกาศในจังหวะเดียว จึงบันทึกทั้งสองเวลา
            $this->approved_at = $now;
            $this->approved_by = $userId;
            $this->published_at = $now;
            $this->published_by = $userId;
        }
        $this->status = $status;
        return $this->save(false);
    }
}
