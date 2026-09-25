<?php

namespace app\modules\plan\components;

use Yii;
use yii\db\Query;
use app\modules\plan\models\PlanIcuMonth;
use app\modules\plan\models\PlanAnnualLedger;

/**
 * ICU 3 มิติ — สัญญาณเตือนภัยทางการเงินล่วงหน้า (ตามเกณฑ์ระบบแผนเงินบำรุง สป.สธ. / เขต เมนู 2.3)
 *
 * ประเมินรายงวดเดือน:
 *  มิติ 1 รายรับสุทธิติดลบ     : รายรับเดือนนี้ − รายจ่ายเดือนนี้ < 0
 *  มิติ 2 เงินบำรุงลดลง        : คงเหลือ < 0 หรือ ลดลงจากสิ้นเดือนก่อน > 10% (รพช.) / 7% (รพท.) / 5% (รพศ.)
 *  มิติ 3 ภาระผูกพันเพิ่มขึ้น   : ภาระผูกพัน/คงเหลือ > 40% (รพช.) / 50% (อื่น) หรือ (โต > 15% และ > 30%)
 * แล้วจัดเป็น 8 Case — ข้อความความหมาย/มาตรการคัดตามระบบเขตเพื่อให้รายงานตรงกัน
 *
 * แหล่งข้อมูล ERP:
 *  - รายรับ/รายจ่าย: finance_cash_txn (IN/OUT ตามวันที่เอกสาร) ชุดเดียวกับหน้ารับ-จ่ายเงินบำรุง
 *  - เงินบำรุงคงเหลือสิ้นเดือน: ยอดยกมาต้นปี + สะสม (รับ − จ่าย); ยกมาจากปิดบัญชีประจำปี → ข้อมูลสภาพคล่องหน้าแผน
 *  - ภาระผูกพันสิ้นเดือน: เจ้าหนี้ (finance_payable อนุมัติแล้ว) ที่ยังจ่ายไม่ครบ ณ สิ้นเดือน
 *  - ทั้งสองยอดกรอกทับได้รายเดือน (plan_icu_month)
 */
class IcuEvaluator
{
    public const LEVELS = ['รพช.' => 'รพช. (โรงพยาบาลชุมชน)', 'รพท.' => 'รพท. (โรงพยาบาลทั่วไป)', 'รพศ.' => 'รพศ. (โรงพยาบาลศูนย์)'];

    /** ระดับความเสี่ยง => [ชื่อ, สี Bootstrap] */
    public const TIERS = [
        'normal' => ['Normal (เขียว)', 'success'],
        'watchlist' => ['Watchlist (เฝ้าระวัง)', 'info'],
        'medium' => ['Medium Risk (เหลือง)', 'warning'],
        'high' => ['High Risk (ส้มเข้ม)', 'orange'],
        'critical' => ['Critical (แดงจัด)', 'danger'],
    ];

    /** เกณฑ์ตามระดับโรงพยาบาล */
    public static function thresholds(string $level): array
    {
        return [
            'cashDrop' => $level === 'รพช.' ? 10 : ($level === 'รพท.' ? 7 : 5),
            'commitRatio' => $level === 'รพช.' ? 40 : 50,
            'commitGrowth' => 15,
            'commitGrowthRatio' => 30,
        ];
    }

