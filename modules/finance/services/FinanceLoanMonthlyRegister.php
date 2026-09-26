<?php

namespace app\modules\finance\services;

use app\modules\finance\models\FinanceLoan;
use yii\db\Expression;
use yii\db\Query;

/**
 * ทะเบียนคุมเอกสารแทนตัวเงิน สัญญารับรองการยืมเงิน — รายเดือน
 *
 * แบบฟอร์มเดิมใน Excel ของงานการเงิน แต่ละเดือนเป็นหนึ่งชีต แถวคือใบยืมที่
 * "ยังไม่คืนครบ ณ สิ้นเดือน" — ค้างยกมาจากเดือนก่อน ๆ รวมกับที่ยืมใหม่ในเดือนนี้
 * ใบที่คืนครบแล้ว (ก่อนหรือภายในเดือนนี้) ไม่ลงทะเบียนเดือนนี้
 * ใบที่คืนบางส่วนในเดือนนี้ยังแสดง พร้อมยอดส่งคืนของเดือน
 *
 *   ยอดคงเหลือยกมา (D) = ยอดยืม − ส่งใช้ก่อนวันที่ 1 ของเดือน  (เฉพาะใบที่ยืมก่อนเดือนนี้)
 *   จำนวนเงิน (E)      = ยอดยืม                                (เฉพาะใบที่ยืมในเดือนนี้)
 *   จำนวนเงินส่งคืน (I) = ใบสำคัญ + เงินสด ที่ส่งใช้ในเดือนนี้
 *   ลูกหนี้คงเหลือ (J)  = D + E − I
 *
 * ทุกยอดคำนวณย้อนจากรายการส่งใช้ ไม่ใช้ยอดคงเหลือปัจจุบันของใบยืม
 * ทะเบียนเดือนเก่าที่พิมพ์ซ้ำจึงได้ตัวเลขเท่าเดิม แม้ใบยืมจะส่งใช้เพิ่มไปแล้ว
 *
 * นับทุกใบที่ไม่ได้ยกเลิก ถือวันที่ยืมเป็นวันเริ่มเป็นลูกหนี้ ตามที่งานการเงินลงทะเบียน
 * อยู่เดิม (ใบที่ยังรอรับเช็คก็ลงทะเบียนแล้ว)
 */
class FinanceLoanMonthlyRegister
{

