<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\modules\booking\models\Vehicle;
use app\modules\hr\models\Employees;
use Yii;

/**
 * Vehicle booking business logic สำหรับ mobile module.
 *
 * เกิดจากการสกัด logic ซ้ำ 5 จุดในเดิม controller (Booking/View/Update/Cancel):
 * - find + owner guard
 * - status Pending guard
 * - Thai d/m/Y ↔ Gregorian Y-m-d conversion + system field assignment
 * - autonumber code generation + transaction-wrapped save
 * - cancel (status flip with whitelisted save attrs)
 *
 * Controller รับผิดชอบ request/response เท่านั้น; ที่นี่รับผิดชอบ "what happens to the model".
 */
class MobileVehicleService
{
    /**
     * สร้าง Vehicle ใหม่พร้อม default ที่ใช้แสดงในฟอร์มเปิดครั้งแรก.
     */
    public function newWithDefaults(): Vehicle
    {
        $model = new Vehicle();
        $model->date_start      = date('d/m/Y');
        $model->date_end        = date('d/m/Y');
        $model->time_start      = '08:00';
        $model->time_end        = '17:00';
        $model->go_type         = 1; // ไปกลับวันเดียวกัน
        $model->vehicle_type_id = 'general';
        $model->urgent          = 'ปกติ';
        $model->status          = 'Pending';
        return $model;
    }

    /**
     * โหลด Vehicle ตาม id ถ้าเป็นของ employee ที่ระบุ.
     * คืน null ถ้าไม่พบหรือไม่ใช่เจ้าของ — caller จัดการ flash + redirect.
     */
    public function findOwnedById(int $id, string $empId): ?Vehicle
    {
        $vehicle = Vehicle::findOne($id);
        if (!$vehicle || (string) $vehicle->emp_id !== $empId) {
            return null;
        }
        return $vehicle;
    }

    /**
     * คำขอแก้ไข/ยกเลิกได้เฉพาะเมื่อสถานะอยู่ใน Pending bucket.
     */
    public function canEdit(Vehicle $vehicle): bool
    {
        return in_array((string) $vehicle->status, ['Pending', 'รออนุมัติ'], true);
    }

    /**
     * รับ Vehicle ที่ load(post) แล้ว, ทำ Thai↔Greg conversion, set system fields,
     * generate code (ถ้าเป็น record ใหม่), แล้ว save แบบ transactional.
     *
     * Mutates $model. Caller ใช้ {@see restoreThaiDates()} ถ้าจะ re-render หลัง fail.
     *
     * @return array{ok:bool, errors:array, exception:?\Throwable}
     */
    public function prepareAndSave(Vehicle $model, Employees $me): array
    {
        $isNew = $model->isNewRecord;

        // Thai date d/m/Y → Gregorian Y-m-d
        $startGreg = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
        $endGreg   = $model->date_end   ? AppHelper::convertToGregorian($model->date_end)   : $startGreg;

        // ไปกลับวันเดียว: end mirror start
        if ((int) $model->go_type === 1) {
            $endGreg = $startGreg;
        }

        $model->date_start = $startGreg;
        $model->date_end   = $endGreg;
        $model->time_start = substr((string) $model->time_start, 0, 5);
        $model->time_end   = substr((string) $model->time_end, 0, 5);
        $model->emp_id     = (string) $me->id;
        $model->leader_id  = (string) ($me->head_id ?? $me->id);
        $model->thai_year  = $startGreg ? (int) AppHelper::YearBudget($startGreg) : (int) date('Y') + 543;
        $model->status     = 'Pending';
        if (empty($model->vehicle_type_id)) $model->vehicle_type_id = 'general';
        if (empty($model->urgent))          $model->urgent          = 'ปกติ';

        if ($isNew) {
            try {
                $model->code = \mdm\autonumber\AutoNumber::generate('VEH' . date('ymd') . '-???');
            } catch (\Throwable $e) {
                $model->code = 'VEH' . date('Ymd') . '-' . substr(uniqid(), -4);
            }
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                $tx->commit();
                return ['ok' => true, 'errors' => [], 'exception' => null];
            }
            $tx->rollBack();
            return ['ok' => false, 'errors' => $model->getFirstErrors(), 'exception' => null];
        } catch (\Throwable $e) {
            $tx->rollBack();
            return ['ok' => false, 'errors' => $model->getFirstErrors(), 'exception' => $e];
        }
    }

    /**
     * แปลง Gregorian date ใน model กลับเป็น Thai d/m/Y เพื่อให้ DatepickerThai
     * แสดงผลถูกต้องตอน re-render (ทั้งหลัง save fail และตอนเปิดฟอร์มแก้ไขครั้งแรก).
     */
    public function restoreThaiDates(Vehicle $model): void
    {
        foreach (['date_start', 'date_end'] as $attr) {
            $val = (string) ($model->$attr ?? '');
            if ($val === '') continue;

            // Already Thai d/m/Y format: leave alone (might be Gregorian-year d/m/Y; promote if so)
            if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $val, $m)) {
                if ((int) $m[3] < 2400) {
                    $model->$attr = $m[1] . '/' . $m[2] . '/' . ((int) $m[3] + 543);
                }
                continue;
            }

            // DB Gregorian Y-m-d → Thai d/m/Y
            $ts = strtotime($val);
            if ($ts) {
                $model->$attr = date('d/m/', $ts) . ((int) date('Y', $ts) + 543);
            }
        }
    }

    /**
     * เปลี่ยนสถานะ Vehicle เป็น Cancel โดย save แค่ attribute ที่อนุญาต.
     */
    public function cancel(Vehicle $vehicle): bool
    {
        $vehicle->status = 'Cancel';
        return (bool) $vehicle->save(false, ['status', 'updated_at', 'updated_by']);
    }

    /**
     * รายการ Vehicle ของ employee ที่ระบุ, เรียงจากใหม่ไปเก่า, จำกัด limit.
     * @return Vehicle[]
     */
    public function findMyBookings(string $empId, int $limit = 50): array
    {
        try {
            return Vehicle::find()
                ->where(['emp_id' => $empId])
                ->andWhere(['IS', 'deleted_at', null])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit($limit)
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
