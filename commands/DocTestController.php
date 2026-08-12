<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\purchase\models\Doc;
use app\modules\purchase\models\DocTemplate;
use app\modules\purchase\models\Order;
use app\modules\purchase\components\DocMergeEngine;
use app\modules\purchase\components\DocRenderer;
use app\modules\purchase\components\DocWordExporter;

/**
 * ทดสอบโมดูลพิมพ์เอกสารผ่าน console (ใช้ตรวจก่อนทดสอบผ่านหน้าเว็บจริง)
 * เรียกด้วย: php yii doc-test
 *
 * เน้นสามเรื่องที่พังแบบเงียบ ๆ ได้ง่ายที่สุดในสายงานนี้
 *   1) ตัวแทนค่า — แท็กที่พิมพ์ผิดหรือ namespace ที่ไม่รู้จักต้องไม่กลายเป็นช่องว่าง
 *      บนกระดาษโดยไม่มีใครเห็น
 *   2) ตราครุฑ — ต้องเดินทางไป-กลับระหว่าง {{emblem}} กับ <img> ได้ครบทุกชั้น
 *      ไม่งั้นครุฑจะหายจาก PDF หลังผู้ใช้บันทึกครั้งแรก
 *   3) PDF กับ Word — ต้องออกไฟล์ที่เปิดได้จริง ไม่ใช่ไฟล์ 0 ไบต์
 *
 * ไม่แตะระบบพิมพ์เดิม (/ms-word/purchase_1..12) ทั้งสิ้น
 */