    /**
     * @param string $month รูปแบบ Y-m (ค.ศ.)
     * @return array{
     *   month: string, start: string, end: string, fiscal_year: int, fiscal_start: string,
     *   rows: array<int, array{loan: FinanceLoan, brought: float, borrowed: float, returned: float, return_dates: string[], balance: float}>,
     *   month_total: array{brought: float, borrowed: float, returned: float, balance: float},
     *   ytd_total: array{brought: float, borrowed: float, returned: float, balance: float}
     * }
     */
    public static function build(string $month): array
    {
        $start = date('Y-m-01', strtotime($month . '-01'));
        $end = date('Y-m-t', strtotime($start));
        $fiscalStart = self::fiscalStart($start);

        /** @var FinanceLoan[] $loans */
        $loans = FinanceLoan::find()
            ->where(['not', ['status' => FinanceLoan::STATUS_CANCELLED]])
            ->andWhere(['<=', 'borrowed_at', $end])
            ->orderBy(['borrowed_at' => SORT_ASC, 'contract_seq' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        $ids = array_map(static fn(FinanceLoan $l) => (int) $l->id, $loans);

        $settled = self::settlementSums($ids, $start, $end, $fiscalStart);

        $rows = [];
        $month = ['brought' => 0.0, 'borrowed' => 0.0, 'returned' => 0.0, 'balance' => 0.0];
        $ytd = ['brought' => 0.0, 'borrowed' => 0.0, 'returned' => 0.0, 'balance' => 0.0];

        foreach ($loans as $loan) {
            $id = (int) $loan->id;
            $amount = (float) $loan->approved_amount;
            $s = $settled[$id] ?? ['before_month' => 0.0, 'in_month' => 0.0, 'before_year' => 0.0, 'in_year' => 0.0, 'dates' => []];

            // ยอดต้นปีงบประมาณและยอดสะสมตั้งแต่ต้นปี — ใช้ในแถว "รวมตั้งแต่ต้นปี"
            if ($loan->borrowed_at < $fiscalStart) {
                $ytd['brought'] += max(0.0, $amount - $s['before_year']);
            } else {
                $ytd['borrowed'] += $amount;
            }
            $ytd['returned'] += $s['in_year'];

            $isNew = $loan->borrowed_at >= $start;
            $brought = $isNew ? 0.0 : round($amount - $s['before_month'], 2);
            $borrowed = $isNew ? $amount : 0.0;
            $returned = round($s['in_month'], 2);
            $balance = round($brought + $borrowed - $returned, 2);
            if ($balance <= 0.004) {
                continue; // คืนครบแล้ว ณ สิ้นเดือน ไม่ใช่ลูกหนี้คงค้างของเดือนนี้
            }
            $rows[] = [
                'loan' => $loan,
                'brought' => max(0.0, $brought),
                'borrowed' => $borrowed,
                'returned' => $returned,
                'return_dates' => $s['dates'],
                'balance' => $balance,
            ];
            $month['brought'] += max(0.0, $brought);
            $month['borrowed'] += $borrowed;
            $month['returned'] += $returned;
            $month['balance'] += $balance;
        }
        $ytd['balance'] = $ytd['brought'] + $ytd['borrowed'] - $ytd['returned'];

        return [
            'month' => substr($start, 0, 7),
            'start' => $start,
            'end' => $end,
            'fiscal_year' => (int) date('Y', strtotime($fiscalStart)) + 544,
            'fiscal_start' => $fiscalStart,
            'rows' => $rows,
            'month_total' => $month,
            'ytd_total' => $ytd,
        ];
    }

    /** วันที่ 1 ตุลาคมของปีงบประมาณที่เดือนนี้สังกัด */
    public static function fiscalStart(string $date): string
    {
        $ts = strtotime($date);
        $year = (int) date('Y', $ts);
        return ((int) date('n', $ts) >= 10 ? $year : $year - 1) . '-10-01';
    }

    /**
     * เดือนให้เลือกย้อนหลัง — ปีงบประมาณปัจจุบันและปีก่อน เรียงล่าสุดก่อน
     *
     * @return array<string,string> Y-m => "สิงหาคม 2569"
     */
    public static function monthOptions(): array
    {
        $options = [];
        $cursor = strtotime(date('Y-m-01'));
        $stop = strtotime(self::fiscalStart(date('Y-m-d')) . ' -1 year');
        while ($cursor >= $stop) {
            $ym = date('Y-m', $cursor);
            $options[$ym] = self::monthLabel($ym);
            $cursor = strtotime('-1 month', $cursor);
        }
        return $options;
    }

    public static function monthLabel(string $ym): string
    {
        static $names = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $ts = strtotime($ym . '-01');
        return $names[(int) date('n', $ts)] . ' ' . ((int) date('Y', $ts) + 543);
    }

    /** วันที่แบบในทะเบียนเดิม เช่น 04 มี.ค. 69 */
    public static function shortDate($value): string
    {
        static $names = [1 => 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        if (!$value || !($ts = strtotime((string) $value))) {
            return '';
        }
        return date('d', $ts) . ' ' . $names[(int) date('n', $ts)] . ' ' . substr((string) ((int) date('Y', $ts) + 543), -2);
    }

    /**
     * ยอดส่งใช้ของแต่ละใบ แยกช่วงเวลา
     *
     * @param int[] $ids
     * @return array<int, array{before_month: float, in_month: float, before_year: float, in_year: float, dates: string[]}>
     */
    private static function settlementSums(array $ids, string $start, string $end, string $fiscalStart): array
    {
        if (!$ids) {
            return [];
        }
        $rows = (new Query())
            ->select(['loan_id', 'settled_at', 'amount' => new Expression('voucher_amount + cash_amount')])
            ->from('{{%finance_loan_settlement}}')
            ->where(['loan_id' => $ids])
            ->andWhere(['<=', 'settled_at', $end])
            ->orderBy(['settled_at' => SORT_ASC, 'seq' => SORT_ASC])
            ->all();

        $sums = [];
        foreach ($rows as $row) {
            $id = (int) $row['loan_id'];
            $sums[$id] ??= ['before_month' => 0.0, 'in_month' => 0.0, 'before_year' => 0.0, 'in_year' => 0.0, 'dates' => []];
            $amount = (float) $row['amount'];
            $date = (string) $row['settled_at'];
            if ($date < $start) {
                $sums[$id]['before_month'] += $amount;
            } else {
                $sums[$id]['in_month'] += $amount;
                $sums[$id]['dates'][] = $date;
            }
            if ($date < $fiscalStart) {
                $sums[$id]['before_year'] += $amount;
            } else {
                $sums[$id]['in_year'] += $amount;
            }
        }
        return $sums;
    }
}
