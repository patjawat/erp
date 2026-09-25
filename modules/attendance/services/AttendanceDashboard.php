<?php

namespace app\modules\attendance\services;

use app\modules\attendance\models\CheckinRecord;
use app\modules\hr\models\Employees;
use yii\db\Query;

/**
 * ภาพรวมการลงเวลารายวันสำหรับผู้ดูแล — นับเป็น "คน" ไม่ใช่จำนวนครั้งที่กด
 * บุคลากร = พนักงานสถานะปฏิบัติงาน; มาแล้ว = มีรายการลงเวลาใด ๆ ในวันนั้น; ลา = ใบลาอนุมัติครอบคลุมวันนั้น
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

        $onLeave = [];
        foreach ((new Query())->from('leave')->select('emp_id')
            ->where(['status' => 'Approve', 'deleted_at' => null])
            ->andWhere(['<=', 'date_start', $date])->andWhere(['>=', 'date_end', $date])
            ->column() as $id) {
            if (isset($deptOf[(int)$id]) && !isset($present[(int)$id])) $onLeave[(int)$id] = true;
        }

        $names = (new Query())->from('tree')->select(['name', 'id'])->where(['id' => array_unique(array_values($deptOf))])->indexBy('id')->column();
        $units = [];
        foreach ($deptOf as $id => $dept) {
            $u = &$units[$dept];
            if (!$u) $u = ['name' => $names[$dept] ?? 'ไม่ระบุหน่วยงาน', 'staff' => 0, 'present' => 0, 'leave' => 0, 'missing' => 0, 'late' => 0, 'outside' => 0];
            $u['staff']++;
            if (isset($present[$id])) $u['present']++;
            elseif (isset($onLeave[$id])) $u['leave']++;
            else $u['missing']++;
            if (isset($late[$id])) $u['late']++;
            if (isset($outside[$id])) $u['outside']++;
            unset($u);
        }
        // หน่วยที่ยังไม่มาเยอะสุดขึ้นก่อน เพื่อให้เห็นว่าต้องติดตามที่ไหน
        uasort($units, fn($a, $b) => [$b['missing'], $a['name']] <=> [$a['missing'], $b['name']]);

        $total = count($deptOf);
        return [
            'total' => $total,
            'present' => count($present),
            'leave' => count($onLeave),
            'missing' => $total - count($present) - count($onLeave),
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
