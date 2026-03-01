<?php

namespace app\components;

use app\modules\approveV2\models\ApproveLevelSetting;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;

/**
 * แปลงตั้งค่าระดับการอนุมัติเป็นรายการผู้อนุมัติตามบริบท (ผู้ขอ = requester_emp_id)
 * โครงสร้างองค์กรจาก tree (Organization) ที่ /hr/organization/diagram
 *
 * ตามหลัก: เรียกจากระดับลึกสุด (แผนก/หน่วยของพนักงาน) ขึ้นมาก่อน แล้วค่อยเดินขึ้นหา parent
 * จนถึงระดับที่ตั้งไว้ (org_node_level)
 */
class ApproveLevelResolver
{
    /**
     * คืนรายการระดับการอนุมัติที่ resolve แล้ว สำหรับระบบและผู้ขอ
     * @param string $system เช่น leave, purchase
     * @param int $requesterEmpId รหัสพนักงานผู้ขอ
     * @return array[] แต่ละ element: [ level, label, approver_type, approver_value, emp_id, org_node_name (ชื่อหน่วยงาน/กลุ่มงาน) ]
     */
    public static function resolve($system, $requesterEmpId)
    {
        $settings = ApproveLevelSetting::getLevelsBySystem($system);
        $result = [];
        $employee = Employees::findOne($requesterEmpId);
        $orgNode = $employee && $employee->department
            ? Organization::findOne($employee->department)
            : null;

        foreach ($settings as $row) {
            $empId = null;
            $node = $orgNode;
            $orgNodeName = null;
            if ($node && in_array($row->approver_type, [ApproveLevelSetting::TYPE_ORG_LEADER1, ApproveLevelSetting::TYPE_ORG_LEADER2], true)) {
                $targetLevel = isset($row->org_node_level) ? (int) $row->org_node_level : null;
                if ($targetLevel !== null && $targetLevel > 0) {
                    while ($node && $node->lvl > $targetLevel) {
                        $parent = $node->getParent();
                        $node = $parent;
                    }
                }
                if ($node) {
                    $orgNodeName = $node->name;
                }
            }
            switch ($row->approver_type) {
                case ApproveLevelSetting::TYPE_ORG_LEADER1:
                    if ($node && !empty($node->data_json['leader1'])) {
                        $empId = (int) $node->data_json['leader1'];
                    }
                    break;
                case ApproveLevelSetting::TYPE_ORG_LEADER2:
                    if ($node && !empty($node->data_json['leader2'])) {
                        $empId = (int) $node->data_json['leader2'];
                    }
                    break;
                case ApproveLevelSetting::TYPE_DIRECTOR:
                    $director = \app\components\SiteHelper::viewDirector();
                    $empId = !empty($director['id']) ? (int) $director['id'] : null;
                    break;
                case ApproveLevelSetting::TYPE_FIXED:
                    if (!empty($row->approver_value)) {
                        $empId = (int) $row->approver_value;
                    }
                    break;
                case ApproveLevelSetting::TYPE_ROLE:
                    // ไม่ระบุ emp_id — ให้ผู้มีบทบาทนั้นเป็นผู้อนุมัติ (ตรวจในระบบว่าใครมี role นั้น)
                    $empId = null;
                    break;
            }

            $result[] = [
                'level' => (int) $row->level,
                'label' => $row->label,
                'approver_type' => $row->approver_type,
                'approver_value' => $row->approver_value,
                'emp_id' => $empId,
                'org_node_name' => $orgNodeName,
            ];
        }

        return $result;
    }

    /**
     * สร้างรายการ approve (สำหรับ insert ลงตาราง approve)
     * level แรก status = Pending, ที่เหลือ None
     *
     * @param string $system
     * @param int $requesterEmpId
     * @param string $fromId รหัสใบขอ (leave id, order id, ...)
     * @return array[] รายการสำหรับสร้าง Approve model แต่ละแถว
     */
    public static function buildApproveRows($system, $requesterEmpId, $fromId)
    {
        $resolved = static::resolve($system, $requesterEmpId);
        $rows = [];
        $first = true;
        foreach ($resolved as $r) {
            $rows[] = [
                'from_id' => $fromId,
                'name' => $system,
                'level' => $r['level'],
                'title' => $r['label'],
                'emp_id' => $r['emp_id'],
                'status' => $first ? 'Pending' : 'None',
                'data_json' => ['label' => $r['label']] + ($r['approver_type'] === ApproveLevelSetting::TYPE_ROLE && $r['approver_value'] ? ['role' => $r['approver_value']] : []),
            ];
            $first = false;
        }
        return $rows;
    }
}
