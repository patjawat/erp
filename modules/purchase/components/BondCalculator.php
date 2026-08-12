<?php

namespace app\modules\purchase\components;

use app\modules\purchase\models\BondPolicy;

/**
 * เกณฑ์และการคำนวณของงานหลักประกัน
 *
 * แยกออกจาก model ด้วยเหตุผลเดียวกับ ContractCalculator — ตัวเลขชุดนี้เป็นตัวบอกว่า
 * ต้องเรียกหลักประกันจากคู่สัญญากี่บาท และถูกพิมพ์ลงทะเบียนคุมที่ใช้ตรวจสอบย้อนหลัง
 * จึงต้องทดสอบได้ตรง ๆ โดยไม่ต้องสร้างเรคคอร์ดในฐานข้อมูล
 * (ดู commands/BondTestController.php)
 *
 * หลักที่ยึด
 *   - เกณฑ์มาจากทะเบียน purchase_bond_policy เท่านั้น ไม่มีค่าสำรองในโค้ด
 *   - เมื่อไม่มีเกณฑ์ที่ครอบวงเงินนั้น ระบบบอกว่า "ยังไม่ได้ตั้งเกณฑ์" ไม่ใช่เดาเอง
 *   - ข้อความอธิบายถูกประกอบจากแถวที่จับคู่ได้ ไม่ใช่ข้อความคงที่ที่เขียนคู่กันไว้
 */
class BondCalculator
{
    /** ถือว่า "ใกล้หมดอายุ" เมื่อเหลือไม่เกินกี่วัน — ใช้ขึ้นป้ายเตือนเท่านั้น */
    const NEAR_DAYS = 30;

    const STATE_NONE = 'none';        // ไม่ได้ระบุวันสิ้นอายุ หรือปิดเรื่องไปแล้ว
    const STATE_OK = 'ok';
    const STATE_NEAR = 'near';
    const STATE_EXPIRED = 'expired';

    /**
     * เกณฑ์ที่ใช้กับวงเงินก้อนหนึ่ง
     *
     * @param float $amount วงเงินตามสัญญา/ใบสั่งซื้อ
     * @param string|null $procKind ประเภทสัญญา (Contract::TYPE_*)
     * @return array{
     *     configured:bool, required:bool, rate:float, amount:float,
     *     title:string, reason:string, law:string|null, range:string|null
     * }
     */
    public static function policyFor(float $amount, ?string $procKind): array
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            return self::result(false, false, 0.0, 0.0, '', 'ยังไม่ได้ระบุวงเงิน จึงยังบอกไม่ได้ว่าต้องวางหลักประกันหรือไม่', null, null);
        }

        $policy = BondPolicy::match($amount, $procKind);

        if ($policy === null) {
            return self::result(
                false,
                false,
                0.0,
                0.0,
                '',
                'ยังไม่ได้ตั้งเกณฑ์หลักประกันสำหรับวงเงินนี้ในหน้าตั้งค่า ระบบจึงไม่ระบุว่าต้องวางหรือไม่',
                null,
                null
            );
        }

        $rate = (float) $policy->rate;
        $required = (int) $policy->required === 1;

        return self::result(
            true,
            $required,
            $rate,
            $required ? self::suggested($amount, $rate) : 0.0,
            $policy->title,
            $required
                ? 'วงเงิน ' . number_format($amount, 2) . ' บาท เข้าเกณฑ์ "' . $policy->title . '"'
                : $policy->title,
            $policy->law_ref,
            $policy->rangeText()
        );
    }

    /** วงเงินหลักประกันที่ควรเรียก = ฐาน × อัตรา */
    public static function suggested(float $base, float $rate): float
    {
        if ($base <= 0 || $rate <= 0) {
            return 0.0;
        }
        return round($base * $rate / 100, 2);
    }

    /**
     * จำนวนวันที่เหลือถึงวันสิ้นอายุ (ติดลบ = หมดอายุแล้ว)
     * คืน null เมื่อไม่ได้ระบุวันสิ้นอายุ
     */
    public static function daysToExpiry(?string $expiry, ?string $today = null): ?int
    {
        if (empty($expiry)) {
            return null;
        }
        $end = strtotime($expiry);
        $now = strtotime($today ?: date('Y-m-d'));
        if ($end === false || $now === false) {
            return null;
        }
        return (int) floor(($end - $now) / 86400);
    }

    /**
     * สถานะอายุของหลักประกันใบหนึ่ง
     *
     * ใบที่คืนหรือยึดไปแล้วไม่ต้องเตือนเรื่องอายุอีก เพราะเรื่องปิดไปแล้ว
     * เช่นเดียวกับใบที่ได้รับยกเว้น ซึ่งไม่มีหลักประกันจริงให้หมดอายุ
     *
     * @param string[] $closedStatuses สถานะที่ถือว่าเรื่องปิดแล้ว
     */
    public static function expiryState(?string $expiry, ?string $status, array $closedStatuses, ?string $today = null): string
    {
        if (in_array((string) $status, $closedStatuses, true)) {
            return self::STATE_NONE;
        }
        $days = self::daysToExpiry($expiry, $today);
        if ($days === null) {
            return self::STATE_NONE;
        }
        if ($days < 0) {
            return self::STATE_EXPIRED;
        }
        return $days <= self::NEAR_DAYS ? self::STATE_NEAR : self::STATE_OK;
    }

    /** @return array<string,mixed> */
    private static function result(
        bool $configured,
        bool $required,
        float $rate,
        float $amount,
        string $title,
        string $reason,
        ?string $law,
        ?string $range
    ): array {
        return [
            'configured' => $configured,
            'required' => $required,
            'rate' => $rate,
            'amount' => $amount,
            'title' => $title,
            'reason' => $reason,
            'law' => $law,
            'range' => $range,
        ];
    }
}
