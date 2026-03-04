<?php

namespace app\modules\leave\components;

use Yii;

/**
 * Resolver สำหรับระบุผู้อนุมัติใบลาตามตั้งค่า approve_level_setting
 * ใช้ raw SQL ทั้งหมด ไม่พึ่ง model นอก modules/leave
 *
 * approve_level_setting.approver_type:
 *   org_leader1 — leader1 ของ node ใน tree ตาม org_node_level
 *   org_leader2 — leader2 ของ node ใน tree ตาม org_node_level
 *   director    — ผู้อำนวยการจาก SiteHelper
 *   fixed       — emp_id ที่ระบุตายตัวใน approver_value
 *   role        — ตามบทบาท (ยังไม่ resolve เป็น emp_id)
 *
 * org_node_level (ใน UI):
 *   null = แผนกของพนักงาน (self node)
 *   1    = ระดับ tree lvl 2 (หัวหน้างาน)
 *   2    = ระดับ tree lvl 1 (หัวหน้ากลุ่ม/ผอ.)
 *   N>2  = tree lvl N (ตรงตัว)
 */
class LeaveApproveResolver
{
    /**
     * แปลง UI org_node_level → tree lvl จริง
     * (ตาม convention เดิมใน ApproveLevelSetting: UI 1↔tree 2, UI 2↔tree 1)
     */
    private static function uiLevelToTreeLvl(?int $uiLevel): ?int
    {
        if ($uiLevel === null) {
            return null; // ใช้ lvl ของ self node
        }
        if ($uiLevel === 1) return 2;
        if ($uiLevel === 2) return 1;
        return $uiLevel;
    }

    /**
     * ดึงรายการตั้งค่าระดับการอนุมัติสำหรับ leave จาก DB
     */
    private static function getSettings(): array
    {
        return Yii::$app->db->createCommand(
            'SELECT id, level, label, approver_type, approver_value, org_node_level
             FROM approve_level_setting
             WHERE system = :sys AND active = 1
             ORDER BY level ASC'
        )->bindValue(':sys', 'leave')->queryAll();
    }

    /**
     * ดึง department_id ของพนักงาน
     */
    private static function getEmployeeDeptId(int $empId): ?int
    {
        $row = Yii::$app->db->createCommand(
            'SELECT department FROM employees WHERE id = :id LIMIT 1'
        )->bindValue(':id', $empId)->queryOne();

        return $row ? (int)$row['department'] : null;
    }

