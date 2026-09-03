<?php

namespace app\modules\finance\components;

use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanFollowup;
use app\modules\finance\models\FinanceLoanItemKind;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use yii\helpers\Html;

/**
 * สร้างเนื้อหาเริ่มต้นของเอกสารเงินยืม ก่อนเก็บเป็น snapshot ที่ผู้ใช้แก้ไขได้
 *
 * ถ้อยคำคงที่ ลำดับบรรทัด และคำชี้แจงท้ายฉบับ ยึดตามไฟล์ Excel ที่งานการเงิน
 * รพร.ด่านซ้าย ใช้อยู่จริง ส่วนข้อมูลที่ทะเบียนยังไม่เก็บ เช่น เลขที่หนังสือที่ยัง
 * ไม่ออก รายชื่อคณะเดินทาง หรือวันที่ลงนาม เว้นเป็นจุดไข่ปลาให้พิมพ์เองบนหน้าจอ
 * ก่อนสั่งพิมพ์ — ดีกว่าเดาค่าให้แล้วผู้ใช้ไม่ทันสังเกตว่าผิด
 */
final class FinanceLoanDocumentBuilder
{
    /**
     * รุ่นของแม่แบบแต่ละฉบับ เพิ่มเลขทุกครั้งที่แก้เนื้อหาแม่แบบ
     * เอกสารที่บันทึกไว้ด้วยรุ่นเก่ากว่านี้จะถูกสร้างใหม่ให้อัตโนมัติตอนเปิด
     */
    private const VERSIONS = [
        FinanceLoanDocumentCatalog::ESTIMATE => 1,
        FinanceLoanDocumentCatalog::CONTRACT => 1,
        FinanceLoanDocumentCatalog::SETTLEMENT_SHEET => 1,
        FinanceLoanDocumentCatalog::EVIDENCE_MEMO => 1,
        FinanceLoanDocumentCatalog::PAYMENT_MEMO => 1,
        FinanceLoanDocumentCatalog::FOLLOWUP_MEMO => 1,
    ];

    /**
     * เลขรุ่นของแม่แบบ เก็บไว้ใน data_json ของเอกสาร ไม่ฝังไว้ในเนื้อหา
     *
     * เคยลองฝังเป็น HTML comment แล้วพบว่า Doc::beforeSave กรองเนื้อหาผ่าน
     * HtmlPurifier ทุกครั้ง ซึ่งลบทั้ง comment และ div ที่ไม่ได้อยู่ใน ALLOWED_HTML
     * ผลคือเครื่องหมายรุ่นหายตั้งแต่บันทึกครั้งแรก แล้วการเปิดเอกสารครั้งถัดไป
     * จะเข้าใจว่าเป็นแม่แบบรุ่นเก่าและสร้างใหม่ทับงานที่ผู้ใช้แก้ไว้ทั้งฉบับ
     */
    public static function version(string $code): int
    {
        return self::VERSIONS[$code] ?? 1;
    }

    public static function build(string $code, FinanceLoan $loan): string
    {
        $d = self::data($loan);
        return match ($code) {
            FinanceLoanDocumentCatalog::ESTIMATE => self::estimate($d),
            FinanceLoanDocumentCatalog::CONTRACT => self::contract($d),
            FinanceLoanDocumentCatalog::SETTLEMENT_SHEET => self::settlementSheet($d),
            FinanceLoanDocumentCatalog::EVIDENCE_MEMO => self::evidenceMemo($d),
            FinanceLoanDocumentCatalog::PAYMENT_MEMO => self::paymentMemo($d),
            default => '<p>ยังไม่มีแม่แบบของเอกสารชนิดนี้</p>',
        };
    }

    // ── รวบรวมข้อมูลที่ทุกฉบับใช้ร่วมกัน ────────────────────────────

