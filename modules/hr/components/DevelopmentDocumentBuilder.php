<?php

namespace app\modules\hr\components;

use yii\helpers\Html;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Development;

/** สร้างเนื้อหาเริ่มต้นจากทะเบียน ก่อนเก็บเป็น snapshot ที่ผู้ใช้แก้ไขได้ */
final class DevelopmentDocumentBuilder
{
    public static function build(string $code, Development $model): string
    {
        $data = self::payload($model);

        switch ($code) {
            case 'travel_expense_8708_part_1':
                return self::partOne($data);
            case 'travel_expense_8708_part_2':
                return self::partTwo($data);
            case 'travel_expense_bk_111':
                return self::bk111($data);
            default:
                throw new \InvalidArgumentException('ไม่พบแม่แบบเอกสารที่เลือก');
        }
    }

    private static function payload(Development $model): array
    {
        $json = is_array($model->data_json) ? $model->data_json : [];
        $employee = $model->createdByEmp;
        $expenses = $model->estimatedCostAmounts(true);
        $members = $model->memberText();
        $site = SiteHelper::getInfo();
        $configuredDocNumber = trim((string) ($site['doc_number'] ?? ''));

        $fullname = $employee
            ? (method_exists($employee, 'fullname') ? $employee->fullname() : trim($employee->fname . ' ' . $employee->lname))
            : '';
        $position = $employee && method_exists($employee, 'positionName')
            ? (string) ($employee->positionName() ?: '')
            : '';

        return [
            'organization' => (string) ($site['company_name'] ?? ''),
            'doc_number' => $configuredDocNumber === '' ? '' : rtrim($configuredDocNumber, '/') . '/',
            'fullname' => $fullname,
            'position' => $position,
            'department' => (string) ($json['department_name'] ?? ''),
            'topic' => (string) ($model->topic ?? ''),
            'location' => trim((string) ($json['location'] ?? '') . ' ' . (string) ($json['province_name'] ?? '')),
            'reference' => $model->document ? trim((string) ($model->document->doc_number ?? '') . ' ' . (string) ($model->document->topic ?? '')) : '',
            'reference_number' => $model->document ? (string) ($model->document->doc_number ?? '') : '',
            'reference_date' => $model->document ? self::thaiDate($model->document->doc_date ?? null) : '',
            'loan_number' => (string) ($json['loan_contract_number'] ?? ''),
            'loan_date' => self::thaiDate($json['loan_contract_date'] ?? null),
            'loan_amount' => (float) ($json['loan_amount'] ?? 0),
            'date_start' => self::thaiDate($model->vehicle_date_start ?: $model->date_start),
            'date_end' => self::thaiDate($model->vehicle_date_end ?: $model->date_end),
            'time_start' => (string) ($json['vehicle_time_start'] ?? $model->time_start ?? ''),
            'time_end' => (string) ($json['vehicle_time_end'] ?? $model->time_end ?? ''),
            'members_text' => (string) ($members['text'] ?? ''),
            'allowance' => (float) ($expenses['allowance_amount'] ?? 0),
            'accommodation' => (float) ($expenses['accommodation_amount'] ?? 0),
            'vehicle' => (float) ($expenses['vehicle_amount'] ?? 0),
            'other' => (float) ($expenses['other_amount'] ?? 0) + (float) ($expenses['registration_amount'] ?? 0),
            'total' => (float) $model->totalEstimatedCost(true),
        ];
    }

