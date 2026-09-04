<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\modules\leave\components\LeaveApprovalService;

/**
 * ซ่อมข้อมูลใบลาที่สถานะไม่ตรงกับ workflow จริง
 *
 * ใช้แก้ผลกระทบจากบั๊กเดิม 2 จุด
 *   1) approve.status ของขั้น ผอ. (level 4) ถูกเขียนทับด้วย emp_id (เช่น '75', '147')
 *   2) leave.status ถูกดีดกลับเป็น 'Pending' ทั้งที่ผ่าน หน.งาน/หน.กลุ่มงาน/จนท.ตรวจสอบ ไปแล้ว
 *      ทำให้ใบลาค้าง ไม่ไปถึงผู้ตรวจสอบและ ผอ.
 *
 * ทุกคำสั่งเป็น dry-run โดยปริยาย — ต้องใส่ --apply=1 จึงจะเขียนลงฐานข้อมูลจริง
 *
 *   php yii leave-repair/report
 *   php yii leave-repair/approve-status            # ดูก่อน
 *   php yii leave-repair/approve-status --apply=1  # เขียนจริง
 *   php yii leave-repair/leave-status --apply=1
 */
class LeaveRepairController extends Controller
{
    /** @var bool เขียนลงฐานข้อมูลจริง (ค่าเริ่มต้นคือ dry-run) */
    public $apply = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['apply']);
    }

    /**
     * สรุปจำนวนข้อมูลที่ผิดปกติ (อ่านอย่างเดียว)
     */
    public function actionReport()
    {
        $db = Yii::$app->db;

        $badApprove = (int) $db->createCommand(
            "SELECT COUNT(*) FROM approve
             WHERE name = 'leave' AND status NOT IN ('Pass', 'Pending', 'Reject', 'None')"
        )->queryScalar();

        $this->stdout("approve.status ที่ไม่ใช่ค่ามาตรฐาน: {$badApprove} แถว\n", Console::FG_YELLOW);

        $rows = $db->createCommand(
            "SELECT status, COUNT(*) c FROM approve
             WHERE name = 'leave' AND status NOT IN ('Pass', 'Pending', 'Reject', 'None')
             GROUP BY status ORDER BY c DESC"
        )->queryAll();
        foreach ($rows as $r) {
            $this->stdout("    status='{$r['status']}' → {$r['c']} แถว\n");
        }

        if ($badApprove > 0) {
            $this->stdout("    ⚠ ต้องรัน leave-repair/approve-status ก่อน แล้วค่อยดู leave.status\n", Console::FG_YELLOW);
        }

        $mismatch = $this->findMismatchedLeaves();
        $this->stdout("\nใบลาที่ leave.status ไม่ตรงกับ workflow: " . count($mismatch) . " ใบ\n", Console::FG_YELLOW);
        $this->printMismatch($mismatch);

        return ExitCode::OK;
    }

    /**
     * ซ่อม approve.status ของขั้น ผอ. ที่ถูกเขียนทับด้วย emp_id
     *
     * ตีความค่าที่เพี้ยนจากตำแหน่งของใบลาใน workflow:
     *   - ใบลาอนุมัติจบแล้ว (leave.status='Approve')            → 'Pass'
     *   - ใบลายกเลิก/ไม่อนุมัติ (Cancel/Reject/ReqCancel)        → 'None'
     *   - ทุกชั้นก่อนหน้าผ่านหมดแล้ว = ชั้นนี้คือชั้นที่กำลังรอ   → 'Pending'
     *   - นอกนั้น (ยังมีชั้นก่อนหน้าที่ยังไม่ผ่าน)               → 'None'
     *
     * เงื่อนไข 'Pending' สำคัญมาก — ถ้าเหมารวมเป็น 'None' ใบลาที่ค้างอยู่ที่ชั้น ผอ.
     * จะหายไปจากกล่องรออนุมัติของ ผอ. และไม่มีใครเห็นอีกเลย
     */
    public function actionApproveStatus()
    {
        $rows = Yii::$app->db->createCommand(
            "SELECT a.id, a.from_id, a.level, a.status, l.status AS leave_status,
                    (SELECT COUNT(*) FROM approve p
                      WHERE p.name = 'leave' AND p.from_id = a.from_id
                        AND p.level < a.level AND p.status <> 'Pass') AS prev_not_passed
             FROM approve a
             JOIN `leave` l ON l.id = a.from_id
             WHERE a.name = 'leave' AND a.status NOT IN ('Pass', 'Pending', 'Reject', 'None')
             ORDER BY a.id"
        )->queryAll();

        if (empty($rows)) {
            $this->stdout("ไม่พบแถวที่ต้องซ่อม\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $plan = [];
        foreach ($rows as $r) {
            if ($r['leave_status'] === 'Approve') {
                $newStatus = 'Pass';
            } elseif ($r['leave_status'] === 'Reject' && (int) $r['prev_not_passed'] === 0) {
                // ใบถูกปฏิเสธและทุกชั้นก่อนหน้าผ่านหมด → ชั้นนี้คือชั้นที่ปฏิเสธ
                $newStatus = 'Reject';
            } elseif (in_array($r['leave_status'], ['Cancel', 'Reject', 'ReqCancel'], true)) {
                $newStatus = 'None';
            } elseif ((int) $r['prev_not_passed'] === 0) {
                $newStatus = 'Pending';
            } else {
                $newStatus = 'None';
            }
            $plan[$newStatus][] = (int) $r['id'];
        }

        foreach ($plan as $newStatus => $ids) {
            $this->stdout('approve.status → ' . $newStatus . ': ' . count($ids) . " แถว\n");
            if ($newStatus === 'Pending') {
                $this->stdout('    approve.id: ' . implode(', ', array_slice($ids, 0, 20)) . "\n");
            }
        }

        if (!$this->apply) {
            $this->stdout("\n(dry-run — ใส่ --apply=1 เพื่อเขียนจริง)\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            foreach ($plan as $newStatus => $ids) {
                foreach (array_chunk($ids, 500) as $chunk) {
                    Yii::$app->db->createCommand()
                        ->update('approve', ['status' => $newStatus], ['id' => $chunk])
                        ->execute();
                }
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            $this->stderr('ล้มเหลว: ' . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("ซ่อมเรียบร้อย\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * ตั้ง leave.status ใหม่ให้ตรงกับระดับที่ผ่านจริง
     */
    public function actionLeaveStatus()
    {
        $mismatch = $this->findMismatchedLeaves();

        if (empty($mismatch)) {
            $this->stdout("ไม่พบใบลาที่สถานะไม่ตรง\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->printMismatch($mismatch);

        if (!$this->apply) {
            $this->stdout("\n(dry-run — ใส่ --apply=1 เพื่อเขียนจริง)\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            foreach ($mismatch as $m) {
                Yii::$app->db->createCommand()
                    ->update('leave', ['status' => $m['expected']], ['id' => $m['id']])
                    ->execute();
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            $this->stderr('ล้มเหลว: ' . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout('ปรับสถานะแล้ว ' . count($mismatch) . " ใบ\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * คืนร่องรอยการปฏิเสธที่หายไป
     *
     * ใบลาที่ถูกปฏิเสธและทุกชั้นก่อนหน้าผ่านหมด แต่ชั้นสุดท้ายเป็น 'None'
     * แปลว่าแถวที่เคยบันทึก 'Reject' ถูกเขียนทับไปแล้ว ต้องประทับกลับเป็น 'Reject'
     * ไม่งั้นไทม์ไลน์จะแสดงชั้น ผอ. ว่า "รออนุมัติ" ทั้งที่ใบถูกปฏิเสธไปแล้ว
     */
    public function actionRejectStamp()
    {
        $rows = Yii::$app->db->createCommand(
            "SELECT a.id, a.from_id, a.level
             FROM approve a
             JOIN `leave` l ON l.id = a.from_id
             WHERE a.name = 'leave'
               AND a.status = 'None'
               AND l.status = 'Reject'
               AND a.level = (SELECT MAX(m.level) FROM approve m
                               WHERE m.name = 'leave' AND m.from_id = a.from_id)
               AND NOT EXISTS (SELECT 1 FROM approve p
                                WHERE p.name = 'leave' AND p.from_id = a.from_id
                                  AND p.level < a.level AND p.status <> 'Pass')
               AND NOT EXISTS (SELECT 1 FROM approve r
                                WHERE r.name = 'leave' AND r.from_id = a.from_id
                                  AND r.status = 'Reject')
             ORDER BY a.id"
        )->queryAll();

        if (empty($rows)) {
            $this->stdout("ไม่พบแถวที่ต้องประทับ Reject\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout('approve.status → Reject: ' . count($rows) . " แถว\n");
        foreach ($rows as $r) {
            $this->stdout("    approve #{$r['id']} (ใบลา #{$r['from_id']} ชั้น {$r['level']})\n");
        }

        if (!$this->apply) {
            $this->stdout("\n(dry-run — ใส่ --apply=1 เพื่อเขียนจริง)\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        $ids = array_map(static fn ($r) => (int) $r['id'], $rows);

        $tx = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()
                ->update('approve', ['status' => 'Reject'], ['id' => $ids])
                ->execute();
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            $this->stderr('ล้มเหลว: ' . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("ประทับเรียบร้อย\n", Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * แสดงรายการที่ไม่ตรง โดยจำกัดจำนวนบรรทัด ไม่ให้ท่วมหน้าจอ
     *
     * @param array<int, array{id:int, current:string, expected:string}> $mismatch
     */
    private function printMismatch(array $mismatch, int $limit = 30): void
    {
        $summary = [];
        foreach ($mismatch as $m) {
            $key = $m['current'] . ' → ' . $m['expected'];
            $summary[$key] = ($summary[$key] ?? 0) + 1;
        }
        foreach ($summary as $key => $count) {
            $this->stdout("    {$key}: {$count} ใบ\n");
        }

        foreach (array_slice($mismatch, 0, $limit) as $m) {
            $this->stdout("      #{$m['id']}  {$m['current']} → {$m['expected']}\n");
        }
        if (count($mismatch) > $limit) {
            $this->stdout('      ... และอีก ' . (count($mismatch) - $limit) . " ใบ\n");
        }
    }

    /**
     * หาใบลาที่ leave.status ไม่ตรงกับระดับที่ผ่านจริง
     * ข้ามใบที่ถูกตัดสินไปแล้ว (Cancel / ReqCancel / Reject) — คำตัดสินบนใบลาถือเป็นหลัก
     * แถว approve จะย้อนคำตัดสินนั้นไม่ได้
     * และข้ามใบที่ยังมี approve.status เพี้ยนอยู่ — ต้องรัน approve-status ก่อน
     * มิฉะนั้นจะคำนวณระดับที่ผ่านต่ำกว่าความจริง
     *
     * @return array<int, array{id:int, current:string, expected:string}>
     */
    private function findMismatchedLeaves(): array
    {
        $rows = Yii::$app->db->createCommand(
            "SELECT l.id, l.status AS `current`,
                    MAX(a.level) AS max_level,
                    MAX(CASE WHEN a.status = 'Pass'   THEN a.level END) AS passed_level,
                    MIN(CASE WHEN a.status = 'Reject' THEN a.level END) AS rejected_level,
                    SUM(a.status NOT IN ('Pass', 'Pending', 'Reject', 'None')) AS bad_rows
             FROM `leave` l
             JOIN approve a ON a.from_id = l.id AND a.name = 'leave'
             WHERE l.status NOT IN ('Cancel', 'ReqCancel', 'Reject')
             GROUP BY l.id, l.status
             HAVING bad_rows = 0"
        )->queryAll();

        $map = LeaveApprovalService::LEVEL_STATUS_MAP;
        $out = [];

        foreach ($rows as $r) {
            $maxLevel = (int) $r['max_level'];
            $passed   = (int) $r['passed_level'];
            $rejected = $r['rejected_level'] === null ? null : (int) $r['rejected_level'];

            if ($rejected !== null) {
                // ตรงกับ LeaveApprovalService::process() ที่ตั้ง 'Reject' เสมอไม่ว่าถูกปฏิเสธชั้นใด
                $expected = 'Reject';
            } elseif ($passed > 0 && $passed >= $maxLevel) {
                $expected = 'Approve';
            } elseif ($passed > 0) {
                $expected = $map[$passed]['Pass'] ?? 'Pending';
            } else {
                $expected = 'Pending';
            }

            if ($expected !== $r['current']) {
                $out[] = ['id' => (int) $r['id'], 'current' => (string) $r['current'], 'expected' => $expected];
            }
        }

        return $out;
    }
}
