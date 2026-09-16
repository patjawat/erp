<?php

namespace app\modules\finance\services;

use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\finance\components\BahtText;
use app\modules\finance\models\FinanceCashAccount;
use app\modules\finance\models\FinanceCashTransfer;
use app\modules\finance\models\FinanceCashTxn;
use app\modules\finance\models\FinanceCashVoucher;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * รายงานปิดบัญชี — 2 แบบ ตามต้นฉบับ mophcash
 *   register  : ทะเบียนปิดบัญชีประจำวัน (line-by-line รับ/จ่าย)
 *   balance407: รายงานเงินคงเหลือประจำวัน (แบบ 407) — ยอดคงเหลือทุกบัญชี + จำนวนเงินตัวอักษร
 *
 * หมายเหตุ: ยอดบัญชีในแบบ 407 ใช้ยอดคงเหลือรายปีงบ (กรอกเอง) ตามโมเดลที่ตกลง —
 * ส่วน "สัญญายืมเงิน" ผูกกับโมดูล loan รอเชื่อมภายหลัง (ตอนนี้แสดง 0)
 */
class CashReportService
{
    private const MONTHS = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

    public static function registerSpreadsheet(string $dbDate): Spreadsheet
    {
        $book = new Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('ปิดบัญชี');
        $info = SiteHelper::getInfo();

        $s->setCellValue('A1', (string) ($info['company_name'] ?? 'โรงพยาบาล'));
        $s->setCellValue('A2', 'ทะเบียนปิดบัญชีประจำวัน วันที่ ' . AppHelper::convertToThai($dbDate));
        $s->setCellValue('A3', 'ส่งออกวันที่ ' . AppHelper::convertToThai(date('Y-m-d')));

        $head = ['ลำดับ', 'วันที่', 'เลขที่ใบสำคัญ', 'รายการ', 'รับจาก / จ่ายให้', 'หมายเหตุ', 'รายรับ', 'รายจ่าย', 'วิธี', 'เลขที่เช็ค', 'บัญชี'];
        $col = 'A';
        foreach ($head as $h) {
            $s->setCellValue($col . '5', $h);
            $col++;
        }

        $r = 6;
        $seq = 1;
        $sumIn = 0.0;
        $sumOut = 0.0;

        foreach (FinanceCashTxn::find()->with('category')
            ->where(['txn_type' => 'IN', 'doc_date' => $dbDate, 'voucher_id' => null])
            ->orderBy(['id' => SORT_ASC])->all() as $t) {
            $s->setCellValue("A$r", $seq++)->setCellValue("B$r", AppHelper::convertToThai($t->doc_date))
                ->setCellValue("C$r", $t->doc_no)->setCellValue("D$r", $t->category ? $t->category->name : '')
                ->setCellValue("E$r", $t->party_name)->setCellValue("F$r", $t->note)
                ->setCellValue("G$r", (float) $t->amount)->setCellValue("I$r", $t->payMethodLabel());
            $sumIn += (float) $t->amount;
            $r++;
        }
        foreach (FinanceCashVoucher::find()->with('account')
            ->where(['pay_date' => $dbDate])->orderBy(['id' => SORT_ASC])->all() as $v) {
            $s->setCellValue("A$r", $seq++)->setCellValue("B$r", AppHelper::convertToThai($v->pay_date))
                ->setCellValue("C$r", $v->doc_no)->setCellValue("D$r", 'ใบสำคัญจ่าย')
                ->setCellValue("E$r", $v->payee_name)->setCellValue("F$r", $v->note)
                ->setCellValue("H$r", (float) $v->net_amount)->setCellValue("I$r", $v->payMethodLabel())
                ->setCellValue("J$r", $v->cheque_no)->setCellValue("K$r", $v->account ? $v->account->label() : '');
            $sumOut += (float) $v->net_amount;
            $r++;
        }

        $s->setCellValue("D$r", 'รวม')->setCellValue("G$r", $sumIn)->setCellValue("H$r", $sumOut);
        foreach (range('A', 'K') as $c) {
            $s->getColumnDimension($c)->setAutoSize(true);
        }
        return $book;
    }

