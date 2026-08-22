<?php

namespace app\modules\purchase\components;

use Yii;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocTemplate;

/**
 * ส่งออกเอกสารเป็นไฟล์ Word (.docx)
 *
 * ใช้ Html::addHtml() ด้วยเหตุผลเดียวกับ TorWordExporter — เนื้อเอกสารเป็น HTML
 * ที่ผู้ใช้จัดรูปแบบเองได้ ถ้าใช้ TemplateProcessor::setValue() แท็ก HTML จะโผล่
 * เป็นตัวอักษรบนกระดาษ
 *
 * ข้อจำกัดที่ต้องรู้ก่อนใช้ไฟล์นี้เป็นฉบับจริง
 *
 * Html::addHtml() ไม่อ่าน CSS ที่อยู่ใน stylesheet — มันอ่านแต่ style ที่เขียนติด
 * มากับแท็ก เอกสารของเราจัดรูปแบบด้วย class ทั้งฉบับ (ดู DocRenderer::sheetCss)
 * ถ้าส่ง HTML เข้าไปตรง ๆ จะได้เอกสารที่ไม่มีเส้นตาราง ไม่มีตัวหนา และความกว้าง
 * คอลัมน์เท่ากันหมด จึงต้องแปลง class เป็น inline style ก่อนเสมอ — inlineStyles()
 * ทำหน้าที่นั้น และเป็นสาเหตุที่ .docx จะเหมือนต้นฉบับ "ใกล้เคียง" ไม่ใช่ "เป๊ะ"
 * ทางที่คุมรูปได้แน่นอนคือ PDF (DocRenderer::pdf) — .docx มีไว้ให้เอาไปแก้ต่อใน Word
 */
class DocWordExporter
{
    /** ฟอนต์ราชการตามระเบียบงานสารบรรณ (ชุดเดียวกับ exporter อื่นในโมดูลนี้) */
    const FONT = 'TH SarabunPSK';

    /** 1 มิลลิเมตร = 56.7 twip — PhpWord คิดระยะขอบเป็น twip */
    const MM_TO_TWIP = 56.7;

    /** ส่งไฟล์ให้ผู้ใช้ดาวน์โหลด */
    public static function send(Doc $doc)
    {
        $tmp = Yii::getAlias('@runtime') . '/' . uniqid('purchase_doc_', true) . '.docx';
        IOFactory::createWriter(self::build($doc), 'Word2007')->save($tmp);

        return Yii::$app->response->sendFile($tmp, $doc->safeFileName('docx'), [
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($tmp) {
            @unlink($tmp);
        });
    }

    public static function build(Doc $doc): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize((int) $doc->font_size);

        $m = $doc->margins();
        $section = $phpWord->addSection([
            'orientation' => $doc->orientation === 'landscape' ? 'landscape' : 'portrait',
            'marginTop' => (int) round($m['top'] * self::MM_TO_TWIP),
            'marginRight' => (int) round($m['right'] * self::MM_TO_TWIP),
            'marginBottom' => (int) round($m['bottom'] * self::MM_TO_TWIP),
            'marginLeft' => (int) round($m['left'] * self::MM_TO_TWIP),
        ]);

        $html = self::inlineStyles(
            self::bodyForWord($doc),
            (int) $doc->font_size
        );

        try {
            Html::addHtml($section, $html, false, false);
        } catch (\Throwable $e) {
            // ถ้าแปลง HTML ไม่ผ่าน ผู้ใช้ต้องยังได้ไฟล์ที่มีเนื้อความอยู่ ไม่ใช่หน้า error
            // เพราะเขากดปุ่มนี้ตอนกำลังจะส่งหนังสือ ไม่ใช่ตอนกำลังทดลองระบบ
            Yii::error('DocWordExporter: แปลง HTML เป็น Word ไม่สำเร็จ — ' . $e->getMessage(), __METHOD__);
            $section->addText(
                'ระบบแปลงรูปแบบเอกสารเป็น Word ไม่สำเร็จ จึงส่งออกเป็นข้อความล้วน'
                . ' กรุณาใช้ปุ่มพริ้นท์ (PDF) สำหรับฉบับที่ต้องใช้จริง',
                ['bold' => true]
            );
            foreach (preg_split('/\R/u', trim(strip_tags(preg_replace('/<\/(p|tr|table)>/i', "\n", $html)))) as $line) {
                if (trim($line) !== '') {
                    $section->addText(html_entity_decode($line, ENT_QUOTES, 'UTF-8'));
                }
            }
        }

        return $phpWord;
    }

    /**
     * เนื้อเอกสารพร้อมตราครุฑแบบที่ PhpWord วางได้
     *
     * PhpWord ต้องรู้ความกว้างและความสูงของรูปเป็น px จึงคำนวณจากขนาดไฟล์จริง
     * เพื่อรักษาสัดส่วน ถ้าใส่แต่ความสูงเหมือนตอนทำ PDF ครุฑจะถูกยืดจนบิด
     */
    private static function bodyForWord(Doc $doc): string
    {
        return str_replace('{{emblem}}', self::emblemForWord($doc->emblem), (string) $doc->body_html);
    }

