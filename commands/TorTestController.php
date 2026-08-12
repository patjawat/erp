<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\purchase\models\Tor;
use app\modules\purchase\models\TorPrice;
use app\modules\purchase\models\TorTemplate;

/**
 * ทดสอบโมดูล TOR ผ่าน console (ชั่วคราว — ใช้ตรวจก่อนทดสอบผ่านหน้าเว็บจริง)
 * เรียกด้วย: php yii tor-test
 */
class TorTestController extends Controller
{
    public function actionIndex()
    {
        $ok = 0;
        $fail = 0;
        $check = function (string $name, bool $pass, string $detail = '') use (&$ok, &$fail) {
            $pass ? $ok++ : $fail++;
            echo ($pass ? "  OK   " : "  FAIL ") . $name . ($detail !== '' ? "  -> $detail" : '') . PHP_EOL;
        };

        echo "== 1) คลังแม่แบบ ==" . PHP_EOL;
        $total = TorTemplate::find()->count();
        $check('มีแม่แบบครบ 61 รายการ', (int) $total === 61, "พบ $total");
        $cats = TorTemplate::activeCategories();
        $check('แบ่งหมวดได้ 14 หมวด', count($cats) === 14, 'พบ ' . count($cats));
        $brand = TorTemplate::find()
            ->where(['or like', 'spec', ['Windows', 'Android', 'iOS', 'Intel', 'AMD', 'Dell', 'Lenovo'], false])
            ->count();
        $check('ไม่มียี่ห้อค้างในคุณลักษณะ', (int) $brand === 0, "พบ $brand");
        $sample = TorTemplate::findOne(['code' => 'TPL-COMPUTER-01']);
        $check('คุณลักษณะแปลงเป็น <ol><li> แล้ว', $sample && strpos($sample->spec, '<ol>') === 0);

        echo PHP_EOL . "== 2) บันทึก TOR + กรอง HTML ==" . PHP_EOL;
        $model = new Tor([
            'thai_year' => 2569,
            'title' => '[ทดสอบ] จัดซื้อเครื่องคอมพิวเตอร์สำหรับงานสำนักงาน',
            'asset_type_id' => null,
            'budget' => 480000,
            'qty' => 20,
            'unit_name' => 'เครื่อง',
            'delivery_days' => 45,
            'mid_method' => 'ค่าเฉลี่ยของราคาที่สืบได้',
            // สคริปต์ปนมากับเนื้อหา ต้องถูกตัดทิ้งโดย HtmlPurifier
            'spec' => '<ol><li>หน่วยประมวลผลไม่น้อยกว่า 4 แกน</li></ol><script>alert(1)</script>'
                . '<img src=x onerror="alert(2)">',
            'purpose' => '<p>เพื่อใช้ในงานสำนักงาน</p><a href="javascript:alert(3)">คลิก</a>',
        ]);
        $saved = $model->save();
        $check('บันทึกหัวเอกสารสำเร็จ', $saved, $saved ? '' : json_encode($model->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        if (!$saved) {
            echo PHP_EOL . "สรุป: ผ่าน $ok / ไม่ผ่าน $fail" . PHP_EOL;
            return 1;
        }

        $check('ตัด <script> ออกแล้ว', strpos($model->spec, '<script') === false, $model->spec);
        $check('ตัด onerror ออกแล้ว', stripos($model->spec, 'onerror') === false);
        $check('ตัด javascript: ออกแล้ว', stripos($model->purpose, 'javascript:') === false, $model->purpose);
        $check('เก็บ <ol><li> ที่ถูกต้องไว้', strpos($model->spec, '<li>') !== false);
        $check('สร้างเลขที่เอกสารอัตโนมัติ', !empty($model->doc_no), (string) $model->doc_no);

        echo PHP_EOL . "== 3) ใบสืบราคา + ราคากลาง ==" . PHP_EOL;
        foreach ([['ร้าน ก', 23900], ['ร้าน ข', 24500], ['ร้าน ค', 23500]] as $i => [$name, $price]) {
            (new TorPrice([
                'tor_id' => $model->id,
                'seq' => $i + 1,
                'vendor_name' => $name,
                'detail' => 'ตัวเลือกที่ ' . ($i + 1),
                'price' => $price,
            ]))->save();
        }
        $model->refresh();
        $check('บันทึกใบสืบราคา 3 แถว', count($model->prices) === 3, 'พบ ' . count($model->prices));
        $check('นับแหล่งสืบราคาได้ถูก', $model->countPriceSources() === 3);

        $avg = $model->calcMidPrice('ค่าเฉลี่ยของราคาที่สืบได้');
        $check('คำนวณค่าเฉลี่ยถูกต้อง', abs($avg - 23966.67) < 0.01, (string) $avg);
        $low = $model->calcMidPrice('ราคาต่ำสุดที่สืบได้');
        $check('คำนวณราคาต่ำสุดถูกต้อง', abs($low - 23500) < 0.01, (string) $low);

        $model->mid_price = $avg;
        $model->save(false, ['mid_price']);

        echo PHP_EOL . "== 4) ส่งออก Word ==" . PHP_EOL;
        $tmp = Yii::getAlias('@runtime') . '/tor_test.docx';
        try {
            $doc = \app\modules\purchase\components\TorWordExporter::build($model);
            \PhpOffice\PhpWord\IOFactory::createWriter($doc, 'Word2007')->save($tmp);
            $size = file_exists($tmp) ? filesize($tmp) : 0;
            $check('ประกอบเอกสาร TOR ทั้งใบและเขียนไฟล์ได้', $size > 0, "$size bytes");

            // อ่านเนื้อหาในไฟล์กลับมาตรวจว่าข้อมูลจริงลงไปในเอกสาร ไม่ใช่แค่ไฟล์เปิดได้
            $zip = new \ZipArchive();
            $xml = '';
            if ($zip->open($tmp) === true) {
                $xml = (string) $zip->getFromName('word/document.xml');
                $zip->close();
            }
            $check('ชื่อโครงการอยู่ในเอกสาร', mb_strpos($xml, 'จัดซื้อเครื่องคอมพิวเตอร์') !== false);
            $check('คุณลักษณะอยู่ในเอกสาร', mb_strpos($xml, 'หน่วยประมวลผลไม่น้อยกว่า') !== false);
            $check('ตารางสืบราคาอยู่ในเอกสาร', mb_strpos($xml, 'ร้าน ก') !== false);
            $check('ราคากลางอยู่ในเอกสาร', mb_strpos($xml, '23,966.67') !== false);
            $check('ไม่มีแท็ก HTML หลุดเป็นตัวอักษร', mb_strpos($xml, '&lt;li&gt;') === false);
        } catch (\Throwable $e) {
            $check('ประกอบเอกสาร TOR ทั้งใบและเขียนไฟล์ได้', false, $e->getMessage());
        }
        @unlink($tmp);

        echo PHP_EOL . "== 5) ล้างข้อมูลทดสอบ ==" . PHP_EOL;
        TorPrice::deleteAll(['tor_id' => $model->id]);
        $model->delete();
        $check('ลบข้อมูลทดสอบแล้ว', Tor::findOne($model->id) === null);

        echo PHP_EOL . "สรุป: ผ่าน $ok / ไม่ผ่าน $fail" . PHP_EOL;
        return $fail === 0 ? 0 : 1;
    }
}
