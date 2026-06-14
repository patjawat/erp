<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\modules\booking\models\Meeting;
use app\modules\booking\models\Room;
use app\modules\booking\models\RoomLayout;
use app\modules\hr\models\Employees;
use Yii;

/**
 * Meeting booking business logic สำหรับ mobile module.
 *
 * เกิดจากการสกัด logic ซ้ำใน controller (BookingMeeting/View/Update/Cancel/RoomAvailability):
 * - room metadata loading (duplicate ระหว่าง actionBookingMeeting + actionMeetingUpdate)
 * - Thai d/m/Y ↔ Gregorian Y-m-d conversion + system field assignment + autonumber
 * - manual error checks (date range, time, phone, overlap) ก่อน save
 * - overlap detection (hasMeetingRoomOverlap private method ใน controller เดิม)
 * - cancel (status flip with whitelisted save attrs)
 *
 * จุดต่างจาก MobileVehicleService:
 * - ไม่ใช้ transaction (เคารพพฤติกรรมเดิม — vehicle ใช้ tx, meeting ไม่ใช้)
 * - มี manual error pipeline ก่อน save (overlap check, time range, phone required)
 * - มี availability AJAX endpoint helper (checkAvailability)
 */
class MobileMeetingService
{
    /**
     * สร้าง Meeting ใหม่พร้อม default ที่ใช้ในฟอร์มเปิดครั้งแรก.
     */
    public function newWithDefaults(Employees $me): Meeting
    {
        $model = new Meeting();
        $model->time_start = '09:00';
        $model->time_end   = '10:00';
        $model->emp_number = 1;

        // Today's Thai-year date ใน d/m/Y form (convertToGregorian round-trips ได้)
        $todayThai = date('d/m/') . ((int) date('Y') + 543);
        $model->date_start = $todayThai;
        $model->date_end   = $todayThai;

        $model->data_json = [
            'period_time'     => 'กำหนดเอง',
            'phone'           => (string) ($me->phone ?? ''),
            'meeting_details' => '',
            'equipment'       => [],
        ];

        return $model;
    }

    /**
     * โหลด Meeting ตาม id ถ้าเป็นของ employee ที่ระบุ.
     */
    public function findOwnedById(int $id, string $empId): ?Meeting
    {
        $meeting = Meeting::findOne($id);
        if (!$meeting || (string) $meeting->emp_id !== $empId) {
            return null;
        }
        return $meeting;
    }

    /**
     * คำขอแก้ไข/ยกเลิกได้เฉพาะเมื่อสถานะอยู่ใน Pending bucket.
     */
    public function canEdit(Meeting $meeting): bool
    {
        return in_array((string) $meeting->status, ['Pending', 'รออนุมัติ'], true);
    }