    /** ทะเบียนคุมบัญชีเงินฝาก — เคลื่อนไหว (โอน + จ่าย) ของบัญชีในเดือนหนึ่ง */
    public static function accountRegisterSpreadsheet(FinanceCashAccount $account, int $buddhaYear, int $month): Spreadsheet
    {
        $gy = $buddhaYear - 543;
        $start = sprintf('%04d-%02d-01', $gy, $month);
        $end = date('Y-m-t', strtotime($start));

        $rows = [];
        foreach (FinanceCashTransfer::find()->with('fromAccount', 'toAccount')
            ->where(['and', ['between', 'transfer_date', $start, $end], ['or', ['from_account_id' => $account->id], ['to_account_id' => $account->id]]])
            ->orderBy(['transfer_date' => SORT_ASC, 'id' => SORT_ASC])->all() as $tr) {
            $isOut = (int) $tr->from_account_id === (int) $account->id;
            $rows[] = [
                'date' => $tr->transfer_date, 'ref' => $tr->doc_ref,
                'desc' => $isOut ? ('โอนไป ' . ($tr->toAccount ? $tr->toAccount->label() : '')) : ('รับโอนจาก ' . ($tr->fromAccount ? $tr->fromAccount->label() : '')),
                'in' => $isOut ? 0 : (float) $tr->amount, 'out' => $isOut ? (float) $tr->amount : 0,
            ];
        }
        foreach (FinanceCashVoucher::find()
            ->where(['account_id' => $account->id])->andWhere(['between', 'pay_date', $start, $end])
            ->orderBy(['pay_date' => SORT_ASC, 'id' => SORT_ASC])->all() as $v) {
            $rows[] = [
                'date' => $v->pay_date, 'ref' => $v->cheque_no ?: $v->doc_no,
                'desc' => 'จ่าย: ' . ($v->payee_name ?: 'ใบสำคัญ ' . $v->doc_no),
                'in' => 0, 'out' => (float) $v->net_amount,
            ];
        }
        usort($rows, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));

        $book = new Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('ทะเบียนคุม');
        $s->setCellValue('A1', 'ทะเบียนคุม บัญชีเงินฝาก');
        $s->setCellValue('A2', ($account->code ? $account->code . ' ' : '') . $account->name . ($account->bank_name ? ' (' . $account->bank_name . ')' : ''));
        $s->setCellValue('A3', 'ประจำเดือน ' . (self::MONTHS[$month] ?? '') . ' พ.ศ. ' . $buddhaYear);

