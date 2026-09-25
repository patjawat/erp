<?php

namespace app\modules\attendance\services;

use app\modules\attendance\models\CheckinRecord;
use app\modules\hr\models\Employees;
use yii\db\Query;

/**
 * ภาพรวมการลงเวลารายวันสำหรับผู้ดูแล — นับเป็น "คน" ไม่ใช่จำนวนครั้งที่กด
 * บุคลากร = พนักงานสถานะปฏิบัติงาน; แต่ละคนอยู่สถานะเดียว: ลงเวลาแล้ว > ลา > ไปราชการ > ยังไม่ลงเวลา (มีเวร/วันทำงาน)
 * > หยุด (ตารางเวร OFF/วันหยุด/นอกวันทำงาน) > ยังไม่ตั้งเวลา (ไม่มีทั้งตารางเวรและชุดเวลาปกติ)
 */
class AttendanceDashboard
{
    public static function daily(string $date): array
    {
        $staff = Employees::find()->select(['id', 'department'])
            ->where(['status' => Employees::STATUS_WORKING])->asArray()->all();
        $deptOf = [];
        foreach ($staff as $e) $deptOf[(int)$e['id']] = (int)$e['department'];

        $rows = CheckinRecord::find()
            ->select(['emp_id', 'check_type', 'is_in_location', 'data_json'])
            ->where(['between', 'checkin_at', $date . ' 00:00:00', $date . ' 23:59:59'])
            ->asArray()->all();
        $present = $late = $outside = $early = [];
        foreach ($rows as $r) {
            $id = (int)$r['emp_id'];
            if (!isset($deptOf[$id])) continue;
            $present[$id] = true;
            if (!(int)$r['is_in_location']) $outside[$id] = true;
            $json = is_array($r['data_json']) ? $r['data_json'] : (json_decode((string)$r['data_json'], true) ?: []);
            $reasons = (array)($json['exception_reasons'] ?? []);
            if (in_array('late', $reasons, true)) $late[$id] = true;
            if (in_array('early', $reasons, true)) $early[$id] = true;
        }

        $ids = array_keys($deptOf);
        // ลา (อนุมัติแล้ว) + ไปราชการ (development อนุมัติแล้ว ช่วงวันเดินทาง) = ไม่ต้องลงเวลา
        $leaveIds = array_map('intval', (new Query())->from('leave')->select('emp_id')
            ->where(['status' => 'Approve', 'deleted_at' => null])
            ->andWhere(['<=', 'date_start', $date])->andWhere(['>=', 'date_end', $date])->column());
        $tripIds = [];
        try {
            $ds = 'COALESCE(d.vehicle_date_start, d.date_start)';
            $de = 'COALESCE(d.vehicle_date_end, d.date_end, d.vehicle_date_start, d.date_start)';
            $tripIds = array_map('intval', (new Query())->select('dd.emp_id')->from(['dd' => 'development_detail'])
                ->innerJoin(['d' => 'development'], 'd.id = dd.development_id')
                ->where(['dd.name' => 'member', 'dd.deleted_at' => null, 'd.deleted_at' => null,
                    'd.status' => \app\modules\attendance\controllers\CheckinController::TRIP_STATUSES])
                ->andWhere(['<=', $ds, $date])->andWhere(['>=', $de, $date])->column());
        } catch (\Throwable $e) {
        }

        // วันนี้ต้องทำงานไหม: มีเวรที่ประกาศใช้ หรือเป็นวันทำงานตามชุดเวลาปกติ (ไม่ใช่วันหยุด)
        $expected = [];
        foreach (RosterAttendance::shifts($ids, $date, $date) as $s) {
            if ($s['work_date'] === $date) $expected[(int)$s['emp_id']] = true;
        }
        // ตั้งตารางไว้แล้วหรือยัง: มีรายการตารางเวรของวันนั้น (รวม OFF) หรือมีชุดเวลาปกติ — ใช้แยก "หยุด" ออกจาก "ยังไม่ตั้งเวลา"
        $configured = $expected;
        try {
            foreach (\app\modules\roster\models\Item::find()->alias('i')->innerJoinWith(['period p'], false)
                ->where(['i.emp_id' => $ids, 'i.work_date' => $date, 'p.status' => \app\modules\roster\models\Period::LIVE_STATUSES, 'p.deleted_at' => null])
                ->andWhere(['<>', 'i.status', \app\modules\roster\models\Item::STATUS_CANCELLED])
                ->select('i.emp_id')->column() as $id) {
                $configured[(int)$id] = true;
            }
        } catch (\Throwable $e) {
        }
        if (WorkScheduleService::ready()) {
            $dir = new ScheduleDirectory();
            foreach ($dir->employees as $e) {
                if (!isset($configured[(int)$e['id']]) && $dir->state($e, $date)['schedule']) $configured[(int)$e['id']] = true;
            }
        }

        // จัดแต่ละคนลงสถานะเดียว ตามลำดับความสำคัญ
        $status = [];
        foreach ($ids as $id) {
            if (isset($present[$id])) $status[$id] = 'present';
            elseif (in_array($id, $leaveIds, true)) $status[$id] = 'leave';
            elseif (in_array($id, $tripIds, true)) $status[$id] = 'trip';
            elseif (isset($expected[$id])) $status[$id] = 'missing';   // มีเวร/วันทำงาน แต่ยังไม่ลงเวลา
            elseif (isset($configured[$id])) $status[$id] = 'off';      // หยุดตามเวร/วันหยุด
            else $status[$id] = 'unset';                                 // ยังไม่ได้ตั้งเวรหรือเวลาทำงาน
        }
        $count = array_count_values($status) + ['present' => 0, 'leave' => 0, 'trip' => 0, 'missing' => 0, 'off' => 0, 'unset' => 0];

        $names = (new Query())->from('tree')->select(['name', 'id'])->where(['id' => array_unique(array_values($deptOf))])->indexBy('id')->column();
        $units = [];
        foreach ($deptOf as $id => $dept) {
            $u = &$units[$dept];
            if (!$u) $u = ['name' => $names[$dept] ?? 'ไม่ระบุหน่วยงาน', 'staff' => 0, 'present' => 0, 'leave' => 0, 'trip' => 0, 'missing' => 0, 'off' => 0, 'unset' => 0, 'late' => 0, 'outside' => 0];
            $u['staff']++;
            $u[$status[$id]]++;
            if (isset($late[$id])) $u['late']++;
            if (isset($outside[$id])) $u['outside']++;
            unset($u);
        }
        // หน่วยที่ยังไม่ลงเวลาเยอะสุดขึ้นก่อน เพื่อให้เห็นว่าต้องติดตามที่ไหน
        uasort($units, fn($a, $b) => [$b['missing'], $b['unset'], $a['name']] <=> [$a['missing'], $a['unset'], $b['name']]);

        return [
            'total' => count($ids),
            'present' => $count['present'],
            'leave' => $count['leave'],
            'trip' => $count['trip'],
            'missing' => $count['missing'],
            'off' => $count['off'],
            'unset' => $count['unset'],
            'late' => count($late),
            'early' => count($early),
            'outside' => count($outside),
            'units' => $units,
        ];
    }

    /** รายการผิดปกติของวัน (สาย/ออกก่อน/นอกพื้นที่/ไม่ตรงเวร) สำหรับติดตาม — ดูอย่างเดียว */
    public static function exceptions(string $date, int $limit = 20): array
    {
        return CheckinRecord::find()->with(['employee'])
            ->where(['between', 'checkin_at', $date . ' 00:00:00', $date . ' 23:59:59'])
            ->andWhere(['or', ['is_in_location' => 0], ['status' => [CheckinRecord::STATUS_PENDING, CheckinRecord::STATUS_REJECTED]]])
            ->orderBy(['checkin_at' => SORT_DESC])->limit($limit)->all();
    }
}