class DocTestController extends Controller
{
    public function actionIndex()
    {
        $ok = 0;
        $fail = 0;
        $check = function (string $name, bool $pass, string $detail = '') use (&$ok, &$fail) {
            $pass ? $ok++ : $fail++;
            echo ($pass ? "  OK   " : "  FAIL ") . $name . ($detail !== '' ? "  -> $detail" : '') . PHP_EOL;
        };

        echo "== 1) แม่แบบตั้งต้นจาก migration ==" . PHP_EOL;
        $template = DocTemplate::findOne(['code' => 'w804_memo_buy']);
        $check('มีแม่แบบ w804_memo_buy ในฐาน', $template !== null);
        if ($template === null) {
            echo PHP_EOL . "หยุดการทดสอบ: ยังไม่ได้รัน migrate" . PHP_EOL;
            return 1;
        }
        $check('แม่แบบเปิดใช้อยู่', (int) $template->active === 1);
        $check('ผูกกับใบขอซื้อ', $template->ref_type === DocTemplate::REF_ORDER);
        $check('มีเนื้อเอกสาร', strlen((string) $template->body_html) > 500,
            strlen((string) $template->body_html) . ' ไบต์');
        $check('มีแท็กตราครุฑ', strpos((string) $template->body_html, '{{emblem}}') !== false);
        $check('มีบล็อกวนซ้ำรายการพัสดุ',
            strpos((string) $template->body_html, '{{#items}}') !== false
            && strpos((string) $template->body_html, '{{/items}}') !== false);
        $check('ขอบกระดาษซ้ายกว้างกว่าขวา (เจาะรูเก็บแฟ้ม)',
            $template->margins()['left'] > $template->margins()['right'],
            'L=' . $template->margins()['left'] . ' R=' . $template->margins()['right']);

        echo PHP_EOL . "== 2) ตัวแทนค่า ==" . PHP_EOL;
        $payload = [
            'org' => ['company_name' => 'โรงพยาบาลทดสอบ'],
            'doc' => ['doc_no' => 'บข.001/2569', 'thai_year' => '2569'],
            'ref' => ['budget' => '12,345.00', 'vendor_name' => 'ห้างเอ แอนด์ บี'],
            'items' => [
                ['no' => '1', 'name' => 'กระดาษ A4', 'qty' => '10', 'unit' => 'รีม', 'price' => '120.00', 'amount' => '1,200.00'],
                ['no' => '2', 'name' => 'หมึกพิมพ์', 'qty' => '2', 'unit' => 'กล่อง', 'price' => '850.00', 'amount' => '1,700.00'],
            ],
        ];

        $check('แทนค่าเดี่ยวได้',
            DocMergeEngine::merge('<p>{{org.company_name}}</p>', $payload) === '<p>โรงพยาบาลทดสอบ</p>');

        $check('ค่าที่หาไม่ได้กลายเป็นจุดไข่ปลา ไม่ใช่ช่องว่าง',
            strpos(DocMergeEngine::merge('<p>{{ref.title}}</p>', $payload), DocMergeEngine::DOTS) !== false);
        // แท็กพิมพ์ผิดต้องได้ผลเหมือนกันไม่ว่าพิมพ์ผิดเป็นไทยหรืออังกฤษ
        $check('แท็กพิมพ์ผิดภาษาอังกฤษได้จุดไข่ปลา',
            strpos(DocMergeEngine::merge('<p>{{ref.budgett}}</p>', $payload), DocMergeEngine::DOTS) !== false);
        $check('แท็กพิมพ์ผิดภาษาไทยได้จุดไข่ปลาเหมือนกัน',
            strpos(DocMergeEngine::merge('<p>{{ref.ไม่มีจริง}}</p>', $payload), DocMergeEngine::DOTS) !== false,
            DocMergeEngine::merge('<p>{{ref.ไม่มีจริง}}</p>', $payload));

        // นี่คือกับดักที่ทำให้ชื่อห้างหายจากเอกสารถ้าไม่ escape
        $check('เครื่องหมาย & ในชื่อผู้ขายถูก escape',
            DocMergeEngine::merge('{{ref.vendor_name}}', $payload) === 'ห้างเอ แอนด์ บี',
            DocMergeEngine::merge('{{ref.vendor_name}}', $payload));

        $check('namespace ที่ไม่รู้จักถูกปล่อยไว้ให้ชั้นอื่นจัดการ',
            DocMergeEngine::merge('<p>{{emblem}}</p>', $payload) === '<p>{{emblem}}</p>');

        $loop = DocMergeEngine::merge('<table>{{#items}}<tr><td>{{item.no}}</td><td>{{item.name}}</td></tr>{{/items}}</table>', $payload);
        $check('บล็อกวนซ้ำสร้างครบทุกรายการ', substr_count($loop, '<tr>') === 2, substr_count($loop, '<tr>') . ' แถว');
        $check('บล็อกวนซ้ำใส่ค่าถูกรายการ',
            strpos($loop, 'กระดาษ A4') !== false && strpos($loop, 'หมึกพิมพ์') !== false);

        $blank = DocMergeEngine::merge('<table>{{#items}}<tr><td>{{item.name}}</td></tr>{{/items}}</table>',
            array_merge($payload, ['items' => []]));
        $check('ไม่มีรายการ = เหลือแถวจุดไข่ปลาให้เขียน 1 แถว',
            substr_count($blank, '<tr>') === 1 && strpos($blank, DocMergeEngine::DOTS) !== false);

        echo PHP_EOL . "== 2.5) จำนวนเงินเป็นตัวหนังสือ ==" . PHP_EOL;
        $baht = [
            [12000.00, 'หนึ่งหมื่นสองพันบาทถ้วน'],
            [0.00, 'ศูนย์บาทถ้วน'],
            [1.00, 'หนึ่งบาทถ้วน'],
            [21.00, 'ยี่สิบเอ็ดบาทถ้วน'],
            [101.50, 'หนึ่งร้อยเอ็ดบาทห้าสิบสตางค์'],
            // ปัดสตางค์ต้องไม่เพี้ยนเพราะ floating point (0.07 ต้องไม่กลายเป็น 6 สตางค์)
            [1000.07, 'หนึ่งพันบาทเจ็ดสตางค์'],
            // เศษที่ปัดขึ้นเต็มบาทต้องขยับจำนวนบาทตามด้วย ไม่ใช่ได้ "ร้อยสตางค์"
            [99.999, 'หนึ่งร้อยบาทถ้วน'],
        ];
        foreach ($baht as [$amount, $expect]) {
            $got = DocMergeEngine::bahtText((float) $amount);
            $check(number_format((float) $amount, 3) . ' = ' . $expect, $got === $expect, $got);
        }

        echo PHP_EOL . "== 3) ตราครุฑเดินทางไป-กลับ ==" . PHP_EOL;
        $check('emblem=none ได้ span เปล่า (ยังมีจุดยึดให้สลับขนาด)',
            DocRenderer::emblemHtml(DocTemplate::EMBLEM_NONE) === '<span class="d-emblem"></span>');
        $check('emblem=1.5 ได้รูปสูง 15mm',
            strpos(DocRenderer::emblemHtml(DocTemplate::EMBLEM_SMALL), 'height:15mm') !== false);
        $check('emblem=3.0 ได้รูปสูง 30mm',
            strpos(DocRenderer::emblemHtml(DocTemplate::EMBLEM_LARGE), 'height:30mm') !== false);
        $check('normalize() แปลงรูปกลับเป็น {{emblem}}',
            DocRenderer::normalize('<p>' . DocRenderer::emblemHtml(DocTemplate::EMBLEM_SMALL) . '</p>') === '<p>{{emblem}}</p>');
        $check('normalize() แปลง span เปล่ากลับได้ด้วย',
            DocRenderer::normalize('<p>' . DocRenderer::emblemHtml(DocTemplate::EMBLEM_NONE) . '</p>') === '<p>{{emblem}}</p>');
        $check('ไฟล์ตราครุฑมีอยู่จริง',
            is_file(Yii::getAlias('@webroot') . '/' . DocRenderer::EMBLEM_FILE),
            DocRenderer::EMBLEM_FILE);

        echo PHP_EOL . "== 3.5) ฟอนต์ไทยใน PDF ==" . PHP_EOL;
        // ข้อทดสอบชุดนี้เกิดจากของจริงที่พิมพ์เสีย — CSS ขอฟอนต์ชื่อหนึ่ง แต่ config
        // ลงทะเบียนอีกชื่อ ผลคือย่อหน้านอกตารางกลายเป็นกล่องสี่เหลี่ยมทั้งย่อหน้า
        // ขณะที่ข้อความในตารางยังอ่านได้ปกติ จึงดูเหมือนไฟล์ฟอนต์เสียทั้งที่ไม่ใช่
        $registered = array_keys(DocRenderer::fontData());
        $stack = DocRenderer::fontStack();
        $generic = ['sans-serif', 'serif', 'monospace', 'cursive', 'fantasy'];

        $asked = array_values(array_filter(
            array_map(
                static fn (string $f): string => strtolower(trim($f, " \t\n\r\0\x0B'\"")),
                explode(',', $stack)
            ),
            static fn (string $f): bool => $f !== '' && !in_array($f, $generic, true)
        ));

        $check('CSS ขอฟอนต์อย่างน้อยหนึ่งชื่อ', !empty($asked), $stack);
        foreach ($asked as $family) {
            $check('ฟอนต์ "' . $family . '" ที่ CSS ขอ ถูกลงทะเบียนกับ mPDF',
                in_array($family, $registered, true),
                'ลงทะเบียนไว้: ' . implode(', ', $registered));
        }
        $check('มีคำสามัญปิดท้าย font stack', strpos($stack, 'sans-serif') !== false, $stack);

        // ป้ายหัวเอกสารเคยถูกบีบจนพิมพ์เป็นตัวอักษรเรียงลงทีละบรรทัด กันไม่ให้กลับมา
        // ตัดคอมเมนต์ออกก่อนตรวจ ไม่งั้นข้อความอธิบายที่พูดถึงกับดักตัวนี้เองจะถูกนับ
        // เป็นการใช้จริง แล้วเทสต์แดงทั้งที่ CSS ถูกแก้ไปแล้ว
        $css = preg_replace(
            '#/\*.*?\*/#s',
            '',
            DocRenderer::sheetCss(new Doc(['font_size' => 14, 'orientation' => 'portrait']))
        );
        $check('CSS ไม่ใช้ width:1% ที่ mPDF บีบช่องจนตัวหนังสือตกบรรทัดทุกตัว',
            strpos($css, 'width:1%') === false);
        $check('CSS ไม่พึ่ง white-space:nowrap ที่ mPDF ไม่รู้จัก',
            strpos($css, 'white-space:nowrap') === false);
        $check('แม่แบบใช้ป้ายแคบสำหรับช่อง "วันที่"',
            strpos((string) $template->body_html, 'd-lbl-sm') !== false);

        // ความกว้างของช่องป้ายต้องพอให้คำภาษาไทยอยู่ในบรรทัดเดียว ที่ต่ำกว่า 10%
        // ของความกว้างพิมพ์ (~160 มม.) คือ 16 มม. ซึ่งไม่พอกับคำว่า "ส่วนราชการ"
        foreach (['d-lbl', 'd-lbl-sm'] as $cls) {
            preg_match('/\.' . preg_quote($cls, '/') . '\s*\{[^}]*width\s*:\s*(\d+)%/', $css, $w);
            $check('.' . $cls . ' กว้างพอให้คำไทยอยู่บรรทัดเดียว',
                isset($w[1]) && (int) $w[1] >= 10,
                isset($w[1]) ? $w[1] . '%' : 'ไม่พบ width');
        }

        $fontDir = Yii::getAlias('@webroot/fonts/THSarabunNew');
        foreach (DocRenderer::fontData()[$registered[0]] as $weight => $file) {
            $check('มีไฟล์ฟอนต์น้ำหนัก ' . $weight . ' (' . $file . ')',
                is_file($fontDir . '/' . $file));
        }

        echo PHP_EOL . "== 4) สร้างเอกสารจริงจากใบขอซื้อในระบบ ==" . PHP_EOL;
        $order = Order::find()->where(['name' => 'order', 'deleted_at' => null])->orderBy(['id' => SORT_DESC])->one();
        if ($order === null) {
            echo "  SKIP  ยังไม่มีใบขอซื้อในระบบ — ทดสอบต่อด้วยเอกสารที่ไม่ผูกเรื่อง" . PHP_EOL;
        } else {
            echo "  ใช้ใบขอซื้อ id={$order->id} " . ($order->pr_number ?: '(ไม่มีเลข PR)') . PHP_EOL;
        }

        $doc = new Doc([
            'thai_year' => 2569,
            'doc_date' => '2026-08-12',
            'doc_no' => 'บข.ทดสอบ/2569',
            'title' => 'ทดสอบบันทึกข้อความขอจัดซื้อ',
            'template_id' => $template->id,
            'template_code' => $template->code,
            'ref_type' => $order ? DocTemplate::REF_ORDER : DocTemplate::REF_NONE,
            'ref_id' => $order ? $order->id : null,
            'orientation' => $template->orientation,
            'emblem' => $template->emblem,
            'font_size' => $template->font_size,
            'margin_json' => $template->margins(),
            'status' => Doc::STATUS_DRAFT,
        ]);

        $merged = DocMergeEngine::merge($template->body_html, DocMergeEngine::payload($doc, $doc->refModel()));
        $doc->body_html = $merged;

        $check('บันทึกเอกสารลงฐานได้', $doc->save(),
            implode(' ', array_merge([], ...array_values($doc->getErrors()))));
        $check('ไม่เหลือแท็ก {{ref.*}} ที่ยังไม่ถูกแทน',
            !preg_match('/\{\{\s*(org|doc|ref|user)\./u', (string) $doc->body_html));
        $check('ไม่เหลือบล็อกวนซ้ำที่ยังไม่ถูกกาง',
            strpos((string) $doc->body_html, '{{#items}}') === false);
        // beforeSave ต้องแปลงกลับเป็น {{emblem}} ไม่ใช่เก็บ <img> ที่ชี้ URL ของเว็บ
        $check('ตราครุฑถูกเก็บเป็น {{emblem}} ในฐาน',
            strpos((string) $doc->body_html, '{{emblem}}') !== false,
            substr((string) $doc->body_html, 0, 80));
        $check('HTMLPurifier ไม่ตัด class ที่คุมรูปแบบทิ้ง',
            strpos((string) $doc->body_html, 'd-items') !== false
            && strpos((string) $doc->body_html, 'd-detail-lbl') !== false);

        echo PHP_EOL . "== 5) ออกไฟล์ PDF ==" . PHP_EOL;
        try {
            $pdf = DocRenderer::pdf($doc);
            $check('สร้าง PDF ได้', strlen($pdf) > 5000, number_format(strlen($pdf)) . ' ไบต์');
            $check('เป็นไฟล์ PDF จริง', substr($pdf, 0, 4) === '%PDF');

            // ข้อทดสอบที่จับอาการ "ตัวหนังสือเป็นกล่องสี่เหลี่ยม" ได้ตรงที่สุด
            // ถ้าชื่อฟอนต์ใน CSS ไม่ตรงกับที่ลงทะเบียน mPDF จะฝัง DejaVu ลงไปแทน
            // ซึ่งไม่มีกลิฟไทย ดูจากรายชื่อฟอนต์ที่ถูกฝังจึงรู้ได้ทันทีโดยไม่ต้องเปิดดู
            preg_match_all('#/BaseFont\s*/([A-Za-z0-9+\-_,]+)#', $pdf, $matches);
            $fonts = array_values(array_unique($matches[1]));
            $check('PDF ฝังฟอนต์ TH Sarabun', !empty(preg_grep('/Sarabun/i', $fonts)),
                implode(', ', $fonts));
            $check('PDF ไม่ถอยไปฝัง DejaVu (ฟอนต์ที่ไม่มีกลิฟไทย)',
                empty(preg_grep('/DejaVu/i', $fonts)));

            // ไม่ตรวจจำนวนหน้าเป็นเงื่อนไขผ่าน/ไม่ผ่าน — เอกสาร ว.804 เต็มฉบับมีตารางสอง
            // ตารางกับช่องลงนามสามชุด ยาวเกินหนึ่งหน้าอยู่แล้วตามเนื้อหาจริง
            preg_match_all('#/Type\s*/Page[^s]#', $pdf, $pageMatches);
            echo '  จำนวนหน้าของเอกสารตัวอย่าง: ' . count($pageMatches[0]) . ' หน้า' . PHP_EOL;
            $out = Yii::getAlias('@runtime') . '/doc-test.pdf';
            file_put_contents($out, $pdf);
            echo "  เขียนไฟล์ตัวอย่างไว้ที่ $out" . PHP_EOL;
        } catch (\Throwable $e) {
            $check('สร้าง PDF ได้', false, $e->getMessage());
        }

        echo PHP_EOL . "== 6) ออกไฟล์ Word ==" . PHP_EOL;
        try {
            $phpWord = DocWordExporter::build($doc);
            $out = Yii::getAlias('@runtime') . '/doc-test.docx';
            \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($out);
            $size = filesize($out);
            $check('สร้าง .docx ได้', $size > 5000, number_format((int) $size) . ' ไบต์');

            // .docx คือ zip — ถ้าเปิด zip ไม่ได้ Word ก็เปิดไม่ได้
            $zip = new \ZipArchive();
            $opened = $zip->open($out) === true;
            $check('เปิดเป็น zip ได้ (Word เปิดได้)', $opened);
            if ($opened) {
                $xml = $zip->getFromName('word/document.xml');

                // ตรวจว่ารูปถูกฝังเข้าแพ็กเกจจริงจากรายชื่อไฟล์ใน zip ไม่ตรวจจากชื่อแท็ก
                // เพราะ PhpWord เขียนรูปเป็น VML (<w:pict>) ไม่ใช่ DrawingML (<w:drawing>)
                // ซึ่ง Word เปิดได้เหมือนกัน การผูกเทสต์กับชื่อแท็กทำให้เทสต์แตกเวลา
                // PhpWord เปลี่ยนวิธีเขียน ทั้งที่ผลลัพธ์บนกระดาษไม่เปลี่ยน
                $media = [];
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (strpos($name, 'word/media/') === 0) {
                        $media[] = $name;
                    }
                }
                $zip->close();
                $check('มีเนื้อความภาษาไทยในเอกสาร',
                    $xml !== false && strpos($xml, 'บันทึกข้อความ') !== false);
                // ถ้า Html::addHtml() พัง ตัวส่งออกจะถอยไปเขียนข้อความล้วนแทน
                // ซึ่งยังมีเนื้อความไทยครบ จึงต้องเช็คแยกว่าไม่ได้ตกไปทางถอย
                $check('ไม่ได้ตกไปใช้ทางถอยข้อความล้วน',
                    $xml !== false && strpos($xml, 'ระบบแปลงรูปแบบเอกสารเป็น Word ไม่สำเร็จ') === false);
                $check('ตารางถูกแปลงเป็นตารางของ Word จริง',
                    $xml !== false && strpos($xml, '<w:tbl>') !== false);
                $check('ตราครุฑถูกฝังเป็นรูปในแพ็กเกจ Word', !empty($media), implode(' ', $media));
                $check('มีการอ้างรูปในเนื้อเอกสาร',
                    $xml !== false
                    && (strpos($xml, '<w:pict') !== false || strpos($xml, '<w:drawing') !== false));
            }
            echo "  เขียนไฟล์ตัวอย่างไว้ที่ $out" . PHP_EOL;
        } catch (\Throwable $e) {
            $check('สร้าง .docx ได้', false, $e->getMessage());
        }

