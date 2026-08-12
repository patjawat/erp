<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use app\modules\purchase\models\Bond;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\BondPolicy;
use app\modules\purchase\models\BondSearch;
use app\modules\purchase\components\BondCalculator;
use app\modules\purchase\components\BondWordExporter;

/**
 * ทดสอบงานหลักประกันผ่าน console (ใช้ตรวจก่อนทดสอบผ่านหน้าเว็บจริง)
 * เรียกด้วย: php yii bond-test
 *
 * เน้นสามเรื่อง
 *   1) การจับคู่เกณฑ์ตามวงเงิน โดยเฉพาะที่ "ปลายช่วง" ซึ่งเป็นจุดที่โปรแกรมต้นแบบ
 *      พูดไม่ตรงกันระหว่างป้ายบนหน้าจอกับผลลัพธ์ที่คำนวณจริง
 *   2) กติกาการบันทึกที่กันข้อมูลกำกวม เช่น ยกเว้นโดยไม่มีเหตุผล หรือคืนโดยไม่มีวันที่
 *   3) ทะเบียนคุมที่พิมพ์ออกไป ต้องมีตัวเลขตรงกับที่อยู่ในฐาน
 *
 * หมายเหตุ: การรันจะสร้างสัญญาทดสอบ 1 ฉบับเพื่อทดสอบการผูกกับหลักประกัน แล้วลบทิ้ง
 * เมื่อจบ จึงกินเลขรันทั้งของหลักประกันและของสัญญา ล้างได้ด้วย
 *   DELETE FROM auto_number WHERE `group` LIKE '%BD-%' OR `group` LIKE '%CT-%';
 */