    private static function data(FinanceLoan $loan): array
    {
        $site = SiteHelper::getInfo();
        $totals = $loan->registerTotals();

        return [
            'loan' => $loan,
            'organization' => (string) ($site['company_name'] ?? ''),
            'province' => trim((string) ($site['province'] ?? '')),
            'doc_number' => trim((string) ($site['doc_number'] ?? '')),
            'director' => self::person($site['director'] ?? null),
            'admin_head' => self::person(self::adminHead($site)),
            'contract_no' => (string) $loan->contract_no,
            'borrower' => (string) $loan->borrower_name,
            'position' => (string) $loan->borrower_position,
            'purpose' => (string) $loan->purpose,
            'account' => $loan->account ? $loan->account->displayName() : '',
            'expense_type' => (string) ($loan->expenseType->name ?? ''),
            'total' => (float) $loan->approved_amount,
            'voucher' => (float) $loan->voucher_amount,
            'cash' => (float) $loan->cash_return_amount,
            'outstanding' => (float) $loan->outstanding_amount,
            'totals' => $totals,
            'items' => $loan->items,
            'settlements' => $loan->settlements,
            'borrowed_at' => self::thaiDate($loan->borrowed_at),
            'received_at' => self::thaiDate($loan->received_at),
            'due_at' => self::thaiDate($loan->due_at),
            'activity_start' => self::thaiDate($loan->activity_start_at),
            'activity_end' => self::thaiDate($loan->activity_end_at),
            'request_no' => (string) $loan->request_document_no,
            'request_date' => self::thaiDate($loan->request_document_date),
            'days' => self::activityDays($loan),
            'today' => self::thaiDate(date('Y-m-d')),
        ];
    }

    private static function activityDays(FinanceLoan $loan): string
    {
        if (!$loan->activity_start_at || !$loan->activity_end_at) {
            return '';
        }
        $days = (int) floor((strtotime($loan->activity_end_at) - strtotime($loan->activity_start_at)) / 86400) + 1;
        return $days > 0 ? (string) $days : '';
    }

    /** หัวหน้าฝ่ายบริหารงานทั่วไป ผู้ลงนามช่องความเห็นก่อนเสนอผู้อำนวยการ */
    private static function adminHead(array $site)
    {
        $unit = Organization::find()->where(['like', 'name', 'บริหารทั่วไป'])->one();
        if ($unit && $unit->leader) {
            return $unit->leader;
        }
        $id = $site['leader'] ?? null;
        return $id ? Employees::findOne($id) : null;
    }

    private static function person($employee): array
    {
        if (!$employee) {
            return ['name' => '', 'position' => ''];
        }
        return [
            'name' => method_exists($employee, 'fullname') ? (string) $employee->fullname() : '',
            'position' => method_exists($employee, 'positionName') ? (string) ($employee->positionName() ?: '') : '',
        ];
    }

    // ── 1) ใบประมาณการค่าใช้จ่ายในการเดินทางไปราชการ ──────────────

    private static function estimate(array $d): string
    {
        $e = fn($v) => self::escape($v);
        $rows = '';
        $no = 0;
        foreach ($d['items'] as $item) {
            $no++;
            $calculation = $item->calculationText();
            $rows .= '<tr>'
                . '<td class="d-loan-c-no">' . $no . '</td>'
                . '<td>' . $e($item->displayName()) . '</td>'
                . '<td>' . ($calculation !== '' ? $e($calculation) : self::dots(24)) . '</td>'
                . '<td class="d-loan-c-money">' . number_format($item->amount, 2) . '</td>'
                . '</tr>';
        }
        if ($rows === '') {
            // ใบประมาณการที่ไม่มีบรรทัดเลยยังต้องพิมพ์ได้ ผู้ใช้จะกรอกบนกระดาษเอง
            for ($i = 1; $i <= 5; $i++) {
                $rows .= '<tr><td class="d-loan-c-no">' . $i . '</td><td>&nbsp;</td><td>&nbsp;</td><td class="d-loan-c-money">&nbsp;</td></tr>';
            }
        }

        return '<p class="d-loan-title">ประมาณการค่าใช้จ่ายในการเดินทางไปราชการ</p>'
            . '<p class="d-loan-sub">(แนบพร้อมบันทึกข้อความขออนุญาตเดินทางไปราชการ)</p>'
            . '<table class="d-loan-fields"><tbody>'
            . '<tr><td class="d-loan-lbl">ข้าพเจ้า</td><td class="d-loan-fill">' . self::valueOrDots($d['borrower'], 40) . '</td>'
            . '<td class="d-loan-lbl">ตำแหน่ง</td><td class="d-loan-fill">' . self::valueOrDots($d['position'], 30) . '</td></tr>'
            . '<tr><td class="d-loan-lbl">สังกัด</td><td class="d-loan-fill">' . self::valueOrDots($d['organization'], 40) . '</td>'
            . '<td class="d-loan-lbl">จังหวัด</td><td class="d-loan-fill">' . self::valueOrDots($d['province'], 20) . '</td></tr>'
            . '</tbody></table>'
            . '<p class="d-loan-line">พร้อมด้วยผู้มีรายชื่อในเอกสารนี้ รวมทั้งสิ้น ' . self::dots(8) . ' คน ประสงค์ขออนุมัติเดินทางไปราชการ</p>'
            . '<p class="d-loan-line">เรื่อง ' . self::valueOrDots($d['purpose'], 70) . '</p>'
            . '<p class="d-loan-line">มีกำหนด ' . self::valueOrDots($d['days'], 6) . ' วัน โดยออกเดินทางตั้งแต่วันที่ '
            . self::valueOrDots($d['activity_start'], 18) . ' กลับวันที่ ' . self::valueOrDots($d['activity_end'], 18) . '</p>'
            . '<p class="d-loan-line">โดยใช้งบประมาณจาก ' . self::valueOrDots($d['account'], 46) . '</p>'
            . '<p class="d-loan-line">ซึ่งมีค่าใช้จ่ายดังรายละเอียดต่อไปนี้</p>'
            . '<table class="d-items d-loan-grid"><thead><tr>'
            . '<th class="d-loan-c-no">ที่</th><th>รายการ</th><th>วิธีคิด</th><th class="d-loan-c-money">เป็นเงิน (บาท)</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody>'
            . '<tfoot><tr class="d-loan-total"><td colspan="3" style="text-align:right">รวมค่าใช้จ่ายทั้งสิ้น (ประมาณ)</td>'
            . '<td class="d-loan-c-money">' . number_format($d['total'], 2) . '</td></tr></tfoot></table>'
            . '<p class="d-loan-line">( ' . $e(self::bahtText($d['total'])) . ' )</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(28) . ' ผู้ขอ<br>' . self::nameLine($d['borrower'], 30)
            . '<br>ตำแหน่ง ' . self::valueOrDots($d['position'], 26) . '</td>'
            . '</tr></tbody></table>';
    }

