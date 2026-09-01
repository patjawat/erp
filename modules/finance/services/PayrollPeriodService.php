<?php

namespace app\modules\finance\services;

use app\modules\hr\models\Employees;
use DateTimeImmutable;
use RuntimeException;
use Yii;
use yii\db\Query;

/** Creates and maintains an auditable employee roster for a payroll period. */
class PayrollPeriodService
{
    public static function decodeSnapshot($value): array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            if (is_array($value)) return $value;
            $value = json_decode((string) $value, true);
        }
        return is_array($value) ? $value : [];
    }

    public function findByCode(string $periodCode): ?array
    {
        $row = (new Query())->from('{{%payroll_period}}')->where(['period_code' => $periodCode])->one();
        return $row ?: null;
    }

    public function open(string $periodCode, array $readinessRows): array
    {
        if ($existing = $this->findByCode($periodCode)) return $existing;

        $start = new DateTimeImmutable($periodCode . '-01');
        $now = date('Y-m-d H:i:s');
        $userId = $this->userId();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()->insert('{{%payroll_period}}', [
                'ref' => $this->ref(), 'period_code' => $periodCode,
                'date_start' => $start->format('Y-m-d'), 'date_end' => $start->modify('last day of this month')->format('Y-m-d'),
                'status' => 'draft', 'created_at' => $now, 'updated_at' => $now,
                'created_by' => $userId, 'updated_by' => $userId,
            ])->execute();
            $periodId = (int) Yii::$app->db->getLastInsertID();
            foreach ($readinessRows as $row) {
                $this->insertRosterRow($periodId, (int) $row['employee_id'], $this->snapshotFromReadiness($row), $row['is_final'] ? 'final' : 'regular', 'นำเข้าจากข้อมูลบุคลากรเมื่อเปิดรอบ');
            }
            $this->audit('period', $periodId, 'open', 'เปิดรอบและนำเข้ารายชื่อ ' . count($readinessRows) . ' คน', null, ['period_code' => $periodCode, 'employee_count' => count($readinessRows)]);
            $transaction->commit();
            return $this->findByCode($periodCode);
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function addEmployee(int $periodId, int $employeeId, string $reason, string $payrollCase): void
    {
        $period = $this->editablePeriod($periodId);
        if (!in_array($payrollCase, ['regular', 'final', 'retroactive', 'adjustment'], true)) {
            throw new RuntimeException('ประเภทการจ่ายไม่ถูกต้อง');
        }
        $employee = Employees::find()->where(['id' => $employeeId])->one();
        if (!$employee) throw new RuntimeException('ไม่พบบุคลากรรหัสที่ระบุ');

        $existing = (new Query())->from('{{%payroll_period_employee}}')->where(['payroll_period_id' => $periodId, 'employee_id' => $employeeId])->one();
        if ($existing) {
            if ($existing['status'] !== 'excluded') throw new RuntimeException('บุคลากรคนนี้อยู่ในรอบแล้ว');
            $this->setRosterStatus((int) $existing['id'], 'needs_review', $reason, 'restore');
            return;
        }

        $snapshot = [
            'employee_id' => (int) $employee->id,
            'employee_code' => (string) $employee->id,
            'full_name' => trim((string) $employee->prefix . (string) $employee->fname . ' ' . (string) $employee->lname),
            'department' => $employee->departmentName(),
            'employee_type' => $employee->positionTypeName() ?: 'ไม่ระบุ',
            'position' => $employee->employeePositionName() ?: 'ไม่ระบุ',
            'join_date' => $employee->join_date, 'end_date' => $employee->end_date,
            'readiness_issues' => ['เพิ่มเข้ารอบด้วยตนเอง ต้องตรวจสอบข้อมูลก่อนคำนวณ'],
        ];
        $this->insertRosterRow($periodId, $employeeId, $snapshot, $payrollCase, $reason);
        $rowId = (int) Yii::$app->db->getLastInsertID();
        $this->audit('period_employee', $rowId, 'manual_add', $reason, null, ['period_id' => (int) $period['id'], 'employee_id' => $employeeId, 'payroll_case' => $payrollCase]);
    }

    public function setRosterStatus(int $rowId, string $status, string $reason, string $action): array
    {
        $row = (new Query())->from('{{%payroll_period_employee}}')->where(['id' => $rowId])->one();
        if (!$row) throw new RuntimeException('ไม่พบรายชื่อในรอบ');
        $this->editablePeriod((int) $row['payroll_period_id']);
        $before = $row;
        Yii::$app->db->createCommand()->update('{{%payroll_period_employee}}', [
            'status' => $status, 'reason' => $reason, 'updated_at' => date('Y-m-d H:i:s'), 'updated_by' => $this->userId(),
        ], ['id' => $rowId])->execute();
        $after = $before; $after['status'] = $status; $after['reason'] = $reason;
        $this->audit('period_employee', $rowId, $action, $reason, $before, $after);
        return $after;
    }

    public function roster(int $periodId): array
    {
        return (new Query())->from('{{%payroll_period_employee}}')->where(['payroll_period_id' => $periodId])->orderBy(['id' => SORT_ASC])->all();
    }

    private function editablePeriod(int $periodId): array
    {
        $period = (new Query())->from('{{%payroll_period}}')->where(['id' => $periodId])->one();
        if (!$period) throw new RuntimeException('ไม่พบรอบเงินเดือน');
        if ($period['status'] !== 'draft' || $period['locked_at']) throw new RuntimeException('รอบนี้ถูกล็อกแล้ว ไม่สามารถแก้ไขรายชื่อได้');
        return $period;
    }

    private function insertRosterRow(int $periodId, int $employeeId, array $snapshot, string $payrollCase, string $reason): void
    {
        $now = date('Y-m-d H:i:s');
        Yii::$app->db->createCommand()->insert('{{%payroll_period_employee}}', [
            'ref' => $this->ref(), 'payroll_period_id' => $periodId, 'employee_id' => $employeeId,
            'employee_snapshot' => $snapshot,
            'payroll_case' => $payrollCase, 'status' => 'needs_review', 'reason' => $reason,
            'created_at' => $now, 'updated_at' => $now, 'created_by' => $this->userId(), 'updated_by' => $this->userId(),
        ])->execute();
    }

    private function snapshotFromReadiness(array $row): array
    {
        return [
            'employee_id' => $row['employee_id'], 'employee_code' => $row['employee_code'], 'full_name' => $row['full_name'],
            'department' => $row['department'], 'employee_type' => $row['employee_type'], 'position' => $row['position'],
            'salary' => $row['salary'], 'end_date' => $row['end_date'], 'is_final' => $row['is_final'],
            'leave_count' => $row['leave_count'], 'leave_days' => $row['leave_days'], 'readiness_issues' => $row['issues'],
        ];
    }

    private function audit(string $type, int $id, string $action, string $reason, ?array $before, ?array $after): void
    {
        Yii::$app->db->createCommand()->insert('{{%payroll_audit_log}}', [
            'ref' => $this->ref(), 'entity_type' => $type, 'entity_id' => $id, 'action' => $action, 'reason' => $reason,
            'before_json' => $before,
            'after_json' => $after,
            'ip_address' => mb_substr((string) Yii::$app->request->userIP, 0, 45),
            'created_at' => date('Y-m-d H:i:s'), 'created_by' => $this->userId(),
        ])->execute();
    }

    private function ref(): string { return substr(Yii::$app->getSecurity()->generateRandomString(), 10); }
    private function userId(): ?int { return Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id; }
}
