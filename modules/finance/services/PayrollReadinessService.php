<?php

namespace app\modules\finance\services;

use app\modules\hr\models\Employees;
use app\modules\leave\models\Leave;
use Yii;
use yii\db\Expression;

/** Builds a read-only payroll readiness view from the HR source of truth. */
class PayrollReadinessService
{
    private const ACTIVE_EMPLOYMENT_STATUS = '1';

    public function build(string $periodStart, string $periodEnd, string $query = ''): array
    {
        $employeeQuery = Employees::find()
            ->alias('employee')
            ->with(['empDepartment', 'employeeType', 'employeePosition', 'positions'])
            ->andWhere(['or', ['employee.join_date' => null], ['<=', 'employee.join_date', $periodEnd]])
            ->andWhere(['or', ['employee.end_date' => null], ['>=', 'employee.end_date', $periodStart]])
            ->andWhere(['or',
                ['employee.status' => self::ACTIVE_EMPLOYMENT_STATUS],
                ['>=', 'employee.end_date', $periodStart],
            ]);

        $query = mb_substr(trim($query), 0, 100);
        if ($query !== '') {
            $employeeQuery->andWhere(['or',
                ['like', 'employee.id', $query],
                ['like', 'employee.fname', $query],
                ['like', 'employee.lname', $query],
                ['like', new Expression("CONCAT(COALESCE(employee.fname, ''), ' ', COALESCE(employee.lname, ''))"), $query],
            ]);
        }

        $employees = $employeeQuery
            ->orderBy(['employee.department' => SORT_ASC, 'employee.fname' => SORT_ASC, 'employee.lname' => SORT_ASC])
            ->all();

        $employeeIds = array_map(static fn(Employees $employee): int => (int) $employee->id, $employees);
        $bankEmployeeIds = $this->verifiedBankEmployeeIds($employeeIds);
        $leaveByEmployee = $this->approvedLeaveByEmployee($periodStart, $periodEnd, $employeeIds);
        $rows = [];

        foreach ($employees as $employee) {
            $fullName = trim(implode(' ', array_filter([
                trim((string) $employee->prefix) . trim((string) $employee->fname),
                trim((string) $employee->lname),
            ])));
            $position = $this->effectivePosition($employee->positions, $periodEnd);
            $salary = (float) ($position['salary'] ?? 0);
            $issues = [];
            if (!$employee->join_date) $issues[] = 'ไม่พบวันที่เริ่มงาน';
            if (!$employee->employee_type_id) $issues[] = 'ไม่พบประเภทบุคลากร';
            if (!$employee->department) $issues[] = 'ไม่พบหน่วยงาน';
            if ($salary <= 0) $issues[] = 'ไม่พบอัตราค่าตอบแทนที่มีผลในรอบ';
            if (!isset($bankEmployeeIds[(int) $employee->id])) $issues[] = 'ยังไม่ยืนยันบัญชีรับเงิน';

            $employmentEnd = (string) $employee->getAttribute('end_date');
            $isFinal = $employmentEnd !== ''
                && $employmentEnd >= $periodStart
                && $employmentEnd <= $periodEnd;
            $leave = $leaveByEmployee[(int) $employee->id] ?? ['count' => 0, 'days' => 0.0];

            $rows[] = [
                'employee' => $employee,
                'employee_id' => (int) $employee->id,
                'employee_code' => (string) $employee->id,
                'full_name' => $fullName !== '' ? $fullName : 'ไม่ระบุชื่อ',
                'department' => $employee->departmentName(),
                'employee_type' => $employee->positionTypeName() ?: 'ไม่ระบุ',
                'position' => $position['title'] ?? ($employee->employeePositionName() ?: 'ไม่ระบุ'),
                'salary' => $salary,
                'issues' => $issues,
                'ready' => $issues === [],
                'is_final' => $isFinal,
                'end_date' => $employmentEnd,
                'leave_count' => (int) $leave['count'],
                'leave_days' => (float) $leave['days'],
            ];
        }

        return $rows;
    }

