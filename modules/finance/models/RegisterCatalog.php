<?php

namespace app\modules\finance\models;

/**
 * ทะเบียนคุมงานการเงิน — แคตตาล็อกกลาง (config-driven register layer, เฟส 0)
 *
 * แหล่งความจริงเดียวของทะเบียนคุมทั้ง 18 เล่ม 5 หมวด
 * ใช้ร่วมกันโดยหน้า hub (register/index) และ FinanceRegisterService (จะทำต่อ)
 * ดูสเปกเต็มที่ docs/finance/control-registry-plan.md
 */
class RegisterCatalog
{
    // ชนิดงาน (ตามสเปก): A=มีข้อมูลแล้ว ขาดหน้าทะเบียน / B=ต่อยอด / C=ยังไม่มี subsystem
    public const KIND_A = 'A';
    public const KIND_B = 'B';
    public const KIND_C = 'C';

    // สถานะการพัฒนาของแต่ละเล่ม
    public const ST_READY  = 'ready';   // 🟢 มีข้อมูลต้นทาง — ต่อ Register Layer ได้ทันที (เฟส 0)
    public const ST_EXTEND = 'extend';  // 🟡 มีบางส่วน — ต้องต่อยอด
    public const ST_TODO   = 'todo';    // 🔴 ยังไม่มี subsystem ต้นทาง

    /**
     * 5 หมวดทะเบียนคุม
     * @return array<string,array{label:string,icon:string}>
     */
    public static function categories(): array
    {
        return [
            'budget'   => ['label' => 'เงินงบประมาณ / นำส่งคลัง', 'icon' => 'bi-bank2'],
            'fund'     => ['label' => 'เงินบำรุง / เงินฝาก', 'icon' => 'bi-cash-coin'],
            'ar'       => ['label' => 'ลูกหนี้ / รายได้ค่ารักษา', 'icon' => 'bi-clipboard2-pulse'],
            'payable'  => ['label' => 'เจ้าหนี้ / ใบสำคัญจ่าย', 'icon' => 'bi-journal-text'],
            'tax'      => ['label' => 'ภาษี / หลักประกัน', 'icon' => 'bi-percent'],
        ];
    }