    /**
     * รายการ Meeting ของ employee ที่ระบุ, เรียงจากใหม่ไปเก่า, จำกัด limit.
     *
     * @param bool $withDeleted ถ้า true จะรวม soft-deleted records (สำหรับหน้า
     *                          my-requests aggregate ที่ต้องการประวัติทั้งหมด).
     *                          Default false (= behavior ของหน้า booking-meeting list).
     * @return Meeting[]
     */
    public function findMyBookings(string $empId, int $limit = 50, bool $withDeleted = false, ?int $thaiYear = null): array
    {
        try {
            $query = Meeting::find()
                ->where(['emp_id' => $empId])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit($limit);
            if (!$withDeleted) {
                $query->andWhere(['IS', 'deleted_at', null]);
            }
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
     * โหลด room metadata แบบครบ (รวม accessories, location, capacity) สำหรับ wizard card UI.
     * @return array<string, array{code:string,title:string,capacity:?int,location:string,accessories:string[]}>
     */
    public function listRoomCards(): array
    {
        $cards = [];
        try {
            $roomModels = Room::find()
                ->where(['name' => 'meeting_room'])
                ->orderBy(['title' => SORT_ASC])
                ->all();

            foreach ($roomModels as $room) {
                $data = is_array($room->data_json ?? null) ? $room->data_json : [];

                $accessories = [];
                if (!empty($data['room_accessory']) && is_array($data['room_accessory'])) {
                    $accessories = array_values(array_filter(array_map('strval', $data['room_accessory'])));
                } else {
                    try {
                        $accessories = array_values($room->listAccessory());
                    } catch (\Throwable $e) {
                        $accessories = [];
                    }
                }

                $location = (string) (
                    $data['building'] ??
                    $data['building_name'] ??
                    $data['location'] ??
                    $room->description ??
                    ''
                );

                $image = '';
                try {
                    $image = (string) $room->showImg();
                } catch (\Throwable $e) {
                    $image = '';
                }

                $cards[(string) $room->code] = [
                    'code'        => (string) $room->code,
                    'title'       => (string) $room->title,
                    'capacity'    => isset($data['seat_capacity']) ? (int) $data['seat_capacity'] : null,
                    'location'    => $location !== '' ? $location : 'ไม่ระบุอาคาร',
                    'accessories' => $accessories,
                    'image'       => $image,
                ];
            }
        } catch (\Throwable $e) {
            return [];
        }
        return $cards;
    }

    /**
     * Lookup ห้องแบบเบา ๆ (code => title) สำหรับ list card / dropdown.
     * @param array $roomCards Optional cache ถ้ามีอยู่แล้ว (หลีกเลี่ยง query ซ้ำ)
     * @return array<string,string>
     */
    public function listRooms(array $roomCards = []): array
    {
        if (!empty($roomCards)) {
            $rooms = [];
            foreach ($roomCards as $code => $card) {
                $rooms[$code] = (string) ($card['title'] ?? $code);
            }
            return $rooms;
        }

        try {
            $rooms = [];
            foreach (Room::find()->where(['name' => 'meeting_room'])->orderBy(['title' => SORT_ASC])->all() as $room) {
                $rooms[(string) $room->code] = (string) $room->title;
            }
            return $rooms;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * ห่อ Meeting::listRoomLayout() ด้วย try/catch + auto-default ค่าใน model.
     */
    public function listRoomLayouts(Meeting $meeting): array
    {
        try {
            $layouts = $meeting->listRoomLayout();
            if (empty($meeting->room_layout_id) && !empty($layouts)) {
                $meeting->room_layout_id = (string) array_key_first($layouts);
            }
            return $layouts;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * คืน URL รูปของแต่ละ room layout (code => url).
     * รูปดึงผ่าน RoomLayout::ShowImg() (มี placeholder fallback ในตัว).
     *
     * @return array<string,string>
     */
    public function listRoomLayoutImages(): array
    {
        $map = [];
        try {
            $rows = RoomLayout::find()->where(['name' => 'room_layout'])->all();
            foreach ($rows as $row) {
                $url = '';
                try {
                    $img = $row->ShowImg();
                    if (is_array($img) && !empty($img['image'])) $url = (string) $img['image'];
                } catch (\Throwable $e) {
                    $url = '';
                }
                $map[(string) $row->code] = $url;
            }
        } catch (\Throwable $e) {
            return [];
        }
        return $map;
    }

    /**
     * ห่อ Meeting::listUrgent() ด้วย try/catch + auto-default ค่าใน model.
     */
    public function listUrgentOptions(Meeting $meeting): array
    {
        try {
            $options = $meeting->listUrgent();
            if (empty($meeting->urgent)) {
                $meeting->urgent = !empty($options) ? (string) array_key_first($options) : 'ปกติ';
            }
            return $options;
        } catch (\Throwable $e) {
            if (empty($meeting->urgent)) $meeting->urgent = 'ปกติ';
            return [];
        }
    }

    /**
     * ห่อ Meeting::equipmentItems() ด้วย try/catch.
     */
    public function listEquipmentItems(): array
    {
        try {
            return Meeting::equipmentItems();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * รับ Meeting ที่ load(post) แล้ว, ทำ Thai↔Greg conversion, manual validation,
     * overlap check, autonumber (ถ้าเป็น new), แล้ว save.
     *
     * @return array{ok:bool, errors:array, exception:?\Throwable}
     */
    public function prepareAndSave(Meeting $meeting, Employees $me, array $urgentOptions = []): array
    {
        $isNew = $meeting->isNewRecord;

        $dateStartGreg = $meeting->date_start ? AppHelper::convertToGregorian($meeting->date_start) : null;
        $dateEndGreg   = $meeting->date_end ? AppHelper::convertToGregorian($meeting->date_end) : $dateStartGreg;

        $meeting->date_start = $dateStartGreg;
        $meeting->date_end   = $dateEndGreg;
        $meeting->time_start = substr((string) $meeting->time_start, 0, 5);
        $meeting->time_end   = substr((string) $meeting->time_end, 0, 5);
        $meeting->emp_id     = (string) $me->id;
        if ($dateStartGreg) {
            $meeting->thai_year = (int) AppHelper::YearBudget($dateStartGreg);
        }
        if ($isNew) {
            $meeting->status = 'Pending';
        }

        if (empty($meeting->urgent)) {
            $meeting->urgent = !empty($urgentOptions) ? (string) array_key_first($urgentOptions) : 'ปกติ';
        }

        $dataJson = is_array($meeting->data_json ?? null) ? $meeting->data_json : [];
        if (empty($dataJson['period_time'])) {
            $dataJson['period_time'] = 'กำหนดเอง';
        }
        if (!isset($dataJson['equipment']) || !is_array($dataJson['equipment'])) {
            $dataJson['equipment'] = empty($dataJson['equipment']) ? [] : [(string) $dataJson['equipment']];
        }
        $meeting->data_json = $dataJson;

        if ($isNew) {
            try {
                $meeting->code = \mdm\autonumber\AutoNumber::generate('MTG' . date('ymd') . '-???');
            } catch (\Throwable $e) {
                $meeting->code = 'MTG' . date('Ymd') . '-' . substr(uniqid(), -4);
            }
        }

        // Manual validation pipeline
        $modelValid   = $meeting->validate();
        $manualErrors = false;
        if (!$dateStartGreg || !$dateEndGreg) {
            $meeting->addError('date_start', 'รูปแบบวันที่ไม่ถูกต้อง');
            $manualErrors = true;
        }
        if ($dateStartGreg && $dateEndGreg && strcmp($dateEndGreg, $dateStartGreg) < 0) {
            $meeting->addError('date_end', 'วันสิ้นสุดต้องไม่ก่อนวันเริ่มต้น');
            $manualErrors = true;
        }
        if (
            $dateStartGreg &&
            $dateEndGreg &&
            $dateStartGreg === $dateEndGreg &&
            $meeting->time_start !== '' &&
            $meeting->time_end !== '' &&
            strcmp($meeting->time_start, $meeting->time_end) >= 0
        ) {
            $meeting->addError('time_end', 'เวลาสิ้นสุดต้องหลังเวลาเริ่ม');
            $manualErrors = true;
        }
        if (trim((string) ($dataJson['phone'] ?? '')) === '') {
            $meeting->addError('data_json', 'ต้องระบุเบอร์โทรศัพท์');
            $manualErrors = true;
        }
        if (
            !$manualErrors &&
            $this->hasRoomOverlap(
                (string) $meeting->room_id,
                $dateStartGreg,
                $meeting->time_start,
                $dateEndGreg,
                $meeting->time_end,
                $isNew ? null : (int) $meeting->id
            )
        ) {
            $meeting->addError('room_id', 'ห้องประชุมที่เลือกมีการจองในช่วงเวลานี้แล้ว');
            $manualErrors = true;
        }

        if (!$manualErrors && $modelValid) {
            try {
                if ($meeting->save(false)) {
                    return ['ok' => true, 'errors' => [], 'exception' => null];
                }
            } catch (\Throwable $e) {
                return ['ok' => false, 'errors' => $meeting->getFirstErrors(), 'exception' => $e];
            }
        }

        return ['ok' => false, 'errors' => $meeting->getFirstErrors(), 'exception' => null];
    }

    /**
     * แปลง Gregorian Y-m-d → Thai d/m/Y สำหรับ re-render หลัง save fail
     * (เรียกหลังจาก prepareAndSave ที่ mutate dates เป็น Gregorian แล้ว).
     */
    public function restoreThaiDates(Meeting $meeting): void
    {
        foreach (['date_start', 'date_end'] as $attr) {
            $val = (string) ($meeting->$attr ?? '');
            if ($val === '') continue;
            $ts = strtotime($val);
            if ($ts) {
                $meeting->$attr = date('d/m/', $ts) . ((int) date('Y', $ts) + 543);
            }
        }
    }

    /**
     * แปลง DB Gregorian Y-m-d → Thai d/m/Y สำหรับ initial GET (ตอนเปิดฟอร์มแก้ไข).
     * (รับ format Y-m-d เท่านั้น; d/m/Y format ไม่แตะ)
     */
    public function convertDbDatesToThai(Meeting $meeting): void
    {
        foreach (['date_start', 'date_end'] as $attr) {
            $val = (string) ($meeting->$attr ?? '');
            if ($val !== '' && preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
                $ts = strtotime($val);
                if ($ts) {
                    $meeting->$attr = date('d/m/', $ts) . ((int) date('Y', $ts) + 543);
                }
            }
        }
        $meeting->time_start = substr((string) $meeting->time_start, 0, 5);
        $meeting->time_end   = substr((string) $meeting->time_end, 0, 5);
    }

    /**
     * เปลี่ยนสถานะ Meeting เป็น Cancel โดย save แค่ attribute ที่อนุญาต.
     */
    public function cancel(Meeting $meeting): bool
    {
        $meeting->status = 'Cancel';
        return (bool) $meeting->save(false, ['status', 'updated_at', 'updated_by']);
    }

    /**
     * ตรวจสอบว่ามีการจองห้องช่วงเวลาที่ระบุซ้อนทับกับการจองอื่นหรือไม่.
     * (ก่อนหน้านี้เป็น private method `hasMeetingRoomOverlap` ใน DefaultController.)
     */
    public function hasRoomOverlap(
        string $roomId,
        ?string $dateStart,
        ?string $timeStart,
        ?string $dateEnd,
        ?string $timeEnd,
        ?int $excludeId = null
    ): bool {
        if ($roomId === '' || !$dateStart || !$dateEnd || !$timeStart || !$timeEnd) {
            return false;
        }

        $newStart = strtotime($dateStart . ' ' . substr((string) $timeStart, 0, 5));
        $newEnd   = strtotime($dateEnd . ' ' . substr((string) $timeEnd, 0, 5));
        if (!$newStart || !$newEnd || $newStart >= $newEnd) {
            return false;
        }

        $query = Meeting::find()
            ->where(['room_id' => $roomId])
            ->andWhere(['<>', 'status', 'Cancel'])
            ->andWhere(['<=', 'date_start', $dateEnd])
            ->andWhere(['>=', 'date_end', $dateStart]);

        if ($excludeId !== null && $excludeId > 0) {
            $query->andWhere(['<>', 'id', $excludeId]);
        }

        foreach ($query->all() as $item) {
            $existStart   = strtotime((string) $item->date_start . ' ' . substr((string) $item->time_start, 0, 5));
            $existEndDate = $item->date_end ?: $item->date_start;
            $existEnd     = strtotime((string) $existEndDate . ' ' . substr((string) $item->time_end, 0, 5));
            if ($existStart && $existEnd && $newStart < $existEnd && $newEnd > $existStart) {
                return true;
            }
        }

        return false;
    }

    /**
     * AJAX availability endpoint: ตรวจสอบห้องว่างทั้งหมดในช่วงเวลาที่ระบุ.
     * รับ Thai d/m/Y dates + HH:MM times.
     *
     * @return array{ok:bool, message?:string, rooms:array}
     */
    public function checkAvailability(
        string $meetingDateThai,
        string $meetingDateEndThai,
        string $timeStart,
        string $timeEnd,
        ?int $excludeId = null
    ): array {
        if (!$meetingDateThai || !$meetingDateEndThai || !$timeStart || !$timeEnd) {
            return ['ok' => false, 'message' => 'กรุณาระบุวันที่และเวลา', 'rooms' => []];
        }

        $dateStartGreg = AppHelper::convertToGregorian($meetingDateThai);
        $dateEndGreg   = AppHelper::convertToGregorian($meetingDateEndThai);
        if (!$dateStartGreg || !$dateEndGreg) {
            return ['ok' => false, 'message' => 'รูปแบบวันที่ไม่ถูกต้อง', 'rooms' => []];
        }
        $timeStart = substr($timeStart, 0, 5);
        $timeEnd   = substr($timeEnd, 0, 5);
        if (strcmp($dateEndGreg, $dateStartGreg) < 0) {
            return ['ok' => false, 'message' => 'วันสิ้นสุดต้องไม่ก่อนวันเริ่มต้น', 'rooms' => []];
        }
        if ($dateStartGreg === $dateEndGreg && strcmp($timeStart, $timeEnd) >= 0) {
            return ['ok' => false, 'message' => 'เวลาสิ้นสุดต้องหลังเวลาเริ่ม', 'rooms' => []];
        }

        $rooms = [];
        try {
            $roomModels = Room::find()->where(['name' => 'meeting_room'])->orderBy(['title' => SORT_ASC])->all();
            foreach ($roomModels as $room) {
                $hasOverlap = $this->hasRoomOverlap(
                    (string) $room->code,
                    $dateStartGreg,
                    $timeStart,
                    $dateEndGreg,
                    $timeEnd,
                    $excludeId && $excludeId > 0 ? $excludeId : null
                );
                $rooms[] = [
                    'code'      => $room->code,
                    'title'     => $room->title,
                    'capacity'  => isset($room->data_json['seat_capacity']) ? (int) $room->data_json['seat_capacity'] : null,
                    'available' => !$hasOverlap,
                ];
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage(), 'rooms' => []];
        }

        return ['ok' => true, 'rooms' => $rooms];
    }
}
