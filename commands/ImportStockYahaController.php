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
use app\modules\inventory\models\Stock;
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
class ImportStockYahaController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        // $this->M7();
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
                ['title' => 'กระดาษกาวขาว 1 นิ้ว', 'unit' => 'ม้วน','unit_price' =>45.00,'qty' =>0],
                ['title' => 'ก้อนดับกลิ่นวิมโป้', 'unit' => 'ก้อน','unit_price' =>30.00,'qty' =>0],
                ['title' => 'กระดาษการ์ดสี A 4', 'unit' => 'รีม','unit_price' =>110.00,'qty' =>0],
                ['title' => 'กระดาษการ์ดสี F 4', 'unit' => 'รีม','unit_price' =>125.00,'qty' =>0],
                ['title' => 'กระดาษคาร์บอน', 'unit' => 'กล่อง','unit_price' =>165.00,'qty' =>0],
                ['title' => 'กระดาษชำระ (ม้วน)', 'unit' => 'ม้วน','unit_price' =>7.00,'qty' =>0],
                ['title' => 'กระดาษถ่ายเอกสาร F.4', 'unit' => 'รีม','unit_price' =>165.00,'qty' =>0],
                ['title' => 'เทปกาวสีน้ำตาล', 'unit' => 'ม้วน','unit_price' =>45.00,'qty' =>0],
                ['title' => 'กระดาษแฟ็กส์', 'unit' => 'ม้วน','unit_price' =>90.00,'qty' =>0],
                ['title' => 'กระดาษโรเนียว A.4', 'unit' => 'รีม','unit_price' =>110.00,'qty' =>0],
                ['title' => 'กระดาษโรเนียว F.4', 'unit' => 'รีม','unit_price' =>145.00,'qty' =>0],
                ['title' => 'กระดาษลอกลาย', 'unit' => 'แผ่น','unit_price' =>4.00,'qty' =>0],
                ['title' => 'กระดาษถ่ายเอกสาร A.4', 'unit' => 'รีม','unit_price' =>110.00,'qty' =>0],
                ['title' => 'กาวน้ำ', 'unit' => 'ขวด','unit_price' =>12.00,'qty' =>0],
                ['title' => 'แก้วน้ำพลาสติก', 'unit' => 'ใบ','unit_price' =>8.00,'qty' =>0],
                ['title' => 'กระบอกไฟฉายใช้ถ่าน 2 ก้อน', 'unit' => 'อัน','unit_price' =>135.00,'qty' =>0],
                ['title' => 'กระดาษเช็ดมือแบบพับขนาด 25.70x20.60 ซม.', 'unit' => 'ห่อ','unit_price' =>65.00,'qty' =>0],
                ['title' => 'กระบอกฉีดใส่น้ำยาเช็ดกระจก', 'unit' => 'อัน','unit_price' =>45.00,'qty' =>0],
                ['title' => 'กรวยพลาสติก', 'unit' => 'อัน','unit_price' =>15.00,'qty' =>0],
                ['title' => 'กาวลาเท็ค', 'unit' => 'ขวด','unit_price' =>75.00,'qty' =>0],
                ['title' => 'ขันน้ำพลาสติก', 'unit' => 'ใบ','unit_price' =>25.00,'qty' =>0],
                ['title' => 'คลิปหนีบดำ 2 ขากลาง เบอร์ 110', 'unit' => 'กล่อง','unit_price' =>60.00,'qty' =>0],
                ['title' => 'คลิปหนีบดำ 2 ขาใหญ่ เบอร์ 108', 'unit' => 'กล่อง','unit_price' =>90.00,'qty' =>0],
                ['title' => 'คลิปหนีบดำ 2 ขาเล็ก เบอร์ 111', 'unit' => 'กล่อง','unit_price' =>40.00,'qty' =>0],
                ['title' => 'คลอรีนผง 60%', 'unit' => 'ถัง','unit_price' =>4,280.00,'qty' =>0],
                ['title' => 'เชือกผูกขาวแดง', 'unit' => 'ม้วน','unit_price' =>35.00,'qty' =>0],
                ['title' => 'เชือกฟาง', 'unit' => 'ม้วน','unit_price' =>75.00,'qty' =>0],
                ['title' => 'ซองจดหมายครุฑสีขาว (500 ซอง/กล่อง)', 'unit' => 'แพ็ค','unit_price' =>500.00,'qty' =>0],
                ['title' => 'ซองจดหมายครุฑสีน้ำตาลขยายข้าง (ใหญ่)', 'unit' => 'ซอง','unit_price' =>5.00,'qty' =>0],
                ['title' => 'ซองสีน้ำตาลไม่ขยายข้าง 9x12 นิ้ว', 'unit' => 'ซอง','unit_price' =>4.00,'qty' =>0],
                ['title' => 'ซองสีน้ำตาลขยายข้าง 9x12 นิ้ว', 'unit' => 'ซอง','unit_price' =>3.00,'qty' =>0],
                ['title' => 'ซองสีน้ำตาลพับ 2', 'unit' => 'ซอง','unit_price' =>2.00,'qty' =>0],
                ['title' => 'ตลับสแตมพลาส', 'unit' => 'กล่อง','unit_price' =>45.00,'qty' =>0],
                ['title' => 'ปลั๊กไฟแบบมีสวิทเปิด-ปิด ยาว 5 เมตร', 'unit' => 'อัน','unit_price' =>385.00,'qty' =>0],
                ['title' => 'เต้าเสียบ', 'unit' => 'อัน','unit_price' =>40.00,'qty' =>0],
                ['title' => 'ถุงใสขยายข้าง ขนาด 16x30', 'unit' => 'กก','unit_price' =>125.00,'qty' =>0],
                ['title' => 'ถุงดำขยายข้างขนาด 16x30 นิ้ว', 'unit' => 'กิโลกรัม','unit_price' =>65.00,'qty' =>0],
                ['title' => 'ถุงดำขยายข้างขนาด 33x33 นิ้ว', 'unit' => 'กิโลกรัม','unit_price' =>65.00,'qty' =>0],
                ['title' => 'ถุงดำขยายข้างขนาด 9x18 นิ้ว', 'unit' => 'กิโลกรัม','unit_price' =>65.00,'qty' =>0],
                ['title' => 'ถุงแดงขยายข้างขนาด 9x18 นิ้ว', 'unit' => 'กิโลกรัม','unit_price' =>70.00,'qty' =>0],
                ['title' => 'ถุงแดงขยายข้างขนาด 16x30 นิ้ว', 'unit' => 'กิโลกรัม','unit_price' =>115.00,'qty' =>0],
                ['title' => 'ถุงแดงขยายข้างขนาด 33x33 นิ้ว', 'unit' => 'กิโลกรัม','unit_price' =>70.00,'qty' =>0],
                ['title' => 'ถังน้ำพลาสติกขนาด 20 แกลลอน', 'unit' => 'ใบ','unit_price' =>425.00,'qty' =>0],
                ['title' => 'ถังน้ำพลาสติกขนาด 3 แกลลอนแบบหิ้ว', 'unit' => 'ใบ','unit_price' =>65.00,'qty' =>0],
                ['title' => 'ถังน้ำพลาสติกขนาด 8 แกลลอน', 'unit' => 'ใบ','unit_price' =>90.00,'qty' =>0],
                ['title' => 'ถ่านไฟฉายก้อนกลาง', 'unit' => 'ก้อน','unit_price' =>18.00,'qty' =>0],
                ['title' => 'ถ่านไฟฉายก้อนใหญ่', 'unit' => 'ก้อน','unit_price' =>14.00,'qty' =>0],
                ['title' => 'ถุงมือทำความสะอาดสีส้ม', 'unit' => 'คู่','unit_price' =>65.00,'qty' =>0],
                ['title' => 'ถุงร้อนใสขนาด 7x14 นิ้ว', 'unit' => 'กิโลกรัม','unit_price' =>105.00,'qty' =>0],
                ['title' => 'ถ่านอังคลายขนาด 2 A', 'unit' => 'ก้อน','unit_price' =>25.00,'qty' =>0],
                ['title' => 'ถ่านอังคลายขนาด 3 A', 'unit' => 'ก้อน','unit_price' =>25.00,'qty' =>0],
                ['title' => 'ถ่านไฟฉาย ก้อนเล็ก', 'unit' => 'ก้อน','unit_price' =>10.00,'qty' =>0],
                ['title' => 'เทปกาว 2 หน้า', 'unit' => 'ม้วน','unit_price' =>175.00,'qty' =>0],
                ['title' => 'ที่โกยขยะพลาสติก', 'unit' => 'อัน','unit_price' =>45.00,'qty' =>0],
                ['title' => 'เทปกาวใส 1 นิ้ว', 'unit' => 'ม้วน','unit_price' =>40.00,'qty' =>0],
                ['title' => 'ที่โกนหนวด', 'unit' => 'อัน','unit_price' =>65.00,'qty' =>0],
                ['title' => 'ที่เช็ดกระจกด้าม (ยาว)', 'unit' => 'อัน','unit_price' =>195.00,'qty' =>0],
                ['title' => 'ที่เช็ดกระจกด้าม (สั้น)', 'unit' => 'อัน','unit_price' =>90.00,'qty' =>0],
                ['title' => 'ผ้าเช็ดเท้า', 'unit' => 'ผืน','unit_price' =>45.00,'qty' =>0],
                ['title' => 'ทะเบียบคุมเงินงบประมาณ (แบบ 402)', 'unit' => 'เล่ม','unit_price' =>80.00,'qty' =>0],
                ['title' => 'ทะเบียบคุมเงินนอกงบประมาณ (404)', 'unit' => 'เล่ม','unit_price' =>140.00,'qty' =>0],
                ['title' => 'ทะเบียนหนังสือรับ', 'unit' => 'เล่ม','unit_price' =>60.00,'qty' =>0],
                ['title' => 'ทะเบียนหนังสือส่ง', 'unit' => 'เล่ม','unit_price' =>60.00,'qty' =>0],
                ['title' => 'เทปพันสายไฟ', 'unit' => 'ม้วน','unit_price' =>35.00,'qty' =>0],
                ['title' => 'ธงชาติไทยขนาด 80x120 ซม.', 'unit' => 'ผืน','unit_price' =>85.00,'qty' =>0],
                ['title' => 'ธงชาติไทยขนาด 150x225 ซม.', 'unit' => 'ผืน','unit_price' =>280.00,'qty' =>0],
                ['title' => 'หน้ากากชนิดสามช่อง', 'unit' => 'อัน','unit_price' =>10.00,'qty' =>0],
                ['title' => 'น้ำกลั่น', 'unit' => 'ขวด','unit_price' =>20.00,'qty' =>0],
                ['title' => 'น้ำมันเครื่อง V120 บรรจุ 1 ลิตร', 'unit' => 'กระป๋อง','unit_price' =>120.00,'qty' =>0],
                ['title' => 'น้ำมันเครื่อง V120 สำหรับเครื่องดีเซล', 'unit' => 'แกลลอน','unit_price' =>480.00,'qty' =>0],
                ['title' => 'น้ำมันเครื่อง V120 สำหรับเครื่องเบนซิล', 'unit' => 'แกลลอน','unit_price' =>480.00,'qty' =>0],
                ['title' => 'น้ำมันทูที', 'unit' => 'กระป๋อง','unit_price' =>120.00,'qty' =>0],
                ['title' => 'น้ำมันเบรก', 'unit' => 'กระป๋อง','unit_price' =>220.00,'qty' =>0],
                ['title' => 'น้ำยาฆ่าแมลงแบบสเปรย์', 'unit' => 'กระป๋อง','unit_price' =>125.00,'qty' =>0],
                ['title' => 'ใบมีดโกน', 'unit' => 'กล่อง','unit_price' =>490.00,'qty' =>0],
                ['title' => 'ใบมีดคัตเตอร์ขนาดใหญ่', 'unit' => 'กล่อง','unit_price' =>50.00,'qty' =>0],
                ['title' => 'ใบมีดคัตเตอร์ขนาดเล็ก', 'unit' => 'กล่อง','unit_price' =>30.00,'qty' =>0],
                ['title' => 'แบบรายงานคงเหลือประจำวัน (407)', 'unit' => 'เล่ม','unit_price' =>150.00,'qty' =>0],
                ['title' => 'บาลาสขนาด 20 W', 'unit' => 'ตัว','unit_price' =>85.00,'qty' =>0],
                ['title' => 'บาลาสขนาด 40 W', 'unit' => 'ตัว','unit_price' =>85.00,'qty' =>0],
                ['title' => 'บล็อกลอย', 'unit' => 'อัน','unit_price' =>15.00,'qty' =>0],
                ['title' => 'ปากกาเขียนไวบอร์ด', 'unit' => 'ด้าม','unit_price' =>25.00,'qty' =>0],
                ['title' => 'ปากกาเคมี', 'unit' => 'ด้าม','unit_price' =>18.00,'qty' =>0],
                ['title' => 'แปรงถูพื้นด้ามยาว', 'unit' => 'อัน','unit_price' =>95.00,'qty' =>0],
                ['title' => 'แปรงล้างห้องน้ำ', 'unit' => 'อัน','unit_price' =>35.00,'qty' =>0],
                ['title' => 'ปลั๊กเสียบตัวผู้', 'unit' => 'อัน','unit_price' =>8.00,'qty' =>0],
                ['title' => 'ผ้าเช็ดมือขนาด 10x10 นิ้ว', 'unit' => 'โหล','unit_price' =>180.00,'qty' =>0],
                ['title' => 'ผ้าเช็ดมือขนาด 16x30 นิ้ว', 'unit' => 'โหล','unit_price' =>300.00,'qty' =>0],
                ['title' => 'ผงซักฟอก', 'unit' => 'ถุง','unit_price' =>75.00,'qty' =>0],
                ['title' => 'ผงซักฟอกสำหรับเครื่องซักผ้า', 'unit' => 'ลัง','unit_price' =>1,940.00,'qty' =>0],
                ['title' => 'ผ้าถูพื้นใช้กับไม้ม็อบพื้น', 'unit' => 'ผืน','unit_price' =>135.00,'qty' =>0],
                ['title' => 'ผ้ายางสองหน้า', 'unit' => 'เมตร','unit_price' =>950.00,'qty' =>0],
                ['title' => 'แผ่นสติกเกอร์ A 9', 'unit' => 'กล่อง','unit_price' =>35.00,'qty' =>0],
                ['title' => 'แผ่นสติ๊กเกอร์สี', 'unit' => 'แผ่น','unit_price' =>35.00,'qty' =>0],
                ['title' => 'แฟ้มเอกสาร 120 F', 'unit' => 'เล่ม','unit_price' =>75.00,'qty' =>0],
                ['title' => 'ฟิวเจอร์บอร์ดเล็ก', 'unit' => 'แผ่น','unit_price' =>45.00,'qty' =>0],
                ['title' => 'แฟ้มดำปกแข็งแบบห่วง', 'unit' => 'เล่ม','unit_price' =>55.00,'qty' =>0],
                ['title' => 'ฟองน้ำ 14 นิ้ว ใช้กับไม้ม็อบพื้น 14 นิ้ว', 'unit' => 'อัน','unit_price' =>145.00,'qty' =>0],
                ['title' => 'ฟองน้ำถูตัว', 'unit' => 'ห่อ','unit_price' =>60.00,'qty' =>0],
                ['title' => 'แฟ้มเสนอเซ็นต์', 'unit' => 'เล่ม','unit_price' =>125.00,'qty' =>0],
                ['title' => 'ไม้กวาดหยากไย่', 'unit' => 'อัน','unit_price' =>55.00,'qty' =>0],
                ['title' => 'ไม้กวาดก้านมะพร้าว', 'unit' => 'อัน','unit_price' =>55.00,'qty' =>0],
                ['title' => 'ไม้กวาดดอกหญ้า', 'unit' => 'อัน','unit_price' =>55.00,'qty' =>0],
                ['title' => 'มีดคัตเตอร์', 'unit' => 'อัน','unit_price' =>55.00,'qty' =>0],
                ['title' => 'ไม้จิ้มฟัน', 'unit' => 'กล่อง','unit_price' =>15.00,'qty' =>0],
                ['title' => 'ไม้ม็อบพื้นแบบผ้า', 'unit' => 'อัน','unit_price' =>295.00,'qty' =>0],
                ['title' => 'ไม้ม็อบพื้นแบบฟองน้ำ 14 นิ้ว', 'unit' => 'อัน','unit_price' =>525.00,'qty' =>0],
                ['title' => 'หมึกใส่สแตมพลาส', 'unit' => 'ขวด','unit_price' =>12.00,'qty' =>0],
                ['title' => 'แม็กซ์เล็ก เบอร์ 10-1M', 'unit' => 'อัน','unit_price' =>75.00,'qty' =>0],
                ['title' => 'แม็กซ์ใหญ่ เบอร์ 35-1M', 'unit' => 'อัน','unit_price' =>325.00,'qty' =>0],
                ['title' => 'ยางรัดของ', 'unit' => 'ถุง','unit_price' =>125.00,'qty' =>0],
                ['title' => 'รองเท้าบู๊ท', 'unit' => 'คู่','unit_price' =>65.00,'qty' =>0],
                ['title' => 'รองเท้าฟองน้ำ', 'unit' => 'คู่','unit_price' =>65.00,'qty' =>0],
                ['title' => 'ลวดเชื่อมสแตนเลส', 'unit' => 'กล่อง','unit_price' =>180.00,'qty' =>0],
                ['title' => 'ลวดเชื่อมไฟฟ้า', 'unit' => 'กล่อง','unit_price' =>225.00,'qty' =>0],
                ['title' => 'หลอดน้ำแข็ง', 'unit' => 'ห่อ','unit_price' =>48.00,'qty' =>0],
                ['title' => 'ไส้แม็กเล็ก เบอร์ 10-1M', 'unit' => 'กล่อง','unit_price' =>8.00,'qty' =>0],
                ['title' => 'ไส้แม็กใหญ่ เบอร์ 35-1M', 'unit' => 'กล่อง','unit_price' =>12.00,'qty' =>0],
                ['title' => 'ลวดเสียบกระดาษ', 'unit' => 'กล่อง','unit_price' =>8.00,'qty' =>0],
                ['title' => 'สก็อตไบร์แบบมีฟองน้ำ', 'unit' => 'แผ่น','unit_price' =>15.00,'qty' =>0],
                ['title' => 'สก็อตไบร์แบบไม่มีฟองน้ำ', 'unit' => 'แผ่น','unit_price' =>12.00,'qty' =>0],
                ['title' => 'สตาร์ทเตอร์', 'unit' => 'ตัว','unit_price' =>15.00,'qty' =>0],
                ['title' => 'สบู่หอม', 'unit' => 'ก้อน','unit_price' =>15.00,'qty' =>0],
                ['title' => 'สเปรย์ดับกลิ่น', 'unit' => 'กระป๋อง','unit_price' =>95.00,'qty' =>0],
                ['title' => 'สายไฟแบบแข็งขนาด 2x1.5 มม.', 'unit' => 'ม้วน','unit_price' =>2,850.00,'qty' =>0],
                ['title' => 'สายไฟแบบแข็งขนาด 2x2.5 มม', 'unit' => 'ม้วน','unit_price' =>2,850.00,'qty' =>0],
                ['title' => 'สายไฟแบบอ่อนขนาด 2x1.5 มม.', 'unit' => 'ม้วน','unit_price' =>1,550.00,'qty' =>0],
                ['title' => 'สมุดเงินสด (แบบ 401) หน่วยงานย่อย', 'unit' => 'เล่ม','unit_price' =>195.00,'qty' =>0],
                ['title' => 'สมุดปกแข็งเบอร์ 1', 'unit' => 'เล่ม','unit_price' =>80.00,'qty' =>0],
                ['title' => 'สมุดปกแข็งเบอร์ 2', 'unit' => 'เล่ม','unit_price' =>35.00,'qty' =>0],
                ['title' => 'สวิทไฟเปิด-ปิดแบบฝาผนัง', 'unit' => 'อัน','unit_price' =>28.00,'qty' =>0],
                ['title' => 'หัวเชื้อน้ำยาเช็ดกระจก', 'unit' => 'แกลลอน','unit_price' =>475.00,'qty' =>0],
                ['title' => 'หัวเชื้อน้ำยาถูพื้นดับกลิ่น', 'unit' => 'แกลลอน','unit_price' =>390.00,'qty' =>0],
                ['title' => 'หัวเชื้อน้ำยาล้างจาน', 'unit' => 'แกลลอน','unit_price' =>295.00,'qty' =>0],
                ['title' => 'หัวเชื้อน้ำยาล้างท่ออุดตัน', 'unit' => 'แกลลอน','unit_price' =>925.00,'qty' =>0],
                ['title' => 'หัวเชื้อน้ำยาล้างห้องสุขภัณฑ์', 'unit' => 'แกลลอน','unit_price' =>330.00,'qty' =>0],
                ['title' => 'หลอดนีออนขนาด 20 W', 'unit' => 'หลอด','unit_price' =>45.00,'qty' =>0],
                ['title' => 'หลอดนีออนขนาด 40 W', 'unit' => 'หลอด','unit_price' =>60.00,'qty' =>0],
                ['title' => 'อื่นๆ', 'unit' => 'ก้อน','unit_price' =>8.00,'qty' =>0],
                
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
                // ['title' => 'กรวยกระดาษ (1*25)', 'unit' => 'แถว','unit_price' =>44.94,'qty' =>22],
               
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
                ['title' => 'หมึก EPSON LQ 310', 'unit' => 'กล่อง','unit_price' =>1,950.00,'qty' =>0],
                ['title' => 'หมึก HP 107A', 'unit' => 'กล่อง','unit_price' =>1,950.00,'qty' =>0],
                ['title' => 'หมึก HP 12A', 'unit' => 'กล่อง','unit_price' =>1,350.00,'qty' =>0],
                ['title' => 'หมึก HP 35A', 'unit' => 'กล่อง','unit_price' =>1,350.00,'qty' =>0],
                ['title' => 'หมึก HP 48A', 'unit' => 'กล่อง','unit_price' =>1,990.00,'qty' =>0],
                ['title' => 'หมึก HP 79A', 'unit' => 'กล่อง','unit_price' =>2,500.00,'qty' =>0],
                ['title' => 'หมึก HP 83A', 'unit' => 'กล่อง','unit_price' =>3,000.00,'qty' =>0],
                ['title' => 'หมึก HP 85A', 'unit' => 'กล่อง','unit_price' =>3,100.00,'qty' =>0],
               
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
                // ['title' => 'Round 008', 'unit' => 'ไม่ระบุบ','unit_price' =>0.00,'qty' =>0],
               
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


    // วัสดุวิทยาศาสตร์หรือการแพทย์ แก้ไขยอกนำเข้าผิด
    public function actionUpdateStockM7()
    {
        $thai_year = 2568;
        $warehouse_id = 2;
        $assettype = 'M7';
        $categoryName = 'วัสดุวิทยาศาสตร์หรือการแพทย์';
        $category_id = 1;
        $code = 'IN-680001';

        $data = [
            ['title' => 'AIR WAY 60 mm. No.0', 'unit' => 'อัน','unit_price' =>20.00,'qty' =>30,'code' =>'M7-1','lot_number' =>'LOT68-00001'],

        ];

        if (BaseConsole::confirm('Are you sure?')) {
            $total = 0; // ตรวจสอบให้แน่ใจว่าเริ่มต้นเป็นตัวเลข
        
            foreach ($data as $key => $value) {
                
                $asetItem = Categorise::findOne(['name' => 'asset_item', 'code' => $value['code']]);
               
                if ($asetItem) {
                    $stockEvent = StockEvent::findOne(['lot_number' => $value['lot_number']]);
                    $stock = Stock::findOne(['lot_number' => $value['lot_number']]);
                    $stockEvent->qty = $value['qty'];
                    $stockEvent->unit_price = $value['unit_price'];
                    $stockEvent->save(false);
                    $stock->qty = $value['qty'];
                    $stock->unit_price = $value['unit_price'];
                    $stock->save(false);
                    // echo $asetItem->title . "\n";
                    // $$asetItem = $value['title']
                    // echo $value['title']."\n";
                    // echo $stockEvent->code.$stockEvent->name."\n";
                    echo $stockEvent->qty." ".$stock->qty." ".$value['qty']."\n";
                    // echo $stock->qty." ".$value['qty']."\n";
                }else{
                    $total++; // ใช้การเพิ่มค่าที่ถูกต้อง
                    echo $value['title'] . "\n";
                    
                }
            }
        
            echo "Total: " . $total . "\n";
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