    private static function emblemForWord(?string $emblem): string
    {
        $mm = DocRenderer::EMBLEM_MM[(string) $emblem] ?? null;
        if ($mm === null) {
            return '';
        }

        $path = Yii::getAlias('@webroot') . '/' . DocRenderer::EMBLEM_FILE;
        if (!is_file($path)) {
            return '';
        }

        // 1 มิลลิเมตร = 3.7795 px ที่ 96 dpi ซึ่งเป็นค่าที่ PhpWord ใช้แปลงกลับเป็น EMU
        $heightPx = (int) round($mm * 3.7795);
        $widthPx = $heightPx;

        $size = @getimagesize($path);
        if (is_array($size) && !empty($size[1])) {
            $widthPx = (int) round($heightPx * ($size[0] / $size[1]));
        }

        return '<img src="' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '"'
            . ' style="width:' . $widthPx . 'px;height:' . $heightPx . 'px">';
    }

    /**
     * แปลง class เป็น inline style ให้ Html::addHtml() มองเห็น
     *
     * ใช้ DOMDocument ไม่ใช่ regex เพราะต้องแก้แอตทริบิวต์ของ element ที่ซ้อนกัน
     * หลายชั้นและต้องไม่ไปแตะข้อความที่ผู้ใช้พิมพ์ซึ่งอาจมีวงเล็บเหลี่ยมหรือ
     * เครื่องหมายที่หลอก regex ได้
     */
    private static function inlineStyles(string $html, int $fontSize): string
    {
        if (trim($html) === '') {
            return '';
        }

        $border = 'border:1px solid #000000;';
        $map = [
            'd-title' => 'font-weight:bold;text-align:center;font-size:' . ($fontSize + 4) . 'pt;',
            'd-masthead-title' => 'text-align:center;',
            'd-lbl' => 'font-weight:bold;',
            'd-val' => 'border-bottom:1px solid #000000;',
            'd-to' => 'text-align:left;',
            'd-body' => 'text-align:justify;',
            'd-list' => 'text-align:justify;',
            'd-approve' => 'text-align:justify;',
            'd-caption' => 'font-weight:bold;text-align:center;',
            'd-detail-lbl' => $border . 'width:32%;',
            'd-detail-val' => $border,
            'd-c-no' => $border . 'text-align:center;width:6%;',
            'd-c-name' => $border . 'width:38%;',
            'd-c-qty' => $border . 'text-align:center;width:10%;',
            'd-c-unit' => $border . 'text-align:center;width:10%;',
            'd-c-price' => $border . 'text-align:right;width:18%;',
            'd-c-amount' => $border . 'text-align:right;width:18%;',
            'd-c-total' => $border . 'text-align:right;font-weight:bold;',
            'd-sign-cell' => 'text-align:center;width:50%;',
        ];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        // ห่อด้วย root เดียวและประกาศ encoding ไว้ข้างใน ไม่งั้น DOMDocument
        // จะเดาว่าเป็น ISO-8859-1 แล้วภาษาไทยกลายเป็นขยะทั้งฉบับ
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="d-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('d-root');
        if ($root === null) {
            return $html;
        }

        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//*[@class]') as $node) {
            /** @var \DOMElement $node */
            $add = '';
            foreach (preg_split('/\s+/', trim($node->getAttribute('class'))) as $class) {
                $add .= $map[$class] ?? '';
            }
            if ($add !== '') {
                $node->setAttribute('style', $add . $node->getAttribute('style'));
            }
        }

        // แถวหัวตารางต้องหนาและอยู่กลางช่อง แต่ style ของ tr ไม่ตกไปถึง td ใน Word
        // จึงต้องเดินไปใส่ที่ td เองทีละช่อง
        foreach ($xpath->query('//tr[contains(@class,"d-items-head")]/td') as $cell) {
            /** @var \DOMElement $cell */
            $cell->setAttribute('style', 'font-weight:bold;text-align:center;' . $cell->getAttribute('style'));
        }

        // ต้องเป็น saveXML ไม่ใช่ saveHTML — Html::addHtml() ของ PhpWord โหลดสตริง
        // ด้วย DOMDocument::loadXML() ไม่ใช่ loadHTML() จึงต้องได้ XML ที่ well-formed
        // saveHTML ปล่อย <img> โดยไม่ปิดแท็ก ทำให้ loadXML พังทั้งก้อน แล้ว PhpWord
        // ได้ body เป็น null → เอกสารที่ออกมาไม่มีตารางและไม่มีรูปเลย
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveXML($child);
        }

        return $out;
    }
}
