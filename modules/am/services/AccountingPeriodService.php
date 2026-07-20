<?php

namespace app\modules\am\services;

use Yii;
use app\modules\am\models\AccountingPeriod;

/**
 * สร้าง/จัดการงวดบัญชีตามปีงบประมาณไทย (1 ต.ค. - 30 ก.ย.)
 *
 * ปีงบ (พ.ศ.) N: เริ่ม 1 ต.ค. ปี (N-1) พ.ศ. = (N-544) ค.ศ. ... สิ้นสุด 30 ก.ย. ปี N พ.ศ. = (N-543) ค.ศ.
 * ตัวอย่าง FY2568: 1 ต.ค. 2024 - 30 ก.ย. 2025 (ค.ศ.)
 *
 * แต่ละปีสร้าง: 12 งวดเดือน (ต.ค.=1..ก.ย.=12), 4 งวดไตรมาส, 1 งวดปีงบ
 * idempotent: ข้ามงวดที่มีอยู่แล้ว
 */
class AccountingPeriodService
{
    /** ชื่อเดือนไทยเรียงตามปีงบ (ต.ค. → ก.ย.) */
    private const MONTH_LABELS = [
        1 => 'ตุลาคม', 2 => 'พฤศจิกายน', 3 => 'ธันวาคม',
        4 => 'มกราคม', 5 => 'กุมภาพันธ์', 6 => 'มีนาคม',
        7 => 'เมษายน', 8 => 'พฤษภาคม', 9 => 'มิถุนายน',
        10 => 'กรกฎาคม', 11 => 'สิงหาคม', 12 => 'กันยายน',
    ];

    /**
     * แปลงลำดับเดือนในปีงบ (1=ต.ค.) → [ค.ศ.ปี, เดือนปฏิทิน 1-12]
     */
    public static function fiscalMonthToCalendar(int $fyBE, int $fiscalMonth): array
    {
        $endCe = $fyBE - 543;      // ปี ค.ศ. ที่ปีงบสิ้นสุด
        $startCe = $endCe - 1;     // ปี ค.ศ. ที่ปีงบเริ่ม (ต.ค.-ธ.ค.)
        if ($fiscalMonth <= 3) {
            // ต.ค.(10), พ.ย.(11), ธ.ค.(12) ของปีเริ่ม
            return [$startCe, $fiscalMonth + 9];
        }
        // ม.ค.(1) .. ก.ย.(9) ของปีสิ้นสุด
        return [$endCe, $fiscalMonth - 3];
    }

    /**
     * สร้างงวดทั้งปีงบ (เดือน/ไตรมาส/ปี) แบบ idempotent
     *
     * @return array{created:int, skipped:int}
     */
    public function generateFiscalYear(int $fyBE): array
    {
        $created = 0;
        $skipped = 0;
        $tx = Yii::$app->db->beginTransaction();
        try {
            // 12 งวดเดือน
            for ($m = 1; $m <= 12; $m++) {
                [$cy, $cm] = self::fiscalMonthToCalendar($fyBE, $m);
                $start = sprintf('%04d-%02d-01', $cy, $cm);
                $end = date('Y-m-t', strtotime($start));
                $name = self::MONTH_LABELS[$m] . ' ' . $fyBE;
                if ($this->upsert($fyBE, $m, AccountingPeriod::TYPE_MONTH, $name, $start, $end)) {
                    $created++;
                } else {
                    $skipped++;
                }
            }

            // 4 งวดไตรมาส
            for ($q = 1; $q <= 4; $q++) {
                $firstMonth = ($q - 1) * 3 + 1;
                $lastMonth = $q * 3;
                [$sy, $sm] = self::fiscalMonthToCalendar($fyBE, $firstMonth);
                [$ey, $em] = self::fiscalMonthToCalendar($fyBE, $lastMonth);
                $start = sprintf('%04d-%02d-01', $sy, $sm);
                $end = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $ey, $em)));
                if ($this->upsert($fyBE, $q, AccountingPeriod::TYPE_QUARTER, "ไตรมาส {$q}/{$fyBE}", $start, $end)) {
                    $created++;
                } else {
                    $skipped++;
                }
            }

            // 1 งวดปีงบ
            [$sy, $sm] = self::fiscalMonthToCalendar($fyBE, 1);
            [$ey, $em] = self::fiscalMonthToCalendar($fyBE, 12);
            $start = sprintf('%04d-%02d-01', $sy, $sm);
            $end = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $ey, $em)));
            if ($this->upsert($fyBE, 1, AccountingPeriod::TYPE_FISCAL_YEAR, "ปีงบประมาณ {$fyBE}", $start, $end)) {
                $created++;
            } else {
                $skipped++;
            }

            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * หางวดเดือน (month) ที่ครอบคลุมวันที่ที่กำหนด
     */
    public static function findMonthPeriodByDate(string $date): ?AccountingPeriod
    {
        return AccountingPeriod::find()
            ->where(['period_type' => AccountingPeriod::TYPE_MONTH])
            ->andWhere(['<=', 'start_date', $date])
            ->andWhere(['>=', 'end_date', $date])
            ->one();
    }

    private function upsert(int $fyBE, int $no, string $type, string $name, string $start, string $end): bool
    {
        $exists = AccountingPeriod::find()
            ->where(['fiscal_year' => $fyBE, 'period_type' => $type, 'period_no' => $no])
            ->exists();
        if ($exists) {
            return false;
        }
        $p = new AccountingPeriod();
        $p->fiscal_year = $fyBE;
        $p->period_no = $no;
        $p->period_type = $type;
        $p->name = $name;
        $p->start_date = $start;
        $p->end_date = $end;
        $p->status = AccountingPeriod::STATUS_OPEN;
        if (!$p->save()) {
            throw new \RuntimeException('สร้างงวดไม่สำเร็จ: ' . json_encode($p->getErrors(), JSON_UNESCAPED_UNICODE));
        }
        return true;
    }
}