    private static function partOne(array $d): string
    {
        $e = [self::class, 'escape'];
        $loanNumber = $d['loan_number'] !== '' ? $e($d['loan_number']) : '........................................................';
        $loanDate = $d['loan_date'] !== '' ? $e($d['loan_date']) : '........................................................';
        $loanAmount = $d['loan_amount'] > 0 ? self::money($d['loan_amount']) : '................................';
        $referenceNumber = $d['reference_number'] !== '' ? $e($d['reference_number']) : '........................................................';
        $referenceDate = $d['reference_date'] !== '' ? $e($d['reference_date']) : '........................................................';
        $members = $d['members_text'] !== '' ? $e($d['members_text']) : '................................................................................';
        $moneyWords = $d['total'] > 0 ? AppHelper::convertNumberToWords((int) round($d['total'])) . 'บาทถ้วน' : '........................................................';

        return '<div class="d-form-version d-8708-part1-v8"></div><div class="d-8708-form"><section class="d-doc-page">'
            . '<table class="d-8708-loan"><tr><td>สัญญาเงินยืมเลขที่ ' . $loanNumber . '</td><td>วันที่ ' . $loanDate . '</td><td class="d-right"><strong>ส่วนที่&nbsp;1<br>แบบ&nbsp;8708</strong></td></tr>'
            . '<tr><td>ชื่อผู้ยืม ' . $e($d['fullname']) . '</td><td>จำนวนเงิน ' . $loanAmount . ' บาท</td><td></td></tr></table>'
            . '<p class="d-8708-title">ใบเบิกค่าใช้จ่ายในการเดินทางไปราชการ</p>'
            . '<table class="d-8708-office"><tr><td></td><td><strong>ที่ทำการ</strong> ' . $e($d['organization']) . '</td></tr>'
            . '<tr><td></td><td><strong>วันที่</strong> ' . $e(self::thaiDate(date('Y-m-d'))) . '</td></tr></table>'
            . '<p class="d-8708-line"><strong>เรื่อง</strong> ขออนุมัติเบิกค่าใช้จ่ายในการเดินทางไปราชการ</p>'
            . '<p class="d-8708-line"><strong>เรียน</strong> ............................................................</p>'
            . '<p class="d-8708-indent">ตามคำสั่ง/บันทึกที่ ' . $referenceNumber . ' ลงวันที่ ' . $referenceDate . ' ได้อนุมัติให้ข้าพเจ้า</p>'
            . '<p class="d-8708-line">' . $e($d['fullname']) . ' ตำแหน่ง ' . $e($d['position']) . '</p>'
            . '<p class="d-8708-line">สังกัด ' . $e($d['department']) . ' พร้อมด้วย ' . $members . '</p>'
            . '<p class="d-8708-indent">เดินทางไปปฏิบัติราชการเรื่อง ' . $e($d['topic']) . ' ณ ' . $e($d['location']) . '</p>'
            . '<p class="d-8708-line">โดยออกเดินทางจาก ○ บ้านพัก&nbsp;&nbsp; ○ สำนักงาน&nbsp;&nbsp; ○ ประเทศไทย ตั้งแต่วันที่ ' . $e($d['date_start']) . ' เวลา ' . $e($d['time_start']) . ' น.</p>'
            . '<p class="d-8708-line">และกลับถึง ○ บ้านพัก&nbsp;&nbsp; ○ สำนักงาน&nbsp;&nbsp; ○ ประเทศไทย วันที่ ' . $e($d['date_end']) . ' เวลา ' . $e($d['time_end']) . ' น.</p>'
            . '<p class="d-8708-line">รวมเวลาไปราชการครั้งนี้ ................ วัน ................ ชั่วโมง</p>'
            . '<p class="d-8708-indent">ข้าพเจ้าขอเบิกค่าใช้จ่ายในการเดินทางไปราชการสำหรับ ○ ข้าพเจ้า&nbsp;&nbsp; ○ คณะเดินทาง ดังนี้</p>'
            . self::expenseTable($d)
            . '<p class="d-8708-line">จำนวนเงิน (ตัวอักษร) ' . self::escape($moneyWords) . '</p>'
            . '<p class="d-8708-indent">ข้าพเจ้าขอรับรองว่ารายการที่กล่าวมาข้างต้นเป็นความจริง และหลักฐานการจ่ายที่ส่งมาด้วย จำนวน ................ ฉบับ รวมทั้งจำนวนเงินที่ขอเบิกถูกต้องตามกฎหมายทุกประการ</p>'
            . self::signature($d['fullname'], $d['position'], 'ผู้ขอรับเงิน')
            . '</section><p class="d-page-break"><br></p><section class="d-doc-page">'
            . '<table class="d-8708-approval"><tr><td><strong>ได้ตรวจสอบหลักฐานการเบิกจ่ายเงินที่แนบถูกต้องแล้ว<br>เห็นควรอนุมัติให้เบิกจ่ายได้</strong><br><br>ลงชื่อ..................................................................<br>(..................................................................)<br>ตำแหน่ง..............................................................<br>วันที่....................................................................</td>'
            . '<td><strong>อนุมัติให้จ่ายได้</strong><br><br><br>ลงชื่อ..................................................................<br>(..................................................................)<br>ตำแหน่ง..............................................................<br>วันที่....................................................................</td></tr></table>'
            . '<p class="d-8708-received">ได้รับเงินค่าใช้จ่ายในการเดินทางไปราชการจำนวน ' . self::money($d['total']) . ' บาท<br>(' . self::escape($moneyWords) . ') ไว้เป็นการถูกต้องแล้ว</p>'
            . '<table class="d-8708-signatures"><tr><td>ลงชื่อ................................................ ผู้รับเงิน<br>(' . $e($d['fullname']) . ')<br>ตำแหน่ง ' . $e($d['position']) . '<br>วันที่................................................</td>'
            . '<td>ลงชื่อ................................................ ผู้จ่ายเงิน<br>(................................................)<br>ตำแหน่ง................................................<br>วันที่................................................</td></tr></table>'
            . '<p class="d-8708-line">จากเงินยืมตามสัญญาเลขที่ ' . $loanNumber . ' วันที่ ' . $loanDate . '</p>'
            . '<div class="d-8708-notes"><strong>หมายเหตุ</strong><br>........................................................................................................................................................<br>........................................................................................................................................................<br>........................................................................................................................................................<br>........................................................................................................................................................</div></section></div>';
    }