    // ── 2) สัญญายืมเงิน แบบ 8500 หน้า 1 ───────────────────────────

    private static function contract(array $d): string
    {
        $e = fn($v) => self::escape($v);
        $details = [];
        foreach ($d['items'] as $item) {
            $calculation = $item->calculationText();
            $details[] = $item->displayName() . ($calculation !== '' ? ' ' . $calculation : '')
                . ' เป็นเงิน ' . number_format($item->amount, 2) . ' บาท';
        }
        $detailText = $details ? implode(' · ', $details) : '';

        return '<p class="d-loan-form">แบบ 8500</p>'
            . '<p class="d-loan-title">สัญญายืมเงิน</p>'
            . '<table class="d-loan-fields"><tbody>'
            . '<tr><td class="d-loan-lbl">เลขที่</td><td class="d-loan-fill">' . self::valueOrDots($d['contract_no'], 22) . '</td>'
            . '<td class="d-loan-lbl">วันครบกำหนด</td><td class="d-loan-fill">' . self::valueOrDots($d['due_at'], 22) . '</td></tr>'
            . '<tr><td class="d-loan-lbl">ข้าพเจ้า</td><td class="d-loan-fill">' . self::valueOrDots($d['borrower'], 40) . '</td>'
            . '<td class="d-loan-lbl">ตำแหน่ง</td><td class="d-loan-fill">' . self::valueOrDots($d['position'], 30) . '</td></tr>'
            . '<tr><td class="d-loan-lbl">สังกัด</td><td class="d-loan-fill">' . self::valueOrDots($d['organization'], 40) . '</td>'
            . '<td class="d-loan-lbl">จังหวัด</td><td class="d-loan-fill">' . self::valueOrDots($d['province'], 20) . '</td></tr>'
            . '</tbody></table>'
            . '<p class="d-loan-line">มีความประสงค์ขอยืมเงินจาก ' . self::valueOrDots($d['account'] ?: $d['organization'], 56) . '</p>'
            . '<p class="d-loan-line">เพื่อใช้เป็นค่าใช้จ่ายใน ' . self::valueOrDots($d['purpose'], 60) . '</p>'
            . '<p class="d-loan-line">' . ($detailText !== '' ? $e($detailText) : self::dots(96)) . '</p>'
            . '<p class="d-loan-line">จำนวนเงิน ' . number_format($d['total'], 2) . ' บาท ( ' . $e(self::bahtText($d['total'])) . ' )</p>'
            . '<p class="d-loan-line">ข้าพเจ้าสัญญาว่าจะปฏิบัติตามระเบียบของทางราชการทุกประการ และจะนำใบสำคัญคู่จ่ายที่ถูกต้อง '
            . 'พร้อมทั้งเงินเหลือจ่าย (ถ้ามี) ส่งใช้ภายในกำหนดไว้ในระเบียบการเบิกจ่ายเงินจากคลัง คือภายในวันที่ '
            . self::valueOrDots($d['due_at'], 20) . ' ถ้าข้าพเจ้าไม่ส่งตามกำหนด ข้าพเจ้ายินยอมให้หักเงินเดือน ค่าจ้าง เบี้ยหวัด '
            . 'บำเหน็จ บำนาญ หรือเงินอื่นใดที่ข้าพเจ้าพึงได้รับจากทางราชการ ชดใช้จำนวนเงินที่ยืมไปจนครบถ้วนได้ทันที</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(26) . ' ผู้ยืม<br>' . self::nameLine($d['borrower'], 30)
            . '<br>วันที่ ' . self::valueOrDots($d['borrowed_at'], 20) . '</td>'
            . '</tr></tbody></table>'
            . self::approvalBlock($d);
    }

