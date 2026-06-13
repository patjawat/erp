<?php

namespace app\modules\mobile\services;

use app\modules\booking\models\Meeting;
use app\modules\booking\models\Room;

/**
 * Admin-side Meeting operations สำหรับ mobile (สำหรับ "ผู้ดูแลห้อง" / room owner).
 *
 * แยกออกจาก {@see MobileMeetingService} เพราะคนละ role:
 * - MobileMeetingService = requester (จองห้อง, ดูคำขอของตัวเอง, แก้ไข, ยกเลิก)
 * - MobileMeetingAdminService = room owner (เห็นการจองของห้องที่ตัวเองดูแล, อนุมัติ/ปฏิเสธ)
 *
 * เกิดจาก logic ที่ซ้ำใน 3 actions:
 * - actionRoomManage (ดึงห้องที่ตัวเองเป็นเจ้าของ + นับสถิติ)
 * - actionMeetingDetail (modal popup สำหรับ approve/reject)
 * - actionMeetingConfirm (POST endpoint approve/reject)
 *
 * จุดซ้ำที่สำคัญ: `data_json['owner']` ของ Room อาจเป็น array หรือ JSON string —
 * {@see decodeRoomData()} รับมือทั้งสองรูปแบบจุดเดียว.
 */
class MobileMeetingAdminService
{
    /**
     * คืนค่า rooms ที่ user ระบุเป็นเจ้าของ ตาม `data_json.owner` field.
     *
     * @return array{codes:string[], titles:array<string,string>}
     */
    public function findOwnedRoomsForUser(string $empId): array
    {
        $codes  = [];
        $titles = [];

        try {
            $allRooms = Room::find()->where(['name' => 'meeting_room'])->all();
            foreach ($allRooms as $room) {
                $data = $this->decodeRoomData($room);
                if (!empty($data['owner']) && (string) $data['owner'] === $empId) {
                    $codes[] = (string) $room->code;
                    $titles[(string) $room->code] = (string) $room->title;
                }
            }
        } catch (\Throwable $e) {
            return ['codes' => [], 'titles' => []];
        }

        return ['codes' => $codes, 'titles' => $titles];
    }

    /**
     * Meeting list ทั้งหมดของห้องที่ระบุ เรียง date_start/time_start ใหม่→เก่า.
     * @return Meeting[]
     */
    public function findMeetingsForOwnedRooms(array $roomCodes, int $limit = 200): array
    {
        if (empty($roomCodes)) return [];

        try {
            return Meeting::find()
                ->where(['room_id' => $roomCodes])
                ->orderBy(['date_start' => SORT_DESC, 'time_start' => SORT_DESC])
                ->limit($limit)
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * นับ Meeting ตาม bucket ของหน้า room-manage (pending/passed/cancelled/total).
     * label keys เฉพาะของ room-manage (ไม่ใช้ {@see MobileBookingStatus::bucketCounts}
     * เพราะ key set ต่างกัน — 'passed' vs 'approved', 'total' vs 'all').
     *
     * @param Meeting[] $meetings
     * @return array{pending:int, passed:int, cancelled:int, total:int}
     */
    public function bucketCountsForRoomManage(array $meetings): array
    {
        $counts = ['pending' => 0, 'passed' => 0, 'cancelled' => 0, 'total' => 0];
        foreach ($meetings as $m) {
            $status = (string) $m->status;
            $counts['total']++;
            if ($status === 'Pending') {
                $counts['pending']++;
            } elseif (in_array($status, ['Pass', 'Approve'], true)) {
                $counts['passed']++;
            } elseif (in_array($status, ['Cancel', 'Reject'], true)) {
                $counts['cancelled']++;
            }
        }
        return $counts;
    }

    /**
     * Decode Room.data_json — รองรับทั้ง array (จาก JSON cast) และ string (legacy).
     */
    public function decodeRoomData(Room $room): array
    {
        if (is_array($room->data_json)) {
            return $room->data_json;
        }
        if (is_string($room->data_json)) {
            $decoded = json_decode($room->data_json, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * โหลด Room จาก Meeting (filter ตาม name='meeting_room' + matching code).
     */
    public function getRoomFromMeeting(Meeting $meeting): ?Room
    {
        if (empty($meeting->room_id)) return null;
        return Room::findOne(['name' => 'meeting_room', 'code' => $meeting->room_id]);
    }

    /**
     * ตรวจสอบว่า user ปัจจุบันเป็นเจ้าของห้องของการจองนี้หรือไม่.
     * (Pattern: ถ้าไม่มี Room → false; ถ้ามี → เทียบ owner กับ $empId)
     */
    public function canManageRoomMeeting(Meeting $meeting, string $empId): bool
    {
        $room = $this->getRoomFromMeeting($meeting);
        if (!$room) return false;
        $data = $this->decodeRoomData($room);
        $owner = isset($data['owner']) ? (string) $data['owner'] : null;
        return $owner === $empId;
    }

    /**
     * อนุมัติ/ยกเลิกการจอง: set status + save + notify (เมื่อ Pass).
     *
     * @return array{ok:bool, message:string}
     */
    public function confirmMeeting(Meeting $meeting, string $status): array
    {
        if (!in_array($status, ['Pass', 'Cancel'], true)) {
            return ['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'];
        }

        $meeting->status = $status;
        if (!$meeting->save(false)) {
            return ['ok' => false, 'message' => 'บันทึกไม่สำเร็จ'];
        }

        if ($status === 'Pass') {
            try {
                $meeting->notifyBookerMeetingApprovedTelegram();
            } catch (\Throwable $e) {
                // Notification failure ไม่บล็อกผลการอนุมัติ
                \Yii::warning('Telegram notify failed: ' . $e->getMessage(), __METHOD__);
            }
        }

        return [
            'ok'      => true,
            'message' => $status === 'Pass' ? 'อนุมัติการจองแล้ว' : 'ยกเลิกการจองแล้ว',
        ];
    }
}