    /**
     * ประเมิน 1 งวด — พอร์ตตรงจากฟังก์ชันประเมินของระบบเขต
     */
    public static function evaluate(string $level, float $rev, float $exp, float $cashPrev, float $cashCur, float $comPrev, float $comCur): array
    {
        $th = self::thresholds($level);
        $net = $rev - $exp;
        $d1 = $net < 0;

        $cashDropPct = $cashPrev > 0 ? ($cashPrev - $cashCur) / $cashPrev * 100 : 0.0;
        $d2 = $cashCur < 0 || $cashDropPct > $th['cashDrop'];

        $comGrowthPct = $comPrev > 0 ? ($comCur - $comPrev) / $comPrev * 100 : ($comCur > 0 ? 100.0 : 0.0);
        $comRatioPct = $cashCur > 0 ? $comCur / $cashCur * 100 : ($comCur > 0 ? 100.0 : 0.0);
        $d3 = $comRatioPct > $th['commitRatio'] || ($comGrowthPct > $th['commitGrowth'] && $comRatioPct > $th['commitGrowthRatio']);

        $a1 = 'ให้ไปตามเก็บเงินจากลูกหนี้และตรวจสอบค่าใช้จ่ายที่ผิดปกติ';
        $a2 = 'ถ้าเงินสดหายให้ตรวจสอบการลงทุนและการใช้หนี้จำนวนมาก';
        $a3 = 'ถ้ามีเจ้าหนี้เพิ่มมากขึ้นให้ตรวจสอบความสามารถในการชำระหนี้';

        if (!$d1 && !$d2 && !$d3) {
            [$case, $tier, $meaning, $actions] = [1, 'normal', 'การเงินมีเสถียรภาพ สภาพคล่องเพียงพอ', ['รายงานผลทางการเงินตามระบบปกติ', 'ดำเนินงานตามแผนงบประมาณเดิม']];
        } elseif ($d1 && !$d2 && !$d3) {
            [$case, $tier, $meaning, $actions] = [2, 'watchlist', 'ขาดทุนเฉพาะงวด แต่เงินสะสมยังหนาแน่นรองรับได้', [$a1, 'ทบทวนอัตราการเร่งเบิกจ่ายในหมวดทั่วไป']];
        } elseif (!$d1 && $d2 && !$d3) {
            [$case, $tier, $meaning, $actions] = [3, 'watchlist', 'เงินสดลดฮวบ อาจเกิดจากจ่ายงวดลงทุนหรือครุภัณฑ์', [$a2, 'แยกแยะยอดเงินสด: เป็นรายจ่ายเพื่อการลงทุน/สินทรัพย์ถาวรตามงวด หรือ รายจ่ายดำเนินงานลดลงมากผิดปกติ']];
        } elseif (!$d1 && !$d2 && $d3) {
            [$case, $tier, $meaning, $actions] = [4, 'watchlist', 'ภาระล่วงหน้าเริ่มสะสม แต่ยังไม่ชนเพดานเงินสดปัจจุบัน', [$a3, 'จัดทำตารางกำหนดจ่ายหนี้ 3 เดือนล่วงหน้า และตรวจสอบสภาพคล่องรองรับ']];
        } elseif ($d1 && $d2 && !$d3) {
            [$case, $tier, $meaning, $actions] = [5, 'medium', 'เงินไหลออก 2 ทาง: ผลการดำเนินงานติดลบแถมเงินสำรองหดตัว', [$a1, $a2, 'ชะลอการจัดซื้อจัดจ้างหมวดไม่เร่งด่วน']];
        } elseif ($d1 && !$d2 && $d3) {
            [$case, $tier, $meaning, $actions] = [6, 'medium', 'เดือนนี้ติดลบ แถมข้างหน้ายังมีภาระผูกพันรออีกเกินเข้าเป้า', [$a1, $a3 . ' และเจรจาขยายเครดิตเทอม']];
        } elseif (!$d1 && $d2 && $d3) {
            [$case, $tier, $meaning, $actions] = [7, 'high', 'สัญญาณวิกฤตสภาพคล่อง: เงินพร้อมใช้ร่อยหรอ + หนี้ก้อนใหม่รอตัด', [$a2, $a3, 'ระงับการเปิด PO/PR จัดซื้อจัดจ้างใหม่ 100% และเร่งรัดเรียกเก็บลูกหนี้']];
        } else {
            [$case, $tier, $meaning, $actions] = [8, 'critical', 'วิกฤตการเงินเต็มรูปแบบ: รายจ่ายล้น + เงินสำรองหมด + หนี้สินท่วม', [$a1, $a2, $a3 . ' (เข้าสู่ Financial ICU)']];
        }

        return [
            'net' => $net, 'd1' => $d1,
            'cashDropPct' => $cashDropPct, 'd2' => $d2,
            'comGrowthPct' => $comGrowthPct, 'comRatioPct' => $comRatioPct, 'd3' => $d3,
            'case' => $case, 'tier' => $tier, 'meaning' => $meaning, 'actions' => $actions,
        ];
    }

    /** ชื่องวดเดือน ต.ค.–ก.ย. ของปีงบ (พ.ศ. 2 หลัก) */
    public static function monthLabels(int $fy): array
    {
        $a = str_pad((string) (($fy - 1) % 100), 2, '0', STR_PAD_LEFT);
        $b = str_pad((string) ($fy % 100), 2, '0', STR_PAD_LEFT);
        return ["ต.ค. $a", "พ.ย. $a", "ธ.ค. $a", "ม.ค. $b", "ก.พ. $b", "มี.ค. $b", "เม.ย. $b", "พ.ค. $b", "มิ.ย. $b", "ก.ค. $b", "ส.ค. $b", "ก.ย. $b"];
    }

    /** วันแรก/วันสุดท้ายของงวดเดือน m (1=ต.ค.) ปีงบ fy (พ.ศ.) เป็น Y-m-d ค.ศ. */
    public static function monthRange(int $fy, int $m): array
    {
        $g = $fy - 543;
        $year = $m <= 3 ? $g - 1 : $g;
        $month = $m <= 3 ? $m + 9 : $m - 3;
        $start = sprintf('%04d-%02d-01', $year, $month);
        return [$start, date('Y-m-t', strtotime($start))];
    }

