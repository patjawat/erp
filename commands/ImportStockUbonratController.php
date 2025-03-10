<?php

/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Uploads;
use yii\console\ExitCode;
use app\models\Categorise;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\helpers\BaseConsole;
use app\components\AppHelper;
use app\modules\inventory\models\StockEvent;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 * คลังวัสดุเทคนิคการแพทย์
 */
class ImportStockUbonratController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        $this->M7();
        return ExitCode::OK;
    }

    // วัสดุวิสำนักงาน
    public function actionM1()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 1,
            'assettype' => 'M1',
            'categoryName' => 'วัสดุวิสำนักงาน',
            'category_id' => 706,
            'code' => 'IN-680002',
            'items' => [
                ['title' => 'กระดาษกาวชิ้น A12', 'unit' => 'แพ็ค', 'unit_price' => 35.0, 'qty' => 116],
                ['title' => 'กระดาษกาวชิ้น A5', 'unit' => 'แพ็ค', 'unit_price' => 35.0, 'qty' => 80],
                ['title' => 'กระดาษกาวชิ้น A9 (1*2)', 'unit' => 'แพ็ค', 'unit_price' => 55.0, 'qty' => 5],
                ['title' => 'กระดาษเข้าปก F14 สีเขียว 150g', 'unit' => 'รีม', 'unit_price' => 117.0, 'qty' => 0],
                ['title' => 'กระดาษเข้าปก F14 สีฟ้า 150g', 'unit' => 'รีม', 'unit_price' => 135.0, 'qty' => 4],
                ['title' => 'กระดาษเข้าปก F14 สีชมพู 150g', 'unit' => 'รีม', 'unit_price' => 150.0, 'qty' => 2],
                ['title' => 'กระดาษเข้าปก F14 สีเหลือง 150g', 'unit' => 'รีม', 'unit_price' => 150.0, 'qty' => 0],
                ['title' => 'กระดาษเข้าปก A4 (สีเขียว) 150g.', 'unit' => 'รีม', 'unit_price' => 90.0, 'qty' => 3],
                ['title' => 'กระดาษเข้าปก A4 (สีฟ้า) 150g.', 'unit' => 'รีม', 'unit_price' => 90.0, 'qty' => 2],
                ['title' => 'กระดาษเข้าปก A4 (สีเหลือง) 150g.', 'unit' => 'รีม', 'unit_price' => 90.0, 'qty' => 2],
                ['title' => 'กระดาษเข้าปก A4 (สีชมพู) 150g.', 'unit' => 'รีม', 'unit_price' => 90.0, 'qty' => 3],
                ['title' => 'กระดาษแบงค์ A4 (สีชมพู) 80g.', 'unit' => 'รีม', 'unit_price' => 200.0, 'qty' => 5],
                ['title' => 'กระดาษแบงค์ A4 (สีฟ้า) 80g.', 'unit' => 'รีม', 'unit_price' => 200.0, 'qty' => 13],
                ['title' => 'กระดาษแบงค์ A4 (สีเขียว) 80g.', 'unit' => 'รีม', 'unit_price' => 200.0, 'qty' => 16],
                ['title' => 'กระดาษคาร์บอนดำ', 'unit' => 'กล่อง', 'unit_price' => 153.0, 'qty' => 6],
                ['title' => 'กระดาษคาร์บอนน้ำเงิน', 'unit' => 'กล่อง', 'unit_price' => 153.0, 'qty' => 9],
                ['title' => 'กระดาษชาร์ดแข็งขาว+สี', 'unit' => 'แผ่น', 'unit_price' => 15.0, 'qty' => 5],
                ['title' => 'กระดาษชาร์ดอ่อน', 'unit' => 'แผ่น', 'unit_price' => 13.0, 'qty' => 5],
                ['title' => 'กระดาษบวกเลข (1*10)', 'unit' => 'ม้วน', 'unit_price' => 10.0, 'qty' => 33],
                ['title' => 'กระดาษปรุ๊ฟ', 'unit' => 'แผ่น', 'unit_price' => 3.0, 'qty' => 0],
                ['title' => 'กระดาษโปสเตอร์', 'unit' => 'แผ่น', 'unit_price' => 13.0, 'qty' => 28],
                ['title' => 'กระดาษแฟ็กซ์ (ม้วนเล็ก)(1*6)', 'unit' => 'ม้วน', 'unit_price' => 99.6, 'qty' => 9],
                ['title' => 'กระดาษโรเนียว A4 70g', 'unit' => 'รีม', 'unit_price' => 70.0, 'qty' => 220],
                ['title' => 'กระดาษสติ๊กเกอร์ พีวีซี', 'unit' => 'แผ่น', 'unit_price' => 30.0, 'qty' => 4],
                ['title' => 'กระดาษสติ๊กเกอร์ใส (หลังเหลือง)', 'unit' => 'แผ่น', 'unit_price' => 23.0, 'qty' => 81],
                ['title' => 'กระดาษสะท้อนแสง', 'unit' => 'แผ่น', 'unit_price' => 12.0, 'qty' => 10],
                ['title' => 'กระดาษสา', 'unit' => 'แผ่น', 'unit_price' => 13.0, 'qty' => 4],
                ['title' => 'กาว 2 หน้า', 'unit' => 'ม้วน', 'unit_price' => 150.0, 'qty' => 3],
                ['title' => 'กาวน้ำ', 'unit' => 'หลอด', 'unit_price' => 9.0, 'qty' => 7],
                ['title' => 'กาวลาเท็กซ์', 'unit' => 'ขวด', 'unit_price' => 45.0, 'qty' => 1],
                ['title' => 'กาวหนังไก่ 1"', 'unit' => 'ม้วน', 'unit_price' => 41.0, 'qty' => 56],
                ['title' => 'เข็มหมุด', 'unit' => 'ชุด', 'unit_price' => 5.0, 'qty' => 8],
                ['title' => 'ไข RZA4', 'unit' => 'หลอด', 'unit_price' => 2140.0, 'qty' => 6],
                ['title' => 'คลิปดำ No.108 (1*12)', 'unit' => 'กล่อง', 'unit_price' => 72.0, 'qty' => 12],
                ['title' => 'คลิปดำ No.109 (1*12)', 'unit' => 'กล่อง', 'unit_price' => 45.0, 'qty' => 2],
                ['title' => 'คลิปดำ No.110 (1*12)', 'unit' => 'กล่อง', 'unit_price' => 27.0, 'qty' => 78],
                ['title' => 'คลิปดำ No.111 (1*12)', 'unit' => 'กล่อง', 'unit_price' => 21.0, 'qty' => 60],
                ['title' => 'คัตเตอร์ใหญ่', 'unit' => 'ด้าม', 'unit_price' => 18.0, 'qty' => 7],
                ['title' => 'เชือกพัสดุ', 'unit' => 'ม้วน', 'unit_price' => 55.0, 'qty' => 2],
                ['title' => 'ซองขยายข้าง A4', 'unit' => 'ซอง', 'unit_price' => 4.5, 'qty' => 191],
                ['title' => 'ซองไม่ขยายข้าง A4', 'unit' => 'ซอง', 'unit_price' => 2.7, 'qty' => 149],
                ['title' => 'ซองขยายข้าง F14', 'unit' => 'ซอง', 'unit_price' => 6.5, 'qty' => 92],
                ['title' => 'ซองซีดี (1*100)', 'unit' => 'แพ็ค', 'unit_price' => 90.0, 'qty' => 2],
                ['title' => 'ซองพับ 2 (น้ำตาลครุฑ)', 'unit' => 'ซอง', 'unit_price' => 1.8, 'qty' => 35],
                ['title' => 'ดินสอดำ (1*12)', 'unit' => 'แท่ง', 'unit_price' => 3.33, 'qty' => 38],
                ['title' => 'ตรายางปั้มวันที่ (ภาษาไทย)', 'unit' => 'อัน', 'unit_price' => 70.0, 'qty' => 3],
                ['title' => 'ตะแกรงเอกสาร 1 ชั้นมีฝาปิด', 'unit' => 'อัน', 'unit_price' => 99.0, 'qty' => 12],
                ['title' => 'ทะเบียนคุมเงินงบประมาณ', 'unit' => 'เล่ม', 'unit_price' => 35.0, 'qty' => 1],
                ['title' => 'ทะเบียนคุมเอกสารแทนตัวเงิน', 'unit' => 'เล่ม', 'unit_price' => 0.0, 'qty' => 0],
                ['title' => 'แบบ 301 (ใบเบิกเพื่อจ่ายฯ)', 'unit' => 'เล่ม', 'unit_price' => 35.0, 'qty' => 6],
                ['title' => 'ทะเบียนรับ-ส่ง', 'unit' => 'เล่ม', 'unit_price' => 90.0, 'qty' => 1],
                ['title' => 'เทปกาวติดสันหนังสือ', 'unit' => 'ม้วน', 'unit_price' => 45.0, 'qty' => 4],
                ['title' => 'เทปปั้มตัวอักษร', 'unit' => 'ม้วน', 'unit_price' => 105.0, 'qty' => 10],
                ['title' => 'แท่นประทับ (ตลับชาดสีแดง)', 'unit' => 'ตลับ', 'unit_price' => 36.0, 'qty' => 3],
                ['title' => 'แท่นประทับ (ตลับชาดสีน้ำเงิน)', 'unit' => 'ตลับ', 'unit_price' => 36.0, 'qty' => 1],
                ['title' => 'ใบมีดคัตเตอร์ (1*6)', 'unit' => 'แพ็ค', 'unit_price' => 31.0, 'qty' => 11],
                ['title' => 'ปากกาเขียนแผ่นใส No.F (1*4)', 'unit' => 'แพ็ค', 'unit_price' => 175.0, 'qty' => 3],
                ['title' => 'ปากกาแดง', 'unit' => 'ด้าม', 'unit_price' => 4.5, 'qty' => 41],
                ['title' => 'ปากกาน้ำเงิน', 'unit' => 'ด้าม', 'unit_price' => 4.5, 'qty' => 152],
                ['title' => 'ปากกาเน้นข้อความ', 'unit' => 'ด้าม', 'unit_price' => 36.0, 'qty' => 9],
                ['title' => 'ปากกาเคมี(น้ำเงิน)', 'unit' => 'ด้าม', 'unit_price' => 18.0, 'qty' => 19],
                ['title' => 'ปากกาเคมี(แดง)', 'unit' => 'ด้าม', 'unit_price' => 18.0, 'qty' => 30],
                ['title' => 'ปากกาเคมี(ดำ)', 'unit' => 'ด้าม', 'unit_price' => 18.0, 'qty' => 12],
                ['title' => 'ปากกาลบคำผิด', 'unit' => 'ด้าม', 'unit_price' => 50.0, 'qty' => 20],
                ['title' => 'ปากกาไวด์บอร์ด (1*12)', 'unit' => 'ด้าม', 'unit_price' => 20.0, 'qty' => 9],
                ['title' => 'ผ้าหมึก Epson LQ310+ S015639', 'unit' => 'กล่อง', 'unit_price' => 135.0, 'qty' => 4],
                ['title' => 'ผ้าหมึก พีรีแกน (บวกเลข)', 'unit' => 'ม้วน', 'unit_price' => 50.0, 'qty' => 3],
                ['title' => 'ผ้าหมึกพิมพ์ดีด (ธรรมดา)', 'unit' => 'ม้วน', 'unit_price' => 135.0, 'qty' => 1],
                ['title' => 'แผ่น CD (1*50)', 'unit' => 'หลอด', 'unit_price' => 420.0, 'qty' => 2],
                ['title' => 'ฟิวส์เจอร์บอร์ด', 'unit' => 'แผ่น', 'unit_price' => 45.0, 'qty' => 13],
                ['title' => 'แฟ้มกระดาษ', 'unit' => 'แฟ้ม', 'unit_price' => 9.0, 'qty' => 0],
                ['title' => 'แฟ้มแขวน', 'unit' => 'แฟ้ม', 'unit_price' => 0.0, 'qty' => 0],
                ['title' => 'แฟ้มเจาะ 1"', 'unit' => 'แฟ้ม', 'unit_price' => 63.0, 'qty' => 23],
                ['title' => 'แฟ้มเจาะ 2"', 'unit' => 'แฟ้ม', 'unit_price' => 81.0, 'qty' => 6],
                ['title' => 'แฟ้มเจาะ 3" (1*6)', 'unit' => 'แฟ้ม', 'unit_price' => 81.0, 'qty' => 6],
                ['title' => 'แฟ้มเสนอเซ็น', 'unit' => 'แฟ้ม', 'unit_price' => 185.0, 'qty' => 7],
                ['title' => 'แฟ้มหนีบ', 'unit' => 'แฟ้ม', 'unit_price' => 54.0, 'qty' => 2],
                ['title' => 'ไม้บรรทัด 12"', 'unit' => 'อัน', 'unit_price' => 4.5, 'qty' => 10],
                ['title' => 'ไม้บรรทัด 24"', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 4],
                ['title' => 'ยางลบดินสอ', 'unit' => 'อัน', 'unit_price' => 4.5, 'qty' => 4],
                ['title' => 'ลวดเย็บ No.10 (1*24)', 'unit' => 'กล่อง', 'unit_price' => 11.0, 'qty' => 53],
                ['title' => 'ลวดเย็บ No.3', 'unit' => 'กล่อง', 'unit_price' => 23.0, 'qty' => 4],
                ['title' => 'ลวดเย็บ No.8 (1*12)', 'unit' => 'กล่อง', 'unit_price' => 20.0, 'qty' => 64],
                ['title' => 'ลวดเสียบกระดาษ (1*10)', 'unit' => 'กล่อง', 'unit_price' => 9.0, 'qty' => 133],
                ['title' => 'ลิ้นแฟ้มพลาสติก (1*100)', 'unit' => 'กล่อง', 'unit_price' => 75.0, 'qty' => 11],
                ['title' => 'สมุดเงินสด', 'unit' => 'เล่ม', 'unit_price' => 40.0, 'qty' => 4],
                ['title' => 'สมุดเบอร์ 2 (1*6)', 'unit' => 'เล่ม', 'unit_price' => 36.0, 'qty' => 21],
                ['title' => 'สมุดเบอร์ 4', 'unit' => 'เล่ม', 'unit_price' => 153.0, 'qty' => 7],
                ['title' => 'สมุดเบอร์ 5', 'unit' => 'เล่ม', 'unit_price' => 90.0, 'qty' => 5],
                ['title' => 'สมุดรายงานเงินคงเหลือ', 'unit' => 'เล่ม', 'unit_price' => 35.0, 'qty' => 12],
                ['title' => 'ไส้แฟ้มพลาสติก (1*20)', 'unit' => 'แพ็ค', 'unit_price' => 22.0, 'qty' => 37],
                ['title' => 'หมึก S-4251', 'unit' => 'หลอด', 'unit_price' => 1070.0, 'qty' => 4],
                ['title' => 'หมึกเติมแท่นประทับ (ตลับชาดสีน้ำเงิน)', 'unit' => 'ขวด', 'unit_price' => 13.0, 'qty' => 0],
                ['title' => 'หมึกเติมแท่นประทับ (ตลับชาดสีแดง)', 'unit' => 'ขวด', 'unit_price' => 13.0, 'qty' => 2],
                ['title' => 'หมึกเติมแท่นประทับ (ตลับชาดสีดำ)', 'unit' => 'ขวด', 'unit_price' => 11.0, 'qty' => 2],
                ['title' => 'เหล็กเสียบกระดาษ', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 0],
                ['title' => 'แฟ้มพลาสติก (1*20)', 'unit' => 'แฟ้ม', 'unit_price' => 6.5, 'qty' => 6],
                ['title' => 'กระดาษถ่ายเอกสาร A4 80g.', 'unit' => 'รีม', 'unit_price' => 117.0, 'qty' => 113],
                ['title' => 'กระดาษ A5 70g', 'unit' => 'รีม', 'unit_price' => 55.0, 'qty' => 116],
                ['title' => 'กระดาษถ่ายเอกสาร F14', 'unit' => 'รีม', 'unit_price' => 135.0, 'qty' => 6],
                ['title' => 'ซองพับ 4 (ขาวครุฑ)', 'unit' => 'แพ็ค', 'unit_price' => 40.0, 'qty' => 36],
                ['title' => 'เทปใส 1/2" (1*12)', 'unit' => 'ม้วน', 'unit_price' => 22.0, 'qty' => 51],
                ['title' => 'เทปใส 3/4" (1*8)', 'unit' => 'ม้วน', 'unit_price' => 30.0, 'qty' => 27],
                ['title' => 'กระดาษความร้อน 57x37 มม.', 'unit' => 'ม้วน', 'unit_price' => 27.0, 'qty' => 396],
                ['title' => 'สติ๊กเกอร์ความร้อน 7.9*3.4', 'unit' => 'ดวง', 'unit_price' => 0.15, 'qty' => 98000],
                ['title' => 'ใบคำสั่งแพทย์ (1*500 ชุด)', 'unit' => 'ชุด', 'unit_price' => 2.75, 'qty' => 4000],
                ['title' => 'แบบฟอร์มปรอท', 'unit' => 'ชุด', 'unit_price' => 0.75, 'qty' => 2000],
                ['title' => 'ใบเสร็จรับเงิน (แบบเล่ม)', 'unit' => 'ชุด', 'unit_price' => 0.75, 'qty' => 3000],
                ['title' => 'ใบเสร็จรับเงิน (ต่อเนื่อง)', 'unit' => 'ชุด', 'unit_price' => 0.75, 'qty' => 3000],
                ['title' => 'ใบรับใบสำคัญ', 'unit' => 'เล่ม', 'unit_price' => 180.0, 'qty' => 0],
                ['title' => 'ใบนำตรวจ Blood Chemistry', 'unit' => 'ชุด', 'unit_price' => 45.0, 'qty' => 300],
                ['title' => 'ใบนำส่งตรวจ Blood hemato', 'unit' => 'ชุด', 'unit_price' => 45.0, 'qty' => 300],
                ['title' => 'ใบนำส่งตรวจ จุลชีววิทยา', 'unit' => 'ชุด', 'unit_price' => 45.0, 'qty' => 100],
                ['title' => 'ใบนำส่งตรวจ Urine', 'unit' => 'ชุด', 'unit_price' => 45.0, 'qty' => 300],
                ['title' => 'ใบนำส่งตรวจ ภูมิคุ้มกันวิทยา', 'unit' => 'ชุด', 'unit_price' => 45.0, 'qty' => 200],
                ['title' => 'ใบนำส่งตรวจ Stool exam', 'unit' => 'ชุด', 'unit_price' => 45.0, 'qty' => 100],
                ['title' => 'กล่องล้อเลื่อนขนาด 90 ลิตร', 'unit' => 'กล่อง', 'unit_price' => 410.0, 'qty' => 4],
                ['title' => 'กระดาษความร้อน 80*80 มม.', 'unit' => 'ม้วน', 'unit_price' => 81.66, 'qty' => 30],
            ]
        ];
        $this->Import($data);
    }


    // วัสดุงานบ้านงานครัว
    public function actionM3()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 1,
            'assettype' => 'M3',
            'categoryName' => 'วัสดุงานบ้านงานครัว',
            'category_id' => 707,
            'code' => 'IN-680003',
            'items' => [
                ['title' => 'กรวยกระดาษ (1*25)', 'unit' => 'แถว','unit_price' =>44.94,'qty' =>22],
                ['title' => 'กระดาษฟรอย์', 'unit' => 'กล่อง','unit_price' =>205.00,'qty' =>0],
                ['title' => 'กระบอกฉีด', 'unit' => 'อัน','unit_price' =>95.00,'qty' =>6],
                ['title' => 'กาวตราช้าง (1*12)', 'unit' => 'หลอด','unit_price' =>32.00,'qty' =>15],
                ['title' => 'เชือกฟาง (1*6)', 'unit' => 'ม้วน','unit_price' =>33.33,'qty' =>8],
                ['title' => 'แชมพูล้างรถ', 'unit' => 'แกลลอน','unit_price' =>90.00,'qty' =>15],
                ['title' => 'ซองซิป 7*10 ซม.', 'unit' => 'แพ็ค','unit_price' =>140.00,'qty' =>4],
                ['title' => 'ซองใส่แก้ว', 'unit' => 'แพ็ค','unit_price' =>75.00,'qty' =>5],
                ['title' => 'ถุงขยะดำ20*30', 'unit' => 'กิโลกรัม','unit_price' =>44.00,'qty' =>240],
                ['title' => 'ถุงขยะดำ40*60', 'unit' => 'กิโลกรัม','unit_price' =>44.00,'qty' =>195],
                ['title' => 'ถุงขยะใส20*30', 'unit' => 'แพ็ค','unit_price' =>50.00,'qty' =>62],
                ['title' => 'ถุงมือสีส้ม (1*12)', 'unit' => 'คู่','unit_price' =>37.50,'qty' =>91],
                ['title' => 'ถุงมืออเนกประสงค์ (1*12)', 'unit' => 'คู่','unit_price' =>14.75,'qty' =>12],
                ['title' => 'ถุงอาหาร5*8', 'unit' => 'แพ็ค','unit_price' =>45.00,'qty' =>36],
                ['title' => 'ถุงใส8*12', 'unit' => 'แพ็ค','unit_price' =>50.00,'qty' =>26],
                ['title' => 'ถุงใส12*18', 'unit' => 'แพ็ค','unit_price' =>50.00,'qty' =>28],
                ['title' => 'ถุงใส14*22', 'unit' => 'แพ็ค','unit_price' =>50.00,'qty' =>26],
                ['title' => 'ถุงหิ้วขาว12*20', 'unit' => 'แพ็ค','unit_price' =>45.00,'qty' =>105],
                ['title' => 'ถุงหิ้วขาว6*14', 'unit' => 'แพ็ค','unit_price' =>15.00,'qty' =>84],
                ['title' => 'ถุงหิ้วขาว9*18', 'unit' => 'แพ็ค','unit_price' =>37.00,'qty' =>83],
                ['title' => 'ถุงหิ้วขาว12*26', 'unit' => 'แพ็ค','unit_price' =>50.00,'qty' =>80],
                ['title' => 'ถุงหิ้วแดง9*18', 'unit' => 'แพ็ค','unit_price' =>45.00,'qty' =>104],
                ['title' => 'ถุงหิ้วแดง12*20', 'unit' => 'แพ็ค','unit_price' =>45.00,'qty' =>63],
                ['title' => 'ทิชชู่ม้วนใหญ่ (1*12)', 'unit' => 'ม้วน','unit_price' =>84.58,'qty' =>37],
                ['title' => 'ทิชชู่ม้วนเล็ก (1*144)', 'unit' => 'ม้วน','unit_price' =>3.86,'qty' =>178],
                ['title' => 'น้ำยาดันฝุ่น3.8gl', 'unit' => 'แกลลอน','unit_price' =>350.00,'qty' =>0],
                ['title' => 'น้ำยาทำความสะอาดพื้น', 'unit' => 'แกลลอน','unit_price' =>180.00,'qty' =>2],
                ['title' => 'น้ำยาปรับผ้านุ่ม', 'unit' => 'แกลลอน','unit_price' =>112.00,'qty' =>54],
                ['title' => 'น้ำยาปั่นเงาพื้น', 'unit' => 'แกลลอน','unit_price' =>350.00,'qty' =>3],
                ['title' => 'น้ำยามาจิคคลีน', 'unit' => 'ขวด','unit_price' =>95.00,'qty' =>2],
                ['title' => 'น้ำยาล้างจาน', 'unit' => 'แกลลอน','unit_price' =>135.00,'qty' =>12],
                ['title' => 'น้ำยาล้างเล็บ', 'unit' => 'ขวด','unit_price' =>15.00,'qty' =>12],
                ['title' => 'แป้งเด็ก (1*6)', 'unit' => 'ขวด','unit_price' =>17.00,'qty' =>3],
                ['title' => 'แปรงล้างขวด', 'unit' => 'อัน','unit_price' =>45.00,'qty' =>8],
                ['title' => 'ผงซักฟอกเครื่อง (1*2)', 'unit' => 'ถุง','unit_price' =>489.00,'qty' =>1],
                ['title' => 'ผงซักฟอกอุสาหกรรม (1*25)', 'unit' => 'กืโลกรัม','unit_price' =>33.80,'qty' =>75],
                ['title' => 'ฝอยสแตนเลส (1*12)', 'unit' => 'อัน','unit_price' =>19.16,'qty' =>25],
                ['title' => 'ฟองน้ำล้างแก้ว (1*6)', 'unit' => 'อัน','unit_price' =>12.50,'qty' =>13],
                ['title' => 'ไม้ขีดไฟ (1*6)', 'unit' => 'กล่อง','unit_price' =>12.50,'qty' =>4],
                ['title' => 'ยาฉีดยุง (1*3)', 'unit' => 'กระป๋อง','unit_price' =>160.00,'qty' =>20],
                ['title' => 'รองเท้าแตะ', 'unit' => 'คู่','unit_price' =>150.00,'qty' =>2],
                ['title' => 'ก้อนดับกลิ่น (1*6)', 'unit' => 'ก้อน','unit_price' =>25.00,'qty' =>6],
                ['title' => 'สบู่เด็ก (1*4)', 'unit' => 'ก้อน','unit_price' =>17.00,'qty' =>24],
                ['title' => 'สเปรย์ปรับอากาศ (1*4)', 'unit' => 'กระป๋อง','unit_price' =>42.50,'qty' =>3],
                ['title' => 'สำลีก้าน', 'unit' => 'ถุง','unit_price' =>90.00,'qty' =>4],
                ['title' => 'หนังยาง (1/2 กก.)', 'unit' => 'ถุง','unit_price' =>8.90,'qty' =>20],
                ['title' => 'หลอดกาแฟ (1*10)', 'unit' => 'แพ็ค','unit_price' =>22.00,'qty' =>5],
                ['title' => 'แก้วใส่ปัสสาวะ (1*50)', 'unit' => 'แถว','unit_price' =>20.00,'qty' =>0],
                ['title' => 'กระบอกไฟฉายสั้น', 'unit' => 'อัน','unit_price' =>150.00,'qty' =>2],
                ['title' => 'ถ่านไฟฉายกลาง C (1*12)', 'unit' => 'ก้อน','unit_price' =>35.00,'qty' =>16],
                ['title' => 'ถ่านไฟฉายเล็ก AA (1*60)', 'unit' => 'ก้อน','unit_price' =>9.00,'qty' =>208],
                ['title' => 'ถ่านไฟฉายใหญ่ D (1*24)', 'unit' => 'ก้อน','unit_price' =>25.00,'qty' =>62],
                ['title' => 'ถ่านอัลคาไลน์ AAA (1*2)', 'unit' => 'แพ็ค','unit_price' =>50.00,'qty' =>112],
                ['title' => 'กระเป๋าน้ำร้อน', 'unit' => 'อัน','unit_price' =>199.00,'qty' =>12],
                ['title' => 'เข็มกลัดเล็ก (1*12)', 'unit' => 'ชุด','unit_price' =>3.00,'qty' =>60],
                ['title' => 'เข็มกลัดใหญ่ (1*36)', 'unit' => 'ชุด','unit_price' =>3.61,'qty' =>2],
                ['title' => 'แก้วน้ำ 9 ออนซ์ (1*12)', 'unit' => 'โหล','unit_price' =>69.55,'qty' =>1],
                ['title' => 'ช้อนส้อม (1*12)', 'unit' => 'กล่อง','unit_price' =>160.50,'qty' =>13],
                ['title' => 'ช้อนสั้น (1*12)', 'unit' => 'กล่อง','unit_price' =>59.00,'qty' =>10],
                ['title' => 'ตะเกียบ (1*12)', 'unit' => 'แพ็ค','unit_price' =>55.00,'qty' =>5],
                ['title' => 'เหยือกน้ำสเกล', 'unit' => 'อัน','unit_price' =>60.00,'qty' =>8],
                ['title' => 'กระบอกปัสสาวะ (ชาย)', 'unit' => 'อัน','unit_price' =>60.00,'qty' =>5],
                ['title' => 'หม้อนอนพลาสติก (หญิง)', 'unit' => 'อัน','unit_price' =>110.00,'qty' =>7],
                ['title' => 'ขันน้ำพลาสติก', 'unit' => 'อัน','unit_price' =>18.50,'qty' =>10],
                ['title' => 'น้ำมันจักร', 'unit' => 'ขวด','unit_price' =>27.00,'qty' =>3],
                ['title' => 'แปรงซักผ้า', 'unit' => 'อัน','unit_price' =>45.00,'qty' =>3],
                ['title' => 'ผ้าอะไหล่ดันฝุ่น 24"', 'unit' => 'ผืน','unit_price' =>230.00,'qty' =>2],
                ['title' => 'ผ้าอะไหล่ถูพื้น 12"', 'unit' => 'ผืน','unit_price' =>90.00,'qty' =>5],
                ['title' => 'พรมเช็ดเท้าผ้า', 'unit' => 'ผืน','unit_price' =>50.00,'qty' =>6],
                ['title' => 'ไม้กวาดถนน', 'unit' => 'ด้าม','unit_price' =>55.00,'qty' =>13],
                ['title' => 'ไม้กวาดพื้น (ดอกหญ้า)', 'unit' => 'ด้าม','unit_price' =>50.00,'qty' =>0],
                ['title' => 'ไม้ขนไก่', 'unit' => 'ด้าม','unit_price' =>250.00,'qty' =>0],
                ['title' => 'ไม้ถูพื้นแบบหนีบ', 'unit' => 'ด้าม','unit_price' =>175.00,'qty' =>4],
                ['title' => 'ไม้มือเสือ', 'unit' => 'ด้าม','unit_price' =>50.00,'qty' =>5],
                ['title' => 'รองเท้าบู๊ท', 'unit' => 'คู่','unit_price' =>170.00,'qty' =>5],
                ['title' => 'สก๊อตไบร์ท (1*24)', 'unit' => 'อัน','unit_price' =>14.00,'qty' =>24],
                ['title' => 'สก๊อตไบร์ท+ฟองน้ำ (1*24)', 'unit' => 'อัน','unit_price' =>17.00,'qty' =>172],
                ['title' => 'ถุงขยะแดง 23*30" พิมพ์ลาย', 'unit' => 'กิโลกรัม','unit_price' =>68.00,'qty' =>190],
                ['title' => 'น้ำยาดับกลิ่น', 'unit' => 'แกลลอน','unit_price' =>149.00,'qty' =>15],
                ['title' => 'น้ำยาล้างห้องน้ำ', 'unit' => 'แกลลอน','unit_price' =>130.00,'qty' =>1],
                ['title' => 'แผ่นใยขัดพื้น (แดง) 18"', 'unit' => 'แผ่น','unit_price' =>550.00,'qty' =>2],
                ['title' => 'ใบมีดโกน', 'unit' => 'ใบ','unit_price' =>3.00,'qty' =>295],
                ['title' => 'ผ้าขนหนู (สีขาว) (1*6)', 'unit' => 'แพ็ค','unit_price' =>1412.40,'qty' =>2],
                ['title' => 'ผ้าขนหนูเช็ดตัวกลาง (1*12)', 'unit' => 'แพ็ค','unit_price' =>430.00,'qty' =>6],
                ['title' => 'ผ้าขนหนูเช็ดตัวเล็ก (1*12)', 'unit' => 'แพ็ค','unit_price' =>200.00,'qty' =>11],
                ['title' => 'ผ้าขนหนูเช็ดหน้า (1*12)', 'unit' => 'แพ็ค','unit_price' =>120.00,'qty' =>6],
                ['title' => 'พลาสติกห่อผ้าห่ม', 'unit' => 'ม้วน','unit_price' =>390.00,'qty' =>9],
                ['title' => 'ด้ายสายสินจน์', 'unit' => 'ม้วน','unit_price' =>65.00,'qty' =>2],
                ['title' => 'ถุงห่อช้อนส้อม', 'unit' => 'แพ็ค','unit_price' =>145.00,'qty' =>0],
                ['title' => 'สบู่เหลวล้างมือ', 'unit' => 'แกลลอน','unit_price' =>135.00,'qty' =>0],
                ['title' => 'กาวดักหนู', 'unit' => 'อัน','unit_price' =>65.00,'qty' =>6],
            ]
        ];
        $this->Import($data);
    }

    // วัสดุคอมพิวเตอร์
    public function actionM12()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 1,
            'assettype' => 'M3',
            'categoryName' => 'วัสดุคอมพิวเตอร์',
            'category_id' => 708,
            'code' => 'IN-680004',
            'items' => [
                ['title' => 'หมึก PB285A', 'unit' => 'กล่อง','unit_price' =>290.00,'qty' =>61],
                ['title' => 'หมึก CF279A', 'unit' => 'กล่อง','unit_price' =>250.00,'qty' =>30],
                ['title' => 'หมึก CF230A', 'unit' => 'กล่อง','unit_price' =>950.00,'qty' =>0],
                ['title' => 'หมึก HP 107/A', 'unit' => 'กล่อง','unit_price' =>580.00,'qty' =>8],
                ['title' => 'หมึก CF511ACY (สีฟ้า)', 'unit' => 'กล่อง','unit_price' =>2530.00,'qty' =>3],
                ['title' => 'หมึก CF512AYL (สีเหลือง)', 'unit' => 'กล่อง','unit_price' =>2205.00,'qty' =>1],
                ['title' => 'หมึก CF513AMA (สีชมพู)', 'unit' => 'กล่อง','unit_price' =>2530.00,'qty' =>2],
                ['title' => 'หมึก CF510ABA (สีดำ)', 'unit' => 'กล่อง','unit_price' =>2530.00,'qty' =>9],
                ['title' => 'หมึกAIBT5000Y (เหลือง)', 'unit' => 'กล่อง','unit_price' =>260.00,'qty' =>2],
                ['title' => 'หมึกAIBT5000M (ชมพู)', 'unit' => 'กล่อง','unit_price' =>260.00,'qty' =>2],
                ['title' => 'หมึกAIBT5000C (ฟ้า)', 'unit' => 'กล่อง','unit_price' =>260.00,'qty' =>2],
                ['title' => 'หมึกAIBTD60BK (ดำ)', 'unit' => 'กล่อง','unit_price' =>290.00,'qty' =>3],
                ['title' => 'หมึก TN2480', 'unit' => 'กล่อง','unit_price' =>380.00,'qty' =>1],
                ['title' => 'หมึก HPW1112A', 'unit' => 'กล่อง','unit_price' =>800.00,'qty' =>5],
                ['title' => 'หมึก TN-269BK (สีดำ)', 'unit' => 'กล่อง','unit_price' =>1750.00,'qty' =>2],
                ['title' => 'หมึก TN-269CY (สีฟ้า)', 'unit' => 'กล่อง','unit_price' =>2340.00,'qty' =>2],
                ['title' => 'หมึก TN-269MA (สีแดง)', 'unit' => 'กล่อง','unit_price' =>2340.00,'qty' =>2],
                ['title' => 'หมึก TN-269YL (สีเหลือง)', 'unit' => 'กล่อง','unit_price' =>2340.00,'qty' =>2],
                ['title' => 'DRUM 2455', 'unit' => 'กล่อง','unit_price' =>800.00,'qty' =>1],
            ]
        ];
        $this->Import($data);
    }


    public static function Import($data)
    {
        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;

            // clear
            $oldItems = Categorise::find()->where([
                'name' => 'asset_item',
                'group_id' => 4,
                'category_id' => $data['assettype']
            ])->all();

            

            if (!empty($oldItems)) {
                foreach ($oldItems as $oldItem) {
                    $clearUpload = Uploads::find()->where(['ref' => $oldItem->ref])->one();
                    if ($clearUpload) {
                        FileManagerHelper::removeUploadDir($clearUpload->ref);
                    }
                    $check = Categorise::findOne($oldItem->id);
                    if($check){
                        $check->delete();
                    }
                }
            }
            // ############################

            foreach ($data['items'] as $key => $value) {
                $itemCode = $data['assettype'] . '-' . ($key + 1);
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $itemCode, 'title' => $value['title']]);
                // echo $itemCode."\n";
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $data['assettype'],
                        'code' => $itemCode,
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $data['categoryName'],
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $lot = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

                // $checkModel = StockEvent::findOne([])
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $data['code'],
                    'category_id' => $data['category_id'],
                    'transaction_type' => 'IN',
                    'thai_year' => $data['thai_year'],
                    'asset_item' => $itemCode,
                    'warehouse_id' => $data['warehouse_id'],
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า ' . $data['code'] . ' รหัส : ' . $data['code'] . "สำเร็จ! \n";
                } else {
                    echo 'นำเข้า ' . $data['code'] . ' รหัส : ' . $data['code'] . "ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุวิทยาศาสตร์หรือการแพทย์ สำเร็จ!!
    public function actionM7()
    {
        $thai_year = 2568;
        $warehouse_id = 2;
        $assettype = 'M7';
        $categoryName = 'วัสดุวิทยาศาสตร์หรือการแพทย์';
        $category_id = 1;
        $code = 'IN-680001';

        $data = [
            ['title' => 'AIR WAY 60 mm. No.0', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 30],
            ['title' => 'AIR WAY 70 mm. No.1', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 57],
            ['title' => 'AIR WAY 80 mm. No.2', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 16],
            ['title' => 'AIR WAY 90 mm. No.3', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 69],
            ['title' => 'AIR WAY 100 mm. No.4', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 30],
            ['title' => 'AIR WAY 110 mm. No.5', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 54],
            ['title' => 'AIR WAY 120 mm. No.6', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 0],
            ['title' => 'AIR WAY 130 mm. No.7', 'unit' => 'อัน', 'unit_price' => 20.0, 'qty' => 0],
            ['title' => 'Arm SLING No. S', 'unit' => 'อัน', 'unit_price' => 32.1, 'qty' => 12],
            ['title' => 'Arm SLING No. M', 'unit' => 'อัน', 'unit_price' => 32.1, 'qty' => 20],
            ['title' => 'Arm SLING No. L', 'unit' => 'อัน', 'unit_price' => 32.1, 'qty' => 47],
            ['title' => 'Arm SLING No. XL', 'unit' => 'อัน', 'unit_price' => 32.1, 'qty' => 25],
            ['title' => 'Arm SLING No.เด็กหญิง,เด็กชาย', 'unit' => 'อัน', 'unit_price' => 32.1, 'qty' => 28],
            ['title' => 'AUTOCLAVE TAPE 1/2"', 'unit' => 'ม้วน', 'unit_price' => 176.55, 'qty' => 86],
            ['title' => 'AUTOCLAVE TAPE 1/2"', 'unit' => 'ม้วน', 'unit_price' => 0.0, 'qty' => 9],
            ['title' => 'BLOOD SET', 'unit' => 'ชุด', 'unit_price' => 30.0, 'qty' => 138],
            ['title' => 'BP CUFF เด็กเล็ก', 'unit' => 'ชุด', 'unit_price' => 330.0, 'qty' => 0],
            ['title' => 'BP CUFF เด็กโต', 'unit' => 'ชุด', 'unit_price' => 330.0, 'qty' => 3],
            ['title' => 'BP CUFF ผู้ใหญ่', 'unit' => 'ชุด', 'unit_price' => 330.0, 'qty' => 0],
            ['title' => 'BP Cuff Digital Weich Allene N0.10 (เด็ก)', 'unit' => 'ชุด', 'unit_price' => 1200.0, 'qty' => 1],
            ['title' => 'BP Cuff Digital Weich Allene N0.11', 'unit' => 'ชุด', 'unit_price' => 1200.0, 'qty' => 17],
            ['title' => 'BP Cuff Digital Weich Allene N0.13', 'unit' => 'ชุด', 'unit_price' => 1200.0, 'qty' => 2],
            ['title' => 'BP Cuff Digital Weich Allene N0.13(วัดต้นขา)', 'unit' => 'ชุด', 'unit_price' => 1200.0, 'qty' => 2],
            ['title' => 'Bacteria friter', 'unit' => 'ชุด', 'unit_price' => 48.0, 'qty' => 71],
            ['title' => 'BREAST PUMP', 'unit' => 'โหล', 'unit_price' => 520.0, 'qty' => 36],
            ['title' => 'BOWIEDICK TEST', 'unit' => 'ชุด', 'unit_price' => 110.0, 'qty' => 46],
            ['title' => 'CUTTING 1/2 CIRCLE NO 24', 'unit' => 'โหล', 'unit_price' => 230.0, 'qty' => 5],
            ['title' => 'CUTTING 1/2 CIRCLE No. 28', 'unit' => 'โหล', 'unit_price' => 230.0, 'qty' => 6],
            ['title' => 'CUTTING 1/2 CIRCLE No. 32', 'unit' => 'โหล', 'unit_price' => 230.0, 'qty' => 4],
            ['title' => 'CUTTING 1/2 CIRCLE No. 36', 'unit' => 'โหล', 'unit_price' => 230.0, 'qty' => 3],
            ['title' => 'CUTTING 1/2 CIRCLE No. 55', 'unit' => 'โหล', 'unit_price' => 220.0, 'qty' => 2],
            ['title' => 'CLAVICLE TRACTION No. SS', 'unit' => 'อัน', 'unit_price' => 150.0, 'qty' => 2],
            ['title' => 'CLAVICLE TRACTION No. S', 'unit' => 'อัน', 'unit_price' => 150.0, 'qty' => 7],
            ['title' => 'CLAVICLE TRACTION No. M', 'unit' => 'อัน', 'unit_price' => 150.0, 'qty' => 8],
            ['title' => 'CLAVICLE TRACTION No. L', 'unit' => 'อัน', 'unit_price' => 150.0, 'qty' => 4],
            ['title' => 'CLAVICLE TRACTION No. XL', 'unit' => 'อัน', 'unit_price' => 150.0, 'qty' => 7],
            ['title' => 'CHROMIC CATGUT No 0 ติดเข็ม 30', 'unit' => 'โหล', 'unit_price' => 950.0, 'qty' => 1],
            ['title' => 'CHROMIC CATGUT No 0 ไม่ติดเข็ม', 'unit' => 'โหล', 'unit_price' => 950.0, 'qty' => 1],
            ['title' => 'CHROMIC CATGUT No. 2/0 ติดเข็ม26', 'unit' => 'โหล', 'unit_price' => 950.0, 'qty' => 2],
            ['title' => 'CHROMIC CATGUT No. 2/0', 'unit' => 'โหล', 'unit_price' => 950.0, 'qty' => 10],
            ['title' => 'CHROMIC CATGUT No. 3/0 ติดเข็ม22', 'unit' => 'โหล', 'unit_price' => 950.0, 'qty' => 0],
            ['title' => 'CHROMIC CATGUT No. 4/0 ติดเข็ม22', 'unit' => 'โหล', 'unit_price' => 950.0, 'qty' => 0],
            ['title' => 'COLOSTOMY BAG (50/กล่อง)', 'unit' => 'อัน', 'unit_price' => 9.0, 'qty' => 7],
            ['title' => 'CRUSH No. 40"', 'unit' => 'คู่', 'unit_price' => 315.0, 'qty' => 8],
            ['title' => 'CRUSH No. 48"', 'unit' => 'คู่', 'unit_price' => 337.0, 'qty' => 11],
            ['title' => 'CRUSH No. 50"', 'unit' => 'คู่', 'unit_price' => 315.0, 'qty' => 8],
            ['title' => 'CRUSH No. 52"', 'unit' => 'คู่', 'unit_price' => 315.0, 'qty' => 6],
            ['title' => 'Clamp โต้ง 14 cm.', 'unit' => 'อัน', 'unit_price' => 800.0, 'qty' => 0],
            ['title' => 'Clamp CVD 125 cm.', 'unit' => 'อัน', 'unit_price' => 800.0, 'qty' => 0],
            ['title' => 'Curetage wound', 'unit' => 'อัน', 'unit_price' => 280.0, 'qty' => 0],
            ['title' => 'Collugate Dispos.', 'unit' => 'เส้น', 'unit_price' => 790.0, 'qty' => 0],
            ['title' => 'CPE (เอี้ยมฟ้าแขนยาว)', 'unit' => 'ชิ้น', 'unit_price' => 10.0, 'qty' => 170],
            ['title' => 'DISPOS. NEEDLE No. 18*1.5" (100)', 'unit' => 'กล่อง', 'unit_price' => 48.15, 'qty' => 103],
            ['title' => 'DISPOS. NEEDLE No. 20*1.5" (100)', 'unit' => 'กล่อง', 'unit_price' => 48.15, 'qty' => 12],
            ['title' => 'DISPOS. NEEDLE No. 21*1.5" (100)', 'unit' => 'กล่อง ', 'unit_price' => 48.15, 'qty' => 19],
            ['title' => 'DISPOS. NEEDLE No. 22*1.5" (100)', 'unit' => 'กล่อง', 'unit_price' => 48.15, 'qty' => 65],
            ['title' => 'DISPOS. NEEDLE No. 23*1.5" (100)', 'unit' => 'กล่อง', 'unit_price' => 48.15, 'qty' => 23],
            ['title' => 'DISPOS. NEEDLE No. 24*1.5" (100)', 'unit' => 'กล่อง', 'unit_price' => 48.15, 'qty' => 31],
            ['title' => 'DISPOS. NEEDLE No. 25*1" (100)', 'unit' => 'กล่อง ', 'unit_price' => 48.15, 'qty' => 104],
            ['title' => 'DISPOS SPINAL NEEDLE No. 20 1*25', 'unit' => 'ชิ้น', 'unit_price' => 40.4, 'qty' => 20],
            ['title' => 'DISPOS SPINAL NEEDLE No. 22 1*25', 'unit' => 'ชิ้น', 'unit_price' => 32.1, 'qty' => 25],
            ['title' => 'DISPOS. SYRING 1 ML ถอดเข็ม26*1"  (100)', 'unit' => 'กล่อง', 'unit_price' => 0.0, 'qty' => 1200],
            ['title' => 'DISPOS. SYRING 1 ML ถอดเข็ม26*1"(LDS) ก.1*100', 'unit' => 'กล่อง', 'unit_price' => 0.0, 'qty' => 1000],
            ['title' => 'DISPOS. SYRING 1 ML ไม่ติกเข็ม ก.1*200', 'unit' => 'กล่อง', 'unit_price' => 0.0, 'qty' => 126],
            ['title' => 'DISPOS. SYRING 1 ML ติดเข็ม (100)', 'unit' => 'กล่อง ', 'unit_price' => 227.91, 'qty' => 120],
            ['title' => 'DISPOS. SYRING 1 ML ไม่ติดเข็ม (100)', 'unit' => 'กล่อง ', 'unit_price' => 191.53, 'qty' => 126],
            ['title' => 'DISPOS. SYRING 3 ML ไม่ติดเข็ม (100)', 'unit' => 'กล่อง', 'unit_price' => 115.56, 'qty' => 61],
            ['title' => 'DISPOS. SYRING 5 ML ไม่ติดเข็ม (100)', 'unit' => 'กล่อง ', 'unit_price' => 133.75, 'qty' => 34],
            ['title' => 'DISPOS. SYRING 10 ML ไม่ติดเข็ม (100)', 'unit' => 'กล่อง ', 'unit_price' => 203.3, 'qty' => 46],
            ['title' => 'DISPOS. SYRING 20 ML ไม่ติดเข็ม (1*100)', 'unit' => 'กล่อง ', 'unit_price' => 383.06, 'qty' => 41],
            ['title' => 'DISPOS. SYRING 20 ML ไม่ติดเข็ม  (50)', 'unit' => 'กล่อง', 'unit_price' => 214.0, 'qty' => 0],
            ['title' => 'DISPOS. SYRING 50 ML ไม่ติดเข็ม 1ก.*(20)', 'unit' => 'กล่อง', 'unit_price' => 331.61, 'qty' => 31],
            ['title' => 'DEXON No. 0 ติดเข็ม', 'unit' => 'โหล', 'unit_price' => 1700.0, 'qty' => 2],
            ['title' => 'ELASTIC BANDAGE No. 2"', 'unit' => 'โหล', 'unit_price' => 145.0, 'qty' => 6],
            ['title' => 'ELASTIC BANDAGE No. 3"', 'unit' => 'โหล', 'unit_price' => 180.0, 'qty' => 37],
            ['title' => 'ELASTIC BANDAGE No. 4"', 'unit' => 'โหล', 'unit_price' => 240.0, 'qty' => 7],
            ['title' => 'ELASTIC BANDAGE No. 6"', 'unit' => 'โหล', 'unit_price' => 360.0, 'qty' => 35],
            ['title' => 'E- T TUBE No. 2.5 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 30.0, 'qty' => 20],
            ['title' => 'E- T TUBE No. 3.0 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 30.0, 'qty' => 30],
            ['title' => 'E- T TUBE No. 3.5 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 30.0, 'qty' => 60],
            ['title' => 'E- T TUBE No. 4.0 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 30.0, 'qty' => 20],
            ['title' => 'E- T TUBE No. 4.5 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 30.0, 'qty' => 30],
            ['title' => 'E- T TUBE No. 5.0 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 40.0, 'qty' => 80],
            ['title' => 'E- T TUBE No. 5.5 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 40.0, 'qty' => 40],
            ['title' => 'E- T TUBE No. 6.0 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 40.0, 'qty' => 40],
            ['title' => 'E- T TUBE No. 6.5 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 40.0, 'qty' => 40],
            ['title' => 'E- T TUBE No. 7.0 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 40.0, 'qty' => 70],
            ['title' => 'E- T TUBE No. 7.5 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 40.0, 'qty' => 70],
            ['title' => 'E- T TUBE No. 8.0 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 50.0, 'qty' => 40],
            ['title' => 'E- T TUBE No. 8.5 (10/กล่อง)', 'unit' => 'อัน', 'unit_price' => 50.0, 'qty' => 20],
            ['title' => 'EXTENTION TUBE 42"', 'unit' => 'เส้น', 'unit_price' => 5.8, 'qty' => 600],
            ['title' => 'EYES PAD (50 ชิ้น)', 'unit' => 'ห่อ', 'unit_price' => 150.0, 'qty' => 10],
            ['title' => 'Ear Suction No. 6', 'unit' => 'อัน', 'unit_price' => 1100.0, 'qty' => 1],
            ['title' => 'Ear Suction No. 8', 'unit' => 'อัน', 'unit_price' => 1100.0, 'qty' => 1],
            ['title' => 'Electrode gel', 'unit' => 'หลอด', 'unit_price' => 300.0, 'qty' => 7],
            ['title' => "FOLEY'S CATHETER No. 8 (10/กล่อง)", 'unit' => 'เส้น', 'unit_price' => 38.0, 'qty' => 10],
            ['title' => "FOLEY'S CATHETER No. 10 (10/กล่อง)", 'unit' => 'เส้น', 'unit_price' => 38.0, 'qty' => 0],
            ['title' => "FOLEY'S CATHETER No. 12 (10/กล่อง)", 'unit' => 'เส้น', 'unit_price' => 17.0, 'qty' => 70],
            ['title' => "FOLEY'S CATHETER No. 14 (10/กล่อง)", 'unit' => 'เส้น', 'unit_price' => 17.0, 'qty' => 140],
            ['title' => "FOLEY'S CATHETER No. 16 (10/กล่อง)", 'unit' => 'เส้น', 'unit_price' => 17.0, 'qty' => 100],
            ['title' => "FOLEY'S CATHETER No. 18 (10/กล่อง)", 'unit' => 'เส้น', 'unit_price' => 17.0, 'qty' => 0],
            ['title' => "FOLEY'S CATHETER No. 20 (10/กล่อง)", 'unit' => 'เส้น', 'unit_price' => 17.0, 'qty' => 40],
            ['title' => 'Foley Cath. 3 way No. 16', 'unit' => 'เส้น', 'unit_price' => 50.0, 'qty' => 40],
            ['title' => 'Foley Cath 3. way No. 18', 'unit' => 'เส้น', 'unit_price' => 50.0, 'qty' => 30],
            ['title' => 'Foley Cath 3. way No. 20', 'unit' => 'เส้น', 'unit_price' => 50.0, 'qty' => 0],
            ['title' => 'FINGER SPLINT 3/4"', 'unit' => 'โหล', 'unit_price' => 310.0, 'qty' => 0],
            ['title' => 'FINGER SPLINT 1/2"', 'unit' => 'โหล', 'unit_price' => 310.0, 'qty' => 2],
            ['title' => 'FINGER TIP', 'unit' => 'อัน', 'unit_price' => 24.0, 'qty' => 0],
            ['title' => 'Face Shield', 'unit' => 'อัน', 'unit_price' => 550.0, 'qty' => 0],
            ['title' => 'หน้ากาก Face Shield', 'unit' => 'อัน', 'unit_price' => 120.0, 'qty' => 0],
            ['title' => 'Face Shield Dispos.', 'unit' => 'อัน', 'unit_price' => 25.0, 'qty' => 180],
            ['title' => 'Forceps None-Tooth', 'unit' => 'อัน', 'unit_price' => 170.0, 'qty' => 0],
            ['title' => 'Forceps Tooth', 'unit' => 'อัน', 'unit_price' => 180.0, 'qty' => 0],
            ['title' => 'Gas แอทลีนออกไซด์', 'unit' => 'กระป๋อง', 'unit_price' => 300.0, 'qty' => 6],
            ['title' => 'Gauze 36 x 100 หลา (วชย. )', 'unit' => 'ม้วน', 'unit_price' => 700.0, 'qty' => 36],
            ['title' => 'Gauze Bandage(Conform)ยืด 3 นิ้ว(วชย.)', 'unit' => 'โหล', 'unit_price' => 65.0, 'qty' => 382],
            ['title' => 'Gauze พับ 3*8 (pack50ซอง)', 'unit' => 'ซอง', 'unit_price' => 8.0, 'qty' => 2],
            ['title' => 'GYPSONA 3" (24ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 642.0, 'qty' => 1],
            ['title' => 'GYPSONA 4" (24ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 856.0, 'qty' => 4],
            ['title' => 'GYPSONA 6" (24ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 941.6, 'qty' => 6],
            ['title' => 'Guild E-Tube No. S (Stylet)', 'unit' => 'อัน', 'unit_price' => 220.0, 'qty' => 2],
            ['title' => 'Guild E-Tube No. M (Stylet)', 'unit' => 'อัน', 'unit_price' => 220.0, 'qty' => 3],
            ['title' => 'Guild E-Tube No. L (Stylet)', 'unit' => 'อัน', 'unit_price' => 220.0, 'qty' => 2],
            ['title' => 'IV Set (100/ห่อ)', 'unit' => 'Set', 'unit_price' => 9.63, 'qty' => 2500],
            ['title' => 'Knee Jerk', 'unit' => 'อัน', 'unit_price' => 280.0, 'qty' => 1],
            ['title' => 'LATEX Tube No. 200 (tuniquet)', 'unit' => 'ม้วน', 'unit_price' => 320.0, 'qty' => 3],
            ['title' => 'LATEX Tube No. 201 (สายยางแดง)', 'unit' => 'ม้วน', 'unit_price' => 550.0, 'qty' => 4],
            ['title' => 'LATEX Tube Selicone 7X12mm.(15 ม.)', 'unit' => 'ม้วน ', 'unit_price' => 120.0, 'qty' => 1],
            ['title' => 'LUMBAR SUPPORT Size S', 'unit' => 'อัน', 'unit_price' => 280.0, 'qty' => 6],
            ['title' => 'LUMBAR SUPPORT Size M', 'unit' => 'อัน', 'unit_price' => 280.0, 'qty' => 8],
            ['title' => 'LUMBAR SUPPORT Size L', 'unit' => 'อัน', 'unit_price' => 280.0, 'qty' => 13],
            ['title' => 'LUMBAR SUPPORT Size XL', 'unit' => 'อัน', 'unit_price' => 280.0, 'qty' => 10],
            ['title' => 'LUMBAR SUPPORT Size XXL', 'unit' => 'อัน', 'unit_price' => 280.0, 'qty' => 6],
            ['title' => 'MEDICUT No. 16 (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 10.38, 'qty' => 100],
            ['title' => 'MEDICUT No. 18 (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 10.38, 'qty' => 325],
            ['title' => 'MEDICUT No. 20 (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 10.38, 'qty' => 445],
            ['title' => 'MEDICUT No. 22 (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 10.38, 'qty' => 195],
            ['title' => 'MEDICUT No. 24 (50ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 10.38, 'qty' => 300],
            ['title' => 'MEDICUT No. 26 (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 10.38, 'qty' => 95],
            ['title' => 'MICRODRIP SET', 'unit' => 'ชุด', 'unit_price' => 12.84, 'qty' => 225],
            ['title' => 'MICROPORE 1/2\" (24ม้วน)', 'unit' => 'ม้วน', 'unit_price' => 14.16, 'qty' => 120],
            ['title' => 'MICROPORE 1\" (12ม้วน)', 'unit' => 'ม้วน ', 'unit_price' => 28.33, 'qty' => 72],
            ['title' => 'MASK DIPOS. (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 60.0, 'qty' => 127],
            ['title' => 'MASK DIPOS.(สสจ.)', 'unit' => 'กล่อง', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'Mask N-95 (กล่องละ20ชิ้น)', 'unit' => 'อัน', 'unit_price' => 0.0, 'qty' => 235],
            ['title' => 'Mask N-95 (บริจาค)', 'unit' => 'อัน', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'Mask N-95 สสจ.', 'unit' => 'อัน', 'unit_price' => 0.0, 'qty' => 840],
            ['title' => 'Mouth piece Adult (COPD Clinic)', 'unit' => 'อัน', 'unit_price' => 60.0, 'qty' => 0],
            ['title' => 'Neubulizer Mask Adult (ชุดพ่นยา)(วชย.) 1 ชุด', 'unit' => 'ชุด', 'unit_price' => 50.0, 'qty' => 40],
            ['title' => 'Neubulizer Mask Child (ชุดพ่นยา)(วชย.) 1 ชุด', 'unit' => 'ชุด', 'unit_price' => 50.0, 'qty' => 50],
            ['title' => 'Needdle Holder 13 cm.', 'unit' => 'อัน', 'unit_price' => 400.0, 'qty' => 0],
            ['title' => 'NYLON No. 2/0 ติดเข็ม 26', 'unit' => 'โหล', 'unit_price' => 550.0, 'qty' => 4],
            ['title' => 'NYLON No. 3/0 ติดเข็ม 24', 'unit' => 'โหล', 'unit_price' => 550.0, 'qty' => 1],
            ['title' => 'NYLON No. 4/0 ติดเข็ม 19', 'unit' => 'โหล', 'unit_price' => 550.0, 'qty' => 3],
            ['title' => 'NYLON No. 5/0 ติดเข็ม 16', 'unit' => 'โหล', 'unit_price' => 550.0, 'qty' => 3],
            ['title' => 'NG TUBE No. 5', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 84],
            ['title' => 'NG TUBE No. 6', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 16],
            ['title' => 'NG TUBE No. 8', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 56],
            ['title' => 'NG TUBE No. 10', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 65],
            ['title' => 'NG TUBE No. 12', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 85],
            ['title' => 'NG TUBE No. 14 (50/ห่อ)', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 100],
            ['title' => 'NG TUBE No. 16 (50/ห่อ)', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 60],
            ['title' => 'NG TUBE No. 18 (50/ห่อ)', 'unit' => 'เส้น', 'unit_price' => 11.0, 'qty' => 111],
            ['title' => 'O2 CANNULAR 2 END', 'unit' => 'เส้น', 'unit_price' => 14.0, 'qty' => 64],
            ['title' => 'O2 CANNULAR 2 END (เด็ก)', 'unit' => 'เส้น', 'unit_price' => 14.0, 'qty' => 20],
            ['title' => 'O2 MASK ADULT', 'unit' => 'ชุด', 'unit_price' => 50.0, 'qty' => 20],
            ['title' => 'O2 MASK CHILD', 'unit' => 'ชุด', 'unit_price' => 35.0, 'qty' => 40],
            ['title' => 'O2 ถังใหญ่', 'unit' => 'ถัง', 'unit_price' => 150.0, 'qty' => 0],
            ['title' => 'O2 ถังเล็ก', 'unit' => 'ถัง', 'unit_price' => 110.0, 'qty' => 0],
            ['title' => 'O2 liquid', 'unit' => 'ลบ.ม.', 'unit_price' => 14.02, 'qty' => 0],
            ['title' => 'Peak flow (เล็ก)', 'unit' => 'ชุด', 'unit_price' => 650.0, 'qty' => 2],
            ['title' => 'Peak flow (ใหญ่)', 'unit' => 'ชุด', 'unit_price' => 1600.0, 'qty' => 4],
            ['title' => 'PENROSE DRAIN 1/2\"', 'unit' => 'ชิ้น', 'unit_price' => 20.0, 'qty' => 0],
            ['title' => 'Plaster กันน้ำ 3 M (4.4x4.4 cm.) (50ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 860.0, 'qty' => 0],
            ['title' => 'Portex Blulite Ultracanular 7.0', 'unit' => 'ชิ้น', 'unit_price' => 1267.95, 'qty' => 0],
            ['title' => 'Portex Blulite Ultracanular 7.5', 'unit' => 'ชิ้น', 'unit_price' => 1267.95, 'qty' => 0],
            ['title' => 'ROUND 1/2 CIRCLE No.18', 'unit' => 'โหล', 'unit_price' => 230.0, 'qty' => 11],
            ['title' => 'ROUND 1/2 CIRCLE No.24', 'unit' => 'โหล', 'unit_price' => 230.0, 'qty' => 3],
            ['title' => 'ROUND 1/2 CIRCLE No.50', 'unit' => 'โหล', 'unit_price' => 230.0, 'qty' => 2],
            ['title' => 'RET DOT 3M (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 1290.0, 'qty' => 0],
            ['title' => 'SYRING แก้ว 1 ML (10อัน)', 'unit' => 'กล่อง ', 'unit_price' => 1150.0, 'qty' => 3],
            ['title' => 'SYRING แก้ว 2 ML', 'unit' => 'โหล', 'unit_price' => 245.0, 'qty' => 3],
            ['title' => 'SYRING แก้ว 5 ML', 'unit' => 'โหล', 'unit_price' => 355.0, 'qty' => 2],
            ['title' => 'SYRING แก้ว 10 ML', 'unit' => 'โหล', 'unit_price' => 460.0, 'qty' => 0],
            ['title' => 'SYRING แก้ว 20 ML', 'unit' => 'โหล', 'unit_price' => 710.0, 'qty' => 6],
            ['title' => 'SYRING แก้ว 50 ML', 'unit' => 'อัน', 'unit_price' => 160.0, 'qty' => 35],
            ['title' => 'Syring IRRIGATE (แก้ว) 50 ML', 'unit' => 'อัน', 'unit_price' => 200.0, 'qty' => 0],
            ['title' => 'Stepper Heparin lock (Injection Plug) (200/กล่อง)', 'unit' => 'อัน', 'unit_price' => 4.0, 'qty' => 304],
            ['title' => 'SOFT CLOT TAPE ON LINER', 'unit' => 'ม้วน', 'unit_price' => 180.0, 'qty' => 13],
            ['title' => 'SOFT COLLAR Size S', 'unit' => 'อัน', 'unit_price' => 147.66, 'qty' => 2],
            ['title' => 'SOFT COLLAR Size M', 'unit' => 'อัน', 'unit_price' => 147.66, 'qty' => 3],
            ['title' => 'SOFT COLLAR Size L', 'unit' => 'อัน', 'unit_price' => 147.66, 'qty' => 7],
            ['title' => 'SOFT COLLAR Size XL', 'unit' => 'อัน', 'unit_price' => 147.66, 'qty' => 1],
            ['title' => 'SUCTION Tube No. 6', 'unit' => 'เส้น', 'unit_price' => 3.5, 'qty' => 30],
            ['title' => 'SUCTION Tube No. 8', 'unit' => 'เส้น', 'unit_price' => 3.5, 'qty' => 140],
            ['title' => 'SUCTION Tube No. 10', 'unit' => 'เส้น', 'unit_price' => 3.5, 'qty' => 77],
            ['title' => 'SUCTION Tube No. 12', 'unit' => 'เส้น', 'unit_price' => 3.5, 'qty' => 90],
            ['title' => 'SUCTION Tube No. 14 (50/ห่อ)', 'unit' => 'เส้น', 'unit_price' => 3.5, 'qty' => 50],
            ['title' => 'SUCTION Tube No. 16 (50/ห่อ)', 'unit' => 'เส้น', 'unit_price' => 3.5, 'qty' => 240],
            ['title' => 'SUCTION Tube No. 18 (50/ห่อ)', 'unit' => 'เส้น', 'unit_price' => 3.5, 'qty' => 71],
            ['title' => 'SILK No. 0 [ Suture # Sterile) ]', 'unit' => 'โหล', 'unit_price' => 750.0, 'qty' => 0],
            ['title' => 'SILK No.2/0', 'unit' => 'โหล', 'unit_price' => 750.0, 'qty' => 1],
            ['title' => 'SILK No. 3/0', 'unit' => 'โหล', 'unit_price' => 750.0, 'qty' => 1],
            ['title' => 'SILK No. 4/0', 'unit' => 'โหล', 'unit_price' => 750.0, 'qty' => 1],
            ['title' => 'SPATULAR (100/กล่อง)', 'unit' => 'อัน', 'unit_price' => 1.68, 'qty' => 0],
            ['title' => 'SOFRA TULLE (Pack ละ 5 กล่อง) (10แผ่น)', 'unit' => 'กล่อง', 'unit_price' => 150.0, 'qty' => 45],
            ['title' => 'Solf Paddle (Telemed)', 'unit' => 'ชุด', 'unit_price' => 1800.0, 'qty' => 0],
            ['title' => 'Solf Paddle (AED Zoll plus)', 'unit' => 'ชุด', 'unit_price' => 2200.0, 'qty' => 0],
            ['title' => 'Steam Strip Comply test (240ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 856.0, 'qty' => 9],
            ['title' => 'Strip Comply Test E.O. (Class5) (250)', 'unit' => 'ห่อ', 'unit_price' => 1000.0, 'qty' => 3],
            ['title' => 'Spore Test (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 3370.0, 'qty' => 3],
            ['title' => 'Spore Test E.O.(Biological Indicator)4ชม. (50)', 'unit' => 'กล่อง', 'unit_price' => 5000.0, 'qty' => 3],
            ['title' => 'SPLINT ROLL [ 3*288*10 ]', 'unit' => 'ม้วน', 'unit_price' => 2354.0, 'qty' => 2],
            ['title' => 'SPLINT ROLL [ 4*288*10 ]', 'unit' => 'ม้วน', 'unit_price' => 2675.0, 'qty' => 4],
            ['title' => 'SPLINT ROLL [ 6*288*15 ]', 'unit' => 'ม้วน', 'unit_price' => 4250.0, 'qty' => 2],
            ['title' => 'SKIN TACK เด็ก (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 350.0, 'qty' => 1],
            ['title' => 'SKIN TACK  (50ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 350.0, 'qty' => 21],
            ['title' => 'Scalp veine No. 22 ( 1 x 50) (50ชิ้น)', 'unit' => 'กล่อง ', 'unit_price' => 450.0, 'qty' => 50],
            ['title' => 'TRANSPORE 3M 12\"', 'unit' => 'ม้วน', 'unit_price' => 299.6, 'qty' => 38],
            ['title' => 'TRANSPORE 3M 1 นิ้ว (ห้องยา) (24ม้วน)', 'unit' => 'กล่อง', 'unit_price' => 363.8, 'qty' => 3],
            ['title' => 'TENSOPLAST 10 cm.', 'unit' => 'ม้วน', 'unit_price' => 330.0, 'qty' => 21],
            ['title' => 'THORACIC CATHETER No. 24', 'unit' => 'เส้น', 'unit_price' => 200.0, 'qty' => 5],
            ['title' => 'THORACIC CATHETER No. 28', 'unit' => 'เส้น', 'unit_price' => 200.0, 'qty' => 0],
            ['title' => 'THORACIC CATHETER No. 32', 'unit' => 'เส้น', 'unit_price' => 200.0, 'qty' => 6],
            ['title' => 'Thoracic cath with trocar tube No. 24', 'unit' => 'เส้น', 'unit_price' => 290.0, 'qty' => 0],
            ['title' => 'Thoracic cath with trocar tube No. 28', 'unit' => 'เส้น', 'unit_price' => 290.0, 'qty' => 0],
            ['title' => 'Thoracic cath with trocar tube No. 32', 'unit' => 'เส้น', 'unit_price' => 2920.0, 'qty' => 0],
            ['title' => 'Termometer วัดอุณหภูมิตู้เย็นแบบแท่ง', 'unit' => 'อัน', 'unit_price' => 125.0, 'qty' => 0],
            ['title' => 'TREE - WAY PLASTIC (50/กล่อง)', 'unit' => 'อัน ', 'unit_price' => 12.0, 'qty' => 500],
            ['title' => 'URINE BAG (10/ห่อ)', 'unit' => 'ชิ้น ', 'unit_price' => 13.5, 'qty' => 160],
            ['title' => 'URENAL ผู้ชาย', 'unit' => 'อัน', 'unit_price' => 26.0, 'qty' => 6],
            ['title' => 'ULTRASONIC เจล', 'unit' => 'กระปุก', 'unit_price' => 520.0, 'qty' => 0],
            ['title' => 'VISIBLE BAG 16\"X 100 M', 'unit' => 'ม้วน', 'unit_price' => 2500.0, 'qty' => 3],
            ['title' => 'VISIBLE BAG 2\"X200 M', 'unit' => 'ม้วน', 'unit_price' => 440.0, 'qty' => 3],
            ['title' => 'VISIBLE BAG 3\"X200 M', 'unit' => 'ม้วน', 'unit_price' => 700.0, 'qty' => 1],
            ['title' => 'VISIBLE BAG 4\"X200 M', 'unit' => 'ม้วน', 'unit_price' => 800.0, 'qty' => 5],
            ['title' => 'VISIBLE BAG 6\"X200 M', 'unit' => 'ม้วน', 'unit_price' => 1200.0, 'qty' => 4],
            ['title' => 'VISIBLE BAG 6\"X100 M ขยายข้าง', 'unit' => 'ม้วน', 'unit_price' => 1000.0, 'qty' => 4],
            ['title' => 'WEBRILL 3\"', 'unit' => 'โหล', 'unit_price' => 120.0, 'qty' => 4],
            ['title' => 'WEBRILL 4\"', 'unit' => 'โหล', 'unit_price' => 180.0, 'qty' => 0],
            ['title' => 'WEBRILL 6\"', 'unit' => 'โหล', 'unit_price' => 240.0, 'qty' => 1],
            ['title' => 'Y-CONNEGTER NO 11/9', 'unit' => 'อัน', 'unit_price' => 55.0, 'qty' => 10],
            ['title' => 'กระดาษEKG เครื่องdfib (A4)', 'unit' => 'ม้วน', 'unit_price' => 120.0, 'qty' => 2],
            ['title' => 'กระดาษEKG ม้วนเล็ก (FX 2111 FUKUDA)', 'unit' => 'ม้วน', 'unit_price' => 120.0, 'qty' => 15],
            ['title' => 'กระดาษ EKG (Zoll)', 'unit' => 'พับ', 'unit_price' => 350.0, 'qty' => 10],
            ['title' => 'กระดาษEKG (Mac 1200) (A4) วอร์ด', 'unit' => 'ม้วน', 'unit_price' => 500.0, 'qty' => 10],
            ['title' => 'กระดาษEKG (Mac 800)', 'unit' => 'พับ', 'unit_price' => 400.0, 'qty' => 8],
            ['title' => 'กระดาษEKG Iocare มีเส้นกราฟ', 'unit' => 'พับ', 'unit_price' => 550.0, 'qty' => 10],
            ['title' => 'กระดาษEKG Iocare ไม่มีเส้นกราฟ', 'unit' => 'พับ', 'unit_price' => 550.0, 'qty' => 10],
            ['title' => 'กระดาษอัลตร้าซาวด์ HD 110', 'unit' => 'ม้วน', 'unit_price' => 655.0, 'qty' => 13],
            ['title' => 'กระดาษเครื่อง NST', 'unit' => 'พับ', 'unit_price' => 280.0, 'qty' => 8],
            ['title' => 'กระดาษห่อเกรด A (2000ชิ้น)', 'unit' => 'ห่อ', 'unit_price' => 1.42, 'qty' => 2200],
            ['title' => 'กาแก้วอันดีน (UNDINE)', 'unit' => 'อัน', 'unit_price' => 550.0, 'qty' => 1],
            ['title' => 'แก้วยาน้ำ 2 ออน์', 'unit' => 'โหล', 'unit_price' => 47.0, 'qty' => 3],
            ['title' => 'กรรไกรตัดฝีเย็บ', 'unit' => 'อัน', 'unit_price' => 1455.0, 'qty' => 2],
            ['title' => 'กรรไกรตัดสะดือเด้ก', 'unit' => 'อัน', 'unit_price' => 1450.0, 'qty' => 2],
            ['title' => 'กรรไกรตัดไหม ปลายแหลม', 'unit' => 'อัน', 'unit_price' => 540.0, 'qty' => 2],
            ['title' => 'กรรไกรตัดไหม 13\"', 'unit' => 'อัน', 'unit_price' => 540.0, 'qty' => 2],
            ['title' => 'กรรไกรตัดเนื้อ (Mezenbaum Siscer)', 'unit' => 'อัน', 'unit_price' => 900.0, 'qty' => 3],
            ['title' => 'กระเปาะ Neubulzer', 'unit' => 'ชุด', 'unit_price' => 250.0, 'qty' => 7],
            ['title' => 'ขวดน้ำยา 250 ML. (ใส)', 'unit' => 'ขวด', 'unit_price' => 90.0, 'qty' => 0],
            ['title' => 'ขวดน้ำยา 250 ML. (สีชา)', 'unit' => 'ขวด', 'unit_price' => 120.0, 'qty' => 22],
            ['title' => 'ขวด Suction Thomus', 'unit' => 'ขวด', 'unit_price' => 1500.0, 'qty' => 0],
            ['title' => 'ขวด Suction มีScale', 'unit' => 'ขวด', 'unit_price' => 800.0, 'qty' => 0],
            ['title' => 'ขวด Chest Drain 1000 ml.', 'unit' => 'ขวด', 'unit_price' => 235.0, 'qty' => 1],
            ['title' => 'จุก Chest Drain 2 ท่อ สั้น-ยาว ไม่ปีก', 'unit' => 'อัน', 'unit_price' => 80.0, 'qty' => 0],
            ['title' => 'จุก Chest Drain 2 ท่อ สั้น-สั้น ไม่ปีก', 'unit' => 'อัน', 'unit_price' => 65.0, 'qty' => 0],
            ['title' => 'จุก Chest Drain 2 ท่อ สั้น-สั้น ปีก', 'unit' => 'อัน', 'unit_price' => 65.0, 'qty' => 0],
            ['title' => 'จุก Chest Drain 3 ท่อ สั้น-ยาว ปีก', 'unit' => 'อัน', 'unit_price' => 70.0, 'qty' => 0],
            ['title' => 'จุกยางไม้ค้ำยัน', 'unit' => 'อัน', 'unit_price' => 17.0, 'qty' => 10],
            ['title' => 'เครื่องวัดออกซิเจน O2', 'unit' => 'อัน', 'unit_price' => 1500.0, 'qty' => 16],
            ['title' => 'ด้ามมีดผ่าตัดเบอร์3', 'unit' => 'อัน', 'unit_price' => 120.0, 'qty' => 40],
            ['title' => 'ถ้วยยาเม็ดพลาสติก', 'unit' => 'โหล', 'unit_price' => 35.0, 'qty' => 35],
            ['title' => 'ถุงมือผ่าตัด เบอร์ 6.0  (50)', 'unit' => 'กล่อง', 'unit_price' => 850.0, 'qty' => 6],
            ['title' => 'ถุงมือผ่าตัด เบอร์ 6.5  (50)', 'unit' => 'กล่อง', 'unit_price' => 850.0, 'qty' => 3],
            ['title' => 'ถุงมือผ่าตัด เบอร์ 7.0 (50)', 'unit' => 'กล่อง ', 'unit_price' => 850.0, 'qty' => 2],
            ['title' => 'ถุงมือผ่าตัด เบอร์ 7.5 (50)', 'unit' => 'กล่อง ', 'unit_price' => 850.0, 'qty' => 3],
            ['title' => 'ถุงมือล้วงรก Size S', 'unit' => 'คู่', 'unit_price' => 200.0, 'qty' => 3],
            ['title' => 'ถุงมือล้วงรก Size M', 'unit' => 'คู่', 'unit_price' => 200.0, 'qty' => 4],
            ['title' => 'ถุงมือล้วงรก Size L', 'unit' => 'คู่', 'unit_price' => 200.0, 'qty' => 16],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ SS  (50คู่)', 'unit' => 'กล่อง', 'unit_price' => 95.0, 'qty' => 175],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ S (50คู่)', 'unit' => 'กล่อง ', 'unit_price' => 95.0, 'qty' => 259],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ M (50คู่)', 'unit' => 'กล่อง ', 'unit_price' => 95.0, 'qty' => 60],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ L  (50คู่)', 'unit' => 'กล่อง', 'unit_price' => 95.0, 'qty' => 46],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ SS สสจ.  (50คู่)', 'unit' => 'กล่อง', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ S สสจ.  (50คู่)', 'unit' => 'กล่อง', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ M สสจ. (50คู่)', 'unit' => 'กล่อง ', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'ถุงมือใช้แล้วทิ้ง เบอร์ L สสจ.  (50คู่)', 'unit' => 'กล่อง', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'ถุงตวงเลือด', 'unit' => 'ถุง', 'unit_price' => 29.0, 'qty' => 40],
            ['title' => 'ถุงพลาสติกห่อศพ (สีขาว)', 'unit' => 'ถุง', 'unit_price' => 350.0, 'qty' => 3],
            ['title' => 'เทปผูกท่อช่วยหายใจ', 'unit' => 'ม้วน', 'unit_price' => 100.0, 'qty' => 0],
            ['title' => 'ใบมีดผ่าตัดเบอร์ 10 (100ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 321.0, 'qty' => 4],
            ['title' => 'ใบมีดผ่าตัดเบอร์ 11 (100ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 321.0, 'qty' => 12],
            ['title' => 'ใบมีดผ่าตัดเบอร์ 15 (100ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 321.0, 'qty' => 3],
            ['title' => 'แป้งคลุกถุงมือ', 'unit' => 'ถุง', 'unit_price' => 20.0, 'qty' => 3],
            ['title' => 'ปรอทแก้ว', 'unit' => 'อัน', 'unit_price' => 45.84, 'qty' => 9],
            ['title' => 'ปรอทแท่งติดตู้เย็น', 'unit' => 'อัน', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'ปรอทวัดไข้ทางทวารหนัก', 'unit' => 'โหล', 'unit_price' => 270.0, 'qty' => 9],
            ['title' => 'ป้ายข้อมือเด็ก สีฟ้า (ชาย) (100ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 260.0, 'qty' => 4],
            ['title' => 'ป้ายข้อมือเด็ก สีชมพู (หญิง) (100ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 260.0, 'qty' => 1],
            ['title' => 'ป้ายข้อมือผู้ใหญ่ สีฟ้า (ชาย) (100ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 260.0, 'qty' => 4],
            ['title' => 'ป้ายข้อมือผู้ใหญ่ สีชมพู (หญิง) (100ชิ้น)', 'unit' => 'กล่อง', 'unit_price' => 260.0, 'qty' => 8],
            ['title' => 'ผ้ายางกันเปื้อน (100ชิ้น)', 'unit' => 'ชิ้น', 'unit_price' => 4.0, 'qty' => 10],
            ['title' => 'ผ้าอ้อมผู้ใหญ่ เบอร์ L', 'unit' => 'ชิ้น', 'unit_price' => 0.0, 'qty' => 210],
            ['title' => 'ผ้าอ้อมผู้ใหญ่ เบอร์ X L', 'unit' => 'ชิ้น', 'unit_price' => 0.0, 'qty' => 10],
            ['title' => 'เฝือกดามคอชนิดแข็ง เบอร์ S ปรับไม่ได้', 'unit' => 'เส้น', 'unit_price' => 900.0, 'qty' => 2],
            ['title' => 'เฝือกดามคอชนิดแข็ง เบอร์ M ปรับไม่ได้', 'unit' => 'เส้น', 'unit_price' => 900.0, 'qty' => 2],
            ['title' => 'เฝือกดามคอชนิดแข็ง เบอร์ L ปรับไม่ได้', 'unit' => 'เส้น', 'unit_price' => 900.0, 'qty' => 2],
            ['title' => 'เฝือกดามคอชนิดแข็ง ชนิด ปรับได้', 'unit' => 'เส้น', 'unit_price' => 1000.0, 'qty' => 2],
            ['title' => 'เครื่องช่วยเดิน 4 ขา', 'unit' => 'อัน', 'unit_price' => 600.0, 'qty' => 0],
            ['title' => 'ไม้เท้า 3 ขา', 'unit' => 'อัน', 'unit_price' => 350.0, 'qty' => 0],
            ['title' => 'ไม้เท้าสระอา', 'unit' => 'อัน', 'unit_price' => 190.0, 'qty' => 0],
            ['title' => 'ไม้พันสำลี Sterile (50ซอง)', 'unit' => 'ห่อ', 'unit_price' => 175.0, 'qty' => 9],
            ['title' => 'ลูกยาง BP + วาล์ว', 'unit' => 'อัน', 'unit_price' => 120.0, 'qty' => 4],
            ['title' => 'ลูกยางแดง เบอร์ 1 (pack1โหล)', 'unit' => 'กล่อง', 'unit_price' => 50.0, 'qty' => 0],
            ['title' => 'ลูกยางแดง เบอร์ 2 (pack1โหล)', 'unit' => 'อัน', 'unit_price' => 55.0, 'qty' => 7],
            ['title' => 'ลูกยางแดง เบอร์ 3 (pack1โหล)', 'unit' => 'อัน', 'unit_price' => 75.0, 'qty' => 10],
            ['title' => 'ส้นยางรองเฝือก เด็ก', 'unit' => 'อัน', 'unit_price' => 100.0, 'qty' => 2],
            ['title' => 'ส้นยางรองเฝือก ผู้ใหญ่', 'unit' => 'อัน', 'unit_price' => 100.0, 'qty' => 5],
            ['title' => 'สำลี 1 ปอนด์ (450กรัม) BPC (วยม.)', 'unit' => 'ม้วน', 'unit_price' => 90.0, 'qty' => 257],
            ['title' => 'สำลีก้อน 450 กร้ม', 'unit' => 'ห่อ', 'unit_price' => 70.0, 'qty' => 71],
            ['title' => 'สำลีก้อน 0.35 กร้ม (pack50ซอง) (5ก้อน)', 'unit' => 'ซอง', 'unit_price' => 4.0, 'qty' => 0],
            ['title' => 'เหล็กกดลิ้น เด็ก 15 ซม', 'unit' => 'อัน', 'unit_price' => 31.0, 'qty' => 19],
            ['title' => 'เหล็กกดลิ้น ผู้ใหญ่ 19 ซม', 'unit' => 'อัน', 'unit_price' => 38.0, 'qty' => 19],
            ['title' => 'อับสำลีขนาด 3 นิ้ว ( กลาง )', 'unit' => 'ชุด', 'unit_price' => 160.0, 'qty' => 1],
            ['title' => 'กระปุกสำลี ขนาด 5x6.5 นิ้ว (ทรงสูง)', 'unit' => 'ชุด', 'unit_price' => 535.0, 'qty' => 0],
            ['title' => 'ถาดหลุมทำแผล', 'unit' => 'อัน', 'unit_price' => 180.0, 'qty' => 15],
            ['title' => 'Blendera MF 2.5 kg', 'unit' => 'ชุด', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'อาหาร สูตรDM', 'unit' => 'ชุด', 'unit_price' => 0.0, 'qty' => 0],
            ['title' => 'ไม้กดลิ้น Dispos Sterile (1x100 ชิ้น)', 'unit' => 'ชิ้น', 'unit_price' => 160.0, 'qty' => 8],
            ['title' => 'Dispos. Needle EACU No.16*13(100)', 'unit' => 'กล่อง', 'unit_price' => 128.4, 'qty' => 0],
            ['title' => 'Dispos. Needle EACU No.0.25*25 (100)', 'unit' => 'กล่อง', 'unit_price' => 128.4, 'qty' => 5],
            ['title' => 'Dispos. Needle EACU No.0.25*40 (100)', 'unit' => 'กล่อง', 'unit_price' => 128.4, 'qty' => 36],
            ['title' => 'Dispos. Needle EACU No.0.25*50 (100)', 'unit' => 'กล่อง', 'unit_price' => 128.4, 'qty' => 0],
            ['title' => 'Dispos. Needle EACU No.0.3*75(100) ', 'unit' => 'กล่อง', 'unit_price' => 128.4, 'qty' => 5],
            ['title' => 'เข็มติดหู + พลาสเตอร์สีเนื้อ (100)', 'unit' => 'กล่อง', 'unit_price' => 149.8, 'qty' => 1],
            ['title' => 'ถ้วยแก้ว ขนาด 5.5 ซม. No.3', 'unit' => 'อัน', 'unit_price' => 64.2, 'qty' => 18],
            ['title' => 'ถ้วยแก้ว ขนาด 6.5 ซม. No.4', 'unit' => 'อัน', 'unit_price' => 64.2, 'qty' => 11],
        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;

            foreach ($data as $key => $value) {
                $itemCode = $assettype . '-' . ($key + 1);
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $itemCode, 'title' => $value['title']]);
                // echo $itemCode."\n";
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $assettype,
                        'code' => $itemCode,
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $categoryName,
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $lot = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);

                // $checkModel = StockEvent::findOne([])
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'thai_year' => $thai_year,
                    'asset_item' => $itemCode,
                    'warehouse_id' => $warehouse_id,
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า ' . $code . ' รหัส : ' . $code . "สำเร็จ! \n";
                } else {
                    echo 'นำเข้า ' . $code . ' รหัส : ' . $code . "ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุการแพทย์ทั่วไป
    public function actionM22()
    {
        // คลังวัสดุกายภาพบำบัด
        $warehouse_id = 4;
        $assettype = 'M22';
        $categoryName = 'วัสดุการแพทย์ทั่วไป';
        $category_id = 1228;
        $code = 'IN-680014';

        $data = [
            ['code' => '22-00162', 'title' => 'กระดา', 'unit' => 'อัน', 'qty' => '11.00', 'unit_price' => '374.5'],
        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;
            foreach ($data as $key => $value) {
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code'], 'title' => $value['title']]);
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $assettype,
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $categoryName,
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $lot = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $value['code'],
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $code,
                    'warehouse_id' => $warehouse_id,
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า ' . $value['code'] . ' รหัส : ' . $value['code'] . "สำเร็จ! \n";
                } else {
                    echo 'นำเข้า ' . $value['code'] . ' รหัส : ' . $value['code'] . "ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุทันตกรรม
    public function actionM19()
    {
        // คลังวัสดุทันตกรรม
        $warehouse_id = 3;
        $assettype = 'M19';
        $categoryName = 'วัสดุทันตกรรม';
        $category_id = 1229;
        $code = 'IN-680015';

        $data = [
            ['code' => '19-00001', 'title' => 'Acrylic - ivory', 'unit' => 'ถุง', 'qty' => '1.00', 'unit_price' => '1500.00000'],
            ['code' => '19-00003', 'title' => 'Alginate', 'unit' => 'ถุง', 'qty' => '5.00', 'unit_price' => '202.00000'],
            ['code' => '19-00004', 'title' => 'Alvogyl', 'unit' => 'ขวด', 'qty' => '1.00', 'unit_price' => '1700.00000'],
            ['code' => '19-00005', 'title' => 'Amalgam 1 spill (500 แคป/10ซองเล็ก)', 'unit' => 'กระปุก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00006', 'title' => 'Amalgam 2 spills (500 แคป/10ซองเล็ก)', 'unit' => 'กระปุก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00007', 'title' => 'Amalgam carrier', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00009', 'title' => 'Artery forceps', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00010', 'title' => 'Articulating paper', 'unit' => 'กล่อง', 'qty' => '6.00', 'unit_price' => '540.00000'],
            ['code' => '19-00012', 'title' => 'Barbed broach - ดำ', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '23.00000'],
            ['code' => '19-00013', 'title' => 'Barbed broach - แดง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00014', 'title' => 'Barbed broach - น้ำเงิน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00015', 'title' => 'Barbed broach - เหลือง', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '23.00000'],
            ['code' => '19-00017', 'title' => 'Blade #12', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '980.00000'],
            ['code' => '19-00018', 'title' => 'Blade #15', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00022', 'title' => 'Calcium hydroxide', 'unit' => 'ขวด', 'qty' => '1.00', 'unit_price' => '200.00000'],
            ['code' => '19-00023', 'title' => 'Carbide cutter - เขียว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00024', 'title' => 'Carbide cutter - ฟ้า', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00026', 'title' => 'Celluloid strip', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00030', 'title' => 'Clove oil', 'unit' => 'ขวด', 'qty' => '1.00', 'unit_price' => '130.00000'],
            ['code' => '19-00032', 'title' => 'Composhape rugby - เหลือง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00033', 'title' => 'Composhape thin taper - แดง', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '45.00000'],
            ['code' => '19-00034', 'title' => 'Composhape thin taper - เหลือง', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '40.00000'],
            ['code' => '19-00035', 'title' => 'Composite A2B (Filtek Z350 XT)', 'unit' => 'หลอด', 'qty' => '4.00', 'unit_price' => '856.00000'],
            ['code' => '19-00038', 'title' => 'Composite A3.5B (Filtek Z350 XT)', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00039', 'title' => 'Composite A3.5 (Estelite)', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00040', 'title' => 'Composite A3B (Filtek Z350 XT)', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00042', 'title' => 'Composite A3E (Filtek Z350 XT)', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00043', 'title' => 'Composite A3 (Estelite)', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00044', 'title' => 'Composite A4B (Filtek Z350 XT)', 'unit' => 'หลอด', 'qty' => '6.00', 'unit_price' => '856.00000'],
            ['code' => '19-00046', 'title' => 'Composite AT (Filtek Z350 XT)', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00048', 'title' => 'Adhesive bonding', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00052', 'title' => 'Compound', 'unit' => 'กล่อง', 'qty' => '6.00', 'unit_price' => '982.91667'],
            ['code' => '19-00057', 'title' => 'Cylinder diamond bur', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00058', 'title' => 'Dental floss', 'unit' => 'กล่อง', 'qty' => '5.00', 'unit_price' => '240.00000'],
            ['code' => '19-00060', 'title' => 'Dycal', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '909.50000'],
            ['code' => '19-00063', 'title' => 'Endo stops', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00064', 'title' => 'Etchant - bottle', 'unit' => 'ขวด', 'qty' => '9.00', 'unit_price' => '666.61000'],
            ['code' => '19-00065', 'title' => 'Etchant - syringe', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00067', 'title' => 'Explorer', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00068', 'title' => 'Face shield - แผ่นใส', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00069', 'title' => 'Face shield - หน้ากาก', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00070', 'title' => 'Fiber post #0.5', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00071', 'title' => 'Fiber post #1', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00072', 'title' => 'Fiber post #2', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00073', 'title' => 'Fiber post #3', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00074', 'title' => 'File 21mm #08', 'unit' => 'กล่อง', 'qty' => '4.00', 'unit_price' => '220.00000'],
            ['code' => '19-00075', 'title' => 'File 21mm #10', 'unit' => 'กล่อง', 'qty' => '5.00', 'unit_price' => '138.60000'],
            ['code' => '19-00076', 'title' => 'File 21mm #15', 'unit' => 'กล่อง', 'qty' => '10.00', 'unit_price' => '143.50000'],
            ['code' => '19-00077', 'title' => 'File 21mm #20', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '220.00000'],
            ['code' => '19-00078', 'title' => 'File 21mm #25', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '135.00000'],
            ['code' => '19-00079', 'title' => 'File 21mm #30', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '220.00000'],
            ['code' => '19-00080', 'title' => 'File 21mm #35', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '178.07333'],
            ['code' => '19-00081', 'title' => 'File 21mm #40', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '206.66667'],
            ['code' => '19-00082', 'title' => 'File 21mm #45', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '135.00000'],
            ['code' => '19-00083', 'title' => 'File 21mm #50', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '235.63000'],
            ['code' => '19-00084', 'title' => 'File 21mm #55', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '185.31500'],
            ['code' => '19-00085', 'title' => 'File 21mm #60', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '157.50000'],
            ['code' => '19-00086', 'title' => 'File 21mm #70', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '235.63000'],
            ['code' => '19-00087', 'title' => 'File 21mm #80', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '217.08000'],
            ['code' => '19-00088', 'title' => 'File 25mm #08', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '135.00000'],
            ['code' => '19-00089', 'title' => 'File 25mm #10', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '220.00000'],
            ['code' => '19-00090', 'title' => 'File 25mm #15', 'unit' => 'กล่อง', 'qty' => '7.00', 'unit_price' => '227.14286'],
            ['code' => '19-00091', 'title' => 'File 25mm #20', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '220.00000'],
            ['code' => '19-00092', 'title' => 'File 25mm #25', 'unit' => 'กล่อง', 'qty' => '5.00', 'unit_price' => '135.00000'],
            ['code' => '19-00093', 'title' => 'File 25mm #30', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '135.00000'],
            ['code' => '19-00094', 'title' => 'File 25mm #35', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '126.66500'],
            ['code' => '19-00095', 'title' => 'File 25mm #40', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '135.00000'],
            ['code' => '19-00096', 'title' => 'File 25mm #45', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '135.00000'],
            ['code' => '19-00097', 'title' => 'File 25mm #50', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '122.50000'],
            ['code' => '19-00098', 'title' => 'File 25mm #55', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '122.50000'],
            ['code' => '19-00099', 'title' => 'File 25mm #60', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '110.00000'],
            ['code' => '19-00100', 'title' => 'File 25mm #70', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '122.50000'],
            ['code' => '19-00101', 'title' => 'File 25mm #80', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '122.50500'],
            ['code' => '19-00105', 'title' => 'Film x-ray #0 - child', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00106', 'title' => 'Film x-ray #2 - adult', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00107', 'title' => 'Film x-ray #4 - occlusal', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00109', 'title' => 'Fissure carbide bur - impact', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00112', 'title' => 'Flowable composite A3.5 (Vertise Flow)', 'unit' => 'ถุง', 'qty' => '13.00', 'unit_price' => '1070.00000'],
            ['code' => '19-00113', 'title' => 'Flowable composite A3 (Z350)', 'unit' => 'ถุง', 'qty' => '5.00', 'unit_price' => '428.00000'],
            ['code' => '19-00115', 'title' => 'Flowable composite A3 (Vertise Flow)', 'unit' => 'ถุง', 'qty' => '2.00', 'unit_price' => '749.00000'],
            ['code' => '19-00116', 'title' => 'Flowable composite A3.5 (Z350)', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00117', 'title' => 'Fluoride gel', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00119', 'title' => 'Fluoride tray M', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00121', 'title' => 'Fluoride varnish', 'unit' => 'กล่อง', 'qty' => '5.00', 'unit_price' => '2080.80000'],
            ['code' => '19-00122', 'title' => 'Forceps', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00126', 'title' => 'Gates glidden drill #1', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00127', 'title' => 'Gates glidden drill #2', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00128', 'title' => 'Gates glidden drill #3', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00129', 'title' => 'Gelfoam', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '1150.00000'],
            ['code' => '19-00130', 'title' => 'Dycal carrier', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00131', 'title' => 'ยาชา 2% lidocaine with epinephrine 1:100,000', 'unit' => 'กล่อง', 'qty' => '20.00', 'unit_price' => '970.12500'],
            ['code' => '19-00132', 'title' => 'Lubricating cleaning agent (Hi-clean spray)', 'unit' => 'ขวด', 'qty' => '6.00', 'unit_price' => '590.00000'],
            ['code' => '19-00133', 'title' => 'GI base (Vitrebond)', 'unit' => 'กล่อง', 'qty' => '9.00', 'unit_price' => '3531.00000'],
            ['code' => '19-00134', 'title' => 'GI cement', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00135', 'title' => 'GI base ฉายแสง', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '2546.87500'],
            ['code' => '19-00136', 'title' => 'Gutta percha #15', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '350.00000'],
            ['code' => '19-00137', 'title' => 'Gutta percha #20', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '220.00000'],
            ['code' => '19-00138', 'title' => 'Gutta percha #25', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '250.00000'],
            ['code' => '19-00139', 'title' => 'Gutta percha #30', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '280.00000'],
            ['code' => '19-00140', 'title' => 'Gutta percha #35', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '250.00000'],
            ['code' => '19-00141', 'title' => 'Gutta percha #40', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '250.00000'],
            ['code' => '19-00142', 'title' => 'Gutta percha #45', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '250.00000'],
            ['code' => '19-00143', 'title' => 'Gutta percha #50', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '90.00000'],
            ['code' => '19-00144', 'title' => 'Gutta percha #55', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '220.00000'],
            ['code' => '19-00145', 'title' => 'Gutta percha #60', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '220.00000'],
            ['code' => '19-00146', 'title' => 'Gutta percha #70', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '90.00000'],
            ['code' => '19-00147', 'title' => 'Gutta percha #80', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '90.00000'],
            ['code' => '19-00148', 'title' => 'Handpiece - airoter', 'unit' => 'ชิ้้น', 'qty' => '5.00', 'unit_price' => '12240.00000'],
            ['code' => '19-00150', 'title' => 'Handpiece - straight', 'unit' => 'ชิ้น', 'qty' => '3.00', 'unit_price' => '6800.00000'],
            ['code' => '19-00151', 'title' => 'High power suction', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00154', 'title' => 'Inverted diamond bur', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00155', 'title' => 'IRM', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '1450.00000'],
            ['code' => '19-00156', 'title' => 'Ivory matrix band', 'unit' => 'ชิ้น', 'qty' => '1.00', 'unit_price' => '120.00000'],
            ['code' => '19-00157', 'title' => 'Ivory matrix retainer', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00158', 'title' => 'Joint สำหรับ handpiece กรอช้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00159', 'title' => 'Lateral cone Fine', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '227.27000'],
            ['code' => '19-00160', 'title' => 'Lateral cone Fine-Fine', 'unit' => 'กล่อง', 'qty' => '5.00', 'unit_price' => '250.00000'],
            ['code' => '19-00161', 'title' => 'Lateral cone M-Fine', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '250.00000'],
            ['code' => '19-00162', 'title' => 'Lateral cone X-Fine', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '250.00000'],
            ['code' => '19-00163', 'title' => 'Lenturo spiral 21mm', 'unit' => 'แพค', 'qty' => '5.00', 'unit_price' => '435.00000'],
            ['code' => '19-00164', 'title' => 'Lenturo spiral 25mm', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00165', 'title' => 'Light body silicone', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '1150.00000'],
            ['code' => '19-00171', 'title' => 'Matrix band 6mm', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '280.00000'],
            ['code' => '19-00174', 'title' => 'Microbrush', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '32.50000'],
            ['code' => '19-00176', 'title' => 'Mixing pad', 'unit' => 'เล่ม', 'qty' => '11.00', 'unit_price' => '147.14273'],
            ['code' => '19-00177', 'title' => 'Mixing plate', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00182', 'title' => 'Monophase silicone', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '1300.00000'],
            ['code' => '19-00183', 'title' => 'Mouth gag - adult', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00185', 'title' => 'Mouth mirror', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00186', 'title' => 'Mouth mirror - front surface', 'unit' => 'กล่อง', 'qty' => '4.00', 'unit_price' => '555.00000'],
            ['code' => '19-00187', 'title' => 'Mouth mirror - rear surface', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '380.00000'],
            ['code' => '19-00188', 'title' => 'Mouth prop - adult', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00190', 'title' => 'Needle holder', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00192', 'title' => 'Paper point L', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '60.00000'],
            ['code' => '19-00193', 'title' => 'Paper point M', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '60.00000'],
            ['code' => '19-00194', 'title' => 'Paper point S', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '60.00000'],
            ['code' => '19-00196', 'title' => 'Papoose board', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00200', 'title' => 'Peeso reamers #2', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '150.00000'],
            ['code' => '19-00202', 'title' => 'Pink wax', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00203', 'title' => 'PIP', 'unit' => 'กระปุก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00204', 'title' => 'Plaster spatula', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00207', 'title' => 'Pop-on เล็ก - coarse', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00208', 'title' => 'Pop-on เล็ก - medium', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00210', 'title' => 'Pop-on ใหญ่ - coarse', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00211', 'title' => 'Pop-on ใหญ่ - medium', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00213', 'title' => 'Prophy brush', 'unit' => 'แพค', 'qty' => '3.00', 'unit_price' => '320.00000'],
            ['code' => '19-00215', 'title' => 'NiTi file - rotary (ProTaper)', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00216', 'title' => 'NiTi file - hand (ProTaper)', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00217', 'title' => 'Curette', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00218', 'title' => 'Pumice', 'unit' => 'กระปุก', 'qty' => '4.00', 'unit_price' => '130.00000'],
            ['code' => '19-00219', 'title' => 'Putty silicone', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '2400.00000'],
            ['code' => '19-00220', 'title' => 'RC Prep', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00221', 'title' => 'Resin cement', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00222', 'title' => 'Retraction cord', 'unit' => 'ขวด', 'qty' => '1.00', 'unit_price' => '420.00000'],
            ['code' => '19-00223', 'title' => 'Root canal sealer', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00225', 'title' => 'Root canal spreader - D11T', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00226', 'title' => 'Root canal spreader - D11TS', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00227', 'title' => 'Root tip pick - double end', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00228', 'title' => 'หัวยางขัด acrylic - เขียว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00230', 'title' => 'Round carbide bur - กรอช้า', 'unit' => 'ตัว', 'qty' => '20.00', 'unit_price' => '63.93900'],
            ['code' => '19-00231', 'title' => 'Round carbide bur - กรอเร็ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00232', 'title' => 'Round carbide bur - impact', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '80.00000'],
            ['code' => '19-00233', 'title' => 'Round carbide bur - long shank', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '107.50000'],
            ['code' => '19-00234', 'title' => 'Round diamond bur', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '60.00000'],
            ['code' => '19-00235', 'title' => 'ถาดโลหะชุดตรวจ', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00236', 'title' => 'Round steel bur', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00237', 'title' => 'Rubber cup', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00238', 'title' => 'Rubber dam clamp #0', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00239', 'title' => 'Rubber dam clamp #14', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00240', 'title' => 'Rubber dam clamp #2', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00241', 'title' => 'Rubber dam forceps', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00244', 'title' => 'Rubber dam sheet - 5x5', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '240.00000'],
            ['code' => '19-00245', 'title' => 'Rubber dam sheet - 6x6', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '335.62500'],
            ['code' => '19-00246', 'title' => 'Rubber point', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00247', 'title' => 'Saliva ejector', 'unit' => 'แพค', 'qty' => '4.00', 'unit_price' => '85.00000'],
            ['code' => '19-00248', 'title' => 'Sandpaper strip', 'unit' => 'กล่อง', 'qty' => '7.00', 'unit_price' => '480.00000'],
            ['code' => '19-00249', 'title' => 'Sealant', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00250', 'title' => 'Sectional matrix band', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00258', 'title' => 'Stone type 2 - สีเขียว', 'unit' => 'กิโลกรัม', 'qty' => '4.00', 'unit_price' => '70.83250'],
            ['code' => '19-00259', 'title' => 'Stone type 3 - Velmix', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00260', 'title' => 'Syringe สำหรับฉีดยาชา', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00262', 'title' => 'Talbot solution', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00263', 'title' => 'Taper diamond bur - ยาว', 'unit' => 'ตัว', 'qty' => '5.00', 'unit_price' => '260.00000'],
            ['code' => '19-00264', 'title' => 'Taper diamond bur - สั้น', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00266', 'title' => 'T-band', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00267', 'title' => 'Temporary cement (Temp-Bond)', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00268', 'title' => 'Temporary filling material (Cavit)', 'unit' => 'ขวด', 'qty' => '1.00', 'unit_price' => '350.00000'],
            ['code' => '19-00271', 'title' => 'Tofflemire matrix band', 'unit' => 'ชิ้น', 'qty' => '5.00', 'unit_price' => '280.00000'],
            ['code' => '19-00272', 'title' => 'Tofflemire matrix retainer', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00273', 'title' => 'Tray adhesive', 'unit' => 'ขวด', 'qty' => '3.00', 'unit_price' => '480.00000'],
            ['code' => '19-00274', 'title' => 'Vitapex', 'unit' => 'กล่อง', 'qty' => '4.00', 'unit_price' => '2292.00000'],
            ['code' => '19-00276', 'title' => 'Wedge L', 'unit' => 'ห่อ', 'qty' => '4.00', 'unit_price' => '120.00000'],
            ['code' => '19-00277', 'title' => 'Wedge M', 'unit' => 'ห่อ', 'qty' => '3.00', 'unit_price' => '120.00000'],
            ['code' => '19-00278', 'title' => 'Wedge S', 'unit' => 'ห่อ', 'qty' => '2.00', 'unit_price' => '50.00000'],
            ['code' => '19-00279', 'title' => 'White stone - flame', 'unit' => 'ตัว', 'qty' => '48.00', 'unit_price' => '62.50000'],
            ['code' => '19-00280', 'title' => 'White stone - round', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00281', 'title' => 'Zinc phosphate cement', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00282', 'title' => 'Zinc oxide', 'unit' => 'กระปุก', 'qty' => '1.00', 'unit_price' => '200.00000'],
            ['code' => '19-00283', 'title' => 'กรรไกรตัดไหม', 'unit' => 'เล่ม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00284', 'title' => 'หัวขูดหินปูนไฟฟ้า P10', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00285', 'title' => 'กระจกถ่ายรูปในช่องปาก', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00286', 'title' => 'เข็มยาว 30 mm gauge 27', 'unit' => 'กล่อง', 'qty' => '8.00', 'unit_price' => '175.00000'],
            ['code' => '19-00287', 'title' => 'เข็มเย็บ 3/8', 'unit' => 'ถุง', 'qty' => '3.00', 'unit_price' => '280.00000'],
            ['code' => '19-00288', 'title' => 'เข็มสั้น 21 mm gauge 27', 'unit' => 'กล่อง', 'qty' => '10.00', 'unit_price' => '165.00000'],
            ['code' => '19-00291', 'title' => 'ถาดโลหะ มีฝาปิด 12x8x2', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00292', 'title' => 'ถุงมือ M', 'unit' => 'กล่อง', 'qty' => '6.00', 'unit_price' => '185.00000'],
            ['code' => '19-00293', 'title' => 'ถุงมือ XS', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '62.73000'],
            ['code' => '19-00294', 'title' => 'น้ำยาฆ่าเชื้อเช็ดพื้นผิว', 'unit' => 'แกลลอน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00296', 'title' => 'น้ำยาฆ่าเชื้อวัสดุพิมพ์ปาก - ขวด', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00297', 'title' => 'น้ำยาล้างฟิล์ม - ชุด', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00300', 'title' => 'น้ำยาล้าง suction', 'unit' => 'ขวด', 'qty' => '2.00', 'unit_price' => '3100.00000'],
            ['code' => '19-00301', 'title' => 'น้ำยาห้ามเลือด', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00302', 'title' => 'แปรงสีฟันเด็ก 0-3 ปี', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00303', 'title' => 'แปรงสีฟันเด็ก 3-6 ปี', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00304', 'title' => 'ผงแช่ทำความสะอาด tray พิมพ์ปาก', 'unit' => 'ขวด', 'qty' => '5.00', 'unit_price' => '1450.00000'],
            ['code' => '19-00305', 'title' => 'ผ้าเช็ดทำความสะอาดพื้นผิว - สำเร็จรูป', 'unit' => 'กระปุก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00308', 'title' => 'ยาชา 2% mepivacaine with epinephrine 1:100,000', 'unit' => 'กล่อง', 'qty' => '9.00', 'unit_price' => '950.00000'],
            ['code' => '19-00309', 'title' => 'ยาชา 4% articaine with epinephrine 1:100,000', 'unit' => 'กล่อง', 'qty' => '4.00', 'unit_price' => '1605.25250'],
            ['code' => '19-00310', 'title' => 'ยาชาเจล', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00313', 'title' => 'หลอดไฟสำหรับเครื่องฉายแสง', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00314', 'title' => 'หัวขัดฟันปลอมโลหะ', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00321', 'title' => 'Amalgam plugger', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00322', 'title' => 'Amalgam carver', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00323', 'title' => 'Ball burnisher', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00324', 'title' => 'Plastic instrument - amalgam', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00325', 'title' => 'Pop-on เล็ก - fine', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00327', 'title' => 'Elevator', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00332', 'title' => 'Triple syringe tip', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00333', 'title' => 'Snap A-Ray', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00335', 'title' => 'Fluocinolone 0.1% orabase 5 g', 'unit' => 'กระปุก', 'qty' => '10.00', 'unit_price' => '80.00000'],
            ['code' => '19-00336', 'title' => 'Pop-on kit', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00337', 'title' => 'Flowable composite (Tetric n-flow)', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00338', 'title' => 'GI base/filling (Fuji II LC)', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00339', 'title' => 'MTA', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00341', 'title' => 'Fluocinolone 0.1% solution 15 ml', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00342', 'title' => 'Fluocinolone oral based', 'unit' => 'กระปุก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00344', 'title' => 'ถุงมือ L', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00345', 'title' => 'Fresh Care Oil', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00346', 'title' => 'ชุดหัวกรอทำเดือยฟัน (D.T. Light Post Drills)', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00347', 'title' => 'Round diamond bur - long shank', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '80.00000'],
            ['code' => '19-00348', 'title' => 'Acrylic -liquid', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '900.00000'],
            ['code' => '19-00349', 'title' => '17% EDTA', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00350', 'title' => 'Matrix band #1', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00351', 'title' => 'Sickle', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00352', 'title' => 'Sickle H6-7', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00353', 'title' => 'Sickle H6-H7', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00354', 'title' => 'Jacquette 30-33', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00355', 'title' => 'Jacquette J1-1S', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00356', 'title' => 'GI Capsule', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00357', 'title' => 'Applicator ปืนฉีด GI', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00358', 'title' => 'Steel cutter bur (หัวมะเฟือง)', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00359', 'title' => 'กระดาษเช็ดพื้นผิว สำหรับฆ่าเชื้อโรค (wipe)', 'unit' => 'แพค', 'qty' => '6.00', 'unit_price' => '317.46833'],
            ['code' => '19-00360', 'title' => 'หัวกรอ NTI Carbide (มะเฟืองคาดเหลือง)', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00361', 'title' => 'Gates glidden drill #4', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00362', 'title' => 'Composite A1B (Filtek Z350 XT)', 'unit' => 'หลอด', 'qty' => '3.00', 'unit_price' => '856.00000'],
            ['code' => '19-00363', 'title' => 'Luxator (ชุด kit)', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00364', 'title' => 'Luxator', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00365', 'title' => 'Luxator periotome', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00366', 'title' => 'Composite carver (IT Spatula)', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00368', 'title' => 'Root elevator - right', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00369', 'title' => 'Root elevator - left', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00371', 'title' => 'Stone 24 kg', 'unit' => 'ลัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00372', 'title' => 'Root tip elevator', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00373', 'title' => 'Fissure Carbide bur - impact (box)', 'unit' => 'กล่อง', 'qty' => '3.00', 'unit_price' => '446.00000'],
            ['code' => '19-00374', 'title' => 'Composite A3.5', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00375', 'title' => 'Composite สี light', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00376', 'title' => 'Composite สี dark', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00377', 'title' => 'Composite สี medium', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00378', 'title' => 'Amalgam 1 spill ชุดเล็ก (50 capsules)', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00379', 'title' => 'Carbide cutter - แดง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00380', 'title' => 'Rubber dam clamp #8', 'unit' => 'อัน', 'qty' => '4.00', 'unit_price' => '380.00000'],
            ['code' => '19-00384', 'title' => 'หัวขัด Enhance - point', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '1284.00000'],
            ['code' => '19-00385', 'title' => 'หัวขัด Enhance - disc', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '1605.00000'],
            ['code' => '19-00386', 'title' => 'ซองกันน้ำลายฟิล์มเบอร์ 2 (500 ชิ้น/กล่อง)', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00387', 'title' => 'Amalgam 2 spill ชุดเล็ก (50 capsules)', 'unit' => 'แพค', 'qty' => '7.00', 'unit_price' => '2950.00000'],
            ['code' => '19-00388', 'title' => 'XCP set x-ray ฟัน', 'unit' => 'ชุด', 'qty' => '1.00', 'unit_price' => '5500.00000'],
            ['code' => '19-00389', 'title' => 'ยาทาลดอาการเสียวฟัน (Gluma)', 'unit' => 'ขวด', 'qty' => '1.00', 'unit_price' => '1500.00000'],
            ['code' => '19-00390', 'title' => 'Rubber dam clamp #9', 'unit' => 'อัน', 'qty' => '2.00', 'unit_price' => '380.00000'],
            ['code' => '19-00391', 'title' => 'Green stone - flame', 'unit' => 'อัน', 'qty' => '15.00', 'unit_price' => '96.66667'],
            ['code' => '19-00392', 'title' => 'CMCP camphophenol', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00393', 'title' => 'round white stone กรอเร็ว 1 โหล', 'unit' => 'กล่อง', 'qty' => '2.00', 'unit_price' => '700.00000'],
            ['code' => '19-00394', 'title' => 'Flame white stone กรอเร็ว 1 โหล', 'unit' => 'กล่อง', 'qty' => '1.00', 'unit_price' => '700.00000'],
            ['code' => '19-00395', 'title' => 'Ketac universal', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00397', 'title' => 'Diamond bur 008', 'unit' => 'ตัว', 'qty' => '30.00', 'unit_price' => '45.00000'],
            ['code' => '19-00398', 'title' => 'Diamond bur 010', 'unit' => 'ตัว', 'qty' => '10.00', 'unit_price' => '45.00000'],
            ['code' => '19-00400', 'title' => 'Proximal composite carver', 'unit' => 'ด้าม', 'qty' => '10.00', 'unit_price' => '250.00000'],
            ['code' => '19-00401', 'title' => 'Prophy paste', 'unit' => 'กระปุก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00404', 'title' => 'Grick No.1', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00405', 'title' => 'ข้อต่อ Suction', 'unit' => 'ชุด', 'qty' => '1.00', 'unit_price' => '2140.00000'],
            ['code' => '19-00409', 'title' => 'Luxator 3mm', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00410', 'title' => 'Luxator 5mm', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00411', 'title' => 'Luxator 2mm', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00412', 'title' => 'ข้อต่อ air motor', 'unit' => 'อัน', 'qty' => '2.00', 'unit_price' => '10000.00000'],
            ['code' => '19-00413', 'title' => 'opal dam', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00414', 'title' => 'Diamon bur 007', 'unit' => 'อัน', 'qty' => '30.00', 'unit_price' => '65.00000'],
            ['code' => '19-00416', 'title' => 'Aquasil ultra smart', 'unit' => '', 'qty' => '1.00', 'unit_price' => '4066.00000'],
            ['code' => '19-00418', 'title' => 'Protaper gold 21 mm', 'unit' => '', 'qty' => '4.00', 'unit_price' => '1754.80000'],
            ['code' => '19-00419', 'title' => 'Protaper gold25mm', 'unit' => '', 'qty' => '3.00', 'unit_price' => '1826.13333'],
            ['code' => '19-00423', 'title' => 'หินลับมีดสีดำ', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '19-00425', 'title' => 'เฟืองมอเตอร์เกียร์พนักพิงหลังยูนิต', 'unit' => '', 'qty' => '1.00', 'unit_price' => '3745.00000'],
            ['code' => '19-00426', 'title' => 'safe tip19', 'unit' => '', 'qty' => '2.00', 'unit_price' => '45.00000'],
            ['code' => '19-00427', 'title' => 'safe tip 19L', 'unit' => '', 'qty' => '2.00', 'unit_price' => '45.00000'],
            ['code' => '19-00428', 'title' => 'safe tip 117A', 'unit' => '', 'qty' => '2.00', 'unit_price' => '45.00000'],
            ['code' => '19-00429', 'title' => 'Eucalyptus oil', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0;
            foreach ($data as $key => $value) {
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code'], 'title' => $value['title']]);
                $unit = Categorise::findOne(['name' => 'unit', 'title' => $value['unit']]);
                // ถ้าไม่มีหน่วยให้สร้างใหม่
                if (!$unit) {
                    $newUnit = new Categorise([
                        'name' => 'unit',
                        'title' => $value['unit'],
                        'active' => 1,
                    ]);
                    $newUnit->save(false);
                }
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => $assettype,
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => $categoryName,
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $lot = \mdm\autonumber\AutoNumber::generate('LOT' . substr(AppHelper::YearBudget(), 2) . '-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => $warehouse_id,
                    'qty' => $value['qty'],
                    'unit_price' => (float) $value['unit_price'],
                    'order_status' => 'pending',
                    'data_json' => [
                        'req_qty' => '0',
                        'exp_date' => '',
                        'mfg_date' => '',
                        'item_type' => 'ยอดยกมา',
                        'po_number' => '',
                        'pq_number' => '',
                        'asset_type' => '',
                        'receive_date' => '',
                        'asset_type_name' => '',
                        'employee_fullname' => 'Administrator Lastname',
                        'employee_position' => 'นักวิชาการคอมพิวเตอร์',
                        'employee_department' => 'งานซ่อมบำรุง',
                    ],
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
                // echo (DOUBLE) $value['unit_price'],"\n";
                if ($model->save(false)) {
                    echo 'นำเข้า ' . $value['code'] . ' รหัส : ' . $value['code'] . "สำเร็จ! \n";
                } else {
                    echo 'นำเข้า ' . $value['code'] . ' รหัส : ' . $value['code'] . "ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }
}