    public static function overlaps(?string $joinDate, ?string $endDate, string $periodStart, string $periodEnd): bool
    {
        return ($joinDate === null || $joinDate === '' || $joinDate <= $periodEnd)
            && ($endDate === null || $endDate === '' || $endDate >= $periodStart);
    }

    public static function normalizeMonth(string $month, string $fallback): string
    {
        if (!preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
            return $fallback;
        }

        $year = (int) $matches[1];
        $monthNumber = (int) $matches[2];

        return checkdate($monthNumber, 1, $year) ? $month : $fallback;
    }

    public static function eligibleForPeriod(?string $status, ?string $endDate, string $periodStart): bool
    {
        return $status === self::ACTIVE_EMPLOYMENT_STATUS
            || ($endDate !== null && $endDate !== '' && $endDate >= $periodStart);
    }

    public function countInactiveWithoutEndDate(string $periodEnd): int
    {
        return (int) Employees::find()
            ->andWhere(['or', ['status' => null], ['<>', 'status', self::ACTIVE_EMPLOYMENT_STATUS]])
            ->andWhere(['end_date' => null])
            ->andWhere(['or', ['join_date' => null], ['<=', 'join_date', $periodEnd]])
            ->count();
    }

    public static function effectivePosition(array $positions, string $asOf): array
    {
        foreach ($positions as $position) {
            $data = is_array($position->data_json) ? $position->data_json : [];
            $start = $data['date_start'] ?? null;
            $end = $data['date_end'] ?? null;
            if (($start === null || $start === '' || $start <= $asOf) && ($end === null || $end === '' || $end >= $asOf)) {
                return [
                    'salary' => (float) ($data['salary'] ?? 0),
                    'title' => $data['employee_position_text'] ?? $data['position_name_text'] ?? $data['position_name'] ?? null,
                ];
            }
        }
        return [];
    }

    private function verifiedBankEmployeeIds(array $employeeIds): array
    {
        if ($employeeIds === []) return [];
        if (Yii::$app->db->getTableSchema('{{%payroll_bank_account}}', true) === null) return [];
        $ids = (new \yii\db\Query())->select('employee_id')->from('{{%payroll_bank_account}}')
            ->where(['status' => 'verified'])
            ->andWhere(['is_active' => 1])
            ->andWhere(['employee_id' => $employeeIds])
            ->column();
        return array_fill_keys(array_map('intval', $ids), true);
    }

    private function approvedLeaveByEmployee(string $periodStart, string $periodEnd, array $employeeIds): array
    {
        if ($employeeIds === []) return [];
        if (Yii::$app->db->getTableSchema(Leave::tableName(), true) === null) return [];
        $rows = Leave::find()->select(['emp_id', 'date_start', 'date_end', 'total_days'])
            ->where(['status' => 'Approve'])
            ->andWhere(['emp_id' => $employeeIds])
            ->andWhere(['<=', 'date_start', $periodEnd])
            ->andWhere(['or', ['date_end' => null], ['>=', 'date_end', $periodStart]])
            ->asArray()->all();
        $result = [];
        foreach ($rows as $row) {
            $employeeId = (int) $row['emp_id'];
            if (!isset($result[$employeeId])) $result[$employeeId] = ['count' => 0, 'days' => 0.0];
            $overlapStart = max($periodStart, (string) $row['date_start']);
            $overlapEnd = min($periodEnd, (string) ($row['date_end'] ?: $row['date_start']));
            $calendarDays = max(0, (int) ((strtotime($overlapEnd) - strtotime($overlapStart)) / 86400) + 1);
            $result[$employeeId]['count']++;
            $result[$employeeId]['days'] += min((float) ($row['total_days'] ?: $calendarDays), (float) $calendarDays);
        }
        return $result;
    }
}