    private static function partTwo(array $d): string
    {
        $rows = '';
        $names = array_filter(array_merge([$d['fullname']], array_map('trim', explode(',', $d['members_text']))));
        foreach (array_values($names) as $index => $name) {
            $rows .= '<tr><td class="d-c-no">' . ($index + 1) . '</td><td>' . self::escape($name) . '</td><td>'
                . ($index === 0 ? self::escape($d['position']) : '') . '</td><td>' . ($index === 0 ? self::money($d['allowance']) : '')
                . '</td><td>' . ($index === 0 ? self::money($d['accommodation']) : '') . '</td><td>'
                . ($index === 0 ? self::money($d['vehicle']) : '') . '</td><td>' . ($index === 0 ? self::money($d['other']) : '')
                . '</td><td>' . ($index === 0 ? self::money($d['total']) : '') . '</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
        }

        return '<p class="d-list d-right"><strong>แบบ 8708 ส่วนที่ 2</strong></p>'
            . '<p class="d-title">หลักฐานการจ่ายเงินค่าใช้จ่ายในการเดินทางไปราชการ</p>'
            . '<p>ชื่อส่วนราชการ ' . self::escape($d['organization']) . ' จังหวัด ........................................................</p>'
            . '<p>ประกอบใบเบิกค่าใช้จ่ายในการเดินทางของ ' . self::escape($d['fullname']) . ' ลงวันที่ ' . self::thaiDate(date('Y-m-d')) . '</p>'
            . '<table class="d-items"><tr class="d-items-head"><td>ลำดับ</td><td>ชื่อ</td><td>ตำแหน่ง</td><td>ค่าเบี้ยเลี้ยง</td><td>ค่าเช่าที่พัก</td><td>ค่าพาหนะ</td><td>ค่าใช้จ่ายอื่น</td><td>รวม</td><td>ลายมือชื่อผู้รับเงิน</td><td>วันที่รับเงิน</td><td>หมายเหตุ</td></tr>'
            . $rows . '<tr><td colspan="3" class="d-c-total">รวมเงิน</td><td>' . self::money($d['allowance']) . '</td><td>'
            . self::money($d['accommodation']) . '</td><td>' . self::money($d['vehicle']) . '</td><td>'
            . self::money($d['other']) . '</td><td>' . self::money($d['total']) . '</td><td colspan="3">&nbsp;</td></tr></table>'
            . '<p class="d-to">จำนวนเงินรวมทั้งสิ้น (ตัวอักษร) .......................................................................................................</p>'
            . self::signature('', '', 'ผู้จ่ายเงิน');
    }