    /**
     * ทะเบียนคุมทั้ง 18 เล่ม
     * key      = รหัสเล่ม (ใช้เป็นพารามิเตอร์ actionView)
     * no       = เลขอ้างอิงในสเปก
     * cat      = หมวด (คีย์จาก categories())
     * label    = ชื่อทะเบียน
     * status   = ready|extend|todo
     * kind     = A|B|C
     * phase    = เฟสที่จะพัฒนา (0-4)
     * source   = ต้นทางข้อมูลปัจจุบัน (โน้ตให้ผู้พัฒนา)
     *
     * @return array<int,array<string,mixed>>
     */
    public static function registers(): array
    {
        return [
            // หมวด 1 — เงินงบประมาณ / นำส่งคลัง
            ['key' => 'budget_allotment', 'no' => '1.1', 'cat' => 'budget', 'label' => 'เงินประจำงวด', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 4, 'source' => 'finance_budget_allotment (จะสร้าง)'],
            ['key' => 'budget_cashbook', 'no' => '1.2', 'cat' => 'budget', 'label' => 'รับ-จ่ายเงินงบประมาณ', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 4, 'source' => 'finance_budget_txn (จะสร้าง)'],
            ['key' => 'treasury_remit', 'no' => '1.3', 'cat' => 'budget', 'label' => 'รับและนำส่งเงิน (นส.02)', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 4, 'source' => 'finance_treasury_remit (จะสร้าง)'],
            ['key' => 'budget_return', 'no' => '1.4', 'cat' => 'budget', 'label' => 'เบิกเกินส่งคืนคลัง', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 4, 'source' => 'finance_budget_return (จะสร้าง)'],
            ['key' => 'central_fund', 'no' => '1.5', 'cat' => 'budget', 'label' => 'ค่าใช้จ่ายงบกลาง (สวัสดิการ)', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 4, 'source' => 'หมวดใน finance_budget_txn'],

            // หมวด 2 — เงินบำรุง / เงินฝาก
            ['key' => 'hospital_fund', 'no' => '2.1', 'cat' => 'fund', 'label' => 'เงินบำรุงโรงพยาบาล', 'status' => self::ST_READY, 'kind' => self::KIND_A, 'phase' => 0, 'source' => 'finance_cash_txn + finance_cash_category'],
            ['key' => 'nonbudget_cashbook', 'no' => '2.2', 'cat' => 'fund', 'label' => 'รับ-จ่ายเงินนอกงบประมาณ', 'status' => self::ST_READY, 'kind' => self::KIND_A, 'phase' => 0, 'source' => 'finance_cash_txn'],
            ['key' => 'bank_deposit', 'no' => '2.3', 'cat' => 'fund', 'label' => 'เงินฝากธนาคาร / เงินฝากคลัง', 'status' => self::ST_EXTEND, 'kind' => self::KIND_B, 'phase' => 3, 'source' => 'finance_cash_account + _balance + _transfer (ขาดงบพิสูจน์ยอด)'],
            ['key' => 'fund_by_project', 'no' => '2.4', 'cat' => 'fund', 'label' => 'เงินนอกงบฯ จำแนกตามโครงการ', 'status' => self::ST_EXTEND, 'kind' => self::KIND_B, 'phase' => 3, 'source' => 'plan_annual + finance_cash_plan (ขาด tag โครงการที่ txn)'],

            // หมวด 3 — ลูกหนี้ / รายได้ค่ารักษา
            ['key' => 'ar_by_fund', 'no' => '3.1', 'cat' => 'ar', 'label' => 'ลูกหนี้ค่ารักษาแยกสิทธิ', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 2, 'source' => 'finance_ar_invoice + _fund (จะสร้าง, import HIS)'],
            ['key' => 'ar_accrued', 'no' => '3.2', 'cat' => 'ar', 'label' => 'รายได้ค่ารักษาค้างรับ', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 2, 'source' => 'view สถานะ unpaid บน finance_ar_invoice'],
            ['key' => 'patient_deposit', 'no' => '3.3', 'cat' => 'ar', 'label' => 'เงินมัดจำ / รับฝากผู้ป่วย', 'status' => self::ST_TODO, 'kind' => self::KIND_C, 'phase' => 2, 'source' => 'finance_patient_deposit (จะสร้าง)'],

            // หมวด 4 — เจ้าหนี้ / ใบสำคัญจ่าย
            ['key' => 'disbursement_voucher', 'no' => '4.1', 'cat' => 'payable', 'label' => 'หลักฐานขอเบิก (ฎีกา/ใบสำคัญ)', 'status' => self::ST_EXTEND, 'kind' => self::KIND_B, 'phase' => 1, 'source' => 'finance_inbox + finance_payable_review (เพิ่มเลขฎีกา)'],
            ['key' => 'creditor', 'no' => '4.2', 'cat' => 'payable', 'label' => 'เจ้าหนี้รายตัว', 'status' => self::ST_READY, 'kind' => self::KIND_A, 'phase' => 0, 'source' => 'finance_payable'],
            ['key' => 'cheque_payment', 'no' => '4.3', 'cat' => 'payable', 'label' => 'จ่ายเช็ค / โอนเงิน', 'status' => self::ST_READY, 'kind' => self::KIND_A, 'phase' => 0, 'source' => 'finance_cheque + finance_payable_payment'],
            ['key' => 'petty_cash', 'no' => '4.4', 'cat' => 'payable', 'label' => 'เงินทดรองจ่าย / เงินสดย่อย', 'status' => self::ST_EXTEND, 'kind' => self::KIND_B, 'phase' => 1, 'source' => 'finance_petty_cash (จะสร้าง, แยกจากเงินยืม)'],

            // หมวด 5 — ภาษี / หลักประกัน
            ['key' => 'wht', 'no' => '5.1', 'cat' => 'tax', 'label' => 'ภาษีหัก ณ ที่จ่าย (ภงด.1/3/53)', 'status' => self::ST_EXTEND, 'kind' => self::KIND_B, 'phase' => 1, 'source' => 'purchase_wht_rate + field WHT บน payable/voucher'],
            ['key' => 'guarantee', 'no' => '5.2', 'cat' => 'tax', 'label' => 'เงินประกันซอง / ประกันสัญญา', 'status' => self::ST_READY, 'kind' => self::KIND_A, 'phase' => 0, 'source' => 'purchase_bond + purchase_bond_policy'],
        ];
    }

    /**
     * หาทะเบียนตาม key
     * @return array<string,mixed>|null
     */
    public static function find(string $key): ?array
    {
        foreach (self::registers() as $r) {
            if ($r['key'] === $key) {
                return $r;
            }
        }
        return null;
    }

    /** ทะเบียนที่พร้อมทำในเฟส 0 (ชนิด A) */
    public static function readyKeys(): array
    {
        return array_column(
            array_filter(self::registers(), fn ($r) => $r['status'] === self::ST_READY),
            'key'
        );
    }
}