        $s->setCellValue('A5', 'วันที่')->setCellValue('B5', 'เลขที่เอกสาร')->setCellValue('C5', 'รายการ')
            ->setCellValue('D5', 'เงินเข้า')->setCellValue('E5', 'เงินออก');
        $r = 6;
        $sumIn = 0.0;
        $sumOut = 0.0;
        foreach ($rows as $row) {
            $s->setCellValue("A$r", AppHelper::convertToThai($row['date']))->setCellValue("B$r", $row['ref'])
                ->setCellValue("C$r", $row['desc'])
                ->setCellValue("D$r", $row['in'] ?: null)->setCellValue("E$r", $row['out'] ?: null);
            $sumIn += $row['in'];
            $sumOut += $row['out'];
            $r++;
        }
        if (!$rows) {
            $s->setCellValue("C$r", 'ไม่มีการเคลื่อนไหวในเดือนนี้');
            $r++;
        }
        $s->setCellValue("C$r", 'รวม')->setCellValue("D$r", $sumIn)->setCellValue("E$r", $sumOut);
        $r += 2;
        $s->setCellValue("A$r", 'หมายเหตุ: ยอดคงเหลือตั้งต้นใช้ยอดกรอกเองรายปี — ตารางนี้แสดงเฉพาะการเคลื่อนไหว (โอน/จ่าย) ของบัญชี');
        foreach (range('A', 'E') as $c) {
            $s->getColumnDimension($c)->setAutoSize(true);
        }
        return $book;
    }

    public static function balance407Spreadsheet(int $year, string $dbDate): Spreadsheet
    {
        $book = new Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('สรุปเงินคงเหลือประจำวัน');
        $info = SiteHelper::getInfo();
        [$y, $m, $d] = array_map('intval', explode('-', $dbDate));

        $s->mergeCells('A1:I1')->setCellValue('A1', 'รายงานเงินคงเหลือประจำวัน');
        $s->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $s->setCellValue('B3', 'ส่วนราชการ')->setCellValue('D3', (string) ($info['company_name'] ?? ''))->setCellValue('H3', 'จังหวัด')->setCellValue('I3', 'เลย');
        $s->setCellValue('B4', 'ประจำวันที่')->setCellValue('C4', $d)->setCellValue('D4', 'เดือน')->setCellValue('E4', self::MONTHS[$m] ?? '')->setCellValue('H4', 'พ.ศ.')->setCellValue('I4', $y + 543);

        $s->setCellValue('B6', 'ลำดับ')->setCellValue('C6', 'รายการ')->setCellValue('I6', 'จำนวนเงิน (บาท)');
        $r = 7;
        $grand = 0.0;

        // 1) เงินสด
        $cashSum = 0.0;
        foreach (FinanceCashAccount::find()->where(['account_type' => FinanceCashAccount::TYPE_CASH])->all() as $a) {
            $cashSum += $a->balanceFor($year);
        }
        $s->setCellValue("B$r", 1)->setCellValue("C$r", 'เงินสด')->setCellValue("I$r", $cashSum);
        $grand += $cashSum;
        $r++;
        $s->setCellValue("C$r", '1.1')->setCellValue("D$r", 'ธนบัตร');
        $r++;
        $s->setCellValue("C$r", '1.2')->setCellValue("D$r", 'เหรียญกษาปณ์');
        $r++;

        // 2) สัญญายืมเงิน (loan — รอเชื่อม)
        $s->setCellValue("B$r", 2)->setCellValue("C$r", 'สัญญารับรองการยืมเงิน     0   ฉบับ')->setCellValue("I$r", 0);
        $r++;

        // 3) เงินฝากคลัง
        $treasury = FinanceCashAccount::find()->where(['account_type' => FinanceCashAccount::TYPE_TREASURY])
            ->orderBy(['sort_order' => SORT_ASC])->all();
        $s->setCellValue("B$r", 3)->setCellValue("C$r", 'เงินฝากคลัง     ' . count($treasury));
        $r++;
        $i = 1;
        foreach ($treasury as $a) {
            $bal = $a->balanceFor($year);
            $s->setCellValue("C$r", '3.' . $i)->setCellValue("D$r", $a->name)->setCellValue("I$r", $bal);
            $grand += $bal;
            $i++;
            $r++;
        }

        // 4) สมุดคู่ฝาก (ธนาคาร)
        $banks = FinanceCashAccount::find()->where(['account_type' => FinanceCashAccount::TYPE_BANK])
            ->orderBy(['sort_order' => SORT_ASC])->all();
        $s->setCellValue("B$r", 4)->setCellValue("C$r", 'สมุดคู่ฝาก     ' . count($banks) . '   เล่ม');
        $r++;
        $i = 1;
        foreach ($banks as $a) {
            $bal = $a->balanceFor($year);
            $label = trim(($a->bank_name ? $a->bank_name . ' ' : '') . $a->name . ' ' . ($a->code ?? ''));
            $s->setCellValue("C$r", '4.' . $i)->setCellValue("D$r", $label)->setCellValue("I$r", $bal);
            $grand += $bal;
            $i++;
            $r++;
        }

        $s->setCellValue("C$r", 'รวมเงินคงเหลือ')->setCellValue("I$r", $grand);
        $r += 2;
        $s->setCellValue("B$r", 'รวมเงิน (ตัวอักษร)')->setCellValue("D$r", '(' . BahtText::convert($grand) . ')');
        $r += 3;

        // ลายเซ็น
        $director = '';
        if (!empty($info['director']) && is_object($info['director']) && method_exists($info['director'], 'fullname')) {
            $director = $info['director']->fullname();
        }
        $s->setCellValue("C$r", '(ลงชื่อ)')->setCellValue("I$r", 'หัวหน้าส่วนราชการ');
        $r++;
        $s->setCellValue("D$r", '(   ' . $director . '   )');
        $r += 2;
        $s->setCellValue("B$r", 'คณะกรรมการเก็บรักษาเงินได้ตรวจนับเงินและหลักฐานแทนตัวเงินถูกต้องตามรายการข้างต้นแล้ว');
        $r++;
        $s->setCellValue("B$r", 'จึงได้นำเงินเข้าเก็บรักษาไว้ในตู้เซฟ');
        $r += 2;
        $s->setCellValue("B$r", 'กรรมการ')->setCellValue("E$r", 'กรรมการ')->setCellValue("H$r", 'กรรมการ');
        $r += 3;
        $s->setCellValue("C$r", '(ลงชื่อ)')->setCellValue("I$r", 'เจ้าหน้าที่การเงิน');

        foreach (range('A', 'I') as $c) {
            $s->getColumnDimension($c)->setAutoSize(true);
        }
        return $book;
    }
}