    /** สามช่องท้ายสัญญา — ผู้ตรวจสอบ ผู้อนุมัติ และใบรับเงิน */
    private static function approvalBlock(array $d): string
    {
        $words = self::escape(self::bahtText($d['total']));
        $amount = number_format($d['total'], 2);

        return '<p class="d-loan-line"><strong>เสนอ</strong> ผู้อำนวยการ' . self::escape($d['organization']) . '</p>'
            . '<p class="d-loan-line">ได้ตรวจสอบแล้ว เห็นสมควรให้ยืมตามใบยืมฉบับนี้ได้ จำนวน ' . $amount . ' บาท ( ' . $words . ' )</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(26) . ' ผู้ตรวจสอบ<br>' . self::nameLine($d['admin_head']['name'], 30)
            . '<br>วันที่ ' . self::dots(20) . '</td>'
            . '</tr></tbody></table>'
            . '<p class="d-loan-line"><strong>คำอนุมัติ</strong></p>'
            . '<p class="d-loan-line">อนุมัติให้ยืมตามเงื่อนไขข้างต้นได้ เป็นเงิน ' . $amount . ' บาท ( ' . $words . ' )</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(26) . ' ผู้อนุมัติ<br>' . self::nameLine($d['director']['name'], 30)
            . '<br>ตำแหน่ง ผู้อำนวยการ' . self::escape($d['organization']) . '<br>วันที่ ' . self::dots(20) . '</td>'
            . '</tr></tbody></table>'
            . '<p class="d-loan-line"><strong>ใบรับเงิน</strong></p>'
            . '<p class="d-loan-line">ได้รับเงินยืมจำนวน ' . $amount . ' บาท ( ' . $words . ' ) ไปเป็นการถูกต้องแล้ว</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(26) . ' ผู้รับเงิน<br>' . self::nameLine($d['borrower'], 30)
            . '<br>วันที่ ' . self::valueOrDots($d['received_at'], 20) . '</td>'
            . '</tr></tbody></table>';
    }

    // ── 3) รายการส่งใช้เงินยืม หน้า 2 ─────────────────────────────

