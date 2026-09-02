<?php

namespace app\modules\purchase\components;

use Yii;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocTemplate;

/**
 * เรนเดอร์เอกสารลงกระดาษ — ทั้งบนจอและใน PDF
 *
 * ทำไม CSS ของจอกับของ PDF อยู่ในไฟล์เดียวกัน
 *
 * ทั้งสองทางต้องได้กระดาษหน้าตาเดียวกัน ถ้าแยกไฟล์ (หรือแย่กว่านั้นคือเขียน CSS
 * ของจอไว้ในไฟล์ view) วันหนึ่งจะมีคนแก้ระยะบนจอให้สวยแล้วไม่ได้แก้ฝั่ง PDF
 * ผู้ใช้จัดเอกสารบนจอจนพอใจแล้วกดพริ้นท์ได้กระดาษที่ไม่เหมือนกับที่เห็น ซึ่งเป็น
 * อาการที่ทำให้คนเลิกเชื่อหน้าแก้ไขทั้งหน้า — sheetCss() จึงเป็นแหล่งเดียวของทั้งคู่
 *
 * ต่างกันแค่สองจุดที่ต่างกันโดยธรรมชาติของสื่อ
 *   1) จอต้องวาดขอบกระดาษและเงาให้เห็นว่านี่คือ A4 ส่วน PDF เป็น A4 อยู่แล้ว
 *   2) ตราครุฑบนจออ้างด้วย URL แต่ mPDF ต้องอ้างด้วย path ในเครื่อง
 */
class DocRenderer
{
    /** ไฟล์ตราครุฑที่มีอยู่ใน ERP แล้ว */
    const EMBLEM_FILE = 'img/krut.png';

    /** ความสูงตราครุฑเป็นมิลลิเมตร ตามคีย์ของ DocTemplate::emblemList() */
    const EMBLEM_MM = [
        DocTemplate::EMBLEM_SMALL => 15,
        DocTemplate::EMBLEM_LARGE => 30,
    ];

    /**
     * ชื่อวงศ์ฟอนต์ที่เอกสารขอใช้ เรียงตามลำดับความชอบ
     *
     * ทุกชื่อในลิสต์นี้ต้องมีคีย์ตรงกันใน fontData() ไม่งั้น mPDF จะถอยไปใช้ DejaVu
     * ซึ่งไม่มีสระและวรรณยุกต์ไทย แล้วข้อความกลายเป็นกล่องสี่เหลี่ยมทั้งย่อหน้า
     *
     * กับดักที่เคยทำให้พิมพ์ออกมาเสียจริง: CSS เขียน 'THSarabunNew' แต่ config
     * ลงทะเบียนไว้แค่คีย์ 'thsarabun' ผลคือข้อความในตารางยังอ่านได้ (เพราะ mPDF
     * ใช้ default_font ให้เมื่อ cascade ไปไม่ถึง) แต่ย่อหน้าที่อยู่นอกตารางกลายเป็น
     * กล่องหมด อาการครึ่ง ๆ แบบนี้หลอกให้คิดว่าไฟล์ฟอนต์เสีย ทั้งที่เป็นเรื่องชื่อไม่ตรง
     * DocTestController จึงมีข้อทดสอบยืนยันว่าชื่อใน CSS กับคีย์ที่ลงทะเบียนตรงกัน
     */
    const FONT_FAMILIES = ['THSarabunNew', 'thsarabun'];

    /**
     * ไฟล์ฟอนต์ต่อน้ำหนัก ใช้ทั้งฝั่ง mPDF และฝั่ง @font-face ของเบราว์เซอร์
     *
     * เขียนไว้ที่เดียวเพราะสองฝั่งต้องชี้ไฟล์เดียวกันจริง ๆ ไม่ใช่แค่ชื่อคล้ายกัน
     * ถ้าจอกับ PDF ใช้ไฟล์ฟอนต์ต่างกัน ความกว้างตัวอักษรจะไม่เท่ากัน แล้วข้อความ
     * ที่จัดพอดีบรรทัดบนจอจะตกบรรทัดบนกระดาษ
     */
    const FONT_FILES = [
        'R' => 'THSarabunNew.ttf',
        'B' => 'THSarabunNew-Bold.ttf',
        'I' => 'THSarabunNew-Italic.ttf',
        'BI' => 'THSarabunNew BoldItalic.ttf',
    ];

