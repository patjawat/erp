<?php

namespace app\modules\purchase\components;

use app\modules\purchase\models\Contract;
use app\modules\purchase\models\ContractMilestone;
use app\modules\purchase\models\WhtRate;

/**
 * สูตรคำนวณของงานบริหารสัญญา — ค่าปรับ และภาษีหัก ณ ที่จ่าย
 *
 * แยกออกจาก model เพราะตัวเลขสองชุดนี้เป็นเงินจริงของคู่สัญญา และถูกพิมพ์ลง
 * เอกสารที่มีลายเซ็น จึงต้องทดสอบได้ตรง ๆ โดยไม่ต้องสร้างเรคคอร์ดในฐานข้อมูล
 * (ดู commands/ContractTestController.php)
 *
 * หลักที่ยึด
 *   - ค่าปรับ = วงเงิน × อัตราต่อวัน × จำนวนวันที่ล่าช้า
 *   - ค่าปรับรวมไม่เกินวงเงินตามสัญญา (เพดาน 100%)
 *   - สัญญาที่แบ่งงวด คิดค่าปรับจากวงเงินของงวดที่ล่าช้า ไม่ใช่วงเงินทั้งสัญญา
 *   - ภาษีหัก ณ ที่จ่ายคิดจากฐานก่อน VAT เมื่อวงเงินที่กรอกรวม VAT แล้ว
 *   - อัตราภาษีมาจากทะเบียน purchase_wht_rate เท่านั้น ไม่มีค่าสำรองในโค้ด
 */
class ContractCalculator
{
    /** อัตราภาษีมูลค่าเพิ่มที่ใช้ถอดฐานก่อน VAT */
    const VAT_RATE = 7.0;

    /**
     * สัดส่วนค่าปรับที่ถือว่า "สูงพอที่ต้องพิจารณาบอกเลิกสัญญา"
     * ใช้เพื่อขึ้นป้ายเตือนบนหน้าจอเท่านั้น ไม่ได้บังคับหรือหยุดการทำงานใด ๆ
     */
    const WARN_RATIO = 10.0;

    // ── ค่าปรับ ──────────────────────────────────────────────────────────────

    /**
     * จำนวนวันที่ล่าช้า นับจากวันถัดจากวันครบกำหนดถึงวันที่ส่งมอบจริง
     * ส่งมอบตรงกำหนดหรือก่อนกำหนด = 0 วัน
     */
    public static function overdueDays(?string $dueDate, ?string $closingDate): int
    {
        if (empty($dueDate) || empty($closingDate)) {
            return 0;
        }
        $due = strtotime($dueDate);
        $closing = strtotime($closingDate);
        if ($due === false || $closing === false || $closing <= $due) {
            return 0;
        }
        return (int) floor(($closing - $due) / 86400);
    }

    /** ค่าปรับดิบของยอดหนึ่งก้อน ยังไม่ตัดเพดาน */
    public static function rawFine(float $amount, float $ratePerDay, int $days): float
    {
        if ($amount <= 0 || $ratePerDay <= 0 || $days <= 0) {
            return 0.0;
        }
        return round($amount * $ratePerDay / 100 * $days, 2);
    }

