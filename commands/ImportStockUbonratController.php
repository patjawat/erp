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
            'category_id' => 346,
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
            'category_id' => 347,
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
            'assettype' => 'M12',
            'categoryName' => 'วัสดุคอมพิวเตอร์',
            'category_id' => 348,
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



    // วัสดุทันตกรรม
    public function actionM19()
    {
        $data = [
            'thai_year' => 2568,
            'warehouse_id' => 21,
            'assettype' => 'M19',
            'categoryName' => 'วัสดุทันตกรรม',
            'category_id' => 349,
            'code' => 'IN-680005',
            'items' => [
                ['title' => 'Round 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 016', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 018', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 020', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 022', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 024', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure short 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure short 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure short 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame superfine', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดน้ำเงิน 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดแดง 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดเหลือง 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดน้ำเงิน 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดแดง 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดเหลือง 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดน้ำเงิน 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดแดง 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra(round end) taper คาดเหลือง 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra taper (Safetip)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra superfine rugby', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra superfine taper', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra Diamond end cutting', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra Diamond 801L', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round white', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame white', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame green', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame คาดเหลือง', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rugby คาดเหลือง', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Needle คาดเหลือง', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame คาดน้ำเงิน', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 016', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 018', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 016', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 018', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 006', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 016', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 018', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 020', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 022', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 016', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 018', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 020', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round 022', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Long shank Round 014', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Long shank Round 016', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 010', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure 012', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round white', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame white', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame green', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Pop on', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rubber cup', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ยางแดง (ขัดอมัลกัม)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ยางเขียว (ขัดอมัลกัม)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round for impaction', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Round for prosthetic', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure for impaction', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fissure for prosthetic', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Barrelshape for prosthetic', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Egg shape for prosthetic', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Pear shape for prosthetic', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Taper shape for prosthetic', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cone shape for prosthetic', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'หัวมะเฟืองแต่งกระดูก(เล็ก)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'หัวมะเฟืองแต่งกระดูก(ใหญ่)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'หัวมะยมสำหรับแต่งกระดูก(เล็ก)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'หัวมะยมสำหรับแต่งกระดูก(ใหญ่)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra green', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra yellow', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Extra grey', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Flame shape', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cup shape', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Knife edge shape', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Torpedo shape', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Barbbroach 21 mm', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Hand file', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File H21 mm no 8', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File H21 mm no 10', 'unit' => 'SD','unit_price' =>178.00,'qty' =>0],
                ['title' => 'File H21 mm no 15', 'unit' => 'SD','unit_price' =>178.00,'qty' =>0],
                ['title' => 'File H21 mm no 15-40', 'unit' => 'SD','unit_price' =>178.00,'qty' =>0],
                ['title' => 'File H21 mm no 45-80', 'unit' => 'SD','unit_price' =>178.00,'qty' =>0],
                ['title' => 'File H25 mm no 8', 'unit' => 'SD','unit_price' =>178.00,'qty' =>0],
                ['title' => 'File H25 mm no 10', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File H25 mm no 15', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File H25 mm no 15-40', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File H25 mm no 45-80', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K21 mm no 8', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K21 mm no 10', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K21 mm no 15', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K21 mm no 15-40', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K21 mm no 45-80', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K25 mm no 8', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K25 mm no 10', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K25 mm no 15', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K25 mm no 15-40', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K25 mm no 45-80', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File Niti flex 25 mm no 15-40', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File Niti flex 25 mm no 45-80', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K31 mm no 20-40', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'File K31 mm no 45-80', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Calcium powder', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Root canal cement', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Clove oil', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'EDTA', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Gutta percha cone', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Lateral cone', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Main cone 15-40', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Main cone 45-80', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Calcium paste (Metapaste) สีขาว', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Lenturo spiral (116)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Paper point', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'S (140)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'M (139)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'L (138)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Sodiumhypochlorite solution', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'RC prep (148)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Gate gridden drill(no.1-6)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Endo excavator spoon', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Endo explorer', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Endo plugger', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Endo ray', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Finger ruler', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'แบตเตอรี่เครื่องวัดความยาวรากฟัน', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Endo spreader', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Glick no.1', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Mouth mirror (ด้าม)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Mouth mirror (กระจก)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Front suface', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rear surface', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cotton plier', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Explorer #5', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Glass', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Trayใส่เครื่องมือ 5x9', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'กระจกถ่ายภาพในช่องปาก', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '1ชุด', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '1ชิ้น', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cement spatula (โลหะ)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cement spatula (plastic)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Face shield (set)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Face shield (refill)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Glass slab', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Syringe for injection', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Straight handpiece', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Airotor handpiece', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Contra handpiece', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Prophy hanspiece', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ข้อต่อ airotor handpiece', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rubber cup', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rubber bowl', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rubber dam sheet', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rubber dam clamp', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Ruler', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Alcohol burner', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Tray (rimlock)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Tray (perforated)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Tray (edentulous)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Tray (partial)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Dental floss', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Film DF OCC', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Film DF (Adult-2)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Film DF (child-0)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Pumice', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Gingival cord no00', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Gingival cord no0', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Gingival cord no1', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Topical anesthesia', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Long needle', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Short needle', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Saliva ejector', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Disinfectant spray', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maintenance oil spray', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Oil spray for hand piece', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'น้ำยาล้างSuction', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Sterile sock', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cavit cement', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'IRM', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Mouth gag', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Mouth prop', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Mouth prop (ใหญ่)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Mouth prop (เล็ก)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'นิ้วเหล็ก', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'หัวพู่กัน', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Articulating paper (horse shoe)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Articulating paper (Curve)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cement : polycarboxylate', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cement : Zinc-phosphate', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Disposable cap', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Snap-A-ray', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cord plugger', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'น้ำยาล้างฟิล์ม', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'สเปรย์ฆ่าเชื้อในบรรยากาศ', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ท่อ High power suction', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Amalgam ball burnisher', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Amalgam carrier', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Amalgam carver', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Amalgam plugger', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Dycal carrier', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Spoon excavator', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Matrix retainer (Tofflemire)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Matrix retainer (Ivory)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Plastic instrument', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Amalgam capsule', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Bonding# self etch', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Bonding# total etch', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Etchant', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'GI filling (self cure)(ฟันน้ำนม)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'GI filling (dual cure)(ฟันแท้)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'GI base (Base)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Dentin conditioner', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Composite (Ant. teeth)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Composite (Post. teeth)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Compomer (Lime lite)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Composite (Flowable)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Ca-hydroxide paste(Dycal)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Desensitizer', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (Ivory no.11)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (Ivory no.12)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (Ivory no.13)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (Ivory no.14)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (Ivory no.15)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (Ivory no.16)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (Tufflemire)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (celluliod)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Micro applicator /brush', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ด้ามพู่กัน', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Sand paper strip', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Wedge : wooden', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Pop on disc', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Calcium hydroxide paste (metapex)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Formocresol', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Maxtrix band (T-band)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Zinc-oxide with acetate', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Zinc-oxide without acetate', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Periodontal curette', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Sickel', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'P-10', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Periodontal pack (Co-pack)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Talbot-s solution', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fluoride gel', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fluoride varnish', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fluoride tray', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ยาสีฟัน', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'แปรงสีฟัน (0-3)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'แปรงสีฟัน (3-6)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'แปรงสีฟัน (6-12)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'แปรงสีฟัน (ผู้ใหญ่)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Sealant', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Prophylaxis paste', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'พู่กันขัดฟัน', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Dental occlusal plate', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Plaster spatula', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Wax knife', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Self cure for reline', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Self cure for temp crown', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Tissue conditioner', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Stone', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Velmix', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Wax(pink)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Wax(utility)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Wax(alu)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Wax(sticky)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Adhesive resin cement', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Alginate', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Automixed tip for gun type', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Porcelain polishing kit', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Pocrelain adjustment kit', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fiber post kit', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Fiber post refilled', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Pressure spot indicator', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'PIP', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Silicone impression(light)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Silicone impression(putty)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Silicone adhesive', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Green stick compound', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Temp bond', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Intraoral tip', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Piezo drill no.1-6', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'คีมดัดลวด', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'คีมตัดลวด', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'crown remover (GC plier)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Torch', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Bridge remover', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'เก๊ยงทันตกรรม', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Bone curette', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Elevator-root tip pick', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Elevator / LUXATOR', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Suction tip high power', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cow horn forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Root forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '88L forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '88R forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '150 Forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '151 Forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '150s Forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => '151s Forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Hemostatic agent(แบบน้ำ)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Hemostatic agent(Gelfoam)', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Cheek retractor', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Molt no.9', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Seldin', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Rongeur forceps', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Needle holder', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'กรรไกรตัดไหม', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ถาดใส่ชุดผ่าฟันคุด', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Bone file', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'DM = Dent mate', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'DDI = Drive Dental Incorporation', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'SD = S.D ทันตเวช', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Ald = Aldenta', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'VRP = วีอาร์พี', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'Emi = Eminence', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
                ['title' => 'ACC = Accord', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
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

}