        echo PHP_EOL . "== 6.5) การผูกกับเมนูพิมพ์เอกสารชุดเดิม ==" . PHP_EOL;
        // เมนูพิมพ์เอกสารเดิมในหน้ารายการจัดซื้อจัดจ้างเลือกทางเดินจาก legacy_key
        // ถ้าคีย์นี้หลุด เมนูจะกลับไปใช้ทาง .docx ทั้ง 13 ใบเงียบ ๆ โดยไม่มีอะไรฟ้อง
        $legacy = DocTemplate::byLegacyKey();
        $check('มีแม่แบบที่ผูกกับชุดเดิมอย่างน้อย 1 ใบ', !empty($legacy),
            'ผูกไว้: ' . implode(', ', array_keys($legacy)));
        $check('แม่แบบ purchase_3 ถูกแปลงแล้ว', isset($legacy['purchase_3']));

        if (isset($legacy['purchase_3'])) {
            $conv = $legacy['purchase_3'];
            $check('แม่แบบที่แปลงมาผูกกับใบขอซื้อ', $conv->ref_type === DocTemplate::REF_ORDER);
            $check('ไม่เหลือรูปแบบตัวแปรของ TemplateProcessor (${...})',
                !preg_match('/\$\{[a-z_]+\}/i', (string) $conv->body_html));
            $check('ใช้แท็กของตัวแทนค่าใหม่แล้ว',
                strpos((string) $conv->body_html, '{{ref.') !== false
                && strpos((string) $conv->body_html, '{{org.') !== false);

            // แท็กสามตัวที่เพิ่มมาเพื่อรองรับเอกสารชุดเดิมต้องแทนค่าได้จริง ไม่ใช่จุดไข่ปลา
            if ($order !== null) {
                $probe = new Doc([
                    'thai_year' => 2569, 'doc_date' => '2026-08-12', 'title' => 'probe',
                    'template_id' => $conv->id, 'ref_type' => DocTemplate::REF_ORDER, 'ref_id' => $order->id,
                ]);
                $payload = DocMergeEngine::payload($probe, $probe->refModel());
                foreach (['leader_name', 'leader_position', 'comment', 'department'] as $key) {
                    $check('payload มีคีย์ ref.' . $key, array_key_exists($key, $payload['ref']));
                }
            }
        }