    /**
     * ดึง ancestors ทั้งหมด (รวม self) ของ dept node เรียงจาก root → leaf
     * คืน [ lvl => ['id'=>.., 'lvl'=>.., 'leader1'=>.., 'leader2'=>..] ]
     */
    private static function getAncestorsByLvl(int $deptId): array
    {
        $self = Yii::$app->db->createCommand(
            'SELECT id, lft, rgt, lvl FROM tree WHERE id = :id LIMIT 1'
        )->bindValue(':id', $deptId)->queryOne();

        if (!$self) {
            return [];
        }

        $rows = Yii::$app->db->createCommand(
            "SELECT n.id, n.lvl,
                    n.data_json->>'\$.leader1'  AS leader1,
                    n.data_json->>'\$.leader2'  AS leader2,
                    n.name
             FROM tree n
             WHERE n.lft <= :lft AND n.rgt >= :rgt
             ORDER BY n.lvl ASC"
        )->bindValues([':lft' => $self['lft'], ':rgt' => $self['rgt']])->queryAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['lvl']] = $row;
        }
        return $map; // key = tree lvl
    }

    /**
     * ดึงข้อมูลพนักงาน (สำหรับใส่ใน Approve row)
     */
    private static function getEmpInfo(int $empId): array
    {
        $row = Yii::$app->db->createCommand(
            'SELECT id, CONCAT(prefix, firstname, " ", lastname) AS fullname
             FROM employees WHERE id = :id LIMIT 1'
        )->bindValue(':id', $empId)->queryOne();

        return $row ?: ['id' => $empId, 'fullname' => ''];
    }

    /**
     * Resolve ผู้อนุมัติแต่ละระดับ
     * คืน array ของ [ 'level', 'title', 'emp_id', 'approver_type' ]
     */
    public static function resolve(int $empId): array
    {
        $settings = static::getSettings();
        if (empty($settings)) {
            return [];
        }

        $deptId      = static::getEmployeeDeptId($empId);
        $ancestorMap = $deptId ? static::getAncestorsByLvl($deptId) : [];
        $selfLvl     = $deptId && !empty($ancestorMap) ? max(array_keys($ancestorMap)) : null;

        $result = [];
        foreach ($settings as $setting) {
            $type    = $setting['approver_type'];
            $uiLevel = isset($setting['org_node_level']) && $setting['org_node_level'] !== null
                       ? (int) $setting['org_node_level']
                       : null;
            $resolvedEmpId = null;

            if ($type === 'org_leader1' || $type === 'org_leader2') {
                // หา node ตาม tree lvl ที่ตั้งค่าไว้
                $targetTreeLvl = static::uiLevelToTreeLvl($uiLevel) ?? $selfLvl;
                $node = $ancestorMap[$targetTreeLvl] ?? null;

                if ($node) {
                    $key           = ($type === 'org_leader1') ? 'leader1' : 'leader2';
                    $resolvedEmpId = !empty($node[$key]) ? (int) $node[$key] : null;
                }

            } elseif ($type === 'director') {
                $dirInfo       = \app\components\SiteHelper::viewDirector();
                $resolvedEmpId = !empty($dirInfo['id']) ? (int) $dirInfo['id'] : null;

            } elseif ($type === 'fixed') {
                $resolvedEmpId = !empty($setting['approver_value']) ? (int) $setting['approver_value'] : null;
            }
            // role: resolvedEmpId = null (handled by role-based permission check)

            $result[] = [
                'level'         => (int) $setting['level'],
                'title'         => $setting['label'],
                'approver_type' => $type,
                'emp_id'        => $resolvedEmpId,
            ];
        }

        return $result;
    }

    /**
     * สร้างรายการ Approve rows สำหรับ insert
     * ระดับแรก status = Pending, ที่เหลือ None
     */
    public static function buildApproveRows(int $empId, string $fromId): array
    {
        $resolved = static::resolve($empId);
        $rows = [];
        $first = true;

        foreach ($resolved as $r) {
            $dataJson = ['label' => $r['title']];
            if ($r['approver_type'] === 'role') {
                // เก็บ role ไว้ใน data_json เพื่อใช้ตรวจสิทธิ์ทีหลัง
                $dataJson['role'] = 'role'; // placeholder
            }

            $rows[] = [
                'from_id'   => $fromId,
                'name'      => 'leave',
                'level'     => $r['level'],
                'title'     => $r['title'],
                'emp_id'    => $r['emp_id'],
                'status'    => $first ? 'Pending' : 'None',
                'data_json' => $dataJson,
            ];
            $first = false;
        }

        return $rows;
    }

    /**
     * คืนข้อมูลผู้อนุมัติ (สำหรับแสดงใน view ก่อน save)
     * คล้ายกับ Leave::Approve() แต่อ่านจาก settings แทน hardcode 3 ระดับ
     */
    public static function getApproverInfoList(int $empId): array
    {
        $resolved = static::resolve($empId);
        $list = [];

        foreach ($resolved as $r) {
            $info = [
                'id'       => $r['emp_id'],
                'fullname' => '',
                'position' => '',
                'avatar'   => '',
                'title'    => $r['title'],
            ];

            if ($r['emp_id']) {
                $row = Yii::$app->db->createCommand(
                    "SELECT e.id,
                            CONCAT(COALESCE(e.prefix,''), e.firstname, ' ', e.lastname) AS fullname,
                            p.name AS position_name
                     FROM employees e
                     LEFT JOIN positions p ON p.id = e.position
                     WHERE e.id = :id LIMIT 1"
                )->bindValue(':id', $r['emp_id'])->queryOne();

                if ($row) {
                    $info['id']       = $row['id'];
                    $info['fullname'] = $row['fullname'];
                    $info['position'] = $row['position_name'] ?? '';
                }
            }

            $list[] = $info;
        }

        return $list;
    }
}