class BondTestController extends Controller
{
    public function actionIndex()
    {
        $ok = 0;
        $fail = 0;
        $check = function (string $name, bool $pass, string $detail = '') use (&$ok, &$fail) {
            $pass ? $ok++ : $fail++;
            echo ($pass ? "  OK   " : "  FAIL ") . $name . ($detail !== '' ? "  -> $detail" : '') . PHP_EOL;
        };

        echo "== 1) ตารางเกณฑ์ต้องถูก seed แล้ว ==" . PHP_EOL;
        $policyCount = (int) BondPolicy::find()->where(['active' => 1])->count();
        $check('มีเกณฑ์ที่เปิดใช้งานอยู่', $policyCount > 0, $policyCount . ' แถว');
        if ($policyCount === 0) {
            echo PHP_EOL . 'ยังไม่ได้รัน migration m260812_000006 — ทดสอบต่อไม่ได้' . PHP_EOL;
            return 1;
        }
        $check('ป้ายเตือนว่ายังไม่ผ่านการยืนยันทำงาน', BondPolicy::needsReview());

        echo PHP_EOL . "== 2) จับคู่เกณฑ์ตามวงเงิน (การจัดซื้อ) ==" . PHP_EOL;
        $buy = function (float $amount) {
            return BondCalculator::policyFor($amount, Contract::TYPE_BUY);
        };
        $check('วงเงิน 0 = ยังบอกไม่ได้', $buy(0)['configured'] === false);
        $check('ซื้อ 50,000 = ไม่ต้องวาง', $buy(50000)['required'] === false, $buy(50000)['title']);
        $check('ซื้อ 50,000.01 = ยังไม่ต้องวาง (เข้าช่วงไม่เกินแสน)', $buy(50000.01)['required'] === false);
        // จุดที่ต้นแบบขัดแย้งกับตัวเอง — ป้ายเขียน "≥ 100,000 ต้องวาง" แต่โค้ดยกเว้นให้
        $check('ซื้อ 100,000 พอดี = ยังไม่ต้องวาง', $buy(100000)['required'] === false);
        $check('ซื้อ 100,000.01 = ต้องวาง', $buy(100000.01)['required'] === true);
        $check('อัตราที่ได้คือ 5%', abs($buy(200000)['rate'] - 5.0) < 0.001, (string) $buy(200000)['rate']);
        $check(
            'ยอดที่ต้องวางของ 200,000 = 10,000.00',
            abs($buy(200000)['amount'] - 10000) < 0.01,
            number_format($buy(200000)['amount'], 2)
        );
        $check('มีการอ้างอิงระเบียบติดมาด้วย', !empty($buy(200000)['law']), (string) $buy(200000)['law']);

        echo PHP_EOL . "== 3) งานจ้างไม่ได้รับยกเว้นชั้น 50,000 ==" . PHP_EOL;
        $hire = BondCalculator::policyFor(30000, Contract::TYPE_HIRE);
        $buy30k = $buy(30000);
        $check('จ้าง 30,000 เข้าเกณฑ์คนละแถวกับซื้อ 30,000', $hire['title'] !== $buy30k['title'], $hire['title']);
        $check('จ้าง 30,000 ยังไม่ต้องวาง (ไม่เกินแสน)', $hire['required'] === false);

        echo PHP_EOL . "== 4) สูตรคำนวณวงเงินหลักประกัน ==" . PHP_EOL;
        $check('500,000 × 5% = 25,000.00', abs(BondCalculator::suggested(500000, 5) - 25000) < 0.001);
        $check('อัตรา 0 = 0', BondCalculator::suggested(500000, 0) === 0.0);
        $check('ฐาน 0 = 0', BondCalculator::suggested(0, 5) === 0.0);
        $check('ปัดทศนิยม 2 ตำแหน่ง', abs(BondCalculator::suggested(133333.33, 5) - 6666.67) < 0.001,
            (string) BondCalculator::suggested(133333.33, 5));

        echo PHP_EOL . "== 5) อายุหลักประกัน ==" . PHP_EOL;
        $today = '2026-08-12';
        $closed = Bond::closedStatuses();
        $check('เหลือ 10 วัน', BondCalculator::daysToExpiry('2026-08-22', $today) === 10);
        $check('หมดอายุไปแล้ว 3 วัน', BondCalculator::daysToExpiry('2026-08-09', $today) === -3);
        $check('ไม่ระบุวันสิ้นอายุ = null', BondCalculator::daysToExpiry(null, $today) === null);
        $check(
            'ยังอยู่ในอายุ',
            BondCalculator::expiryState('2026-12-31', Bond::STATUS_ACTIVE, $closed, $today) === BondCalculator::STATE_OK
        );
        $check(
            'ใกล้หมดอายุ (ภายใน ' . BondCalculator::NEAR_DAYS . ' วัน)',
            BondCalculator::expiryState('2026-08-30', Bond::STATUS_ACTIVE, $closed, $today) === BondCalculator::STATE_NEAR
        );
        $check(
            'หมดอายุแล้ว',
            BondCalculator::expiryState('2026-08-01', Bond::STATUS_ACTIVE, $closed, $today) === BondCalculator::STATE_EXPIRED
        );
        // คืนไปแล้วไม่ต้องเตือนเรื่องอายุอีก เพราะของไม่ได้อยู่กับหน่วยงานแล้ว
        $check(
            'ใบที่คืนแล้วไม่ถูกนับว่าหมดอายุ',
            BondCalculator::expiryState('2026-08-01', Bond::STATUS_RETURNED, $closed, $today) === BondCalculator::STATE_NONE
        );

        echo PHP_EOL . "== 6) กติกาการบันทึก ==" . PHP_EOL;
        $base = [
            'thai_year' => 2569,
            'title' => '[ทดสอบ] ซื้อครุภัณฑ์คอมพิวเตอร์',
            'bond_type' => Bond::TYPE_CONTRACT,
            'bond_form' => Bond::FORM_BANK_GUARANTEE,
            'base_amount' => 500000,
            'rate' => 5,
            'amount' => 25000,
            'place_date' => '2026-08-01',
            'expiry_date' => '2027-08-01',
            'status' => Bond::STATUS_ACTIVE,
        ];

        $model = new Bond($base);
        $check('ใบที่กรอกครบผ่าน validation', $model->validate(), json_encode($model->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        $noAmount = new Bond(array_merge($base, ['amount' => 0]));
        $check('กันบันทึกโดยไม่มีวงเงิน', !$noAmount->validate(), json_encode($noAmount->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        $exemptNoReason = new Bond(array_merge($base, ['status' => Bond::STATUS_EXEMPT, 'amount' => 0]));
        $check('ยกเว้นต้องมีเหตุผลกำกับ', !$exemptNoReason->validate(), json_encode($exemptNoReason->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        $exempt = new Bond(array_merge($base, [
            'status' => Bond::STATUS_EXEMPT,
            'amount' => 0,
            'exempt_reason' => 'วงเงินไม่เกินเกณฑ์และตรวจรับก่อนจ่ายเงิน',
        ]));
        $check('ยกเว้นพร้อมเหตุผลผ่านได้', $exempt->validate(), json_encode($exempt->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        $badExpiry = new Bond(array_merge($base, ['expiry_date' => '2026-07-01']));
        $check('กันวันสิ้นอายุมาก่อนวันวาง', !$badExpiry->validate(), json_encode($badExpiry->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        $returnNoDate = new Bond(array_merge($base, ['status' => Bond::STATUS_RETURNED]));
        $check('คืนต้องมีวันที่คืน', !$returnNoDate->validate(), json_encode($returnNoDate->getFirstErrors(), JSON_UNESCAPED_UNICODE));

        echo PHP_EOL . "== 6.5) ชื่อฟอร์มต้องเป็นของ Yii ไม่ใช่ของเรา ==" . PHP_EOL;
        // เคยตั้งชื่อเมธอด formName() ทับของ yii\base\Model ทำให้ load() พังทั้งหน้าจอ
        // ทั้งฟอร์มบันทึกและฟอร์มค้นหา — console เดิมจับไม่ได้เพราะไม่เคยเรียก load()
        $check('Bond::formName() = "Bond"', (new Bond())->formName() === 'Bond', (new Bond())->formName());
        $check('BondSearch::formName() = "BondSearch"', (new BondSearch())->formName() === 'BondSearch');
        $check('ชื่อรูปแบบยังอ่านได้จาก bondFormName()',
            (new Bond(['bond_form' => Bond::FORM_CASH]))->bondFormName() === 'เงินสด');
        $check('bondFormName() ไม่พังเมื่อยังไม่ได้เลือกรูปแบบ',
            (new Bond(['bond_form' => null]))->bondFormName() === '');

        $loadSearch = new BondSearch();
        $loadSearch->load(['BondSearch' => ['thai_year' => 2569, 'status' => Bond::STATUS_ACTIVE]]);
        $check('ฟอร์มค้นหารับค่าเข้าได้', (int) $loadSearch->thai_year === 2569 && $loadSearch->status === Bond::STATUS_ACTIVE);

        $loadBond = new Bond();
        $loadBond->load(['Bond' => ['title' => 'ทดสอบ load', 'amount' => 1234.56, 'bond_form' => Bond::FORM_GOV_BOND]]);
        $check('ฟอร์มบันทึกรับค่าเข้าได้', $loadBond->title === 'ทดสอบ load' && (float) $loadBond->amount === 1234.56);

        // เส้นทางเดียวกับที่ BondController::actionIndex() เรียกจริง
        try {
            $provider = (new BondSearch())->search([
                'BondSearch' => ['thai_year' => 2569, 'q' => 'ทดสอบ', 'flag' => 'near'],
            ]);
            $check('หน้าทะเบียนค้นหาได้โดยไม่ error', $provider->getTotalCount() >= 0);
        } catch (\Throwable $e) {
            $check('หน้าทะเบียนค้นหาได้โดยไม่ error', false, $e->getMessage());
        }

        echo PHP_EOL . "== 7) บันทึกจริงลงฐาน ==" . PHP_EOL;
        $saved = new Bond(array_merge($base, ['note' => 'สร้างโดย bond-test']));
        $check('บันทึกได้', $saved->save(), json_encode($saved->getFirstErrors(), JSON_UNESCAPED_UNICODE));
        $check('ออกเลขที่ให้อัตโนมัติ', !empty($saved->doc_no), (string) $saved->doc_no);
        $check('สร้าง ref สำหรับแนบไฟล์', !empty($saved->ref), (string) $saved->ref);

        // ถอนสถานะคืนแล้ว ข้อมูลการคืนต้องหายไปด้วย ไม่งั้นทะเบียนจะมีใบที่ยังไม่คืน
        // แต่มีวันที่คืนติดอยู่ ซึ่งอ่านแล้วไม่รู้ว่าอันไหนจริง
        $saved->status = Bond::STATUS_RETURNED;
        $saved->return_date = '2026-09-01';
        $saved->return_doc_no = 'ลย 0032.301/1234';
        $saved->save();
        $check('บันทึกการคืนได้', $saved->status === Bond::STATUS_RETURNED && $saved->return_date === '2026-09-01');

        $saved->status = Bond::STATUS_ACTIVE;
        $saved->save();
        $saved->refresh();
        $check('ถอนสถานะคืนแล้ว วันที่คืนถูกล้าง', empty($saved->return_date), (string) $saved->return_date);
        $check('เลขที่หนังสือคืนถูกล้างด้วย', empty($saved->return_doc_no), (string) $saved->return_doc_no);

        $saved->status = Bond::STATUS_EXEMPT;
        $saved->exempt_reason = 'ทดสอบการยกเว้น';
        $saved->save();
        $saved->refresh();
        $check('ใบที่ยกเว้นถูกล้างวงเงินเป็น 0', abs((float) $saved->amount) < 0.001, (string) $saved->amount);

        $saved->status = Bond::STATUS_ACTIVE;
        $saved->amount = 25000;
        $saved->save();
        $saved->refresh();
        $check('กลับมาสถานะปกติ เหตุผลยกเว้นถูกล้าง', empty($saved->exempt_reason), (string) $saved->exempt_reason);

        echo PHP_EOL . "== 8) การรวมยอดในทะเบียน ==" . PHP_EOL;
        $search = new BondSearch(['thai_year' => 2569]);
        $counters = $search->counters();
        $check('นับใบในทะเบียนได้', $counters['total'] > 0, (string) $counters['total']);
        $check('ยอดรวมนับเฉพาะใบที่ยังเดินอยู่', $counters['amount'] >= 25000, number_format((float) $counters['amount'], 2));

        $saved->status = Bond::STATUS_RETURNED;
        $saved->return_date = '2026-09-01';
        $saved->save();
        $after = (new BondSearch(['thai_year' => 2569]))->counters();
        $check(
            'คืนแล้วยอดรวมลดลง (ไม่ใช่เงินที่หน่วยงานถืออยู่แล้ว)',
            (float) $after['amount'] < (float) $counters['amount'],
            number_format((float) $counters['amount'], 2) . ' -> ' . number_format((float) $after['amount'], 2)
        );
        $saved->status = Bond::STATUS_ACTIVE;
        $saved->save();

        echo PHP_EOL . "== 9) รายการสัญญาที่ต้องวางแต่ยังไม่มี ==" . PHP_EOL;
        $contract = new Contract([
            'thai_year' => 2569,
            'title' => '[ทดสอบ] จ้างปรับปรุงระบบไฟฟ้า',
            'contract_type' => Contract::TYPE_HIRE,
            'budget' => 800000,
            'sign_date' => '2026-08-01',
            'status' => Contract::STATUS_ACTIVE,
        ]);
        $contract->save();
        $ids = function () {
            return array_map(function ($row) {
                return (int) $row['contract']->id;
            }, BondSearch::missingContracts(2569));
        };
        $check('สัญญา 800,000 ที่ยังไม่มีหลักประกันขึ้นในรายการ', in_array((int) $contract->id, $ids(), true));

        $cover = new Bond([
            'thai_year' => 2569,
            'title' => $contract->title,
            'source_type' => Bond::SOURCE_CONTRACT,
            'source_id' => $contract->id,
            'bond_type' => Bond::TYPE_CONTRACT,
            'bond_form' => Bond::FORM_BANK_GUARANTEE,
            'base_amount' => 800000,
            'rate' => 5,
            'amount' => 40000,
            'place_date' => '2026-08-01',
            'expiry_date' => '2027-08-01',
            'status' => Bond::STATUS_ACTIVE,
        ]);
        $check('บันทึกหลักประกันผูกกับสัญญาได้', $cover->save(), json_encode($cover->getFirstErrors(), JSON_UNESCAPED_UNICODE));
        $check('บันทึกแล้วสัญญาหลุดจากรายการ', !in_array((int) $contract->id, $ids(), true));
        $check('ดึงหลักประกันของสัญญากลับมาได้', count(Bond::forSource(Bond::SOURCE_CONTRACT, $contract->id)) === 1);

        // relation ต้องยิงคิวรีได้จริงตอน lazy load ไม่ใช่พังเพราะอ้างคอลัมน์ข้ามตาราง
        try {
            $label = $cover->sourceLabel();
            $check('อ่านเอกสารต้นทางผ่าน relation ได้', $label !== '' && $label !== '—', $label);
            $check('ใบที่ผูกสัญญาไม่ไปดึงใบสั่งซื้อมาปน', $cover->order === null);
        } catch (\Throwable $e) {
            $check('อ่านเอกสารต้นทางผ่าน relation ได้', false, $e->getMessage());
        }

        $loose = new Bond([
            'thai_year' => 2569,
            'title' => '[ทดสอบ] หลักประกันซองที่ยังไม่มีสัญญา',
            'bond_type' => Bond::TYPE_BID,
            'bond_form' => Bond::FORM_CASH,
            'amount' => 5000,
            'status' => Bond::STATUS_ACTIVE,
        ]);
        $loose->save();
        $check('ใบที่ไม่ผูกเอกสารไม่ดึงสัญญามาปน', $loose->contract === null);
        $check('ใบที่ไม่ผูกเอกสารแสดงขีดกลาง', $loose->sourceLabel() === '—', $loose->sourceLabel());
        $loose->delete();

        // ใบที่คืนไปแล้วไม่ถือว่าสัญญายังมีหลักประกันค้ำอยู่ สัญญาต้องกลับเข้ารายการ
        $cover->status = Bond::STATUS_RETURNED;
        $cover->return_date = '2026-09-01';
        $cover->save();
        $check('คืนแล้วสัญญากลับเข้ารายการอีกครั้ง', in_array((int) $contract->id, $ids(), true));

        $cover->delete();
        $contract->delete();

        echo PHP_EOL . "== 10) ส่งออกทะเบียนคุม Word ==" . PHP_EOL;
        $tmp = Yii::getAlias('@runtime') . '/bond_test.docx';
        try {
            \PhpOffice\PhpWord\IOFactory::createWriter(
                BondWordExporter::buildRegister([$saved], 2569),
                'Word2007'
            )->save($tmp);
            $size = file_exists($tmp) ? filesize($tmp) : 0;
            $check('ประกอบทะเบียนคุมและเขียนไฟล์ได้', $size > 0, "$size bytes");

            $zip = new \ZipArchive();
            $xml = '';
            if ($zip->open($tmp) === true) {
                $xml = (string) $zip->getFromName('word/document.xml');
                $zip->close();
            }
            foreach (['ทะเบียนคุมหลักประกัน', '25,000.00', 'หนังสือค้ำประกันของธนาคาร'] as $needle) {
                $check("  ทะเบียนคุมมี \"$needle\"", mb_strpos($xml, $needle) !== false);
            }
        } catch (\Throwable $e) {
            $check('ประกอบทะเบียนคุมและเขียนไฟล์ได้', false, $e->getMessage());
        }
        @unlink($tmp);

        echo PHP_EOL . "== 11) ล้างข้อมูลทดสอบ ==" . PHP_EOL;
        $saved->delete();
        $check('ลบข้อมูลทดสอบแล้ว', Bond::findOne($saved->id) === null);

        echo PHP_EOL . "สรุป: ผ่าน $ok / ไม่ผ่าน $fail" . PHP_EOL;
        return $fail === 0 ? 0 : 1;
    }
}
