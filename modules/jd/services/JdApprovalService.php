<?php

namespace app\modules\jd\services;

use app\modules\approveV2\models\Approve;
use app\modules\hr\models\Employees;
use app\modules\jd\models\JdEmployee;
use app\modules\jd\models\JdEmployeeSection;
use Yii;

class JdApprovalService
{
    public const APPROVE_NAME = 'jd';

    public function rows(JdEmployee $jd): array
    {
        return Approve::find()
            ->with('employee')
            ->where(['name' => self::APPROVE_NAME, 'from_id' => (string) $jd->id])
            ->orderBy(['level' => SORT_ASC])
            ->all();
    }

    public function start(JdEmployee $jd): void
    {
        if ($jd->status !== JdEmployee::STATUS_DRAFT) {
            throw new \DomainException('เอกสารนี้ไม่ได้อยู่ในสถานะฉบับร่าง');
        }

        $signers = $this->configuredSigners($jd);
        $requiredRoles = ['ผู้จัดทำ', 'ผู้ตรวจสอบ', 'ผู้อนุมัติ'];
        $roles = array_column($signers, 'role');
        $missingRoles = array_diff($requiredRoles, $roles);
        if ($missingRoles !== []) {
            throw new \DomainException('กรุณากำหนดผู้ลงนามให้ครบ: ' . implode(', ', $missingRoles));
        }

        $employeeIds = array_column($signers, 'employee_id');
        $employees = Employees::find()->where(['id' => $employeeIds])->indexBy('id')->all();
        foreach ($signers as $signer) {
            if (!isset($employees[$signer['employee_id']]) || empty($employees[$signer['employee_id']]->user_id)) {
                throw new \DomainException('ผู้ลงนาม “' . $signer['employee_name'] . '” ยังไม่มีบัญชีผู้ใช้สำหรับลงนาม');
            }
        }

        Approve::deleteAll(['name' => self::APPROVE_NAME, 'from_id' => (string) $jd->id]);
        foreach ($signers as $index => $signer) {
            $row = new Approve([
                'name' => self::APPROVE_NAME,
                'from_id' => (string) $jd->id,
                'title' => 'JD Revision ' . $jd->revision_no . ' ' . $jd->position_title,
                'emp_id' => $signer['employee_id'],
                'level' => $index + 1,
                'status' => $index === 0 ? 'Pending' : 'None',
                'data_json' => [
                    'label' => $signer['role'],
                    'role' => $signer['role'],
                    'employee_name' => $signer['employee_name'],
                    'revision_no' => (int) $jd->revision_no,
                ],
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
            ]);
            if (!$row->save()) {
                throw new \RuntimeException(implode(', ', $row->getFirstErrors()));
            }
        }

        $jd->status = JdEmployee::STATUS_PENDING;
        if (!$jd->save(false)) {
            throw new \RuntimeException('ไม่สามารถเริ่มกระบวนการลงนามได้');
        }
    }

    public function sign(JdEmployee $jd, Employees $employee): bool
    {
        $row = Approve::findOne([
            'name' => self::APPROVE_NAME,
            'from_id' => (string) $jd->id,
            'emp_id' => (int) $employee->id,
            'status' => 'Pending',
        ]);
        if (!$row) {
            throw new \DomainException('ยังไม่ถึงลำดับลงนามของคุณ หรือรายการนี้ดำเนินการแล้ว');
        }

        $data = (array) $row->data_json;
        $data['approve_date'] = date('Y-m-d H:i:s');
        $data['signed_by_user_id'] = (int) Yii::$app->user->id;
        $data['ip_address'] = Yii::$app->request->userIP;
        $data['user_agent'] = mb_substr((string) Yii::$app->request->userAgent, 0, 255);
        $row->status = 'Pass';
        $row->data_json = $data;
        $row->updated_at = date('Y-m-d H:i:s');
        $row->updated_by = (int) Yii::$app->user->id;
        if (!$row->save(false)) {
            throw new \RuntimeException('บันทึกลายมือชื่อไม่สำเร็จ');
        }

        $next = Approve::find()
            ->where(['name' => self::APPROVE_NAME, 'from_id' => (string) $jd->id, 'status' => 'None'])
            ->orderBy(['level' => SORT_ASC])
            ->one();
        if ($next) {
            $next->status = 'Pending';
            $next->save(false);
            return false;
        }

        $this->publish($jd, $employee);
        return true;
    }

    public function configuredSigners(JdEmployee $jd): array
    {
        $section = JdEmployeeSection::findOne([
            'jd_employee_id' => $jd->id,
            'section_code' => 'approval',
        ]);
        $items = $section ? ($section->getData()['items'] ?? []) : [];
        $roleOrder = ['ผู้จัดทำ' => 1, 'ผู้ตรวจสอบ' => 2, 'ผู้อนุมัติ' => 3];
        $signers = [];
        foreach ((array) $items as $item) {
            $role = trim((string) ($item['role'] ?? ''));
            $employeeId = (int) ($item['employee_id'] ?? 0);
            if ($employeeId > 0 && isset($roleOrder[$role]) && !isset($signers[$role])) {
                $signers[$role] = [
                    'role' => $role,
                    'employee_id' => $employeeId,
                    'employee_name' => trim((string) ($item['employee_name'] ?? ('บุคลากร #' . $employeeId))),
                    'order' => $roleOrder[$role],
                ];
            }
        }
        usort($signers, static fn(array $a, array $b): int => $a['order'] <=> $b['order']);
        return array_values($signers);
    }

    private function publish(JdEmployee $jd, Employees $approver): void
    {
        $effectiveFrom = $jd->effective_from ?: date('Y-m-d');
        $current = JdEmployee::findCurrent((int) $jd->emp_id);
        if ($current && (int) $current->id !== (int) $jd->id) {
            $current->status = JdEmployee::STATUS_RETIRED;
            $current->effective_to = date('Y-m-d', strtotime($effectiveFrom . ' -1 day'));
            $current->save(false);
            $jd->supersedes_id = $current->id;
        }
        $jd->status = JdEmployee::STATUS_ACTIVE;
        $jd->effective_from = $effectiveFrom;
        $jd->effective_to = null;
        $jd->approved_by = (int) $approver->user_id;
        $jd->approved_at = date('Y-m-d H:i:s');
        $jd->save(false);
    }
}