    /**
     * คำนวณค่าปรับของทั้งสัญญา
     *
     * @param ContractMilestone[] $milestones งวดงาน (ใช้เมื่อ fine_base = milestone)
     * @return array{days:int, amount:float, raw:float, capped:bool, per_milestone:array}
     */
    public static function fine(Contract $contract, array $milestones = []): array
    {
        $budget = (float) $contract->budget;
        $rate = (float) $contract->fine_rate;

        $perMilestone = [];
        $raw = 0.0;
        $days = 0;

        if ($contract->fine_base === Contract::FINE_BASE_MILESTONE) {
            foreach ($milestones as $ms) {
                $msDays = self::overdueDays($ms->due_date, $ms->closingDate());
                $msFine = self::rawFine((float) $ms->amount, $rate, $msDays);
                $perMilestone[$ms->seq ?: count($perMilestone) + 1] = [
                    'days' => $msDays,
                    'amount' => $msFine,
                ];
                $raw += $msFine;
                // วันล่าช้าที่รายงานของทั้งสัญญา = งวดที่ล่าช้ามากที่สุด
                // (การบวกวันของทุกงวดรวมกันจะได้ตัวเลขที่ไม่มีความหมายทางปฏิทิน)
                $days = max($days, $msDays);
            }
        } else {
            $days = self::overdueDays($contract->end_date, $contract->closingDate());
            $raw = self::rawFine($budget, $rate, $days);
        }

        $raw = round($raw, 2);
        $capped = $budget > 0 && $raw > $budget;

        return [
            'days' => $days,
            'raw' => $raw,
            'amount' => $capped ? round($budget, 2) : $raw,
            'capped' => $capped,
            'per_milestone' => $perMilestone,
        ];
    }

    /** ค่าปรับคิดเป็นร้อยละของวงเงินสัญญา ใช้ตัดสินว่าจะขึ้นป้ายเตือนหรือไม่ */
    public static function fineRatio(float $fineAmount, float $budget): float
    {
        if ($budget <= 0) {
            return 0.0;
        }
        return round($fineAmount * 100 / $budget, 2);
    }

    // ── ภาษีหัก ณ ที่จ่าย ────────────────────────────────────────────────────

    /**
     * คำนวณภาษีหัก ณ ที่จ่ายจากทะเบียนอัตรา
     *
     * คืน rate = null เมื่อไม่มีอัตราตั้งไว้ในทะเบียน — ผู้เรียกต้องแสดงข้อความให้
     * ผู้ใช้ไปตั้งค่าก่อน ระบบต้องไม่เดาอัตราเอง เพราะตัวเลขนี้ถูกพิมพ์ลงเอกสาร
     *
     * @return array{rate:float|null, base:float, amount:float, reason:string, law:string|null}
     */
    public static function wht(float $budget, ?string $contractType, ?string $partyType, bool $vatIncluded = true): array
    {
        $budget = round($budget, 2);
        $setting = WhtRate::findRate($contractType, $partyType);

        if ($setting === null) {
            return [
                'rate' => null,
                'base' => $budget,
                'amount' => 0.0,
                'reason' => 'ยังไม่ได้ตั้งอัตราภาษีหัก ณ ที่จ่ายของสัญญาประเภทนี้ในหน้าตั้งค่า',
                'law' => null,
            ];
        }

        if ($budget <= 0) {
            return [
                'rate' => (float) $setting->rate,
                'base' => 0.0,
                'amount' => 0.0,
                'reason' => 'ยังไม่ได้ระบุวงเงิน',
                'law' => $setting->law_ref,
            ];
        }

        if ($budget < (float) $setting->threshold) {
            return [
                'rate' => (float) $setting->rate,
                'base' => $budget,
                'amount' => 0.0,
                'reason' => 'ยอดจ่ายต่ำกว่าเกณฑ์ ' . number_format((float) $setting->threshold, 2) . ' บาท จึงไม่ต้องหัก',
                'law' => $setting->law_ref,
            ];
        }

        $base = $vatIncluded
            ? round($budget * 100 / (100 + self::VAT_RATE), 2)
            : $budget;
        $amount = round($base * (float) $setting->rate / 100, 2);

        return [
            'rate' => (float) $setting->rate,
            'base' => $base,
            'amount' => $amount,
            'reason' => $vatIncluded
                ? 'คำนวณจากฐานก่อนภาษีมูลค่าเพิ่ม (วงเงินที่กรอกรวม VAT แล้ว)'
                : 'คำนวณจากวงเงินเต็ม (วงเงินที่กรอกยังไม่รวม VAT)',
            'law' => $setting->law_ref,
        ];
    }
}
