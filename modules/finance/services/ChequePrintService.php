<?php

namespace app\modules\finance\services;

use Yii;
use setasign\Fpdi\Fpdi;
use app\modules\finance\components\BahtText;
use app\modules\finance\models\FinanceChequeTemplate;
use app\modules\finance\models\FinanceCheque;

/**
 * พิมพ์ข้อความลงบนแผ่นเช็ค (overlay) ด้วย FPDI/FPDF
 *
 * โหมดพิมพ์จริง : สร้างหน้า PDF ขนาดเท่าแผ่นเช็ค วางเฉพาะข้อความตามพิกัด แล้วป้อนเช็คจริงเข้าเครื่องพิมพ์
 * โหมดพรีวิว   : วางรูปสแกนเช็คเป็นพื้นหลังก่อน เพื่อดูตำแหน่งบนจอ (ไม่ใช้ตอนพิมพ์จริง)
 *
 * พิกัดฟิลด์เก็บเป็น % (0-100) ของขนาดแผ่นเช็ค + ชดเชย (calibrate offset) หน่วย มม.
 */
class ChequePrintService
{
    /**
     * @param FinanceChequeTemplate $tpl แม่แบบ (ขนาดแผ่น + พิกัดฟิลด์ + offset)
     * @param array $data ['cheque_date'=>'Y-m-d','payee'=>string,'amount'=>float]
     * @param bool $preview true = วาดพื้นหลังรูปสแกนด้วย (ดูบนจอ)
     * @return string PDF binary
     */
    public function renderPdf(FinanceChequeTemplate $tpl, array $data, bool $preview = false): string
    {
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', Yii::getAlias('@webroot') . '/fonts/');
        }

        $w = (float) ($tpl->page_width_mm ?: 178);
        $h = (float) ($tpl->page_height_mm ?: 82);
        $ox = (float) $tpl->calibrate_offset_x;
        $oy = (float) $tpl->calibrate_offset_y;

        $pdf = new Fpdi();
        // ต้องใช้ไฟล์นิยามฟอนต์ .php ของ FPDF (ไฟล์ .json เป็นของ mPDF ใช้กับ FPDI ไม่ได้ → error)
        $pdf->AddFont('THSarabunNew', '', 'THSarabunNew.php');
        $pdf->AddFont('THSarabunNew', 'B', 'THSarabunNew Bold.php');
        $pdf->SetAutoPageBreak(false);
        $orientation = $w >= $h ? 'L' : 'P';
        $pdf->AddPage($orientation, [$w, $h]);

        // พื้นหลัง (เฉพาะพรีวิว)
        if ($preview) {
            $bg = $this->backgroundFile($tpl);
            if ($bg !== null) {
                try {
                    $pdf->Image($bg, 0, 0, $w, $h);
                } catch (\Throwable $e) {
                    // ไม่มีรูป/รูปเสีย — ข้ามพื้นหลังไป
                }
            }
        }

        $values = $this->fieldValues($data);

        // รูปแบบเช็ค → คุมการขีดฆ่า "หรือผู้ถือ" / ขีดคร่อม / ข้อความในคร่อม
        [$doStrike, $doCross, $crossText] = FinanceCheque::resolveForm(
            (string) ($data['form_type'] ?? FinanceCheque::FORM_AC_PAYEE),
            (string) ($data['bank_name'] ?? '')
        );

        // วัดตำแหน่ง "ท้ายชื่อผู้รับ" ไว้ก่อน เพื่อลากเส้นขีดฆ่าต่อจากชื่อไปจนคร่อม "หรือผู้ถือ"
        $payeeEndX = null;
        foreach ($tpl->layout() as $f) {
            if (($f['key'] ?? '') === 'payee' && !empty($f['enabled'])) {
                $pt = (string) ($values['payee'] ?? '');
                $px = ((float) ($f['x'] ?? 0)) / 100 * $w + $ox;
                $pdf->SetFont('THSarabunNew', !empty($f['bold']) ? 'B' : '', (float) ($f['font_size'] ?? 16));
                $payeeEndX = $px + ($pt !== '' ? $pdf->GetStringWidth(iconv('UTF-8', 'cp874//IGNORE', $pt)) : 0);
                break;
            }
        }

        $pdf->SetTextColor(0, 0, 0);

