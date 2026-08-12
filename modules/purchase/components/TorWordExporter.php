<?php

namespace app\modules\purchase\components;

use Yii;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use app\components\AppHelper;
use app\modules\purchase\models\Tor;

/**
 * ส่งออกเอกสาร TOR เป็นไฟล์ Word (.docx)
 *
 * ทำไมไม่ใช้ TemplateProcessor เหมือนเอกสารอื่นในโมดูลนี้:
 * TemplateProcessor::setValue() แทนค่าได้เฉพาะข้อความล้วน แต่เนื้อความของ TOR
 * (คุณลักษณะ/เงื่อนไข) ผู้ใช้จัดรูปแบบเองได้และเก็บเป็น HTML ถ้าใช้ setValue
 * แท็ก HTML จะโผล่เป็นตัวอักษรในเอกสาร จึงต้องประกอบเอกสารจากโค้ดด้วย
 * Html::addHtml() ซึ่งแปลงแท็กที่อนุญาตไว้ใน Tor::ALLOWED_HTML ได้ครบ
 */
class TorWordExporter
{
    /** ฟอนต์ราชการตามระเบียบงานสารบรรณ */
    const FONT = 'TH SarabunPSK';
    const FONT_SIZE = 16;

    /** ส่งไฟล์ให้ผู้ใช้ดาวน์โหลด */
    public static function send(Tor $model)
    {
        $filename = 'TOR_' . preg_replace('/[\\\\\/:*?"<>|]/u', '', $model->title) . '.docx';
        $tmp = Yii::getAlias('@runtime') . '/' . uniqid('tor_', true) . '.docx';
        IOFactory::createWriter(self::build($model), 'Word2007')->save($tmp);

        return Yii::$app->response->sendFile($tmp, $filename, [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tmp) {
            @unlink($tmp);
        });
    }