    private static function bk111(array $d): string
    {
        return '<p class="d-list d-right"><strong>แบบ บก.111</strong></p>'
            . '<p class="d-title">ใบรับรองแทนใบเสร็จรับเงิน</p>'
            . '<p class="d-to">ส่วนราชการ ' . self::escape($d['organization']) . '</p>'
            . '<table class="d-items"><tr class="d-items-head"><td>วัน เดือน ปี</td><td>รายละเอียดรายจ่าย</td><td>จำนวนเงิน</td><td>หมายเหตุ</td></tr>'
            . '<tr><td>' . self::escape($d['date_start']) . '</td><td>' . self::escape($d['topic']) . ' ณ ' . self::escape($d['location']) . '</td><td class="d-right">' . self::money($d['total']) . '</td><td>&nbsp;</td></tr>'
            . '<tr><td colspan="2" class="d-c-total">รวมเป็นเงิน</td><td class="d-right"><strong>' . self::money($d['total']) . '</strong></td><td>&nbsp;</td></tr></table>'
            . '<p class="d-to">รวมทั้งสิ้น (ตัวอักษร) ....................................................................................................................</p>'
            . '<p class="d-body">ข้าพเจ้า <strong>' . self::escape($d['fullname']) . '</strong> ตำแหน่ง ' . self::escape($d['position'])
            . ' ขอรับรองว่ารายจ่ายข้างต้นนี้ไม่อาจเรียกใบเสร็จรับเงินจากผู้รับได้ และข้าพเจ้าได้จ่ายไปในงานของราชการโดยแท้</p>'
            . self::signature($d['fullname'], $d['position'], 'ผู้รับรอง');
    }

    private static function expenseTable(array $d): string
    {
        return '<table class="d-8708-expense"><tr><td class="d-8708-expense-name">ค่าเบี้ยเลี้ยงเดินทางประเภท ............</td><td class="d-8708-expense-days">จำนวน ........ วัน</td><td class="d-8708-expense-total">รวม ' . self::money($d['allowance']) . ' บาท</td></tr>'
            . '<tr><td class="d-8708-expense-name">ค่าเช่าที่พักประเภท ............</td><td class="d-8708-expense-days">จำนวน ........ วัน</td><td class="d-8708-expense-total">รวม ' . self::money($d['accommodation']) . ' บาท</td></tr>'
            . '<tr><td colspan="2">ค่าพาหนะ ........................................................................................</td><td class="d-8708-expense-total">รวม ' . self::money($d['vehicle']) . ' บาท</td></tr>'
            . '<tr><td colspan="2">ค่าใช้จ่ายอื่น ๆ ..................................................................................</td><td class="d-8708-expense-total">รวม ' . self::money($d['other']) . ' บาท</td></tr>'
            . '<tr><td colspan="2"></td><td class="d-8708-expense-total"><strong>รวมเงินทั้งสิ้น ' . self::money($d['total']) . ' บาท</strong></td></tr></table>';
    }

    private static function signature(string $name, string $position, string $role): string
    {
        return '<table class="d-sign"><tr><td></td><td class="d-sign-cell">ลงชื่อ........................................................ ' . self::escape($role)
            . '<br>(' . ($name !== '' ? self::escape($name) : '........................................................') . ')'
            . '<br>ตำแหน่ง ' . ($position !== '' ? self::escape($position) : '........................................................') . '</td></tr></table>';
    }

    private static function thaiDate(?string $date): string
    {
        return $date ? (string) ThaiDateHelper::formatThaiDate($date, 'long', 'short') : '';
    }

    private static function money(float $amount): string
    {
        return $amount > 0 ? number_format($amount, 2) : '';
    }

    private static function escape($value): string
    {
        return Html::encode((string) $value);
    }
}