    /**
     * แผนผังไฟล์ฟอนต์ที่ส่งให้ mPDF
     *
     * ลงทะเบียนสองชื่อชี้ไปไฟล์ชุดเดียวกัน เพราะโค้ดในระบบเรียกฟอนต์นี้ด้วยสองชื่อ
     * (โมดูล am ใช้ 'thsarabun' ส่วน CSS ของเอกสารเขียนตามชื่อไฟล์จริงคือ
     * 'THSarabunNew') การลงทะเบียนทั้งคู่ถูกกว่าการไปไล่แก้ให้ทุกที่เรียกชื่อเดียวกัน
     * และกันปัญหาเดิมไม่ให้กลับมาเมื่อมีคนเขียน CSS ด้วยชื่อใดชื่อหนึ่งในอนาคต
     */
    public static function fontData(): array
    {
        // mPDF เทียบชื่อวงศ์ฟอนต์แบบตัวพิมพ์เล็กทั้งหมด คีย์จึงต้องเป็นตัวเล็ก
        // แต่ฝั่ง CSS ต้องเขียน 'THSarabunNew' ให้ตรงกับชื่อใน @font-face
        return array_fill_keys(
            array_map('strtolower', self::FONT_FAMILIES),
            self::FONT_FILES
        );
    }

    /** ค่า font-family สำหรับ CSS — ประกอบจาก FONT_FAMILIES เพื่อไม่ให้สองที่หลุดจากกัน */
    public static function fontStack(): string
    {
        return implode(', ', array_map(
            static fn (string $name): string => "'" . $name . "'",
            self::FONT_FAMILIES
        )) . ', sans-serif';
    }

    /**
     * เนื้อเอกสารที่พร้อมวางลงกระดาษ
     *
     * แทน {{emblem}} ที่ DocMergeEngine ตั้งใจปล่อยผ่านมา — ต้องทำที่ชั้นนี้เพราะ
     * ค่าที่ถูกต้องต่างกันระหว่างจอกับ PDF และเพราะผู้ใช้เปลี่ยนขนาดครุฑได้ทุกเมื่อ
     * โดยไม่ต้อง merge ใหม่ทั้งฉบับ (ถ้าฝัง <img> ลง body_html ตอน merge
     * การเปลี่ยนขนาดครุฑจะต้องไปแก้ HTML ที่ผู้ใช้แก้มือไว้แล้ว ซึ่งเสี่ยงลบงานเขา)
     */
    public static function body(Doc $doc, bool $forPdf = false): string
    {
        return str_replace(
            '{{emblem}}',
            self::emblemHtml($doc->emblem, $forPdf),
            (string) $doc->body_html
        );
    }

    /**
     * แท็กรูปตราครุฑ ห่อด้วย span ที่มี class กำกับไว้เสมอ
     *
     * ห่อ span ไว้แม้ตอนที่เลือก "ไม่แสดง" (ได้ span เปล่า) เพราะแถบเครื่องมือบนหน้า
     * แก้ไขต้องมีจุดยึดสำหรับสลับขนาดครุฑ ถ้าคืนค่าว่างเปล่า ๆ เมื่อผู้ใช้เลือกไม่แสดง
     * แล้วเปลี่ยนใจอยากให้แสดงอีกครั้ง จะไม่มี element ไหนเหลือให้ JS หาเจอ
     * และ normalize() ก็ไม่มีอะไรให้แปลงกลับเป็น {{emblem}} ตอนบันทึก
     */
    public static function emblemHtml(?string $emblem, bool $forPdf = false): string
    {
        $mm = self::EMBLEM_MM[(string) $emblem] ?? null;
        if ($mm === null) {
            return '<span class="d-emblem"></span>';
        }

        if ($forPdf) {
            $src = Yii::getAlias('@webroot') . '/' . self::EMBLEM_FILE;
        } else {
            // @web มีเฉพาะในแอปเว็บ ถ้าเรียกจาก console (เช่นคำสั่งทดสอบหรือ cron
            // ที่เรนเดอร์เอกสาร) Yii::getAlias('@web') จะโยน exception ทิ้งทั้งงาน
            // ส่งอาร์กิวเมนต์ที่สองเป็น false เพื่อให้คืน false แทนการโยน แล้วถอยไปใช้
            // path แบบ root-relative ซึ่งใช้ได้เมื่อ ERP ติดตั้งที่ราก domain
            $base = Yii::getAlias('@web', false);
            $src = ($base === false ? '' : $base) . '/' . self::EMBLEM_FILE;
        }

        return '<span class="d-emblem"><img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'
            . ' alt="ตราครุฑ" style="height:' . $mm . 'mm"></span>';
    }