    private static function settlementSheet(array $d): string
    {
        $rows = '';
        foreach ($d['settlements'] as $settlement) {
            $kind = [];
            if ((float) $settlement->voucher_amount > 0) {
                $kind[] = 'ใบสำคัญ';
            }
            if ((float) $settlement->cash_amount > 0) {
                $kind[] = 'เงินสด';
            }
            $rows .= '<tr>'
                . '<td class="d-loan-c-no">' . (int) $settlement->seq . '</td>'
                . '<td class="d-loan-c-date">' . self::escape(self::thaiDate($settlement->settled_at)) . '</td>'
                . '<td>' . self::escape(implode(' / ', $kind)) . '</td>'
                . '<td class="d-loan-c-money">' . number_format($settlement->totalAmount(), 2) . '</td>'
                . '<td class="d-loan-c-money">' . number_format($settlement->balance_after, 2) . '</td>'
                . '<td class="d-loan-c-sign">&nbsp;</td>'
                . '<td class="d-loan-c-date">' . self::escape($settlement->receipt_no ?: '') . '</td>'
                . '</tr>';
        }
        // เผื่อบรรทัดว่างให้เขียนมือ ตามแบบฟอร์มจริงที่มีสามช่องเสมอแม้ยังไม่ได้ส่งใช้
        for ($i = count($d['settlements']); $i < 3; $i++) {
            $rows .= '<tr><td class="d-loan-c-no">' . ($i + 1) . '</td><td>&nbsp;</td><td>&nbsp;</td>'
                . '<td class="d-loan-c-money">&nbsp;</td><td class="d-loan-c-money">&nbsp;</td>'
                . '<td class="d-loan-c-sign">&nbsp;</td><td>&nbsp;</td></tr>';
        }

        return '<p class="d-loan-form">หน้า 2</p>'
            . '<p class="d-loan-title">รายการส่งใช้เงินยืม</p>'
            . '<table class="d-loan-fields"><tbody><tr>'
            . '<td class="d-loan-lbl">สัญญายืมเงินเลขที่</td><td class="d-loan-fill">' . self::valueOrDots($d['contract_no'], 20) . '</td>'
            . '<td class="d-loan-lbl">ผู้ยืม</td><td class="d-loan-fill">' . self::valueOrDots($d['borrower'], 34) . '</td>'
            . '</tr></tbody></table>'
            . '<table class="d-items d-loan-grid"><thead><tr>'
            . '<th class="d-loan-c-no">ครั้งที่</th><th class="d-loan-c-date">วัน เดือน ปี</th>'
            . '<th>เงินสด หรือใบสำคัญ</th><th class="d-loan-c-money">จำนวนเงิน</th>'
            . '<th class="d-loan-c-money">คงค้าง</th><th class="d-loan-c-sign">ลายมือชื่อผู้รับ</th><th class="d-loan-c-date">ใบรับเลขที่</th>'
            . '</tr></thead><tbody>' . $rows . '</tbody>'
            . '<tfoot><tr class="d-loan-total"><td colspan="3" style="text-align:right">รวมส่งใช้แล้ว</td>'
            . '<td class="d-loan-c-money">' . number_format($d['voucher'] + $d['cash'], 2) . '</td>'
            . '<td class="d-loan-c-money">' . number_format($d['outstanding'], 2) . '</td>'
            . '<td colspan="2">&nbsp;</td></tr></tfoot></table>'
            . '<div class="d-loan-note">'
            . '<p>(1) ยื่นต่อ ผู้อำนวยการกองคลัง หัวหน้ากองคลัง หัวหน้าแผนกคลัง หรือตำแหน่งอื่นใดที่ปฏิบัติงานเช่นเดียวกันแล้วแต่กรณี</p>'
            . '<p>(2) ให้ระบุชื่อส่วนราชการที่จ่ายเงินยืม</p>'
            . '<p>(3) ยืมเพื่อเป็นค่าใช้จ่ายในการเดินทางไปราชการ ให้ส่งใช้ภายใน 15 วันนับแต่วันกลับมาถึง '
            . 'กรณีอื่นให้ส่งใช้ภายใน 30 วันนับแต่วันที่ได้รับเงิน</p>'
            . '</div>';
    }

    // ── 4) บันทึกนำส่งหลักฐานใบสำคัญชดใช้เงินยืม ──────────────────

