<?php

namespace app\modules\roster\helpers;

use app\modules\leave\models\Leave;
use yii\db\Query;

/**
 * ข้อมูลรอบข้างที่ต้องทาบลงบนกริดจัดเวร — วันหยุด, ใบลา, ไปราชการ
 *
 * ดึงทั้งเดือนครั้งเดียวแล้ว map เป็นรายวัน เพื่อไม่ให้กริด 25 คน × 31 วัน ยิง query ต่อช่อง
 * รูปแบบ query ยึดตามที่ modules/attendance ใช้อยู่ จะได้ตีความข้อมูลตรงกันทั้งสองระบบ
 */
class RosterContext
{
    /**
     * วันหยุดนักขัตฤกษ์ในเดือน — [วันที่ => ชื่อวันหยุด]
     */
    public static function holidays(string $fromDate, string $toDate): array
    {
        $holidays = [];
        try {
            $rows = (new Query())->select(['title', 'date_start', 'date_end'])->from('calendar')
                ->where(['name' => 'holiday'])
                ->andWhere(['deleted_at' => null])
                ->andWhere(['<=', 'date_start', $toDate])
                ->andWhere(['or', ['date_end' => null], ['>=', 'date_end', $fromDate]])
                ->all();
            $lo = strtotime($fromDate);
            $hi = strtotime($toDate);
            foreach ($rows as $row) {
                if (empty($row['date_start'])) {
                    continue;
                }
                $start = max(strtotime($row['date_start']), $lo);
                $end = min(strtotime($row['date_end'] ?: $row['date_start']), $hi);
                for ($t = $start; $t <= $end; $t += 86400) {
                    $holidays[(int) date('j', $t)] = (string) $row['title'];
                }
            }
        } catch (\Throwable $e) {
            // ตาราง calendar ไม่พร้อม — กริดยังใช้ได้ แค่ไม่ไฮไลต์วันหยุด
        }
        return $holidays;
    }

    /**
     * ใบลาที่อนุมัติแล้ว — [emp_id][วันที่] => ['ab' => ตัวย่อ, 'title' => ชื่อ]
     * ใช้กันไม่ให้หัวหน้าจัดเวรทับวันที่ลูกน้องลา
     */
    public static function leaves(array $empIds, string $fromDate, string $toDate): array
    {
        $map = [];
        if (empty($empIds)) {
            return $map;
        }
        $abbr = [];
        $titles = [];
        try {
            $types = (new Query())->select(['code', 'title'])->from('categorise')
                ->where(['name' => 'leave_type'])->all();
            foreach ($types as $type) {
                $title = (string) $type['title'];
                $titles[$type['code']] = $title;
                $ab = 'ล';
                if (mb_strpos($title, 'ป่วย') !== false) {
                    $ab = 'ป';
                } elseif (mb_strpos($title, 'กิจ') !== false) {
                    $ab = 'ก';
                } elseif (mb_strpos($title, 'พักผ่อน') !== false) {
                    $ab = 'พ';
                } elseif (mb_strpos($title, 'คลอด') !== false) {
                    $ab = 'ค';
                }
                $abbr[$type['code']] = $ab;
            }
        } catch (\Throwable $e) {
        }

        try {
            $rows = Leave::find()
                ->select(['emp_id', 'date_start', 'date_end', 'leave_type_id'])
                ->where(['status' => 'Approve'])
                ->andWhere(['emp_id' => $empIds])
                ->andWhere(['deleted_at' => null])
                ->andWhere(['<=', 'date_start', $toDate])
                ->andWhere(['>=', 'date_end', $fromDate])
                ->asArray()->all();
            $lo = strtotime($fromDate);
            $hi = strtotime($toDate);
            foreach ($rows as $row) {
                if (empty($row['date_start'])) {
                    continue;
                }
                $code = (string) ($row['leave_type_id'] ?? '');
                $info = [
                    'ab' => $abbr[$code] ?? 'ล',
                    'title' => $titles[$code] ?? 'ลา',
                ];
                $start = max(strtotime($row['date_start']), $lo);
                $end = min(strtotime($row['date_end'] ?: $row['date_start']), $hi);
                for ($t = $start; $t <= $end; $t += 86400) {
                    $map[(int) $row['emp_id']][(int) date('j', $t)] = $info;
                }
            }
        } catch (\Throwable $e) {
            // ตาราง leave ไม่พร้อม — ข้ามการแสดงลา
        }
        return $map;
    }

    /**
     * ไปราชการ/อบรม — [emp_id][วันที่] => ['title' => เรื่อง]
     * ยึดวันเดินทางเป็นหลัก fallback เป็นวันอบรม เหมือนที่ระบบลงเวลาใช้
     */
    public static function trips(array $empIds, string $fromDate, string $toDate): array
    {
        $map = [];
        if (empty($empIds)) {
            return $map;
        }
        try {
            $startExpr = 'COALESCE(d.vehicle_date_start, d.date_start)';
            $endExpr = 'COALESCE(d.vehicle_date_end, d.date_end, d.vehicle_date_start, d.date_start)';
            $rows = (new Query())
                ->select(['emp_id' => 'dd.emp_id', 'topic' => 'd.topic', 'ds' => $startExpr, 'de' => $endExpr])
                ->from(['dd' => 'development_detail'])
                ->innerJoin(['d' => 'development'], 'd.id = dd.development_id')
                ->where(['dd.name' => 'member'])
                ->andWhere(['dd.deleted_at' => null])
                ->andWhere(['d.deleted_at' => null])
                ->andWhere(['dd.emp_id' => array_map('strval', $empIds)])
                ->andWhere(['<=', $startExpr, $toDate])
                ->andWhere(['>=', $endExpr, $fromDate])
                ->all();
            $lo = strtotime($fromDate);
            $hi = strtotime($toDate);
            foreach ($rows as $row) {
                if (empty($row['ds'])) {
                    continue;
                }
                $info = ['title' => (string) $row['topic']];
                $start = max(strtotime($row['ds']), $lo);
                $end = min(strtotime($row['de'] ?: $row['ds']), $hi);
                for ($t = $start; $t <= $end; $t += 86400) {
                    $map[(int) $row['emp_id']][(int) date('j', $t)][] = $info;
                }
            }
        } catch (\Throwable $e) {
            // ตาราง development ไม่พร้อม — ข้ามการแสดงไปราชการ
        }
        return $map;
    }

    /** [วันที่ => true] สำหรับเสาร์-อาทิตย์ */
    public static function weekends(int $year, int $month): array
    {
        $days = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $weekends = [];
        for ($d = 1; $d <= $days; $d++) {
            $dow = (int) date('w', mktime(0, 0, 0, $month, $d, $year));
            $weekends[$d] = ($dow === 0 || $dow === 6);
        }
        return $weekends;
    }
}
