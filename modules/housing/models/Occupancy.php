<?php

declare(strict_types=1);

namespace app\modules\housing\models;

use app\modules\hr\models\Employees;
use yii\db\ActiveQuery;

final class Occupancy extends HousingActiveRecord
{
    public const STATUS_ALLOCATED = 'allocated';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CANCELLED = 'cancelled';

    public static function tableName(): string
    {
        return '{{%housing_occupancy}}';
    }

    public function rules(): array
    {
        return [
            [['emp_id', 'payer_emp_id', 'unit_id', 'occupancy_type'], 'required'],
            [['request_id', 'emp_id', 'payer_emp_id', 'unit_id', 'room_id', 'created_by', 'updated_by'], 'integer'],
            [['allocated_at', 'start_date', 'end_date'], 'safe'],
            [['move_out_reason', 'note'], 'string'],
            [['occupancy_type'], 'in', 'range' => array_keys(Unit::modeOptions())],
            [['status'], 'in', 'range' => [self::STATUS_ALLOCATED, self::STATUS_ACTIVE, self::STATUS_ENDED, self::STATUS_CANCELLED]],
            [['status'], 'default', 'value' => self::STATUS_ALLOCATED],
            [['room_id'], 'validateRoomBelongsToUnit'],
            [['unit_id'], 'validateAvailability'],
        ];
    }

    public function validateRoomBelongsToUnit(string $attribute): void
    {
        if ($this->room_id && !Room::find()->where(['id' => $this->room_id, 'unit_id' => $this->unit_id])->exists()) {
            $this->addError($attribute, 'ห้องย่อยที่เลือกไม่ได้อยู่ในห้องนี้');
        }
    }

    public function validateAvailability(string $attribute): void
    {
        if (!in_array($this->status, [self::STATUS_ALLOCATED, self::STATUS_ACTIVE], true)) {
            return;
        }
        $query = self::find()->where(['status' => [self::STATUS_ALLOCATED, self::STATUS_ACTIVE]]);
        if (!$this->isNewRecord) {
            $query->andWhere(['<>', 'id', $this->id]);
        }
        $query->andWhere(['unit_id' => $this->unit_id]);
        if ($this->room_id) {
            $query->andWhere(['or', ['room_id' => $this->room_id], ['room_id' => null]]);
        }
        if ($query->exists()) {
            $this->addError($attribute, 'ที่พักนี้ถูกจัดสรรหรือมีผู้พักอยู่แล้ว');
        }
    }

    public function getUnit(): ActiveQuery
    {
        return $this->hasOne(Unit::class, ['id' => 'unit_id']);
    }

    public function getRoom(): ActiveQuery
    {
        return $this->hasOne(Room::class, ['id' => 'room_id']);
    }

    public function getResidents(): ActiveQuery
    {
        return $this->hasMany(Resident::class, ['occupancy_id' => 'id']);
    }

    /**
     * นับจำนวนผู้พักอาศัยของ occupancy นี้
     * - total  = ผู้พัก active ทั้งหมด (รวมเจ้าหน้าที่ผู้ครอบครอง)
     * - over15 = ผู้ที่คิดค่าใช้จ่ายรายหัวและอายุเกิน 15 ปี (เจ้าหน้าที่นับเป็นผู้ใหญ่เสมอ)
     *
     * @param string|null $chargeEndDate วันสิ้นสุดรอบสำหรับอ้างอิงอายุ (ว่าง = ใช้วันนี้)
     * @return array{total:int,over15:int}
     */
    public function occupantCounts(?string $chargeEndDate = null): array
    {
        $active = array_filter(
            $this->residents,
            static fn(Resident $r): bool => $r->status === 'active'
        );
        $hasEmployeeRow = false;
        foreach ($active as $r) {
            if ($r->resident_type === 'employee') {
                $hasEmployeeRow = true;
                break;
            }
        }
        $hasOfficer = (int) $this->emp_id > 0;
        // ถ้ายังไม่มีแถวเจ้าหน้าที่ (occupancy ที่ไม่ได้ผ่าน flow รับมอบ) ให้บวกเจ้าหน้าที่เข้าไป 1
        $total = count($active) + (!$hasEmployeeRow && $hasOfficer ? 1 : 0);

        $cutoff = date('Y-m-d', strtotime(($chargeEndDate ?: date('Y-m-d')) . ' -15 years'));
        $over15 = $hasOfficer ? 1 : 0; // เจ้าหน้าที่ผู้ครอบครองนับเป็นผู้ใหญ่เสมอ
        foreach ($active as $r) {
            if ($r->resident_type === 'employee') {
                continue; // นับเจ้าหน้าที่ไปแล้ว
            }
            if (!$r->count_for_charge) {
                continue;
            }
            if ($r->birth_date && $r->birth_date <= $cutoff) {
                $over15++;
            }
        }

        return ['total' => $total, 'over15' => $over15];
    }

    public function getEmployee(): ActiveQuery
    {
        return $this->hasOne(Employees::class, ['id' => 'emp_id']);
    }

    public function getHandover(): ActiveQuery
    {
        return $this->hasOne(Handover::class, ['occupancy_id' => 'id']);
    }

    public function getCheckout(): ActiveQuery
    {
        return $this->hasOne(Checkout::class, ['occupancy_id' => 'id']);
    }
}