    private static function evidenceMemo(array $d): string
    {
        $e = fn($v) => self::escape($v);
        $box = fn(bool $checked) => $checked ? '[&#10003;]' : '[&nbsp;&nbsp;]';
        $spent = $d['voucher'] + $d['cash'];
        $hasCash = $d['cash'] > 0;
        $lowVoucher = $d['total'] > 0 && ($d['voucher'] / $d['total']) < 0.7;

        $checks = '<table class="d-loan-checks"><tbody>'
            . '<tr><td class="d-loan-box">' . $box($spent > 0) . '</td><td>ค่าใช้จ่ายที่จ่ายไปทั้งสิ้น จำนวน '
            . number_format($spent, 2) . ' บาท</td></tr>'
            . '<tr><td class="d-loan-box">' . $box($hasCash) . '</td><td>มีเงินเหลือจ่ายส่งคืนไปแล้ว จำนวน '
            . number_format($d['cash'], 2) . ' บาท</td></tr>'
            . '<tr><td class="d-loan-box">' . $box(false) . '</td><td>ขอเบิกเพิ่ม จำนวน ' . self::dots(14) . ' บาท</td></tr>'
            . '<tr><td class="d-loan-box">' . $box(!$hasCash && $spent > 0) . '</td><td>ไม่มีเงินเหลือจ่าย</td></tr>'
            . '<tr><td class="d-loan-box">' . $box(false) . '</td><td>ส่งชดใช้เงินยืมล่าช้า เนื่องจาก ' . self::dots(44) . '</td></tr>'
            . '<tr><td class="d-loan-box">' . $box($lowVoucher) . '</td><td>ส่งหลักฐานใบสำคัญต่ำกว่า 70% เนื่องจาก ' . self::dots(36) . '</td></tr>'
            . '</tbody></table>';

        $receipt = null;
        foreach ($d['settlements'] as $settlement) {
            if ($settlement->receipt_book_no || $settlement->receipt_number) {
                $receipt = $settlement;
            }
        }

        return self::memoHead($d, 'นำส่งหลักฐานใบสำคัญชดใช้เงินยืมตามสัญญาเงินยืมเลขที่ ' . $d['contract_no'])
            . '<p class="d-body">ตามบันทึกข้อความที่ ' . self::valueOrDots($d['request_no'], 22)
            . ' ลงวันที่ ' . self::valueOrDots($d['request_date'], 18) . ' ได้ดำเนินโครงการ '
            . self::valueOrDots($d['purpose'], 50) . ' และยืมเงินตามสัญญาเงินยืมเลขที่ '
            . self::valueOrDots($d['contract_no'], 18) . ' วงเงินตามสัญญาจำนวน ' . number_format($d['total'], 2)
            . ' บาท ( ' . $e(self::bahtText($d['total'])) . ' ) เพื่อสำรองจ่าย ' . self::valueOrDots($d['purpose'], 40) . ' นั้น</p>'
            . '<p class="d-body">บัดนี้ การดำเนินงานโครงการดังกล่าวเสร็จสิ้นแล้ว ข้าพเจ้า '
            . self::valueOrDots($d['borrower'], 34) . ' จึงขอนำส่งหลักฐานเพื่อชดใช้เงินยืม โดยมีรายละเอียดดังนี้</p>'
            . $checks
            . '<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณา</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(26) . '<br>' . self::nameLine($d['borrower'], 30)
            . '<br>ตำแหน่ง ' . self::valueOrDots($d['position'], 26) . '</td>'
            . '</tr></tbody></table>'
            . '<table class="d-loan-review"><tbody><tr>'
            . '<td><strong>งานการเงินตรวจสอบ</strong><br>'
            . $box(true) . ' เอกสารถูกต้อง ครบถ้วน<br>'
            . $box(false) . ' เอกสารไม่ถูกต้อง ครบถ้วน<br><br>'
            . '<strong>หลักฐานทางการเงิน</strong><br>'
            . $box((bool) $receipt) . ' ใบเสร็จรับเงิน เล่มที่ ' . self::valueOrDots($receipt->receipt_book_no ?? '', 10)
            . ' เลขที่ ' . self::valueOrDots($receipt->receipt_number ?? '', 10) . '<br>'
            . 'จำนวนเงิน ' . number_format($spent, 2) . ' บาท<br><br>'
            . 'ลงชื่อ ' . self::dots(22) . ' (เจ้าหน้าที่การเงิน)</td>'
            . '<td><strong>ความเห็นหัวหน้ากลุ่มงานบริหาร</strong><br>'
            . 'เรียน ผู้อำนวยการ' . $e($d['organization']) . '<br>- เพื่อโปรดทราบ<br><br>'
            . 'ลงชื่อ ' . self::dots(22) . '<br>' . self::nameLine($d['admin_head']['name'], 26) . '<br><br>'
            . '<strong>ความเห็นผู้อำนวยการ</strong><br>'
            . $box(false) . ' อนุมัติ &nbsp;&nbsp; ' . $box(false) . ' ไม่อนุมัติ<br><br>'
            . 'ลงชื่อ ' . self::dots(22) . '<br>' . self::nameLine($d['director']['name'], 26) . '</td>'
            . '</tr></tbody></table>';
    }

    // ── 5) บันทึกขออนุมัติจ่ายเงินบำรุง ──────────────────────────