    /**
     * ผลรายเดือนทั้งปีงบ — แถวละงวด: รายรับ/รายจ่าย/คงเหลือ/ภาระผูกพัน (ค่าคำนวณ + ค่ากรอกทับ) + ผลประเมิน
     * เดือนที่ยังไม่ถึง (หลังวันนี้) และไม่มีข้อมูล → evaluated = false
     */
    public static function yearly(int $fy, string $level): array
    {
        [$fyStart] = self::monthRange($fy, 1);
        [, $fyEnd] = self::monthRange($fy, 12);

        $flow = [];
        foreach ((new Query())->select(['ym' => "DATE_FORMAT(doc_date,'%Y-%m')", 'txn_type', 's' => 'SUM(amount)'])
            ->from('{{%finance_cash_txn}}')
            ->where(['between', 'doc_date', $fyStart, $fyEnd])
            ->groupBy(['ym', 'txn_type'])->all() as $r) {
            $flow[$r['ym']][$r['txn_type']] = (float) $r['s'];
        }

        $opening = self::openingBalance($fy);
        $overrides = PlanIcuMonth::find()->where(['fiscal_year' => $fy])->indexBy('month_no')->all();
        $labels = self::monthLabels($fy);
        $today = date('Y-m-d');

        $rows = [];
        $chain = $opening['amount']; // คงเหลือสิ้นงวดก่อน (ค่ากรอกทับส่งต่อให้เดือนถัดไป)
        $cashPrev = $opening['amount'];
        $comPrev = self::payableOutstanding(date('Y-m-d', strtotime($fyStart . ' -1 day')));
        for ($m = 1; $m <= 12; $m++) {
            [$s, $e] = self::monthRange($fy, $m);
            $ym = substr($s, 0, 7);
            $rev = $flow[$ym]['IN'] ?? 0.0;
            $exp = $flow[$ym]['OUT'] ?? 0.0;
            $ov = $overrides[$m] ?? null;

            $cashAuto = $chain === null ? null : $chain + $rev - $exp;
            $cash = $ov && $ov->cash_balance !== null ? (float) $ov->cash_balance : $cashAuto;
            $chain = $cash;
            $comAuto = $s <= $today ? self::payableOutstanding(min($e, $today)) : null;
            $com = $ov && $ov->commitment !== null ? (float) $ov->commitment : $comAuto;

            $hasData = $rev > 0 || $exp > 0 || ($ov && ($ov->cash_balance !== null || $ov->commitment !== null));
            $evaluated = $s <= $today && $hasData && $cash !== null;
            $res = $evaluated ? self::evaluate($level, $rev, $exp, (float) ($cashPrev ?? 0), (float) $cash, (float) ($comPrev ?? 0), (float) ($com ?? 0)) : null;

            $rows[$m] = [
                'm' => $m, 'label' => $labels[$m - 1], 'start' => $s, 'end' => $e,
                'rev' => $rev, 'exp' => $exp,
                'cashPrev' => $cashPrev, 'cash' => $cash, 'cashAuto' => $cashAuto, 'cashOverride' => $ov && $ov->cash_balance !== null,
                'comPrev' => $comPrev, 'com' => $com, 'comAuto' => $comAuto, 'comOverride' => $ov && $ov->commitment !== null,
                'note' => $ov ? $ov->note : null,
                'evaluated' => $evaluated, 'res' => $res,
            ];
            if ($s <= $today) {
                $cashPrev = $cash;
                $comPrev = $com;
            }
        }
        return ['opening' => $opening, 'rows' => $rows];
    }

    /**
     * ยอดยกมาต้นปีงบ: ปิดบัญชีประจำปี (finance_cash_year_close) → ข้อมูลสภาพคล่องหน้าแผน (plan_annual_ledger)
     * @return array{amount: float|null, source: string}
     */
    public static function openingBalance(int $fy): array
    {
        $v = (new Query())->select('carried_forward')->from('{{%finance_cash_year_close}}')->where(['fiscal_year' => $fy])->scalar();
        if ($v !== false && $v !== null) {
            return ['amount' => (float) $v, 'source' => 'ปิดบัญชีประจำปี (รับ-จ่ายเงินบำรุง)'];
        }
        $l = PlanAnnualLedger::findOne(['fiscal_year' => $fy]);
        if ($l) {
            return ['amount' => (float) $l->carry_forward, 'source' => 'ข้อมูลสภาพคล่อง หน้าแผนประจำปี'];
        }
        return ['amount' => null, 'source' => ''];
    }

    /** เจ้าหนี้ที่อนุมัติแล้วและยังจ่ายไม่ครบ ณ วันที่ $asOf (Y-m-d) */
    public static function payableOutstanding(string $asOf): float
    {
        $db = Yii::$app->db;
        if ($db->getTableSchema('{{%finance_payable}}') === null) {
            return 0.0;
        }
        $paid = (new Query())->select(['payable_id', 'paid' => 'SUM(amount)'])
            ->from('{{%finance_payable_settlement}}')->where(['<=', 'settle_date', $asOf])->groupBy('payable_id');
        $sum = (new Query())->from(['p' => '{{%finance_payable}}'])
            ->leftJoin(['s' => $paid], 's.payable_id = p.id')
            ->where(['p.status' => 'approved'])
            ->andWhere(['<=', new \yii\db\Expression('DATE(COALESCE(p.approved_at, p.billing_date, p.invoice_date))'), $asOf])
            ->andWhere(new \yii\db\Expression('p.net_amount - COALESCE(s.paid, 0) > 0.005'))
            ->sum(new \yii\db\Expression('p.net_amount - COALESCE(s.paid, 0)'));
        return (float) $sum;
    }
}
