<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\modules\am\models\Asset;
use app\modules\am\models\AssetSearch;
use app\modules\booking\models\Vehicle;
use app\modules\booking\models\VehicleDetail;
use app\modules\booking\components\VehicleTelegramNotify;
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
     * รายการรถยนต์สำหรับ wizard เลือกรถ.
     *
     * Mirror 1:1 จาก {@see \app\modules\booking\controllers\AssetController::actionIndex()} —
     * ใช้ AssetSearch model + dataProvider->query pattern เดียวกัน เพื่อให้แน่ใจว่า
     * scope/qualifier/cast เหมือนกันตามที่ระบบ desktop ทำงานอยู่:
     * - `asset_group_id = 4` (ครุภัณฑ์ยานพาหนะ)
     * - `asset_type_id IN ['VEH']`
     * - `asset_status <> 2` (ตัดที่จำหน่าย/ปลดระวาง)
     * - `asset.deleted_at IS NULL` + `asset.license_plate IS NOT NULL`
     *
     * @return array<int, array{license_plate:string, title:string, asset_type:string, image:string}>
     */
    public function listCars(): array
    {
        $rows = [];
        $assets = [];
        try {
            // ใช้ Asset::find() ตรง ๆ พร้อม qualifier 'asset.' กัน ambiguous +
            // log SQL ตอน debug. Logic เทียบเท่า AssetController::actionIndex().
            $query = Asset::find()
                ->andWhere(['asset.asset_group_id' => 4])
                ->andWhere(['IN', 'asset.asset_type_id', ['VEH']])
                ->andWhere(['<>', 'asset.asset_status', 2])
                ->andWhere('asset.deleted_at IS NULL')
                ->andWhere('asset.license_plate IS NOT NULL')
                ->andWhere(['<>', 'asset.license_plate', ''])
                ->orderBy(['asset.license_plate' => SORT_ASC]);

            $assets = $query->all();
            Yii::info('listCars query returned ' . count($assets) . ' rows; SQL: ' . $query->createCommand()->getRawSql(), __METHOD__);

            foreach ($assets as $asset) {
                $image = '';
                try {
                    $img = $asset->ShowImg();
                    if (is_array($img) && !empty($img['image'])) $image = (string) $img['image'];
                } catch (\Throwable $e) {
                    $image = '';
                }

                // ชื่อรถ: ใช้ asset_name ก่อน, fallback ที่ data_json['asset_type_text'] หรือ code
                $title = '';
                try {
                    $title = trim((string) ($asset->asset_name ?? ''));
                    if ($title === '' && is_array($asset->data_json ?? null)) {
                        $title = (string) ($asset->data_json['asset_type_text'] ?? '');
                    }
                } catch (\Throwable $e) {
                    $title = '';
                }
                if ($title === '') $title = (string) ($asset->code ?? $asset->license_plate);

                $assetType = '';
                try {
                    if (is_array($asset->data_json ?? null)) {
                        $assetType = (string) ($asset->data_json['asset_type_text'] ?? '');
                    }
                } catch (\Throwable $e) {
                    $assetType = '';
                }

                $rows[] = [
                    'license_plate' => (string) $asset->license_plate,
                    'title'         => $title,
                    'asset_type'    => $assetType,
                    'image'         => $image,
                ];
            }
        } catch (\Throwable $e) {
            // Log แล้วคืน [] (caller จะแสดง empty state ที่ทำให้สังเกตเห็น)
            Yii::error('listCars failed: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            return [];
        }
        return $rows;
    }

    /**
     * รายชื่อพนักงานขับรถ (Employees role='driver') + ข้อมูลที่ใช้แสดงใน card grid.
     *
     * @return array<int, array{id:int, fullname:string, position:string, phone:string, avatar:string}>
     */
    public function listDrivers(): array
    {
        $rows = [];
        try {
            $employees = Employees::find()
                ->from('employees e')
                ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
                ->where(['a.item_name' => 'driver'])
                ->all();

            foreach ($employees as $emp) {
                $avatar = '';
                try {
                    if (method_exists($emp, 'showAvatar')) $avatar = (string) $emp->showAvatar();
                } catch (\Throwable $e) {
                    $avatar = '';
                }

                $position = '';
                try {
                    if (method_exists($emp, 'positionName')) $position = (string) $emp->positionName();
                } catch (\Throwable $e) {
                    $position = '';
                }

                $rows[] = [
                    'id'       => (int) $emp->id,
                    'fullname' => (string) ($emp->fullname ?? ''),
                    'position' => $position,
                    'phone'    => (string) ($emp->phone ?? ''),
                    'avatar'   => $avatar,
                ];
            }
        } catch (\Throwable $e) {
            return [];
        }
        return $rows;
    }

    /**
     * รายการ Vehicle ของ employee ที่ระบุ, เรียงจากใหม่ไปเก่า, จำกัด limit.
     * @return Vehicle[]
     */
    public function findMyBookings(string $empId, int $limit = 50, ?int $thaiYear = null): array
    {
        try {
            $query = Vehicle::find()
                ->where(['emp_id' => $empId])
                ->andWhere(['IS', 'deleted_at', null])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit($limit);

            if ($thaiYear !== null) {
                $query->andWhere(['thai_year' => $thaiYear]);
            }

            return $query->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * รายการปีงบประมาณสำหรับ filter dropdown.
     *
     * @return array<int,string>
     */
    public function listFiscalYears(int $back = 10): array
    {
        $current = (int) AppHelper::YearBudget();
        $years = [];
        for ($y = $current; $y > $current - $back; $y--) {
            $years[$y] = 'พ.ศ. ' . $y;
        }
        return $years;
    }

    /**
     * ภารกิจรถที่มอบหมายให้พนักงานขับรถคนปัจจุบัน.
     *
     * @return VehicleDetail[]
     */
    public function findDriverMissions(string $empId, int $limit = 100, ?int $thaiYear = null): array
    {
        try {
            return $this->driverMissionQuery($empId, $thaiYear)
                ->orderBy([
                    'vd.date_start' => SORT_DESC,
                    'vd.time_start' => SORT_DESC,
                    'vd.id' => SORT_DESC,
                ])
                ->limit($limit)
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * นับภารกิจที่ยังต้องติดตามหรือยังไม่ปิดงาน.
     */
    public function countOpenDriverMissions(string $empId, ?int $thaiYear = null): int
    {
        try {
            return (int) $this->driverMissionQuery($empId, $thaiYear)
                ->andWhere([
                    'or',
                    ['is', 'vd.status', null],
                    ['vd.status' => ''],
                    ['not in', 'vd.status', ['Success', 'Cancel']],
                ])
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * โหลดภารกิจเดียว โดยล็อกสิทธิ์ว่าเป็นงานของพนักงานขับรถคนนี้เท่านั้น.
     */
    public function findDriverMissionById(int $id, string $empId): ?VehicleDetail
    {
        try {
            return $this->driverMissionQuery($empId)
                ->andWhere(['vd.id' => $id])
                ->one();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * แปลงวันที่ DB เป็น Thai d/m/Y สำหรับฟอร์ม mobile.
     */
    public function restoreThaiDatesForDetail(VehicleDetail $detail): void
    {
        foreach (['date_start', 'date_end'] as $attr) {
            $val = (string) ($detail->$attr ?? '');
            if ($val === '') continue;

            if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $val, $m)) {
                if ((int) $m[3] < 2400) {
                    $detail->$attr = $m[1] . '/' . $m[2] . '/' . ((int) $m[3] + 543);
                }
                continue;
            }

            $ts = strtotime($val);
            if ($ts) {
                $detail->$attr = date('d/m/', $ts) . ((int) date('Y', $ts) + 543);
            }
        }

        $detail->time_start = substr((string) $detail->time_start, 0, 5);
        $detail->time_end   = substr((string) $detail->time_end, 0, 5);
    }

    /**
     * สร้าง ref ชั่วคราวให้ภารกิจ เพื่อใช้เป็น folder ผูกไฟล์แนบก่อนบันทึก.
     */
    public function ensureDriverMissionRef(VehicleDetail $detail): void
    {
        if (trim((string) $detail->ref) !== '') {
            return;
        }
        $detail->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
    }

    /**
     * บันทึกผลภารกิจรถจาก mobile: เวลา เลขไมล์ ระยะทาง น้ำมัน และสถานะ.
     *
     * @return array{ok:bool, errors:array, exception:?\Throwable}
     */
    public function prepareAndSaveDriverMission(VehicleDetail $detail): array
    {
        $this->ensureDriverMissionRef($detail);
        $previousStatus = (string) $detail->getOldAttribute('status');
        $detail->date_start = $detail->date_start ? AppHelper::convertToGregorian($detail->date_start) : null;
        $detail->date_end   = $detail->date_end ? AppHelper::convertToGregorian($detail->date_end) : $detail->date_start;
        $detail->time_start = substr((string) $detail->time_start, 0, 5);
        $detail->time_end   = substr((string) $detail->time_end, 0, 5);
        if (empty($detail->status)) {
            $detail->status = 'Pass';
        }

        $startMileage = is_numeric($detail->mileage_start) ? (float) $detail->mileage_start : null;
        $endMileage   = is_numeric($detail->mileage_end) ? (float) $detail->mileage_end : null;
        if ($startMileage !== null && $endMileage !== null) {
            $detail->distance_km = max(0, $endMileage - $startMileage);
        }

        try {
            if ($detail->save()) {
                if ($detail->vehicle) {
                    $detail->vehicle->status = (string) $detail->status;
                    $detail->vehicle->save(false, ['status', 'updated_at', 'updated_by']);
                    // เสร็จสิ้นภารกิจ → ส่งลิงก์แบบประเมินความพึงพอใจให้ผู้ขอทาง Telegram
                    if ((string) $detail->status === VehicleDetail::STATUS_SUCCESS
                        && $previousStatus !== VehicleDetail::STATUS_SUCCESS) {
                        VehicleTelegramNotify::notifyRequesterSurvey($detail->vehicle, $detail);
                    }
                }
                return ['ok' => true, 'errors' => [], 'exception' => null];
            }
            return ['ok' => false, 'errors' => $detail->getFirstErrors(), 'exception' => null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => $detail->getFirstErrors(), 'exception' => $e];
        }
    }

    private function driverMissionQuery(string $empId, ?int $thaiYear = null)
    {
        $query = VehicleDetail::find()
            ->alias('vd')
            ->leftJoin(['v' => Vehicle::tableName()], 'v.id = vd.vehicle_id')
            ->with([
                'vehicle.employee',
                'vehicle.locationOrg',
                'car',
                'driver',
                'vehicleDetailStatus',
            ])
            ->where(['vd.driver_id' => $empId])
            ->andWhere(['is', 'vd.deleted_at', null]);

        if ($thaiYear !== null) {
            $query->andWhere(['v.thai_year' => $thaiYear]);
        }

        return $query;
    }
}
