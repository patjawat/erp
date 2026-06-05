<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use app\modules\hr\models\Employees;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use app\models\Categorise;
use app\components\ApproveLevelResolver;
use app\modules\approveV2\models\Approve;
use app\modules\approveV2\models\ApproveLevelSetting;
use app\modules\leave\models\Leave;

/**
 * แก้ไขรหัสตำแหน่งใหม่ v2
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FixController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
        // แก้ bug price ที่เป็น array
        if (BaseConsole::confirm("แก้ bug price ที่เป็น array?")) {
            $sql = "UPDATE categorise SET data_json = JSON_SET( data_json, '$.price', CAST( JSON_UNQUOTE( JSON_EXTRACT(data_json, '$.price[1]') ) AS DECIMAL(15,2) ) ) WHERE JSON_TYPE(JSON_EXTRACT(data_json, '$.price')) = 'ARRAY';";
            Yii::$app->db->createCommand($sql)->execute();
        }
    }

    public function actionEmployee()
    {
        if (BaseConsole::confirm("Are you sure?")) {
            $data = [];
            $employees = Employees::find()->all();
            foreach ($employees as $model) {
                if ($model->ref == '') {
                    $emp = Employees::findOne($model->id);
                    $emp->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                    if ($emp->save(false)) {
                        echo $emp->fname . "\n";
                    } else {
                        echo 'ผิดพลาด' . "\n";
                    }
                }
            }
        }
        // echo  $data;
    }

    public function actionAssetItem()
    {
        if (BaseConsole::confirm("Are you sure?")) {
            $data = [];
            $employees = Categorise::find()->where(['name' => 'asset_item'])->all();
            foreach ($employees as $model) {
                if ($model->ref == '') {
                    $item = Categorise::findOne($model->id);
                    $item->ref = substr(Yii::$app->getSecurity()->generateRandomString(), 10);
                    if ($item->save(false)) {
                        echo $item->title . "\n";
                    } else {
                        echo 'ผิดพลาด' . "\n";
                    }
                }
            }
        }
        // echo  $data;
    }

    // ปรับปรุงเลเวลที่เขียนผิดจากเอไอ
    public function actionApproveLabel()
    {
        if (\yii\helpers\BaseConsole::confirm("คุณแน่ใจหรือไม่ที่จะปรับปรุงข้อมูลสถานะการอนุมัติ?")) {

            // นำคำสั่ง SQL มาแยกเก็บใน Array
            $queries = [
                // แก้คำผิด 
                "UPDATE `approve_level_setting` SET `label` = 'เห็นชอบ' WHERE `system` = 'leave' AND `level` = 1",
                "UPDATE `approve_level_setting` SET `label` = 'เห็นชอบ' WHERE `system` = 'leave' AND `level` = 2",
                "UPDATE `approve_level_setting` SET `label` = 'ผาน' WHERE `system` = 'leave' AND `level` = 3",
                "UPDATE `approve_level_setting` SET `label` = 'อนุมัติ' WHERE `system` = 'leave' AND `level` = 4",

                // 1. เปลี่ยน เจ้าหน้าที่ตรวจสอบ -> ผ่าน
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'ผ่าน') WHERE `data_json`->>'$.label' = 'เจ้าหน้าที่ตรวจสอบ'",

                // 2. เปลี่ยน หัวหน้าเห็นชอบ -> เห็นชอบ
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'เห็นชอบ') WHERE `data_json`->>'$.label' = 'หัวหน้าเห็นชอบ'",

                // 3. เปลี่ยน หัวหน้ากลุ่มงานเห็นชอบ -> เห็นชอบ
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'เห็นชอบ') WHERE `data_json`->>'$.label' = 'หัวหน้ากลุ่มงานเห็นชอบ'",

                // 4. เปลี่ยน ผอ.อนุมัติ -> อนุมัติ
                "UPDATE `approve` SET `data_json` = JSON_SET(`data_json`, '$.label', 'อนุมัติ') WHERE `data_json`->>'$.label' = 'ผอ.อนุมัติ'"
            ];

            // เริ่มต้น Transaction
            $transaction = \Yii::$app->db->beginTransaction();
            $totalAffected = 0;

            try {
                // วนลูปรันทีละคำสั่ง
                foreach ($queries as $index => $sql) {
                    $affectedRows = \Yii::$app->db->createCommand($sql)->execute();
                    $totalAffected += $affectedRows;

                    // แสดงผลความคืบหน้าทีละคำสั่ง
                    \yii\helpers\BaseConsole::output("ชุดที่ " . ($index + 1) . " ปรับปรุงสำเร็จ: $affectedRows รายการ");
                }

                // ยืนยันการบันทึกข้อมูล (Commit)
                $transaction->commit();

                \yii\helpers\BaseConsole::output("-------------------------------------");
                \yii\helpers\BaseConsole::output("เสร็จสมบูรณ์! ปรับปรุงข้อมูลรวมทั้งหมด: $totalAffected รายการ");
            } catch (\Exception $e) {
                // ยกเลิกการบันทึกหากเกิดข้อผิดพลาด (Rollback)
                $transaction->rollBack();
                \yii\helpers\BaseConsole::error("เกิดข้อผิดพลาด: " . $e->getMessage());
                \yii\helpers\BaseConsole::error("ได้ทำการยกเลิกคำสั่งทั้งหมดแล้ว (Rolled back)");
            }
        } else {
            \yii\helpers\BaseConsole::output("ยกเลิกการทำงาน");
        }
    }

    /**
     * ตรวจสอบและแก้ไขผู้อนุมัติวันลาให้ตรงตามการตั้งค่า
     * /approve-v2/setting/levels?system=leave&emp_id={requester}
     *
     * ค้นหา: SELECT * FROM approve WHERE name = 'leave' AND status IN ('None','Pending')
     * ไม่กรองใบลาที่ถูกลบ
     *
     * วิธีทำ: สำหรับแต่ละใบลา → resolve ทุก level จาก ApproveLevelResolver โดยใช้
     * ผู้ขอจริง (leave.emp_id) แล้วเทียบกับ approve table ที่ level เดียวกัน:
     *   - emp_id   ← ค่า resolved (เป็น NULL ถ้าเป็น role-based เช่น L3 = role/leave)
     *   - title    ← title หรือ label จาก setting
     *   - data_json ← {label, title} + {role: <approver_value>} ถ้าเป็น role-based
     *
     * ใช้:
     *   ./yii fix/leave-approver           ตรวจ + ถามยืนยัน + บันทึก
     *   ./yii fix/leave-approver 1         dry-run ไม่บันทึก
     */
    public function actionLeaveApprover($dryRun = 0)
    {
        $dryRun = (int) $dryRun === 1;

        // เช็คว่ามี settings ของ system=leave หรือไม่
        $hasSettings = ApproveLevelSetting::find()->where(['system' => 'leave'])->exists();
        if (!$hasSettings) {
            BaseConsole::error("ไม่พบการตั้งค่า approve_level_setting (system=leave)");
            return 1;
        }

        $rows = Approve::find()
            ->where(['name' => 'leave'])
            ->andWhere(['status' => ['None', 'Pending']])
            ->orderBy(['from_id' => SORT_ASC, 'level' => SORT_ASC])
            ->all();

        if (empty($rows)) {
            BaseConsole::output("ไม่พบรายการ approve (name=leave, status IN ('None','Pending')) ที่ต้องตรวจสอบ");
            return 0;
        }

        // จัดกลุ่มตาม from_id เพื่อ resolve ครั้งเดียวต่อใบลา
        $groups = [];
        foreach ($rows as $r) {
            $groups[(string) $r->from_id][] = $r;
        }

        // สถานะใบลาที่ถือว่าจบ flow แล้ว → ไม่แก้ approve ของใบลาเหล่านี้
        $skipLeaveStatuses = [
            'Approve', 'Checkup_pass', 'Cancel', 'Reject',
            'Checkup_reject', 'Checking1_reject', 'Checking2_reject', 'ReqCancel',
        ];

        $plan = [];
        $missingLeave = [];
        $noRequester = [];
        $noMatchLevel = [];
        $emptyResolve = [];
        $skippedByLeaveStatus = []; // นับตามสถานะใบลา
        $alreadyCorrect = 0;

        foreach ($groups as $fromId => $items) {
            $leave = Leave::findOne($fromId);
            if (!$leave) {
                foreach ($items as $a) {
                    $missingLeave[] = $a->id;
                }
                continue;
            }
            if (in_array((string) $leave->status, $skipLeaveStatuses, true)) {
                $key = (string) $leave->status;
                $skippedByLeaveStatus[$key] = ($skippedByLeaveStatus[$key] ?? 0) + count($items);
                continue;
            }
            $requesterEmpId = (int) $leave->emp_id;
            if ($requesterEmpId <= 0) {
                foreach ($items as $a) {
                    $noRequester[] = $a->id;
                }
                continue;
            }

            $resolved = ApproveLevelResolver::resolve('leave', $requesterEmpId);
            if (empty($resolved)) {
                foreach ($items as $a) {
                    $emptyResolve[] = $a->id;
                }
                continue;
            }

            $byLevel = [];
            foreach ($resolved as $r) {
                $byLevel[(int) $r['level']] = $r;
            }

            foreach ($items as $a) {
                $lvl = (int) $a->level;
                if (!isset($byLevel[$lvl])) {
                    $noMatchLevel[] = ['id' => $a->id, 'from_id' => $fromId, 'level' => $lvl];
                    continue;
                }
                $r = $byLevel[$lvl];

                $title = trim((string) ($r['title'] ?? ''));
                if ($title === '') {
                    $title = (string) ($r['label'] ?? '');
                }

                $newDataJson = ['label' => $r['label'], 'title' => $title];
                if ($r['approver_type'] === ApproveLevelSetting::TYPE_ROLE && !empty($r['approver_value'])) {
                    $newDataJson['role'] = $r['approver_value'];
                }

                $currentJson = is_array($a->data_json)
                    ? $a->data_json
                    : (json_decode((string) $a->data_json, true) ?: []);
                $mergedJson = array_merge($currentJson, $newDataJson);

                $newEmpId = $r['emp_id'] !== null ? (int) $r['emp_id'] : null;
                $changes = [];

                // emp_id: resolved=NULL ⇒ ตั้ง emp_id=NULL (เช่น role-based); resolved=ตัวเลข ⇒ ตั้งตามนั้น
                $curEmpId = $a->emp_id === null ? null : (int) $a->emp_id;
                if ($curEmpId !== $newEmpId) {
                    $changes['emp_id'] = [$a->emp_id, $newEmpId];
                }
                if ((string) $a->title !== (string) $title) {
                    $changes['title'] = [$a->title, $title];
                }
                if (json_encode($currentJson) !== json_encode($mergedJson)) {
                    $changes['data_json'] = [
                        json_encode($currentJson, JSON_UNESCAPED_UNICODE),
                        json_encode($mergedJson, JSON_UNESCAPED_UNICODE),
                    ];
                }

                if (empty($changes)) {
                    $alreadyCorrect++;
                    continue;
                }

                $plan[] = [
                    'approve' => $a,
                    'new_emp_id' => $newEmpId,
                    'new_title' => $title,
                    'new_data_json' => $mergedJson,
                    'changes' => $changes,
                ];
            }
        }

        BaseConsole::output("-------------------------------------");
        BaseConsole::output("ตรวจสอบ approve (name=leave, status IN ('None','Pending')) ทั้งหมด: " . count($rows) . " รายการ");
        BaseConsole::output("ถูกต้องตามการตั้งค่าอยู่แล้ว: $alreadyCorrect รายการ");
        BaseConsole::output("ต้องปรับปรุง: " . count($plan) . " รายการ");
        if (!empty($skippedByLeaveStatus)) {
            $total = array_sum($skippedByLeaveStatus);
            BaseConsole::output("ข้ามใบลาที่ flow จบแล้ว: $total รายการ");
            foreach ($skippedByLeaveStatus as $status => $cnt) {
                BaseConsole::output("  - $status: $cnt รายการ");
            }
        }
        if (!empty($missingLeave)) {
            BaseConsole::output("ไม่พบใบลา (from_id หาย): " . count($missingLeave) . " รายการ — approve.id: " . implode(',', $missingLeave));
        }
        if (!empty($noRequester)) {
            BaseConsole::output("ใบลาไม่มีผู้ขอ (emp_id ว่าง): " . count($noRequester) . " รายการ — approve.id: " . implode(',', $noRequester));
        }
        if (!empty($emptyResolve)) {
            BaseConsole::output("resolve ไม่ได้: " . count($emptyResolve) . " รายการ — approve.id: " . implode(',', $emptyResolve));
        }
        if (!empty($noMatchLevel)) {
            BaseConsole::output("level ไม่มีในการตั้งค่าแล้ว: " . count($noMatchLevel) . " รายการ");
            foreach ($noMatchLevel as $m) {
                BaseConsole::output("  - approve.id={$m['id']} from_id={$m['from_id']} level={$m['level']}");
            }
        }
        BaseConsole::output("-------------------------------------");

        if (empty($plan)) {
            BaseConsole::output("ไม่มีรายการที่ต้องปรับปรุง");
            return 0;
        }

        $preview = array_slice($plan, 0, 20);
        BaseConsole::output("ตัวอย่างการเปลี่ยนแปลง (แสดงสูงสุด 20 รายการ):");
        foreach ($preview as $p) {
            $a = $p['approve'];
            BaseConsole::output("  approve.id={$a->id} from_id={$a->from_id} level={$a->level} status={$a->status}");
            foreach ($p['changes'] as $field => $diff) {
                BaseConsole::output("    $field: " . var_export($diff[0], true) . "  =>  " . var_export($diff[1], true));
            }
        }
        if (count($plan) > 20) {
            BaseConsole::output("  ... และอีก " . (count($plan) - 20) . " รายการ");
        }
        BaseConsole::output("-------------------------------------");

        if ($dryRun) {
            BaseConsole::output("Dry-run: ไม่ได้บันทึกข้อมูลใดๆ");
            return 0;
        }

        if (!BaseConsole::confirm("ยืนยันการปรับปรุง " . count($plan) . " รายการ?")) {
            BaseConsole::output("ยกเลิกการทำงาน");
            return 0;
        }

        $transaction = Yii::$app->db->beginTransaction();
        $updated = 0;
        try {
            foreach ($plan as $p) {
                $a = $p['approve'];
                $a->emp_id = $p['new_emp_id'];
                $a->title = $p['new_title'];
                $a->data_json = $p['new_data_json'];
                if (!$a->save(false)) {
                    throw new \Exception("บันทึกล้มเหลวที่ approve.id={$a->id}");
                }
                $updated++;
            }
            $transaction->commit();
            BaseConsole::output("เสร็จสมบูรณ์! ปรับปรุงทั้งหมด: $updated รายการ");
        } catch (\Throwable $e) {
            $transaction->rollBack();
            BaseConsole::error("เกิดข้อผิดพลาด: " . $e->getMessage());
            BaseConsole::error("ได้ทำการยกเลิกคำสั่งทั้งหมดแล้ว (Rolled back)");
            return 1;
        }

        return 0;
    }
}
