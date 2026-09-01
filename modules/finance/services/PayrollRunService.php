<?php

namespace app\modules\finance\services;

use DateTimeImmutable;
use RuntimeException;
use Yii;
use yii\db\Query;

class PayrollRunService
{
    public const TYPES = ['salary' => 'เงินเดือน', 'compensation' => 'ค่าตอบแทน', 'overtime' => 'OT'];

    public function create(string $month, string $type, ?string $payDate): int
    {
        if (!isset(self::TYPES[$type]) || !preg_match('/^\d{4}-\d{2}$/', $month)) throw new RuntimeException('เดือนหรือประเภทรอบไม่ถูกต้อง');
        $start = new DateTimeImmutable($month . '-01');
        if ($start->format('Y-m') !== $month) throw new RuntimeException('เดือนไม่ถูกต้อง');
        if ($payDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $payDate)) throw new RuntimeException('วันที่จ่ายไม่ถูกต้อง');
        $existing = (new Query())->from('{{%payroll_period}}')->where(['period_code' => $month, 'period_type' => $type])->one();
        if ($existing) return (int) $existing['id'];

        $end = $start->modify('last day of this month')->format('Y-m-d');
        $items = (new Query())->select(['pei.employee_id', 'pei.amount', 'pei.item_type_id', 'pit.code', 'pit.name', 'pit.direction', 'e.cid', 'e.prefix', 'e.fname', 'e.lname'])
            ->from(['pei' => '{{%payroll_employee_item}}'])
            ->innerJoin(['pit' => '{{%payroll_item_type}}'], 'pit.id = pei.item_type_id')
            ->innerJoin(['e' => '{{%employees}}'], 'e.id = pei.employee_id')
            ->where(['pei.status' => 'active', 'pit.status' => 'active', 'pit.payroll_scope' => $type])
            ->andWhere(['<=', 'pei.effective_from', $end])
            ->andWhere(['or', ['pei.effective_to' => null], ['>=', 'pei.effective_to', $start->format('Y-m-d')]])
            ->orderBy(['pei.employee_id' => SORT_ASC, 'pit.direction' => SORT_ASC, 'pit.name' => SORT_ASC])->all();
        if (!$items) throw new RuntimeException('ไม่พบรายการรับหรือรายการจ่ายที่เปิดใช้งานสำหรับรอบนี้');

        $byEmployee = [];
        foreach ($items as $item) {
            $employeeId = (int) $item['employee_id'];
            if (!isset($byEmployee[$employeeId])) $byEmployee[$employeeId] = ['employee' => $item, 'earnings' => [], 'deductions' => []];
            $line = ['item_type_id' => (int) $item['item_type_id'], 'code' => $item['code'], 'name' => $item['name'], 'amount' => (float) $item['amount']];
            $item['direction'] === 'deduction' ? $byEmployee[$employeeId]['deductions'][] = $line : $byEmployee[$employeeId]['earnings'][] = $line;
        }
        $now = date('Y-m-d H:i:s'); $userId = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()->insert('{{%payroll_period}}', [
                'ref' => substr(Yii::$app->security->generateRandomString(), 10), 'period_code' => $month, 'period_type' => $type,
                'date_start' => $start->format('Y-m-d'), 'date_end' => $end, 'pay_date' => $payDate ?: null, 'status' => 'calculated',
                'created_at' => $now, 'updated_at' => $now, 'created_by' => $userId, 'updated_by' => $userId,
            ])->execute();
            $periodId = (int) Yii::$app->db->getLastInsertID();
            $bankRows = (new Query())->select(['employee_id', 'bank_code', 'account_last4'])->from('{{%payroll_bank_account}}')->where(['employee_id' => array_keys($byEmployee), 'is_active' => 1])->all();
            $banks = []; foreach ($bankRows as $bank) if (!isset($banks[(int) $bank['employee_id']])) $banks[(int) $bank['employee_id']] = $bank;
            foreach ($byEmployee as $employeeId => $data) {
                $gross = array_sum(array_column($data['earnings'], 'amount')); $deduction = array_sum(array_column($data['deductions'], 'amount'));
                $person = $data['employee']; $bank = $banks[$employeeId] ?? null;
                Yii::$app->db->createCommand()->insert('{{%payroll_period_employee}}', [
                    'ref' => substr(Yii::$app->security->generateRandomString(), 10), 'payroll_period_id' => $periodId, 'employee_id' => $employeeId,
                    'employee_snapshot' => ['full_name' => trim($person['prefix'] . $person['fname'] . ' ' . $person['lname']), 'cid' => $person['cid'], 'bank_code' => $bank['bank_code'] ?? null, 'account_last4' => $bank['account_last4'] ?? null],
                    'calculation_snapshot' => ['earnings' => $data['earnings'], 'deductions' => $data['deductions']],
                    'payroll_case' => 'regular', 'status' => 'calculated', 'gross_amount' => $gross, 'deduction_amount' => $deduction, 'net_amount' => $gross - $deduction,
                    'reason' => 'คำนวณจากรายการที่เปิดใช้งาน', 'created_at' => $now, 'updated_at' => $now, 'created_by' => $userId, 'updated_by' => $userId,
                ])->execute();
            }
            $transaction->commit(); return $periodId;
        } catch (\Throwable $e) { if ($transaction->isActive) $transaction->rollBack(); throw $e; }
    }
}