    /**
     * แปลงตราครุฑที่เรนเดอร์แล้วกลับเป็น {{emblem}} ก่อนบันทึกลงฐาน
     *
     * ต้องมีเพราะสิ่งที่หน้าแก้ไขส่งกลับมาคือ HTML ที่ผ่านการเรนเดอร์แล้ว — {{emblem}}
     * กลายเป็น <img> ที่ชี้ไป URL ของเว็บไปแล้ว ถ้าเก็บอย่างนั้นลงฐาน ตอนทำ PDF
     * mPDF จะได้ URL ที่มันต้องออกไปโหลดผ่านเน็ต ซึ่งบนอินทราเน็ตโรงพยาบาลมักโหลด
     * ไม่ได้และครุฑจะหายไปจากกระดาษ การเก็บเป็น {{emblem}} ทำให้แต่ละชั้นเรนเดอร์
     * เลือก path ที่ถูกต้องของตัวเองได้ทุกครั้ง
     */
    public static function normalize(?string $html): string
    {
        return (string) preg_replace(
            '/<span[^>]*class="[^"]*\bd-emblem\b[^"]*"[^>]*>.*?<\/span>/su',
            '{{emblem}}',
            (string) $html
        );
    }

    /**
     * CSS ของตัวเอกสาร — ใช้ร่วมกันระหว่างจอและ PDF
     *
     * ใช้หน่วย pt กับ mm ไม่ใช้ px หรือ rem เพราะสองหน่วยแรกแปลงเป็นขนาดจริงบน
     * กระดาษได้ตรงกันทั้งในเบราว์เซอร์และใน mPDF ส่วน px ขึ้นกับ dpi ที่แต่ละตัว
     * สมมติเอง ทำให้ระยะบนจอกับบนกระดาษเลื่อนจากกัน
     */
    public static function sheetCss(Doc $doc): string
    {
        $fs = (int) $doc->font_size;
        $titleSize = $fs + 4;
        $labelSize = $fs + 2;
        // ตารางแนวนอนที่มีหลายคอลัมน์ (แบบ 8707) ใช้ตัวเล็กกว่าเนื้อความหนึ่งระดับ ตามต้นฉบับของงานการเงิน
        $tableSize = max(9, $fs - 2);
        $headSize = max(8, $fs - 3);
        // ย่อหน้าหนังสือราชการย่อหน้าแรก 2.5 ซม. ตามระเบียบงานสารบรรณ
        $indent = '2.5cm';
        $fontStack = self::fontStack();

        /*
         * ทุกกฎขึ้นต้นด้วย .d-sheet ด้วยเหตุผลสองข้อ
         *
         * 1) กันสไตล์ของแอปรั่วเข้ามาในกระดาษ — CSS กลางของ ERP ตั้ง font-size ให้ td
         *    ทั่วเว็บไว้ที่ราว 10pt เมื่อเอากระดาษไปวางในหน้าเว็บ ทุกช่องในตารางจึงเล็ก
         *    ลงเหลือ 10pt ขณะที่ย่อหน้ายังเป็น 16pt ตามที่ตั้งไว้ ผลคือบนจอตัวหนังสือ
         *    ในตารางเล็กกว่าย่อหน้าชัดเจน แต่ใน PDF เท่ากันหมด (mPDF ไม่มี CSS ของแอป)
         *    จึงต้องประกาศ font-size ที่ td/th/p ตรง ๆ ไม่พึ่งการสืบทอดจาก .d-sheet
         *
         * 2) ความจำเพาะ (specificity) ต้องสูงกว่ากฎ element เปล่า ๆ ของแอป และกฎของ
         *    คลาสเฉพาะ (.d-title ฯลฯ) ต้องสูงกว่ากฎพื้นฐาน .d-sheet p ไม่งั้นขนาด
         *    หัวเรื่องจะถูกกฎพื้นฐานทับ
         */
        return <<<CSS
.d-sheet { font-family: {$fontStack}; font-size: {$fs}pt; color:#000; line-height:1.4; }
.d-sheet p, .d-sheet td, .d-sheet th, .d-sheet span, .d-sheet li, .d-sheet div {
    font-family: {$fontStack};
    font-size: {$fs}pt;
    line-height: 1.4;
    color: #000;
}
.d-sheet p { margin:0; }
.d-sheet table { border-collapse:collapse; width:100%; margin:0; }

.d-sheet .d-masthead, .d-sheet .d-masthead td { border:none; }
.d-sheet .d-masthead-side { width:22%; vertical-align:top; }
.d-sheet .d-masthead-title { width:56%; text-align:center; vertical-align:bottom; }
.d-sheet .d-title { font-size: {$titleSize}pt; font-weight:bold; text-align:center; }
.d-sheet .d-emblem { display:inline-block; text-align:center; }
.d-sheet .d-memo-masthead, .d-sheet .d-memo-masthead td { border:none; }
.d-sheet .d-memo-masthead { margin-bottom:4pt; }
.d-sheet .d-memo-emblem { width:22%; text-align:left; vertical-align:bottom; }
.d-sheet .d-memo-title { width:56%; text-align:center; vertical-align:bottom; font-size:{$titleSize}pt; font-weight:bold; }
.d-sheet .d-memo-balance { width:22%; }
.d-sheet .d-memo-fields td { border:none; padding:2pt 3pt; vertical-align:baseline; line-height:1.25; }
.d-sheet .d-memo-label { width:18%; font-weight:bold; font-size:{$labelSize}pt; }
.d-sheet .d-memo-value { border-bottom:0.5pt dotted #000; }
.d-sheet .d-memo-ref { width:32%; }
.d-sheet .d-memo-date-label { width:12%; text-align:right; }
.d-sheet .d-memo-rule { border-bottom:1.5pt solid #000; margin-top:2pt; }
.d-sheet .d-memo-line { margin-top:6pt; }
.d-sheet .d-form-version { display:none; }
.d-sheet .d-8708-form, .d-sheet .d-8708-form p, .d-sheet .d-8708-form td { line-height:1.15; }
.d-sheet .d-8708-loan td { border:none; padding:1pt 3pt; vertical-align:top; }
.d-sheet .d-8708-loan td:first-child { width:45%; }
.d-sheet .d-8708-loan td:nth-child(2) { width:37%; }
.d-sheet .d-8708-loan td:last-child { width:18%; }
.d-sheet .d-8708-title { font-size:{$titleSize}pt; font-weight:bold; text-align:center; margin-top:8pt; }
.d-sheet .d-8708-office td { border:none; padding:2pt 3pt; }
.d-sheet .d-8708-office td:first-child { width:38%; }
.d-sheet .d-8708-line { margin-top:3pt; }
.d-sheet .d-8708-indent { text-indent:{$indent}; margin-top:4pt; }
.d-sheet .d-8708-expense td { border:none; padding:1pt 3pt; }
.d-sheet .d-8708-expense-name { width:44%; }
.d-sheet .d-8708-expense-days { width:24%; }
.d-sheet .d-8708-expense-total { width:32%; text-align:right; }
.d-sheet .d-8708-form .d-sign { margin-top:7pt; }
.d-sheet .d-8708-approval td { width:50%; border:0.5pt solid #000; padding:5pt 7pt; vertical-align:top; height:66mm; }
.d-sheet .d-8708-received { text-align:center; margin-top:12pt; }
.d-sheet .d-8708-signatures { margin-top:12pt; }
.d-sheet .d-8708-signatures td { width:50%; border:none; padding:4pt 8pt; vertical-align:top; }
.d-sheet .d-8708-notes { border-top:1.5pt solid #000; border-bottom:1.5pt solid #000; margin-top:10pt; padding:7pt 3pt; }
/* ช่องติ๊ก [ ] และบรรทัดที่ต้องมีข้อความชิดขวาของแบบ 8708 ส่วนที่ 1 */
.d-sheet .d-8708-box { letter-spacing:0.5pt; }
.d-sheet .d-8708-split td { border:none; padding:1pt 3pt; }
.d-sheet .d-8708-split td:first-child { width:72%; }
.d-sheet .d-8708-clarify { margin-top:10pt; }
.d-sheet .d-8708-clarify td { border:none; padding:0 3pt; vertical-align:top; }
.d-sheet .d-8708-clarify-label { width:12%; font-weight:bold; }

/* หลักฐานการจ่ายเงินเป็นหมู่คณะ (แบบ 8707 ส่วนที่ 2) พิมพ์แนวนอน ตารางกว้างเต็มหน้า
   ความกว้างคอลัมน์ยึดตามไฟล์ Excel ต้นฉบับของงานการเงิน */
.d-sheet .d-8707-head td { border:none; padding:0; vertical-align:top; }
.d-sheet .d-8707-head-form { width:14%; text-align:right; }
.d-sheet .d-8707-org { text-align:center; }
.d-sheet .d-8707-ref { margin-top:4pt; }
.d-sheet .d-8707-items { margin-top:4pt; table-layout:fixed; }
.d-sheet .d-8707-items td { border:0.5pt solid #000; padding:2pt 3pt; vertical-align:top; font-size:{$tableSize}pt; }
/* หัวตารางเล็กกว่าข้อมูลอีกหนึ่งระดับ เพื่อให้ชื่อคอลัมน์อยู่ในบรรทัดเดียวตามต้นฉบับ */
.d-sheet .d-8707-items .d-items-head td { font-size:{$headSize}pt; }
.d-sheet .d-8707-c-no { width:4.5%; text-align:center; }
.d-sheet .d-8707-c-name { width:16%; }
.d-sheet .d-8707-c-position { width:18%; }
.d-sheet .d-8707-c-expense { width:34%; }
.d-sheet .d-8707-c-money { width:9%; text-align:center; }
.d-sheet .d-8707-c-sum { width:7.5%; text-align:center; }
.d-sheet .d-8707-c-sign { width:6%; text-align:center; }
.d-sheet .d-8707-c-date { width:8%; text-align:center; }
.d-sheet .d-8707-c-note { width:6%; text-align:center; }
.d-sheet .d-8707-row td { height:7mm; }
.d-sheet .d-8707-total td { font-weight:bold; }
.d-sheet .d-8707-words { margin-top:4pt; }
.d-sheet .d-8707-clarify { margin-top:4pt; }
.d-sheet .d-8707-foot td { border:none; padding:6pt 3pt 0; vertical-align:top; }
.d-sheet .d-8707-foot-sign { width:32%; text-align:center; }

/* ใบรับรองแทนใบเสร็จรับเงิน (บก.111) และใบหน้างบสำคัญ ใช้ตารางเดียวกัน */
.d-sheet .d-bill-org { margin-top:4pt; }
.d-sheet .d-bill-items { margin-top:8pt; table-layout:fixed; }
.d-sheet .d-bill-items td { border:0.5pt solid #000; padding:3pt 5pt; vertical-align:top; }
.d-sheet .d-bill-c-date { width:18%; }
.d-sheet .d-bill-c-detail { width:50%; }
.d-sheet .d-bill-c-amount { width:16%; text-align:center; }
.d-sheet .d-bill-c-note { width:16%; text-align:center; }
.d-sheet .d-bill-blank td { height:8mm; }
.d-sheet .d-bill-sub { display:inline-block; padding-left:24pt; }
.d-sheet .d-bill-words { text-align:center; margin-top:8pt; }
.d-sheet .d-bill-certify { text-indent:{$indent}; margin-top:8pt; }
/* ---- เอกสารชุดเดินทางไปราชการ (โมดูล hr) ใช้คำนำหน้า d-tvm- ของตัวเอง
   ไม่ปนกับชุด d-memo- ที่แม่แบบบันทึกข้อความของงานพัสดุใช้อยู่ ---- */
/* ป้ายอยู่ในช่องเดียวกับเนื้อความ ระยะห่างมาจากเว้นวรรคสองตัวจริง ไม่ใช่ความกว้าง
   คอลัมน์ ระยะจึงคงที่ทั้งบนกระดาษและในหน้าจอแก้ไขที่กรอบกว้างกว่ากระดาษ */
.d-sheet .d-tvm-head { margin-top:3pt; }
.d-sheet .d-tvm-head td { border:none; padding:1pt 0; vertical-align:bottom; }
.d-sheet .d-tvm-lbl { font-weight:bold; }
.d-sheet .d-tvm-lbl-plain { font-weight:normal; }
.d-sheet .d-tvm-rule { border-bottom:0.5pt solid #000; }
.d-sheet .d-tvm-ref { width:55%; }
.d-sheet .d-tvm-space { width:4%; }
.d-sheet .d-tvm-line { margin-top:2pt; }
.d-sheet .d-tvm-amounts { margin-top:4pt; }
.d-sheet .d-tvm-amounts td { border:none; padding:1pt 3pt; vertical-align:top; }
.d-sheet .d-tvm-amount { width:34%; text-align:right; }
.d-sheet .d-tvm-sign { margin-top:10pt; }
.d-sheet .d-tvm-sign td { border:none; padding:0 6pt; width:50%; text-align:center; vertical-align:top; }
.d-sheet .d-tvm-decision { margin-top:10pt; }
.d-sheet .d-tvm-decision td { border:0.5pt solid #000; padding:5pt 7pt; width:50%; vertical-align:top; }
/* ท้ายบันทึก: ความเห็นหัวหน้าฝ่ายบริหาร (ซ้าย) คู่กับช่องอนุมัติ (ขวา) */
.d-sheet .d-tvm-approve { margin-top:10pt; }
.d-sheet .d-tvm-approve td { border:none; padding:0 6pt; vertical-align:top; width:50%; }
.d-sheet .d-tvm-approve-right { text-align:center; }

.d-sheet .d-head { margin-top:4pt; }
.d-sheet .d-head td { padding:3pt 4pt; }
/* ต้องกำหนดความกว้างเป็นสัดส่วนที่ใช้ได้จริง ห้ามใช้ width:1% + white-space:nowrap
   แบบที่เบราว์เซอร์ยอมให้ — mPDF ไม่รู้จัก white-space:nowrap แต่เชื่อ width:1%
   ช่องป้ายจึงถูกบีบจนเหลือความกว้างตัวอักษรเดียว แล้ว "ส่วนราชการ" พิมพ์ออกมา
   เป็นตัวอักษรเรียงลงมาทีละบรรทัด (เห็นจริงตอนสั่งพิมพ์ครั้งแรก) */
.d-sheet .d-lbl { font-weight:bold; width:20%; border:none; vertical-align:bottom; }
.d-sheet .d-lbl-sm { font-weight:bold; width:12%; border:none; vertical-align:bottom; }
.d-sheet .d-val { border-bottom:0.5pt solid #000; }

.d-sheet .d-to { margin-top:10pt; }
.d-sheet .d-body { text-align:justify; text-indent:{$indent}; margin-top:8pt; }
.d-sheet .d-caption { text-align:center; font-weight:bold; margin-top:12pt; margin-bottom:4pt; }
.d-sheet .d-list { text-indent:{$indent}; margin-top:3pt; }
.d-sheet .d-approve { text-indent:{$indent}; margin-top:8pt; }

.d-sheet .d-detail td, .d-sheet .d-items td { border:0.5pt solid #000; padding:3pt 5pt; vertical-align:top; }
.d-sheet .d-detail-lbl { width:32%; }
.d-sheet .d-items-head td { font-weight:bold; text-align:center; background-color:#eef2f7; }
.d-sheet .d-c-no { width:6%; text-align:center; }
.d-sheet .d-c-name { width:38%; }
.d-sheet .d-c-qty { width:10%; text-align:center; }
.d-sheet .d-c-unit { width:10%; text-align:center; }
.d-sheet .d-c-price { width:18%; text-align:right; }
.d-sheet .d-c-amount { width:18%; text-align:right; }
.d-sheet .d-c-total { text-align:right; font-weight:bold; }
.d-sheet .d-right { text-align:right; }
.d-sheet .d-page-break { page-break-before:always; margin:0; }

/* ใบสั่งซื้อ/สั่งจ้าง (แบบฟอร์มปี ๒๕๖๙) — คลาสเฉพาะฉบับ ตั้งชื่อขึ้นต้น d-po8-
   ตามแบบเดียวกับ d-8708- เพราะรูปแบบพวกนี้ใช้กับเอกสารใบเดียว ถ้าเอาไปแก้คลาส
   กลาง (.d-c-*, .d-list) เอกสารอีกสิบกว่าใบที่ใช้คลาสเดียวกันจะเปลี่ยนตามไปด้วย */
.d-sheet .d-po8-head { margin-top:8pt; }
.d-sheet .d-po8-head td { border:none; padding:1pt 2pt; vertical-align:top; width:50%; }
/* ไม่ใช้ text-align:justify กับย่อหน้าของฟอร์มนี้ — ภาษาไทยไม่มีช่องว่างระหว่างคำ
   mPDF จึงยืดช่องไฟที่ช่องว่างไม่กี่จุดที่มีจนเห็นเป็นรู เช่น "สงวนสิ ทธิ์ ค่ าปรั บ"
   (เห็นจริงตอนเรนเดอร์ทดสอบ) ชิดซ้ายอ่านง่ายกว่าและตรงกับแบบฟอร์มต้นฉบับ */
.d-sheet .d-po8-intro { text-indent:{$indent}; margin-top:10pt; }
.d-sheet .d-po8-heading { font-weight:bold; margin-top:12pt; margin-bottom:2pt; }
/* หัวข้อ "หมายเหตุ" ในแบบฟอร์มนี้มีเส้นใต้ ไม่ใช่แค่ตัวหนา */
.d-sheet .d-po8-note-heading { font-weight:bold; text-decoration:underline; margin-top:12pt; margin-bottom:2pt; }
/* เงื่อนไขการซื้อ/จ้างไม่ย่อหน้า — เลขข้อต้องเรียงตรงกันทุกข้อที่ขอบซ้าย
   ถ้าใช้ .d-list ที่ย่อหน้า 2.5 ซม. เลขข้อจะถูกดันเข้าไปกลางหน้ากระดาษ */
.d-sheet .d-po8-term { margin-top:2pt; }
/* ช่องจำนวนเงินเป็นตัวหนังสือ กินพื้นที่ซ้ายของบล็อกยอดรวมทั้งสี่บรรทัด (rowspan)
   ต้องเจาะจงถึง td ไม่งั้น vertical-align:top ของ .d-items td จะชนะเพราะเจาะจงกว่า */
.d-sheet .d-items td.d-po8-words { text-align:center; vertical-align:middle; }
.d-sheet .d-po8-sum-lbl { width:18%; }
.d-sheet .d-po8-sum-amt { width:18%; text-align:right; }
.d-sheet .d-po8-ref { margin-top:14pt; }

.d-sheet .d-sign { margin-top:14pt; }
.d-sheet .d-sign td { border:none; }
.d-sheet .d-sign-cell { width:50%; text-align:center; }
CSS;
    }

    /**
     * ประกาศ @font-face ให้เบราว์เซอร์โหลดไฟล์ฟอนต์ชุดเดียวกับที่ mPDF ใช้
     *
     * ทำไมต้องมี: mPDF รู้จักฟอนต์จาก config fontdata ของมันเอง แต่เบราว์เซอร์ไม่รู้
     * ถ้าเครื่องผู้ใช้ไม่ได้ติดตั้ง TH SarabunNew ไว้ (หรือชื่อวงศ์ฟอนต์ใน CSS ไม่ตรง
     * กับชื่อที่ติดตั้ง) เบราว์เซอร์จะถอยไปใช้ sans-serif ของระบบ แล้วกระดาษบนจอกับ
     * กระดาษที่พิมพ์ออกมาใช้ฟอนต์ต่างกัน ความกว้างตัวอักษรไม่เท่ากัน การจัดบรรทัด
     * ที่ทำไว้บนจอจึงเลื่อนเมื่อพิมพ์ — วัดได้จริงว่าต่างกันราว 4% ที่ 16pt
     *
     * ไม่ใช้ web/css/thsarabunnew.css ที่มีอยู่เดิม เพราะไฟล์นั้นประกาศแค่น้ำหนักปกติ
     * (ตัวหนาจะถูกเบราว์เซอร์ปลอมให้แทนการใช้ THSarabunNew-Bold.ttf จริง) และมัน
     * ตั้ง font-family ให้ body ทั้งหน้าด้วย ซึ่งจะเปลี่ยนหน้าตาทั้ง ERP ไม่ใช่แค่กระดาษ
     */
    public static function fontFaceCss(): string
    {
        $base = Yii::getAlias('@web', false);
        $base = ($base === false ? '' : $base) . '/fonts/THSarabunNew/';

        $faces = [
            ['R', 'normal', 'normal'],
            ['B', 'bold', 'normal'],
            ['I', 'normal', 'italic'],
            ['BI', 'bold', 'italic'],
        ];

        $css = '';
        foreach ($faces as [$key, $weight, $style]) {
            $url = $base . str_replace(' ', '%20', self::FONT_FILES[$key]);
            $css .= "@font-face{font-family:'THSarabunNew';"
                . "font-weight:{$weight};font-style:{$style};font-display:block;"
                . "src:url('{$url}') format('truetype');}\n";
        }

        return $css;
    }

    /**
     * CSS เฉพาะบนจอ — วาดกระดาษ A4 ให้เห็นขอบจริง
     *
     * ขนาดกระดาษกับขอบมาจากค่าเดียวกับที่ส่งให้ mPDF ผู้ใช้จึงเห็นว่าข้อความจะตก
     * ขอบกระดาษตรงไหนตั้งแต่ตอนแก้ ไม่ต้องพิมพ์ออกมาก่อนแล้วค่อยรู้
     */
    public static function screenCss(Doc $doc): string
    {
        $m = $doc->margins();
        $portrait = $doc->orientation !== 'landscape';
        $width = $portrait ? 210 : 297;
        $minHeight = $portrait ? 297 : 210;

        return self::fontFaceCss() . <<<CSS
.d-stage { background:#e9ecef; padding:16px; overflow:auto; }
.d-sheet {
    width: {$width}mm;
    min-height: {$minHeight}mm;
    padding: {$m['top']}mm {$m['right']}mm {$m['bottom']}mm {$m['left']}mm;
    margin: 0 auto;
    background:#fff;
    box-shadow: 0 2px 10px rgba(0,0,0,.18);
    box-sizing: border-box;
}
/* กันภาพที่ผู้ใช้วางมาจากที่อื่นล้นขอบกระดาษ */
.d-sheet img { max-width:100%; }
/* ให้เห็นว่ากำลังแก้ช่องไหนอยู่ — contenteditable ไม่มีสัญญาณนี้ให้เอง */
.d-sheet[contenteditable="true"]:focus { outline:none; }
.d-sheet[contenteditable="true"] td:hover { background-color: rgba(13,110,253,.05); }
.d-sheet:has(.d-doc-page) {
    width:auto;
    min-height:0;
    padding:0;
    background:transparent;
    box-shadow:none;
}
.d-sheet .d-doc-page {
    width:{$width}mm;
    min-height:{$minHeight}mm;
    padding:{$m['top']}mm {$m['right']}mm {$m['bottom']}mm {$m['left']}mm;
    margin:0 auto 16px;
    background:#fff;
    box-shadow:0 2px 10px rgba(0,0,0,.18);
    box-sizing:border-box;
}
.d-sheet:has(.d-doc-page) .d-page-break { display:none; }
CSS;
    }

    /**
     * สร้าง PDF ด้วย mPDF
     *
     * ค่า config ชุดฟอนต์ไทยถอดมาจาก modules/am/controllers/AssetController.php
     * ซึ่งใช้งานได้จริงอยู่แล้ว (มี font cache ใน runtime/mpdf ยืนยัน) ที่นั่นเขียน
     * ชุดเดียวกันซ้ำสองรอบในไฟล์เดียว ที่นี่จึงรวบไว้ในเมธอดเดียวเพื่อไม่เพิ่มสำเนาที่สาม
     *
     * @return string ข้อมูลไบนารีของไฟล์ PDF
     */
    public static function pdf(Doc $doc): string
    {
        $m = $doc->margins();

        $fontPath = Yii::getAlias('@webroot/fonts/THSarabunNew');
        $fontDirs = (new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'];
        $fontData = (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'];

        $tmpDir = Yii::getAlias('@runtime/mpdf');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $doc->orientation === 'landscape' ? 'L' : 'P',
            'margin_left' => $m['left'],
            'margin_right' => $m['right'],
            'margin_top' => $m['top'],
            'margin_bottom' => $m['bottom'],
            'fontDir' => array_merge($fontDirs, [$fontPath]),
            'fontdata' => $fontData + self::fontData(),
            'default_font' => self::FONT_FAMILIES[0],
            // ปิดการเดาภาษาเอง ไม่งั้น mPDF สลับไปใช้ฟอนต์อื่นกลางเอกสารเมื่อเจอ
            // อักขระที่มันคิดว่าไม่ใช่ไทย แล้วบรรทัดนั้นจะสูงไม่เท่าบรรทัดอื่น
            'autoScriptToLang' => false,
            'autoLangToFont' => false,
            'tempDir' => $tmpDir,
        ]);

        $mpdf->SetTitle($doc->title);
        // เลขหน้าอยู่ที่ท้ายกระดาษ ไม่ได้อยู่ในเนื้อเอกสาร ผู้ใช้จึงลบทิ้งด้วยความเข้าใจผิดไม่ได้
        $mpdf->SetFooter('||- {PAGENO} -');
        $mpdf->WriteHTML(self::sheetCss($doc), \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML('<div class="d-sheet">' . self::body($doc, true) . '</div>', \Mpdf\HTMLParserMode::HTML_BODY);

        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }
}