        foreach ($tpl->layout() as $f) {
            $key = $f['key'] ?? '';
            if ($key === '' || empty($f['enabled'])) {
                continue;
            }
            $x = ((float) ($f['x'] ?? 0)) / 100 * $w + $ox;
            $y = ((float) ($f['y'] ?? 0)) / 100 * $h + $oy;
            $pitch = (float) ($f['pitch'] ?? 0); // ช่องตัวเลข = ระยะ/ตัวอักษร

            // ขีดฆ่า "หรือผู้ถือ": ลากเส้นจากท้ายชื่อผู้รับ → ปลายบรรทัด (field.x=ปลายเส้น, field.y=ระดับเส้น)
            // กันเติมชื่อ/ข้อความแทรก และคร่อมทับ "หรือผู้ถือ/or bearer" ที่พิมพ์มาบนเช็ค
            if ($key === 'strike_bearer') {
                if (!$doStrike) {
                    continue;
                }
                $endX = $x;
                $startX = $payeeEndX !== null ? $payeeEndX + 2 : $endX - ($pitch > 0 ? $pitch : 40) / 100 * $w;
                if ($endX > $startX) {
                    $pdf->SetLineWidth(0.4);
                    $pdf->Line($startX, $y, $endX, $y);
                }
                continue;
            }

            // ขีดคร่อม: เส้นทแยงคู่ขนาน (//) + ข้อความ (A/C PAYEE ONLY / ชื่อธนาคาร) ตามรูปแบบเช็ค
            if ($key === 'ac_payee') {
                if (!$doCross) {
                    continue;
                }
                $pdf->SetLineWidth(0.5);
                // เส้นทแยงคู่ขนานจากล่างซ้าย → บนขวา
                $pdf->Line($x - 11, $y + 1.5, $x - 4, $y - 6.5);
                $pdf->Line($x - 7, $y + 1.5, $x, $y - 6.5);
                if ($crossText !== '') {
                    $pdf->SetFont('THSarabunNew', 'B', (float) ($f['font_size'] ?? 14));
                    $pdf->Text($x, $y, iconv('UTF-8', 'cp874//IGNORE', $crossText));
                }
                continue;
            }

            if (!isset($values[$key])) {
                continue;
            }
            $text = (string) $values[$key];
            if ($text === '') {
                continue;
            }
            $size = (float) ($f['font_size'] ?? 16);
            $style = !empty($f['bold']) ? 'B' : '';
            $align = $f['align'] ?? 'L';

            $pdf->SetFont('THSarabunNew', $style, $size);

            // โหมดช่องตัวเลข: พิมพ์ทีละตัวเว้นระยะเท่า ๆ กัน (เช่น วันที่ในช่อง วว ดด ปปปป)
            if ($pitch > 0) {
                $raw = $key === 'cheque_date' ? preg_replace('/\D/', '', $text) : $text;
                $step = $pitch / 100 * $w;
                $cx = $x;
                foreach (preg_split('//u', $raw, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
                    $pdf->Text($cx, $y, iconv('UTF-8', 'cp874//IGNORE', $ch));
                    $cx += $step;
                }
                continue;
            }

            $enc = iconv('UTF-8', 'cp874//IGNORE', $text);
            $textWidth = $pdf->GetStringWidth($enc);
            if ($align === 'R') {
                $x -= $textWidth;
            } elseif ($align === 'C') {
                $x -= $textWidth / 2;
            }
            // FPDF Text() วางที่ baseline — y ที่เก็บคือ baseline
            $pdf->Text($x, $y, $enc);
        }

        return $pdf->Output('', 'S');
    }

    /** แปลงข้อมูลดิบเป็นค่าที่จะพิมพ์ในแต่ละฟิลด์ */
    public function fieldValues(array $data): array
    {
        $amount = isset($data['amount']) ? (float) $data['amount'] : 0.0;
        $text = $amount > 0 ? BahtText::convert($amount) : '';
        // ac_payee: เช็คเปล่าจริงไม่มี ต้องพิมพ์เอง เปิด/ปิดได้ต่อใบ (default เปิด)
        $printAcPayee = array_key_exists('print_ac_payee', $data) ? !empty($data['print_ac_payee']) : true;
        return [
            'cheque_date' => $this->thaiDate($data['cheque_date'] ?? null),
            'payee' => trim((string) ($data['payee'] ?? '')),
            'amount_text' => $text !== '' ? ('-' . $text . '-') : '',
            'amount_number' => $amount > 0 ? number_format($amount, 2) : '',
            'ac_payee' => $printAcPayee ? 'A/C PAYEE ONLY' : '',
        ];
    }

    /** Y-m-d -> วว/ดด/ปปปป (พ.ศ.) */
    private function thaiDate(?string $ymd): string
    {
        if (!$ymd || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
            return '';
        }
        return sprintf('%02d/%02d/%d', (int) $m[3], (int) $m[2], (int) $m[1] + 543);
    }

    /** path ไฟล์รูปพื้นหลัง (สำหรับพรีวิว) หรือ null */
    private function backgroundFile(FinanceChequeTemplate $tpl): ?string
    {
        if (empty($tpl->background_path)) {
            return null;
        }
        $path = $tpl->background_path;
        if (!preg_match('#^([a-zA-Z]:[\\\\/]|/)#', $path)) {
            $path = Yii::getAlias('@webroot') . '/' . ltrim($path, '/');
        }
        return is_file($path) ? $path : null;
    }
}