        echo PHP_EOL . "== 6.6) ทะเบียนเอกสารชุดเดิมครบไหม ==" . PHP_EOL;
        // หน้าการ์ดกับเมนูในหน้ารายการจัดซื้อจัดจ้างอ่านรายชื่อจาก LegacyDocCatalog ที่เดียว
        // ถ้ารายชื่อหาย ผู้ใช้จะเห็นเอกสารไม่ครบตามที่มีจริง ซึ่งเป็นข้อที่ผู้ใช้ทักมาแล้วครั้งหนึ่ง
        $catalog = \app\modules\purchase\components\LegacyDocCatalog::resolved();
        $progress = \app\modules\purchase\components\LegacyDocCatalog::progress();

        $check('ทะเบียนเอกสารชุดเดิมมี 13 รายการ', count($catalog) === 13, count($catalog) . ' รายการ');
        $check('ทุกรายการมีทั้ง key และชื่อ', !array_filter($catalog, static function (array $d): bool {
            return empty($d['key']) || empty($d['label']);
        }));
        $check('นับจำนวนที่แปลงแล้วตรงกับรายการจริง',
            $progress['total'] === count($catalog) && $progress['done'] >= 1,
            $progress['done'] . '/' . $progress['total']);

        // ทุกรายการต้องมีปลายทางที่ไปได้จริง — แปลงแล้วต้องมี template ที่เปิดใช้อยู่
        // ยังไม่แปลงต้องมีไฟล์ .docx ต้นทางอยู่จริงใน web/msword
        foreach ($catalog as $doc) {
            if ($doc['converted']) {
                $check('แปลงแล้ว: ' . $doc['label'] . ' มีแม่แบบเปิดใช้',
                    $doc['template'] !== null && (int) $doc['template']->active === 1);
            } else {
                $path = Yii::getAlias('@webroot') . '/msword/' . $doc['key'] . '.docx';
                $check('ยังไม่แปลง: ' . $doc['label'] . ' ยังมีไฟล์เดิม', is_file($path));
            }
        }

