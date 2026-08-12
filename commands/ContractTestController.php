<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\WhtRate;
use app\modules\purchase\models\ContractMilestone;
use app\modules\purchase\components\ContractCalculator;
use app\modules\purchase\components\ContractWordExporter;

/**
 * ทดสอบโมดูลบริหารสัญญาผ่าน console (ใช้ตรวจก่อนทดสอบผ่านหน้าเว็บจริง)
 * เรียกด้วย: php yii contract-test
 *
 * เน้นสองเรื่องที่เป็นเงินจริงของคู่สัญญาและถูกพิมพ์ลงเอกสารที่มีลายเซ็น
 * คือค่าปรับกับภาษีหัก ณ ที่จ่าย รวมถึงจำนวนเงินเป็นตัวอักษรในร่างสัญญา
 *
 * หมายเหตุ: การรันจะกินเลขรัน auto_number ล้างได้ด้วย
 *   DELETE FROM auto_number WHERE `group` LIKE '%CT-%';
 */
class ContractTestController extends Controller
{
    public function actionIndex()
    {
        $ok = 0;
        $fail = 0;
        $check = function (string $name, bool $pass, string $detail = '') use (&$ok, &$fail) {
            $pass ? $ok++ : $fail++;
            echo ($pass ? "  OK   " : "  FAIL ") . $name . ($detail !== '' ? "  -> $detail" : '') . PHP_EOL;
        };

        echo "== 1) นับวันล่าช้า ==" . PHP_EOL;
        $check('ส่งตรงกำหนด = 0 วัน', ContractCalculator::overdueDays('2026-08-10', '2026-08-10') === 0);
        $check('ส่งก่อนกำหนด = 0 วัน', ContractCalculator::overdueDays('2026-08-10', '2026-08-05') === 0);
        $check('ส่งช้า 5 วัน', ContractCalculator::overdueDays('2026-08-10', '2026-08-15') === 5);
        $check('ข้ามเดือน', ContractCalculator::overdueDays('2026-08-30', '2026-09-02') === 3);
        $check('ไม่ระบุวัน = 0 วัน', ContractCalculator::overdueDays(null, '2026-08-15') === 0);

        echo PHP_EOL . "== 2) สูตรค่าปรับ ==" . PHP_EOL;
        // 100,000 × 0.01% × 10 วัน = 100 บาท (ตัวอย่างมาตรฐานที่ใช้อ้างกันทั่วไป)
        $check(
            '100,000 × 0.01%/วัน × 10 วัน = 100.00',
            abs(ContractCalculator::rawFine(100000, 0.01, 10) - 100.00) < 0.001,
            (string) ContractCalculator::rawFine(100000, 0.01, 10)
        );
        $check('อัตรา 0 = ไม่มีค่าปรับ', ContractCalculator::rawFine(100000, 0, 10) === 0.0);
        $check('ล่าช้า 0 วัน = ไม่มีค่าปรับ', ContractCalculator::rawFine(100000, 0.01, 0) === 0.0);

        echo PHP_EOL . "== 3) ค่าปรับทั้งสัญญา + เพดาน ==" . PHP_EOL;
        $model = new Contract([
            'thai_year' => 2569,
            'title' => '[ทดสอบ] จ้างปรับปรุงระบบไฟฟ้าอาคารผู้ป่วยนอก',
            'contract_type' => Contract::TYPE_HIRE,
            'party_type' => WhtRate::PARTY_JURISTIC,
            'vendor_name' => 'ห้างหุ้นส่วนจำกัดทดสอบระบบ',
            'budget' => 500000,
            'vat_included' => 1,
            'sign_date' => '2026-06-01',
            'end_date' => '2026-07-01',
            'delivery_date' => '2026-07-21',   // ล่าช้า 20 วัน
            'fine_rate' => 0.1,
            'fine_base' => Contract::FINE_BASE_CONTRACT,
            'status' => Contract::STATUS_RECEIVED,
        ]);

        $fine = ContractCalculator::fine($model);
        $check('นับวันล่าช้าได้ 20 วัน', $fine['days'] === 20, (string) $fine['days']);
        // 500,000 × 0.1% × 20 = 10,000
        $check('ค่าปรับ 10,000.00', abs($fine['amount'] - 10000) < 0.01, (string) $fine['amount']);
        $check('ยังไม่ชนเพดาน', $fine['capped'] === false);
        $check('สัดส่วน 2% ของวงเงิน', abs(ContractCalculator::fineRatio($fine['amount'], 500000) - 2.0) < 0.01);

        // ล่าช้านานจนค่าปรับเกินวงเงิน ต้องถูกจำกัดไว้เท่าวงเงิน
        $model->delivery_date = '2029-07-01';
        $capped = ContractCalculator::fine($model);
        $check('ค่าปรับดิบเกินวงเงิน', $capped['raw'] > 500000, number_format($capped['raw'], 2));
        $check('ถูกจำกัดไว้เท่าวงเงิน 500,000', abs($capped['amount'] - 500000) < 0.01, (string) $capped['amount']);
        $check('ตั้งธงว่าชนเพดานแล้ว', $capped['capped'] === true);
        $model->delivery_date = '2026-07-21';

        echo PHP_EOL . "== 4) ค่าปรับแบบรายงวด ==" . PHP_EOL;
        // สองงวด งวดแรกส่งตรงเวลา งวดสองช้า 10 วัน
        // ฐานรายงวดต้องคิดจากวงเงินงวดที่ล่าช้า (200,000) ไม่ใช่ทั้งสัญญา (500,000)
        $milestones = [
            new ContractMilestone([
                'seq' => 1,
                'amount' => 300000,
                'due_date' => '2026-06-15',
                'delivered_date' => '2026-06-15',
            ]),
            new ContractMilestone([
                'seq' => 2,
                'amount' => 200000,
                'due_date' => '2026-07-01',
                'delivered_date' => '2026-07-11',
            ]),
        ];
        $model->fine_base = Contract::FINE_BASE_MILESTONE;
        $byMs = ContractCalculator::fine($model, $milestones);

        $check('งวดที่ส่งตรงเวลาไม่มีค่าปรับ', abs($byMs['per_milestone'][1]['amount']) < 0.001);
        // 200,000 × 0.1% × 10 = 2,000
        $check('งวดที่ล่าช้าปรับ 2,000.00', abs($byMs['per_milestone'][2]['amount'] - 2000) < 0.01, (string) $byMs['per_milestone'][2]['amount']);
        $check('รวมค่าปรับ 2,000.00', abs($byMs['amount'] - 2000) < 0.01, (string) $byMs['amount']);
        $check('รายงานวันล่าช้าเป็นงวดที่ช้าที่สุด', $byMs['days'] === 10, (string) $byMs['days']);
        $check(
            'ฐานรายงวดให้ผลต่างจากฐานทั้งสัญญา',
            abs($byMs['amount'] - 10000) > 0.01,
            'รายงวด ' . $byMs['amount'] . ' vs ทั้งสัญญา 10000'
        );
        $model->fine_base = Contract::FINE_BASE_CONTRACT;

        echo PHP_EOL . "== 5) ภาษีหัก ณ ที่จ่าย ==" . PHP_EOL;
        $rate = WhtRate::findRate(Contract::TYPE_HIRE, WhtRate::PARTY_JURISTIC);
        $check('มีอัตราในทะเบียนแล้ว', $rate !== null, $rate ? $rate->rate . '%' : 'ไม่พบ — ต้องรัน migration ก่อน');

        if ($rate === null) {
            echo PHP_EOL . "สรุป: ผ่าน $ok / ไม่ผ่าน $fail" . PHP_EOL;
            return 1;
        }

        // วงเงินรวม VAT 107,000 -> ฐาน 100,000 -> ภาษี 1% = 1,000
        $wht = ContractCalculator::wht(107000, Contract::TYPE_HIRE, WhtRate::PARTY_JURISTIC, true);
        $check('ถอด VAT ได้ฐาน 100,000.00', abs($wht['base'] - 100000) < 0.01, (string) $wht['base']);
        $check('ภาษี 1% = 1,000.00', abs($wht['amount'] - 1000) < 0.01, (string) $wht['amount']);

        $whtNoVat = ContractCalculator::wht(100000, Contract::TYPE_HIRE, WhtRate::PARTY_JURISTIC, false);
        $check('ไม่รวม VAT ใช้ฐานเต็ม', abs($whtNoVat['base'] - 100000) < 0.01, (string) $whtNoVat['base']);

        // ต่ำกว่าเกณฑ์ 500 บาท ต้องไม่หัก
        $whtLow = ContractCalculator::wht(400, Contract::TYPE_HIRE, WhtRate::PARTY_JURISTIC, true);
        $check('ยอดต่ำกว่าเกณฑ์นิติบุคคลไม่หัก', abs($whtLow['amount']) < 0.001, (string) $whtLow['amount']);

        // บุคคลธรรมดาเกณฑ์ 10,000 — 9,000 ยังไม่ถึง
        $whtPerson = ContractCalculator::wht(9000, Contract::TYPE_HIRE, WhtRate::PARTY_PERSONAL, true);
        $check('ยอดต่ำกว่าเกณฑ์บุคคลธรรมดาไม่หัก', abs($whtPerson['amount']) < 0.001, (string) $whtPerson['amount']);

        // ไม่มีอัตราตั้งไว้ ต้องคืน null ไม่ใช่เดาเป็น 0%
        $whtNone = ContractCalculator::wht(100000, 'ไม่มีประเภทนี้', WhtRate::PARTY_JURISTIC, true);
        $check('ไม่มีอัตราตั้งไว้ คืน rate = null', $whtNone['rate'] === null);

        echo PHP_EOL . "== 6) จำนวนเงินเป็นตัวอักษร ==" . PHP_EOL;
        $bahtCases = [
            [0, 'ศูนย์บาทถ้วน'],
            [1, 'หนึ่งบาทถ้วน'],
            [10, 'สิบบาทถ้วน'],
            [11, 'สิบเอ็ดบาทถ้วน'],
            [21, 'ยี่สิบเอ็ดบาทถ้วน'],
            [100, 'หนึ่งร้อยบาทถ้วน'],
            [101, 'หนึ่งร้อยเอ็ดบาทถ้วน'],
            [500000, 'ห้าแสนบาทถ้วน'],
            [1000000, 'หนึ่งล้านบาทถ้วน'],
            [1234567, 'หนึ่งล้านสองแสนสามหมื่นสี่พันห้าร้อยหกสิบเจ็ดบาทถ้วน'],
            [107000.50, 'หนึ่งแสนเจ็ดพันบาทห้าสิบสตางค์'],
        ];
        foreach ($bahtCases as [$value, $expect]) {
            $actual = ContractWordExporter::bahtText((float) $value);
            $check(number_format($value, 2) . ' = ' . $expect, $actual === $expect, $actual);
        }

        echo PHP_EOL . "== 7) บันทึกลงฐานข้อมูล ==" . PHP_EOL;
        $model->extra_term = '<p>ผู้รับจ้างต้องรับประกันผลงาน 2 ปี</p><script>alert(1)</script>'
            . '<img src=x onerror="alert(2)">';
        $saved = $model->save();
        $check('บันทึกสัญญาสำเร็จ', $saved, $saved ? '' : json_encode($model->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        if (!$saved) {
            echo PHP_EOL . "สรุป: ผ่าน $ok / ไม่ผ่าน $fail" . PHP_EOL;
            return 1;
        }

        $check('สร้างเลขที่เอกสารอัตโนมัติ', !empty($model->doc_no), (string) $model->doc_no);
        $check('ตัด <script> ออกแล้ว', strpos($model->extra_term, '<script') === false, $model->extra_term);
        $check('ตัด onerror ออกแล้ว', stripos($model->extra_term, 'onerror') === false);
        $check('เก็บเนื้อความที่ถูกต้องไว้', mb_strpos($model->extra_term, 'รับประกันผลงาน 2 ปี') !== false);
        $check('เขียนภาษีลงคอลัมน์ตอนบันทึก', (float) $model->wht_amount > 0, (string) $model->wht_amount);

        // วันครบกำหนดมาก่อนวันลงนาม ต้องไม่ผ่าน validation
        $bad = new Contract([
            'thai_year' => 2569,
            'title' => '[ทดสอบ] สัญญาวันที่ผิดลำดับ',
            'budget' => 1000,
            'sign_date' => '2026-07-01',
            'end_date' => '2026-06-01',
        ]);
        $check('กันวันครบกำหนดมาก่อนวันลงนาม', !$bad->validate(), json_encode($bad->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        echo PHP_EOL . "== 8) งวดงานและการเขียนค่าปรับกลับ ==" . PHP_EOL;
        foreach ($milestones as $ms) {
            $ms->contract_id = $model->id;
            $ms->save();
        }
        $model->refresh();
        $check('บันทึกงวดงาน 2 งวด', count($model->milestones) === 2, 'พบ ' . count($model->milestones));

        $model->refreshFine();
        $check('เขียนค่าปรับลงหัวสัญญา', abs((float) $model->fine_amount - 10000) < 0.01, (string) $model->fine_amount);
        $check('เขียนจำนวนวันล่าช้า', (int) $model->fine_days === 20, (string) $model->fine_days);

        echo PHP_EOL . "== 9) ส่งออก Word ==" . PHP_EOL;
        $tmp = Yii::getAlias('@runtime') . '/contract_test.docx';
        foreach (
            [
                ['ร่างสัญญา', fn() => ContractWordExporter::buildContract($model), [
                    'ห้างหุ้นส่วนจำกัดทดสอบระบบ',
                    'ห้าแสนบาทถ้วน',
                    'ผู้รับจ้างต้องรับประกันผลงาน',
                    'มิใช่แบบสัญญามาตรฐาน',
                ]],
                ['หนังสือแจ้งค่าปรับ', fn() => ContractWordExporter::buildFineNotice($model), [
                    '10,000.00',
                    'หนึ่งหมื่นบาทถ้วน',
                    '20 วัน',
                ]],
                ['ทะเบียนคุมสัญญา', fn() => ContractWordExporter::buildRegister([$model], 2569), [
                    'ทะเบียนคุมสัญญา',
                    '500,000.00',
                ]],
            ] as [$label, $builder, $expects]
        ) {
            try {
                \PhpOffice\PhpWord\IOFactory::createWriter($builder(), 'Word2007')->save($tmp);
                $size = file_exists($tmp) ? filesize($tmp) : 0;
                $check("ประกอบ$label และเขียนไฟล์ได้", $size > 0, "$size bytes");

                // อ่านเนื้อหากลับมาตรวจว่าข้อมูลจริงลงไปในเอกสาร ไม่ใช่แค่ไฟล์เปิดได้
                $zip = new \ZipArchive();
                $xml = '';
                if ($zip->open($tmp) === true) {
                    $xml = (string) $zip->getFromName('word/document.xml');
                    $zip->close();
                }
                foreach ($expects as $needle) {
                    $check("  $label มี \"$needle\"", mb_strpos($xml, $needle) !== false);
                }
                $check("  $label ไม่มีแท็ก HTML หลุดเป็นตัวอักษร", mb_strpos($xml, '&lt;p&gt;') === false);
            } catch (\Throwable $e) {
                $check("ประกอบ$label และเขียนไฟล์ได้", false, $e->getMessage());
            }
            @unlink($tmp);
        }

        echo PHP_EOL . "== 10) ล้างข้อมูลทดสอบ ==" . PHP_EOL;
        ContractMilestone::deleteAll(['contract_id' => $model->id]);
        $model->delete();
        $check('ลบข้อมูลทดสอบแล้ว', Contract::findOne($model->id) === null);

        echo PHP_EOL . "สรุป: ผ่าน $ok / ไม่ผ่าน $fail" . PHP_EOL;
        return $fail === 0 ? 0 : 1;
    }
}