    /** ประกอบเอกสาร (แยกจาก send เพื่อให้ทดสอบได้โดยไม่ต้องมี response ของเว็บ) */
    public static function build(Tor $model): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(self::FONT_SIZE);

        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER]);

        $section = $phpWord->addSection([
            'marginTop' => 1134,      // 2 ซม.
            'marginBottom' => 1134,
            'marginLeft' => 1701,     // 3 ซม.
            'marginRight' => 1134,
        ]);

        $agency = self::agencyName();

        // ── หัวเอกสาร ────────────────────────────────────────────────────────
        $section->addText($agency, ['bold' => true, 'size' => 18], ['alignment' => Jc::CENTER]);
        $section->addText(
            'เอกสารข้อกำหนดขอบเขตงาน / รายละเอียดคุณลักษณะเฉพาะ (TOR)',
            ['bold' => true, 'size' => 18],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
        );
        $section->addText($model->title, ['bold' => true, 'size' => 17], ['alignment' => Jc::CENTER, 'spaceAfter' => 160]);

        $section->addText(
            'เลขที่ ' . ($model->doc_no ?: '-')
                . '     วันที่ ' . self::thaiDate($model->tor_date)
                . '     ปีงบประมาณ ' . $model->thai_year,
            [],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 160]
        );

        // ── ข้อ 1 ข้อมูลทั่วไป ───────────────────────────────────────────────
        self::heading($section, 'ข้อ 1  ข้อมูลทั่วไป');
        $table = self::table($section);
        self::row($table, 'ชื่อโครงการ/รายการ', $model->title);
        self::row($table, 'ประเภทพัสดุ', $model->assetTypeName());
        self::row($table, 'วิธีจัดซื้อจัดจ้าง', $model->purchaseMethodName());
        self::row($table, 'จำนวน', self::qtyText($model));
        self::row($table, 'วงเงินงบประมาณ', number_format((float) $model->budget, 2) . ' บาท');
        self::row($table, 'เลขที่โครงการ e-GP', $model->egp_no ?: '-');
        self::rowHtml($table, 'วัตถุประสงค์และความจำเป็น', $model->purpose);

        // ── ข้อ 2 คุณลักษณะเฉพาะ ────────────────────────────────────────────
        self::heading($section, 'ข้อ 2  คุณลักษณะเฉพาะ (Specification)');
        self::addHtml($section, $model->spec);

        // ── ข้อ 3 มาตรฐานและการรับประกัน ────────────────────────────────────
        self::heading($section, 'ข้อ 3  มาตรฐานและการรับประกัน');
        $table = self::table($section);
        self::rowHtml($table, 'มาตรฐาน/การรับรองคุณภาพ', $model->standard);
        self::rowHtml($table, 'เงื่อนไขการรับประกัน', $model->warranty);

        // ── ข้อ 4 เงื่อนไขการส่งมอบและชำระเงิน ──────────────────────────────
        self::heading($section, 'ข้อ 4  เงื่อนไขการส่งมอบและการชำระเงิน');
        $table = self::table($section);
        self::row(
            $table,
            'ระยะเวลาส่งมอบ',
            $model->delivery_days ? $model->delivery_days . ' วันทำการ นับถัดจากวันลงนามในสัญญา/ใบสั่งซื้อ' : '-'
        );
        self::row($table, 'สถานที่ส่งมอบ', $model->delivery_place ?: $agency);
        self::rowHtml($table, 'เงื่อนไขการส่งมอบ', $model->delivery_term);
        self::rowHtml($table, 'เงื่อนไขการชำระเงิน', $model->payment_term);
        self::rowHtml($table, 'คุณสมบัติผู้เสนอราคา', $model->vendor_qualification);

        // ── ข้อ 5 รายงานราคากลาง ────────────────────────────────────────────
        self::heading($section, 'ข้อ 5  รายงานผลการสืบราคาและการกำหนดราคากลาง');

        $priceTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
        $priceTable->addRow();
        foreach ([['ที่', 6], ['ชื่อผู้เสนอราคา/แหล่งอ้างอิง', 34], ['รายละเอียดที่เสนอ', 38], ['ราคา (บาท)', 22]] as [$label, $w]) {
            $priceTable->addCell($w * 50, ['bgColor' => 'D9E1F2'])
                ->addText($label, ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        $prices = $model->prices;
        if ($prices) {
            foreach ($prices as $i => $p) {
                $priceTable->addRow();
                $priceTable->addCell(6 * 50)->addText((string) ($i + 1), [], ['alignment' => Jc::CENTER]);
                $priceTable->addCell(34 * 50)->addText($p->displayName());
                $priceTable->addCell(38 * 50)->addText($p->detail ?: '-');
                $priceTable->addCell(22 * 50)
                    ->addText(number_format((float) $p->price, 2), [], ['alignment' => Jc::END]);
            }
        } else {
            $priceTable->addRow();
            $priceTable->addCell(100 * 50, ['gridSpan' => 4])
                ->addText('ยังไม่ได้บันทึกผลการสืบราคา', [], ['alignment' => Jc::CENTER]);
        }

        $priceTable->addRow();
        $priceTable->addCell(78 * 50, ['gridSpan' => 3, 'bgColor' => 'D9E1F2'])
            ->addText('ราคากลาง (' . ($model->mid_method ?: '-') . ')', ['bold' => true], ['alignment' => Jc::END]);
        $priceTable->addCell(22 * 50, ['bgColor' => 'D9E1F2'])
            ->addText(number_format((float) $model->mid_price, 2), ['bold' => true], ['alignment' => Jc::END]);

        if (!empty($model->mid_note)) {
            $section->addText('หมายเหตุ: ' . $model->mid_note, ['size' => 15], ['spaceBefore' => 80]);
        }

        // จำนวนแหล่งที่สืบราคาได้จริงต่ำกว่าเกณฑ์ ให้ระบุไว้ในเอกสารตรง ๆ
        // ผู้ตรวจจะได้เห็นทันทีว่าเอกสารฉบับนี้ยังสืบราคาไม่ครบ ไม่ใช่ปล่อยให้ดูเหมือนครบ
        $sources = $model->countPriceSources();
        if ($sources < 3) {
            $section->addText(
                'หมายเหตุ: เอกสารฉบับนี้บันทึกผลการสืบราคาไว้ ' . $sources . ' แหล่ง '
                    . 'ซึ่งยังไม่ครบ 3 แหล่งตามที่ระเบียบกำหนด',
                ['size' => 15, 'italic' => true],
                ['spaceBefore' => 80]
            );
        }

        $section->addText(
            'เอกสารนี้จัดทำตามพระราชบัญญัติการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. 2560 '
                . 'มาตรา 7 โดยไม่ระบุยี่ห้อหรือแหล่งกำเนิดของสินค้า',
            ['size' => 15],
            ['spaceBefore' => 160]
        );

        // ── ลงนาม ───────────────────────────────────────────────────────────
        $section->addTextBreak(2);
        $sign = $section->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $sign->addRow();
        foreach (['ผู้จัดทำ', 'ผู้ตรวจสอบ', 'ผู้อนุมัติ'] as $role) {
            $cell = $sign->addCell(33 * 50);
            $cell->addText('ลงชื่อ .............................................', [], ['alignment' => Jc::CENTER]);
            $cell->addText('(.............................................)', [], ['alignment' => Jc::CENTER]);
            $cell->addText($role, [], ['alignment' => Jc::CENTER]);
        }

        return $phpWord;
    }

    /**
     * ชื่อหน่วยงานสำหรับหัวเอกสาร
     * อ่านจากทะเบียนตั้งค่าโดยตรงแทนการเรียก SiteHelper::getInfo() เพราะ getInfo()
     * ดึงโลโก้ผ่าน FileManagerHelper ซึ่งต้องมี alias @web — ใช้ไม่ได้ตอนรันจาก console
     * (เช่นงาน batch/ทดสอบ) และการออกเอกสารไม่ควรพังเพราะตั้งค่าโลโก้ไม่ครบ
     */
    private static function agencyName(): string
    {
        try {
            $site = \app\models\Categorise::findOne(['name' => 'site']);
            return (string) ($site->data_json['company_name'] ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function heading($section, string $text): void
    {
        $section->addText($text, ['bold' => true, 'size' => 17], ['spaceBefore' => 200, 'spaceAfter' => 80]);
    }

    private static function table($section)
    {
        return $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 60,
            'width' => 100 * 50,
            'unit' => 'pct',
        ]);
    }

    private static function row($table, string $label, string $value): void
    {
        $table->addRow();
        $table->addCell(30 * 50, ['bgColor' => 'D9E1F2'])->addText($label, ['bold' => true]);
        $table->addCell(70 * 50)->addText($value !== '' ? $value : '-');
    }

    /** ช่องที่เก็บ HTML — แปลงลง cell ด้วย addHtml เพื่อรักษาตัวหนา/ข้อ/ตาราง */
    private static function rowHtml($table, string $label, ?string $html): void
    {
        $table->addRow();
        $table->addCell(30 * 50, ['bgColor' => 'D9E1F2'])->addText($label, ['bold' => true]);
        $cell = $table->addCell(70 * 50);
        if (trim(strip_tags((string) $html)) === '') {
            $cell->addText('-');
            return;
        }
        self::addHtml($cell, $html);
    }

    /**
     * PhpWord รองรับ HTML เพียงชุดย่อย ถ้าเจอโครงสร้างที่แปลงไม่ได้จะโยน exception
     * ซึ่งจะทำให้ผู้ใช้ดาวน์โหลดไม่ได้ทั้งไฟล์ จึงถอยไปใส่เป็นข้อความล้วนแทน
     * เอกสารจะเสียรูปแบบบางส่วนแต่เนื้อหาไม่หาย
     */
    private static function addHtml($container, ?string $html): void
    {
        $html = (string) $html;
        if (trim(strip_tags($html)) === '') {
            $container->addText('-');
            return;
        }
        try {
            Html::addHtml($container, $html, false, false);
        } catch (\Throwable $e) {
            Yii::warning('แปลง HTML ลง Word ไม่สำเร็จ ใช้ข้อความล้วนแทน: ' . $e->getMessage(), __METHOD__);
            foreach (preg_split('/\R/', trim(strip_tags($html, '<br><p><li>'))) as $line) {
                $line = trim(html_entity_decode(strip_tags($line), ENT_QUOTES, 'UTF-8'));
                if ($line !== '') {
                    $container->addText($line);
                }
            }
        }
    }

    private static function qtyText(Tor $model): string
    {
        if ($model->qty === null || (float) $model->qty <= 0) {
            return '-';
        }
        $qty = rtrim(rtrim(number_format((float) $model->qty, 2), '0'), '.');
        return $qty . ' ' . ($model->unit_name ?: '');
    }

    private static function thaiDate(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }
        try {
            return AppHelper::convertToThai($date);
        } catch (\Throwable $e) {
            return $date;
        }
    }
}