        echo PHP_EOL . "== 6.7) เรนเดอร์ทุกแม่แบบกับใบขอซื้อจริง ==" . PHP_EOL;
        // ตรวจทุกแม่แบบในระบบ ไม่ใช่เฉพาะใบที่ seed ไว้ตอนแรก เพราะแม่แบบที่แปลงมาจาก
        // เอกสารชุดเดิมมีบล็อกกรรมการและแท็กเฉพาะใบ ซึ่งพังได้ทีละใบโดยใบอื่นยังปกติ
        if ($order === null) {
            echo "  SKIP  ยังไม่มีใบขอซื้อในระบบ" . PHP_EOL;
        } else {
            foreach (DocTemplate::find()->orderBy(['sort_order' => SORT_ASC])->all() as $tpl) {
                $probe = new Doc([
                    'thai_year' => 2569,
                    'doc_date' => date('Y-m-d'),
                    'title' => 'probe ' . $tpl->code,
                    'template_id' => $tpl->id,
                    'template_code' => $tpl->code,
                    'ref_type' => $tpl->ref_type,
                    'ref_id' => $tpl->ref_type === DocTemplate::REF_ORDER ? $order->id : null,
                    'orientation' => $tpl->orientation,
                    'emblem' => $tpl->emblem,
                    'font_size' => $tpl->font_size,
                    'margin_json' => $tpl->margins(),
                ]);
                $probe->body_html = DocMergeEngine::merge(
                    $tpl->body_html,
                    DocMergeEngine::payload($probe, $probe->refModel())
                );

                // {{emblem}} ถูกปล่อยไว้ให้ชั้นเรนเดอร์แทน ที่เหลือต้องถูกแทนหมด
                $leftover = [];
                if (preg_match_all('/\{\{[#\/]?(?!emblem\}\})[^}]{0,60}\}\}/u', $probe->body_html, $m)) {
                    $leftover = array_unique($m[0]);
                }

                // ห้ามใช้ชื่อ $ok ที่นี่ — closure $check จับตัวนับ $ok ไว้ด้วย reference
                // การเขียนทับจะทำให้ยอดสรุปท้ายรายงานผิด (เคยเกิดแล้ว รายงานว่าผ่าน 1 ข้อ
                // ทั้งที่ผ่านหมด) ปัญหานี้ไม่มีอะไรฟ้อง เพราะทุกบรรทัดยังขึ้น OK ตามปกติ
                $pass = empty($leftover) && $probe->save();
                if ($pass) {
                    try {
                        $pdf = DocRenderer::pdf($probe);
                        $pass = substr($pdf, 0, 4) === '%PDF'
                            && !preg_match('#/BaseFont\s*/[A-Za-z0-9+]*DejaVu#', $pdf);
                        if (!$pass) {
                            $leftover[] = 'PDF ผิดรูปหรือฟอนต์ไทยหลุด';
                        }
                    } catch (\Throwable $e) {
                        $pass = false;
                        $leftover[] = 'PDF: ' . $e->getMessage();
                    }
                    Doc::deleteAll(['id' => $probe->id]);
                }

                $check($tpl->name, $pass, implode(' ', $leftover));
            }
        }

        echo PHP_EOL . "== 7) เก็บกวาดข้อมูลทดสอบ ==" . PHP_EOL;
        $deleted = Doc::deleteAll(['title' => 'ทดสอบบันทึกข้อความขอจัดซื้อ']);
        echo "  ลบเอกสารทดสอบ $deleted แถว" . PHP_EOL;

        echo PHP_EOL . str_repeat('=', 52) . PHP_EOL;
        echo "ผ่าน $ok ข้อ / ไม่ผ่าน $fail ข้อ" . PHP_EOL;

        return $fail === 0 ? 0 : 1;
    }
}