    private static function paymentMemo(array $d): string
    {
        $e = fn($v) => self::escape($v);

        return self::memoHead($d, 'ขออนุมัติจ่ายเงินบำรุง')
            . '<p class="d-body">ตามบันทึกข้อความที่ ' . self::valueOrDots($d['request_no'], 22)
            . ' ลงวันที่ ' . self::valueOrDots($d['request_date'], 18) . ' ได้รับอนุมัติให้ยืมเงินตามสัญญายืมเงินเลขที่ '
            . self::valueOrDots($d['contract_no'], 18) . ' ของ ' . self::valueOrDots($d['borrower'], 30)
            . ' เพื่อสำรองจ่าย ' . self::valueOrDots($d['purpose'], 46) . ' นั้น</p>'
            . '<p class="d-body">งานการเงินได้ตรวจสอบแล้ว เห็นควรอนุมัติจ่ายเงินบำรุงตามสัญญายืมเงินฉบับดังกล่าว '
            . 'เป็นเงิน ' . number_format($d['total'], 2) . ' บาท ( ' . $e(self::bahtText($d['total'])) . ' ) '
            . 'โดยจ่ายจากบัญชี ' . self::valueOrDots($d['account'], 36) . '</p>'
            . '<p class="d-body">จึงเรียนมาเพื่อโปรดพิจารณาอนุมัติ</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(26) . '<br>' . self::nameLine($d['admin_head']['name'], 30)
            . '<br>ตำแหน่ง ' . self::valueOrDots($d['admin_head']['position'], 26) . '</td>'
            . '</tr></tbody></table>'
            . '<table class="d-loan-review"><tbody><tr>'
            . '<td><strong>ความเห็นผู้อำนวยการ</strong><br>'
            . '[&nbsp;&nbsp;] อนุมัติ &nbsp;&nbsp; [&nbsp;&nbsp;] ไม่อนุมัติ</td>'
            . '<td style="text-align:center">ลงชื่อ ' . self::dots(22) . '<br>' . self::nameLine($d['director']['name'], 26)
            . '<br>ผู้อำนวยการ' . $e($d['organization']) . '</td>'
            . '</tr></tbody></table>';
    }

    // ── 6) บันทึกขอติดตามลูกหนี้เงินยืม ครั้งที่ N ────────────────

    /**
     * หนังสือทวงถามหนึ่งฉบับ ผูกกับรายการติดตามที่ออกไว้
     *
     * รับ followup มาเป็นพารามิเตอร์แทนที่จะไปหยิบฉบับล่าสุดเอง เพราะใบยืมหนึ่งใบ
     * ออกหนังสือได้หลายฉบับ และแต่ละฉบับต้องพิมพ์ซ้ำได้ตามเลขครั้งที่ของตัวเอง
     */
    public static function buildLetter(FinanceLoan $loan, FinanceLoanFollowup $letter): string
    {
        $d = self::data($loan);
        $e = fn($v) => self::escape($v);
        $subject = 'ขอติดตามลูกหนี้เงินยืม สัญญายืมเงินที่ ' . $loan->contract_no . ' ครั้งที่ ' . (int) $letter->letter_seq;

        return self::memoHead($d, $subject, (string) $letter->letter_no, self::thaiDate($letter->letter_date))
            . '<p class="d-body">ตามบันทึกข้อความที่ ' . self::valueOrDots($d['request_no'], 22)
            . ' ลงวันที่ ' . self::valueOrDots($d['request_date'], 18) . ' ได้รับอนุมัติให้ยืมเงินตามสัญญายืมเงินเลขที่ '
            . self::valueOrDots($d['contract_no'], 18) . ' วงเงินตามสัญญาจำนวน ' . number_format($d['total'], 2)
            . ' บาท ( ' . $e(self::bahtText($d['total'])) . ' ) เพื่อสำรองจ่าย ' . self::valueOrDots($d['purpose'], 44)
            . ' ของ ' . self::valueOrDots($d['borrower'], 30) . ' นั้น</p>'
            . '<p class="d-body">งานการเงินได้ตรวจสอบแล้วพบว่า ' . self::valueOrDots($d['borrower'], 30)
            . ' ยังมิได้ส่งใช้เงินยืมให้ครบถ้วนตามกำหนด โดยครบกำหนดส่งใช้เมื่อวันที่ '
            . self::valueOrDots($d['due_at'], 18) . ' และคงเหลือที่ต้องส่งใช้อีกจำนวน '
            . number_format($d['outstanding'], 2) . ' บาท ( ' . $e(self::bahtText($d['outstanding'])) . ' )'
            . ($loan->daysOverdue() > 0 ? ' เกินกำหนดมาแล้ว ' . $loan->daysOverdue() . ' วัน' : '') . '</p>'
            . '<p class="d-body">จึงขอให้เร่งดำเนินการโดยด่วน ให้แล้วเสร็จภายในวันที่ '
            . self::valueOrDots(self::thaiDate($letter->new_due_at), 20)
            . ' หากพ้นกำหนดระยะเวลาดังกล่าว ทางงานการเงินจะดำเนินการตามระเบียบฯ ต่อไป</p>'
            . '<p class="d-body">จึงเรียนมาเพื่อโปรดทราบ และโปรดพิจารณา</p>'
            . '<table class="d-sign"><tbody><tr>'
            . '<td class="d-sign-cell">&nbsp;</td>'
            . '<td class="d-sign-cell">ลงชื่อ ' . self::dots(26) . '<br>' . self::nameLine($d['admin_head']['name'], 30)
            . '<br>ตำแหน่ง ' . self::valueOrDots($d['admin_head']['position'], 26) . '</td>'
            . '</tr></tbody></table>'
            . '<table class="d-loan-review"><tbody><tr>'
            . '<td><strong>ความเห็นผู้อำนวยการ</strong><br>[&nbsp;&nbsp;] ทราบ &nbsp;&nbsp; [&nbsp;&nbsp;] อื่น ๆ</td>'
            . '<td style="text-align:center">ลงชื่อ ' . self::dots(22) . '<br>' . self::nameLine($d['director']['name'], 26)
            . '<br>ผู้อำนวยการ' . $e($d['organization']) . '</td>'
            . '</tr></tbody></table>';
    }

    // ── ส่วนหัวบันทึกข้อความ ที่ใช้ร่วมกันสองฉบับ ────────────────

    private static function memoHead(array $d, string $subject, string $docNo = '', string $docDate = ''): string
    {
        return '<p class="d-title">บันทึกข้อความ</p>'
            . '<table class="d-memo-fields"><tbody>'
            . '<tr><td class="d-memo-label">ส่วนราชการ</td><td class="d-memo-value" colspan="3">'
            . self::valueOrDots(trim($d['organization'] . ' ' . ($d['province'] !== '' ? 'จ.' . $d['province'] : '')), 60) . '</td></tr>'
            . '<tr><td class="d-memo-label">ที่</td><td class="d-memo-value">' . self::valueOrDots($docNo ?: $d['doc_number'], 24) . '</td>'
            . '<td class="d-memo-label d-memo-date-label">วันที่</td><td class="d-memo-value">' . self::valueOrDots($docDate, 24) . '</td></tr>'
            . '<tr><td class="d-memo-label">เรื่อง</td><td class="d-memo-value" colspan="3">' . self::escape($subject) . '</td></tr>'
            . '</tbody></table>'
            . '<p class="d-to"><strong>เรียน</strong> ผู้อำนวยการ' . self::escape($d['organization']) . '</p>';
    }

    // ── ตัวช่วย ──────────────────────────────────────────────────

    private static function dots(int $length): string
    {
        return str_repeat('.', max(1, $length));
    }

    private static function valueOrDots($value, int $length): string
    {
        $value = trim((string) $value);
        return $value !== '' ? self::escape($value) : self::dots($length);
    }

    private static function nameLine(string $name, int $length): string
    {
        return '(' . ($name !== '' ? self::escape($name) : self::dots($length)) . ')';
    }

    private static function thaiDate(?string $date): string
    {
        return $date ? (string) ThaiDateHelper::formatThaiDate($date, 'short') : '';
    }

    /** จำนวนเงินเป็นตัวอักษรพร้อมหน่วย รองรับเศษสตางค์ */
    private static function bahtText(float $amount): string
    {
        if ($amount <= 0) {
            return self::dots(30);
        }
        $baht = (int) floor($amount);
        $satang = (int) round(($amount - $baht) * 100);
        if ($satang >= 100) {
            $baht += 1;
            $satang = 0;
        }
        $text = ($baht > 0 ? AppHelper::convertNumberToWords($baht) : 'ศูนย์') . 'บาท';
        return $satang > 0 ? $text . AppHelper::convertNumberToWords($satang) . 'สตางค์' : $text . 'ถ้วน';
    }

    private static function escape($value): string
    {
        return Html::encode((string) $value);
    }
}
