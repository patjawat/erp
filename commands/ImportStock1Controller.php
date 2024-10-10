<?php
/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\AppHelper;
use app\models\Categorise;
use app\modules\inventory\models\StockEvent;
use yii\console\Controller;
use yii\helpers\BaseConsole;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 * นำเข้าคลังพัสดุ
 */
class ImportStock1Controller extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
    }

    // นำเข้าวัสดุสำนักงาน
    public static function actionM1()
    {
        $data = [
            ['code' => '01-00001', 'title' => 'กระดาษถ่ายเอกสาร ขนาด A4', 'unit' => 'รีม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00002', 'title' => 'กระดาษถ่ายเอกสาร ขนาด A3', 'unit' => 'รีม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00003', 'title' => 'กระดาษถ่ายเอกสาร ขนาด F4', 'unit' => 'รีม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00004', 'title' => 'กระดาษถ่ายเอกสาร ขนาด A5', 'unit' => 'รีม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00005', 'title' => 'กระดาษเทอร์มอล 58', 'unit' => 'ม้วน', 'qty' => '30', 'unit_price' => '48.15000'],
            ['code' => '01-00006', 'title' => 'กระดาษโฟโต้ ขนาด A4', 'unit' => 'รีม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00007', 'title' => 'กระดาษการ์ดหอม', 'unit' => 'รีม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00009', 'title' => 'กระดาษคาร์บอน', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00010', 'title' => 'กระดาษทำปก สีเขียว', 'unit' => 'รีม', 'qty' => '27', 'unit_price' => '124.44444'],
            ['code' => '01-00011', 'title' => 'กระดาษทำปก สีชมพู', 'unit' => 'รีม', 'qty' => '32', 'unit_price' => '130'],
            ['code' => '01-00012', 'title' => 'กระดาษทำปก สีฟ้า', 'unit' => 'รีม', 'qty' => '20', 'unit_price' => '130'],
            ['code' => '01-00013', 'title' => 'กระดาษทำปก สีเหลือง', 'unit' => 'รีม', 'qty' => '22', 'unit_price' => '130'],
            ['code' => '01-00014', 'title' => 'กระดาษทำปก สีม่วง', 'unit' => 'รีม', 'qty' => '4', 'unit_price' => '130'],
            ['code' => '01-00015', 'title' => 'กระดาษทำปก สีส้ม', 'unit' => 'รีม', 'qty' => '3', 'unit_price' => '120'],
            ['code' => '01-00019', 'title' => 'กระดาษกาวย่น ขนาด 1 นิ้ว', 'unit' => 'ม้วน', 'qty' => '204', 'unit_price' => '39.75289'],
            ['code' => '01-00020', 'title' => 'กระดาษกาวย่น ขนาด 2 นิ้ว', 'unit' => 'ม้วน', 'qty' => '128', 'unit_price' => '75.00000'],
            ['code' => '01-00021', 'title' => 'กระดาษกาวสองหน้า แบบบาง', 'unit' => 'ม้วน', 'qty' => '21', 'unit_price' => '30'],
            ['code' => '01-00022', 'title' => 'กระดาษกาวสองหน้า แบบหนา', 'unit' => 'ม้วน', 'qty' => '5', 'unit_price' => '59.00000'],
            ['code' => '01-00023', 'title' => 'กระดาษกาวสองหน้า 3M', 'unit' => 'ม้วน', 'qty' => '7', 'unit_price' => '89.28571'],
            ['code' => '01-00024', 'title' => 'กระดาษห่อของขวัญ', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00025', 'title' => 'กาวช้าง', 'unit' => 'หลอด', 'qty' => '11', 'unit_price' => '25.00000'],
            ['code' => '01-00026', 'title' => 'กาวน้ำ', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00027', 'title' => 'กาวสติ๊ก', 'unit' => 'หลอด', 'qty' => '55', 'unit_price' => '17.20000'],
            ['code' => '01-00028', 'title' => 'กรรไกร', 'unit' => 'ด้าม', 'qty' => '19', 'unit_price' => '47.63158'],
            ['code' => '01-00029', 'title' => 'ครีมนับเงิน', 'unit' => 'ตลับ', 'qty' => '5', 'unit_price' => '35.00000'],
            ['code' => '01-00030', 'title' => 'ครีมล้างแห้ง', 'unit' => 'กระปุก', 'qty' => '5', 'unit_price' => '380'],
            ['code' => '01-00031', 'title' => 'คลิปบอร์ด A4', 'unit' => 'อัน', 'qty' => '26', 'unit_price' => '45.00000'],
            ['code' => '01-00032', 'title' => 'คลิปบอร์ด A5', 'unit' => 'ตัว', 'qty' => '8', 'unit_price' => '35.00000'],
            ['code' => '01-00033', 'title' => 'คลิปหนีบกระดาษ', 'unit' => 'โหล', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00034', 'title' => 'ลวดเสียบกระดาษ', 'unit' => 'กล่อง', 'qty' => '55', 'unit_price' => '19.27273'],
            ['code' => '01-00035', 'title' => 'คัตเตอร์เล็ก', 'unit' => 'ด้าม', 'qty' => '12', 'unit_price' => '35.00000'],
            ['code' => '01-00036', 'title' => 'คัตเตอร์ใหญ่', 'unit' => 'ด้าม', 'qty' => '13', 'unit_price' => '58.00000'],
            ['code' => '01-00037', 'title' => 'ใบมีดคัตเตอร์เล็ก', 'unit' => 'ห่อ', 'qty' => '25', 'unit_price' => '23.00000'],
            ['code' => '01-00038', 'title' => 'ใบมีดคัตเตอร์ใหญ่', 'unit' => 'ห่อ', 'qty' => '15', 'unit_price' => '38.00000'],
            ['code' => '01-00039', 'title' => 'เครื่องคิดเลข', 'unit' => 'เครื่อง', 'qty' => '3', 'unit_price' => '370'],
            ['code' => '01-00041', 'title' => 'เครื่องเย็บกระดาษ No.10', 'unit' => 'ตัว', 'qty' => '24', 'unit_price' => '99.00000'],
            ['code' => '01-00042', 'title' => 'เครื่องเย็บกระดาษ No.8', 'unit' => 'ตัว', 'qty' => '7', 'unit_price' => '333.42857'],
            ['code' => '01-00043', 'title' => 'ลวดเย็บกระดาษ No.10', 'unit' => 'ชิ้น', 'qty' => '86', 'unit_price' => '11.44174'],
            ['code' => '01-00044', 'title' => 'ลวดเย็บกระดาษ No.8', 'unit' => 'กล่อง', 'qty' => '16', 'unit_price' => '18.00000'],
            ['code' => '01-00045', 'title' => 'ลวดเย็บกระดาษ No.3', 'unit' => 'กล่อง', 'qty' => '18', 'unit_price' => '29.00000'],
            ['code' => '01-00046', 'title' => 'ลวดเย็บกระดาษ No.23/15', 'unit' => 'กล่อง', 'qty' => '12', 'unit_price' => '175.00000'],
            ['code' => '01-00047', 'title' => 'ลวดเย็บกระดาษ No.23/17', 'unit' => 'กล่อง', 'qty' => '11', 'unit_price' => '189.00000'],
            ['code' => '01-00048', 'title' => 'ลวดเย็บกระดาษ No.23/8', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00049', 'title' => 'สมุด No.2', 'unit' => 'เล่ม', 'qty' => '8', 'unit_price' => '50'],
            ['code' => '01-00051', 'title' => 'ไส้แฟ้ม 11 รู', 'unit' => 'ห่อ', 'qty' => '13', 'unit_price' => '20'],
            ['code' => '01-00052', 'title' => 'เหล็กยึดแฟ้ม', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00053', 'title' => 'หมึกเติมแท่นประทับ สีแดง', 'unit' => 'ขวด', 'qty' => '4', 'unit_price' => '12.00000'],
            ['code' => '01-00054', 'title' => 'หมึกเติมแท่นประทับ สีน้ำเงิน', 'unit' => 'ขวด', 'qty' => '6', 'unit_price' => '27.66667'],
            ['code' => '01-00056', 'title' => 'แท่นตัดเทปใส', 'unit' => 'อัน', 'qty' => '2', 'unit_price' => '98.00000'],
            ['code' => '01-00057', 'title' => 'แท่นประทับ สีแดง', 'unit' => 'อัน', 'qty' => '1', 'unit_price' => '32.66000'],
            ['code' => '01-00058', 'title' => 'แท่นประทับ สีน้ำเงิน', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00059', 'title' => 'แท่นประทับ สีดำ', 'unit' => 'อัน', 'qty' => '1', 'unit_price' => '38.00000'],
            ['code' => '01-00060', 'title' => 'ซองเอกสารน้ำตาล A4 ขยายข้าง', 'unit' => 'ซอง', 'qty' => '150', 'unit_price' => '5.00000'],
            ['code' => '01-00061', 'title' => 'ซองเอกสารน้ำตาล A4', 'unit' => 'ซอง', 'qty' => '50', 'unit_price' => '5.00000'],
            ['code' => '01-00062', 'title' => 'ซองพับ 4 สีขาว', 'unit' => 'ซอง', 'qty' => '1000', 'unit_price' => '0.80000'],
            ['code' => '01-00065', 'title' => 'ซองพับ 2 สีน้ำตาล', 'unit' => 'ซอง', 'qty' => '200', 'unit_price' => '4.00000'],
            ['code' => '01-00066', 'title' => 'ซองฟิล์ม x-ray ฟัน ขนาด 7*10 นิ้ว', 'unit' => 'ซอง', 'qty' => '500', 'unit_price' => '3.20000'],
            ['code' => '01-00067', 'title' => 'ดินน้ำมัน', 'unit' => 'ก้อน', 'qty' => '3', 'unit_price' => '23.00000'],
            ['code' => '01-00068', 'title' => 'ดินสอ', 'unit' => 'แท่ง', 'qty' => '109', 'unit_price' => '3.62688'],
            ['code' => '01-00070', 'title' => 'ยางลบ', 'unit' => 'ก้อน', 'qty' => '59', 'unit_price' => '6.00000'],
            ['code' => '01-00071', 'title' => 'ปากกาลบคำผิด', 'unit' => 'แท่ง', 'qty' => '24', 'unit_price' => '15.00000'],
            ['code' => '01-00072', 'title' => 'ปากกาเพ้นท์', 'unit' => 'แท่ง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00073', 'title' => 'ปากกามารค์เกอร์ 2 หัว สีน้ำเงิน', 'unit' => 'ด้าม', 'qty' => '41', 'unit_price' => '13.00000'],
            ['code' => '01-00074', 'title' => 'ปากกามาร์คเกอร์ 2 หัว สีแดง', 'unit' => 'แท่ง', 'qty' => '18', 'unit_price' => '13.00000'],
            ['code' => '01-00075', 'title' => 'ปากกามาร์คเกอร์ 2 หัว สีดำ', 'unit' => 'ด้าม', 'qty' => '49', 'unit_price' => '13.00000'],
            ['code' => '01-00076', 'title' => 'ปากกาไวท์บอร์ด สีน้ำเงิน', 'unit' => 'ด้าม', 'qty' => '10', 'unit_price' => '25.00000'],
            ['code' => '01-00077', 'title' => 'ปากกาไวท์บอร์ด สีดำ', 'unit' => 'ด้าม', 'qty' => '11', 'unit_price' => '25.00000'],
            ['code' => '01-00078', 'title' => 'ปากกาไวท์บอร์ด สีแดง', 'unit' => 'ด้าม', 'qty' => '10', 'unit_price' => '22.22800'],
            ['code' => '01-00079', 'title' => 'ธงชาติ ขนาด 90*135 นิ้ว', 'unit' => 'ผืน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00080', 'title' => 'ธงประจำพระองค์ รัชกาลที่ 10', 'unit' => 'ผืน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00081', 'title' => 'เชือกขาวแดง', 'unit' => 'ม้วน', 'qty' => '4', 'unit_price' => '52.00000'],
            ['code' => '01-00082', 'title' => 'ตรายาง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00083', 'title' => 'ตรายางวันที่', 'unit' => 'อัน', 'qty' => '3', 'unit_price' => '55.00000'],
            ['code' => '01-00084', 'title' => 'ที่ถอนลวดเย็บกระดาษ', 'unit' => 'อัน', 'qty' => '10', 'unit_price' => '68.00000'],
            ['code' => '01-00085', 'title' => 'เทปผ้ากาว ขนาด 2 นิ้ว', 'unit' => 'ม้วน', 'qty' => '4', 'unit_price' => '50'],
            ['code' => '01-00086', 'title' => 'เทปผ้ากาว ขนาด 1.5 นิ้ว', 'unit' => 'ม้วน', 'qty' => '7', 'unit_price' => '40'],
            ['code' => '01-00087', 'title' => 'เทปใสแกนใหญ่', 'unit' => 'ม้วน', 'qty' => '19', 'unit_price' => '20'],
            ['code' => '01-00089', 'title' => 'แผ่นเคลือบบัตร A4', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00090', 'title' => 'หมึกพิมพ์สำเนา เครื่องก๊อปปี้ปรินท์', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00091', 'title' => 'กระดาษพิมพ์สำเนา เครื่องก๊อปปี้ปรินท์', 'unit' => 'ม้วน', 'qty' => '1', 'unit_price' => '2600'],
            ['code' => '01-00092', 'title' => 'หมึกเครื่องถ่ายเอกสาร', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00094', 'title' => 'สติ๊กเกอร์ ต่อเนื่อง ขนาด 3*7 ซม.', 'unit' => 'ห่อ', 'qty' => '6', 'unit_price' => '1750'],
            ['code' => '01-00095', 'title' => 'สติ๊กเกอร์ ไดเร็คเทอร์มอล ขนาด 5*2 ซม.', 'unit' => 'ดวง', 'qty' => '58000', 'unit_price' => '0.15000'],
            ['code' => '01-00096', 'title' => 'หนังสือ คู่มือผู้ป่วยโรคความดันโลหิตสูง', 'unit' => 'เล่ม', 'qty' => '156', 'unit_price' => '50'],
            ['code' => '01-00097', 'title' => 'หนังสือ คู่มือผู้ป่วยโรคเบาหวาน', 'unit' => 'เล่ม', 'qty' => '310', 'unit_price' => '50'],
            ['code' => '01-00098', 'title' => 'ใบเสร็จรับเงิน (เล่มเขียว)', 'unit' => 'เล่ม', 'qty' => '60', 'unit_price' => '110'],
            ['code' => '01-00099', 'title' => 'แบบฟอร์ม ใบเสร็จรับเงินต่อเนื่อง', 'unit' => 'กล่อง', 'qty' => '57', 'unit_price' => '1043.85965'],
            ['code' => '01-00100', 'title' => 'แบบฟอร์ม ใบคำสั่งการรักษาของแพทย์', 'unit' => 'ห่อ', 'qty' => '11', 'unit_price' => '302.45000'],
            ['code' => '01-00101', 'title' => 'แบบฟอร์ม ใบรับรองแพทย์เพื่อการรักษา', 'unit' => 'ห่อ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00103', 'title' => 'แบบฟอร์ม แบบการเตรียมสารน้ำที่ให้ทางหลอดเลือดดำ', 'unit' => 'เล่ม', 'qty' => '159', 'unit_price' => '19.00000'],
            ['code' => '01-00104', 'title' => 'แบบฟอร์ม แบบบันทึกการตรวจอัลตร้าซาวน์', 'unit' => 'เล่ม', 'qty' => '80', 'unit_price' => '108.00000'],
            ['code' => '01-00105', 'title' => 'แบบฟอร์ม แบบบันทึกการให้ข้อมูลจำหน่าย', 'unit' => 'ห่อ', 'qty' => '8', 'unit_price' => '450'],
            ['code' => '01-00106', 'title' => 'แบบฟอร์ม แบบบันทึกทางการพยาบาล', 'unit' => 'ห่อ', 'qty' => '4', 'unit_price' => '450'],
            ['code' => '01-00107', 'title' => 'แบบฟอร์ม แบบบันทึกประวัติผู้ป่วย', 'unit' => 'ห่อ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00108', 'title' => 'แบบฟอร์ม Admission Note', 'unit' => 'ห่อ', 'qty' => '18', 'unit_price' => '250'],
            ['code' => '01-00109', 'title' => 'แบบฟอร์ม Anesthesia record', 'unit' => 'เล่ม', 'qty' => '120', 'unit_price' => '99.33333'],
            ['code' => '01-00111', 'title' => 'แบบฟอร์ม ปรอท', 'unit' => 'ห่อ', 'qty' => '11', 'unit_price' => '440'],
            ['code' => '01-00112', 'title' => 'แบบฟอร์ม ใบลงนามยินยอมรับการรักษา', 'unit' => 'ห่อ', 'qty' => '8', 'unit_price' => '350'],
            ['code' => '01-00113', 'title' => 'แบบฟอร์ม ใบส่งต่อผู้ป่วย (รพ)', 'unit' => 'เล่ม', 'qty' => '125', 'unit_price' => '78.40000'],
            ['code' => '01-00114', 'title' => 'แบบฟอร์ม ใบ ORDER', 'unit' => 'เล่ม', 'qty' => '196', 'unit_price' => '33.00000'],
            ['code' => '01-00117', 'title' => 'เครื่องเจาะกระดาษ (เล็ก)', 'unit' => 'ตัว', 'qty' => '1', 'unit_price' => '75.00000'],
            ['code' => '01-00118', 'title' => 'แบบฟอร์ม ใบรับรองแพทย์', 'unit' => 'ห่อ', 'qty' => '26', 'unit_price' => '37.54000'],
            ['code' => '01-00120', 'title' => 'แบบฟอร์ม บัตรคิวต่อเนื่อง 5*6 นิ้ว', 'unit' => 'ห่อ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00126', 'title' => 'โทรศัพท์ ธรรมดา', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00127', 'title' => 'แฟ้มสอดไส้', 'unit' => 'แฟ้ม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00128', 'title' => 'ปากกาเน้นข้อความ', 'unit' => 'ด้าม', 'qty' => '29', 'unit_price' => '25.00000'],
            ['code' => '01-00130', 'title' => 'กระดาษเทอร์มอล 58 แกรมขาว', 'unit' => 'ม้วน', 'qty' => '690', 'unit_price' => '15.28913'],
            ['code' => '01-00136', 'title' => 'กระดาษทำปก สีแดง', 'unit' => 'รีม', 'qty' => '4', 'unit_price' => '130'],
            ['code' => '01-00138', 'title' => 'สายคล้องบัตร', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00140', 'title' => 'กระดาษสีน้ำตาล', 'unit' => 'ห่อ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00144', 'title' => 'เทปกาวใส OPP 2 นิ้ว', 'unit' => 'ม้วน', 'qty' => '8', 'unit_price' => '55.00000'],
            ['code' => '01-00145', 'title' => 'กรวยจราจร', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00149', 'title' => 'พานดอกไม้', 'unit' => 'คู่', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00150', 'title' => 'พานเงิน - พานทอง', 'unit' => 'คู่', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00160', 'title' => 'ไม้บรรทัด', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00161', 'title' => 'เคมีดับเพลิง ชนิดผงแห้ง ขนาด 15 ปอนด์', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00163', 'title' => 'เคมีดับเพลิง ชนิดไนโตรเจน ขนาด 10 ปอนด์', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00166', 'title' => 'เครื่องเย็บกระดาษ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00167', 'title' => 'ลวดเย็บกระดาษ No.35-1', 'unit' => 'กล่อง', 'qty' => '25', 'unit_price' => '15.00000'],
            ['code' => '01-00169', 'title' => 'แฟ้มเสนอเซ็น', 'unit' => 'เล่ม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00170', 'title' => 'เทปกาวสองหน้า 3M', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00171', 'title' => 'แผ่นพลาสติกใส', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00175', 'title' => 'ธงประจำพระองค์ พระราชินี รัชกาลที่ 10', 'unit' => 'ผืน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00176', 'title' => 'ธงชาติ ขนาด 60*90 นิ้ว', 'unit' => 'ผืน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00177', 'title' => 'พานพุ่ม เงิน-ทอง', 'unit' => 'คู่', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00180', 'title' => 'พู่กัน', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00187', 'title' => 'ป้ายทางหนีไฟฉุกเฉิน', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00188', 'title' => 'ถ่าน ขนาด 3V', 'unit' => 'ก้อน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00189', 'title' => 'เครื่องเหลาดินสอ', 'unit' => 'เครื่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00192', 'title' => 'โทรศัพท์ ไร้สาย', 'unit' => 'เครื่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00193', 'title' => 'ชั้นลิ้นชัก 3 ลิ้น', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00194', 'title' => 'ป้ายอะคริลิคติดผนัง', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00195', 'title' => 'ขอบขางตู้เก็บถังดับเพลิง', 'unit' => 'เมตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00197', 'title' => 'กบเหลาดินสอ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00198', 'title' => 'แผ่นเคลือบบัตร', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00199', 'title' => 'พลาสติกลูกฟูก (ฟิวเจอร์บอร์ด)', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00200', 'title' => 'สมุดบันทึกสุขภาพผู้ป่วยคลีนิกโรคปอดอุดกลั้นเรื้อรัง', 'unit' => 'เล่ม', 'qty' => '300', 'unit_price' => '26.00000'],
            ['code' => '01-00201', 'title' => 'ปากกาลงนาม', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00202', 'title' => 'เทปตีเส้น (สีแดง)', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00203', 'title' => 'โอเอซิส', 'unit' => 'ก้อน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00204', 'title' => 'ปกพลาสติกใส', 'unit' => 'ห่อ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00205', 'title' => 'สายคล้องบัตร (สีน้ำเงิน)', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00207', 'title' => 'หนังสือ คู่มือผู้ป่วยโรคไตเสื่อมเรื้อรัง', 'unit' => 'เล่ม', 'qty' => '600', 'unit_price' => '26.00000'],
            ['code' => '01-00208', 'title' => 'พัดลมเพดาน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00209', 'title' => 'สติ๊กเกอร์ 4.5*1.5 cm.', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00210', 'title' => 'สติ๊กเกอร์ใสหลังเหลือง', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00211', 'title' => 'ป้ายจราจร', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00212', 'title' => 'สติ๊กเกอร์ PVC', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00213', 'title' => 'เครื่องยิงสติ๊กเกอร์ 2HG', 'unit' => 'เครื่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00214', 'title' => 'สติ๊กเกอร์สะท้อนแสง', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00216', 'title' => 'แปรงลบกระดาน', 'unit' => 'อัน', 'qty' => '1', 'unit_price' => '15.00000'],
            ['code' => '01-00220', 'title' => 'ซองพลาสติกใส่เอกสาร', 'unit' => 'ซอง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00221', 'title' => 'แฟ้ม 2 นิ้ว', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00222', 'title' => 'ธงชาติ ขนาด 150*225 นิ้ว', 'unit' => 'ผืน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00223', 'title' => 'แท่นตัดกระดาษ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00225', 'title' => 'ชุดเครื่องทองน้อย', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00226', 'title' => 'ผ้าลูกไม้', 'unit' => 'เมตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00227', 'title' => 'ลวดเย็บกระดาษ No.T3-10MB', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00228', 'title' => 'แฟ้มหนีบ', 'unit' => 'แฟ้ม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00229', 'title' => 'แผงกั้นจราจรบรรจุน้ำ', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00230', 'title' => 'รางลิ้นชักรับใต้', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00231', 'title' => 'พัดลมปีกเพดาน ขนาด 48 นิ้ว', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00232', 'title' => 'ริบบิ้นห่อเหรียญ', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00233', 'title' => 'ดอกริบบิ้นมัด', 'unit' => 'ดอก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00234', 'title' => 'หนังสือ คู่มือการประเมินและส่งเสริมพัฒนาการเด็กแรกเกิด-5 ปี', 'unit' => 'เล่ม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00239', 'title' => 'ป้ายบ่งชี้', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00242', 'title' => 'สันรูด', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00243', 'title' => 'โทรศัพท์ภายในไร้สาย', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00244', 'title' => 'นาฬิกาแขวนผนัง', 'unit' => 'เรือน', 'qty' => '2', 'unit_price' => '1300'],
            ['code' => '01-00245', 'title' => 'เทปกั้นเขต ขาวแดง', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00246', 'title' => 'ธงชาติ ขนาด 120x180 นิ้ว', 'unit' => 'ผืน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00247', 'title' => 'สติกเกอร์สีเทา แผ่นใหญ่', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00248', 'title' => 'ธูปไฟฟ้า', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00249', 'title' => 'เทียนไฟฟ้า', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00250', 'title' => 'เก้าอี้พลาสติก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00259', 'title' => 'ชุดลูกยางเครื่องสแกนเนอร์', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00260', 'title' => 'สติกเกอร์สี', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00261', 'title' => 'เครื่องยิงบอร์ด', 'unit' => 'เครื่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00263', 'title' => 'เข็มหมุดหัวกลม', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00265', 'title' => 'ใบ บันทึกข้อความ', 'unit' => 'เล่ม', 'qty' => '50', 'unit_price' => '61.00000'],
            ['code' => '01-00267', 'title' => 'แผ่นยาง EVA rubber รองกันกระแทก', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00268', 'title' => 'ถ่านแบตเตอรี่ ลิเธียม ขนาด 3 V', 'unit' => 'ก้อน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00277', 'title' => 'กล่องกระดาษ ขนาด M+', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00278', 'title' => 'สติ๊กเกอร์ตัวเลข', 'unit' => 'ชิ้้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00279', 'title' => 'พานดอกไม้', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00280', 'title' => 'ลวดขด', 'unit' => 'มัด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00281', 'title' => 'ภาพพระฉายาลักษณ์ ร.10', 'unit' => 'รายการ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00282', 'title' => 'พาเลทพลาสติก', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00283', 'title' => 'เทปกันลื่น', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00284', 'title' => 'แผ่นสติ๊กเกอร์กันลื่น', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00285', 'title' => 'เหล็กเสียบกระดาษ', 'unit' => 'อัน', 'qty' => '2', 'unit_price' => '35.00000'],
            ['code' => '01-00286', 'title' => 'สติ๊กเกอร์ ขยะติดเชื้อ ขนาด A4', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00287', 'title' => 'สติ๊กเกอร์ ขยะติดเชื้อ ขนาด 11x17 ซม', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00288', 'title' => 'สติ๊กเกอร์ ถังดับเพลิง ขนาด A4', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00289', 'title' => 'พลาสติกการ์ด', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00290', 'title' => 'เครื่องดับเพลิงชนิดน้ำยาเหลวระเหย', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00296', 'title' => 'เคมีดับเพลิง ชนิดไนโตรเจน ขนาด 5 ปอนด์', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00299', 'title' => 'สติ๊กเกอร์ ขั้นตอนการล้างมือ', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '01-00300', 'title' => 'สติ๊กเกอร์ การทำความสะอาดพื้นผิวที่เปื้อนสิ่งคัดหลั่ง', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M1',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุสำนักงาน',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 1;
                $code = 'IN-680001';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }

        //     SELECT s.asset_item,i.title,s.qty,
        // FORMAT(s.unit_price,5),
        // FORMAT(sum((s.qty* FORMAT(s.unit_price,5))),2) as total
        // FROM `stock_events` s
        // LEFT JOIN categorise i ON i.code = s.asset_item AND i.name = 'asset_item'
        // WHERE s.name = 'order_item'
        // GROUP BY s.id;
    }

    // นำเข้าวัสดุก่อสร้าง
    public static function actionProduct2()
    {
        $data = [
            ['code' => '04-00001', 'title' => 'กาวอีพ็อกซี่ งานเหล็ก', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00002', 'title' => 'วาล์วฝักบัว 1ทาง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00003', 'title' => 'ก๊อกน้ำ ขนาด 3/4" นิ้ว (6 หุน)', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00004', 'title' => 'ก๊อกน้ำ ขนาด 1/2" นิ้ว (4 หุน)', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00005', 'title' => 'ก๊อกล้างพื้น 1 ทาง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00006', 'title' => 'ก๊อกซิงค์ เดี่ยว เคาน์เตอร์', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00007', 'title' => 'สต๊อปวาล์ว 1 ทาง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00008', 'title' => 'ก๊อกซิงค์ เดี่ยว ผนัง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00009', 'title' => 'ก๊อกน้ำ อ่างล้างหน้า เดี่ยว', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00010', 'title' => 'กลอน ทั่วไป', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00011', 'title' => 'กระดาษทรายสายพาน', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00012', 'title' => 'กระดาษทรายขัดเหล็ก', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00013', 'title' => 'กระดาษทรายกลม', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00014', 'title' => 'เทปพันเกลียว', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00015', 'title' => 'กรวดคัด', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00016', 'title' => 'กรรไกรตัด PVC', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00017', 'title' => 'สเปรย์หล่อลื่นเอนกประสงค์', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00020', 'title' => 'หัวฉีดชำระ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00021', 'title' => 'สายฉีดชำระ', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00022', 'title' => 'ตัวรัดสายยาง', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00023', 'title' => 'เหล็กรัด 4 หุน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00024', 'title' => 'ฝักบัว', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00026', 'title' => 'แปรงลวด', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00027', 'title' => 'ไขควงหัวแบน', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00028', 'title' => 'ตะปูเกลียวดำ', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00031', 'title' => 'สกรูปลายสว่าน', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00033', 'title' => 'เช็ควาล์ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00034', 'title' => 'ลวดเชื่อม', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00038', 'title' => 'ใบจิ๊กซอ', 'unit' => 'แผง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00041', 'title' => 'ประแจเอนกประสงค์', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00044', 'title' => 'เครื่องเป่าลม', 'unit' => 'เครื่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00048', 'title' => 'ไขควง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00054', 'title' => 'คีมปากจิ้งจก SOLO', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00055', 'title' => 'ตลับเมตร', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00056', 'title' => 'คีมล็อค', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00057', 'title' => 'ฆ้อน', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00059', 'title' => 'สีน้ำมัน', 'unit' => 'แกลลอน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00061', 'title' => 'สะดืออ่าง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00062', 'title' => 'ท่อน้ำทิ้ง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00063', 'title' => 'แค้มรัดท่อ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00064', 'title' => 'สามทาง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00065', 'title' => 'ข้อต่อตรง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00066', 'title' => 'ข้องอ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00068', 'title' => 'พุกพลาสติก', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00069', 'title' => 'ท่อ PVC', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00070', 'title' => 'ฝาชักโครก', 'unit' => 'ชิ้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00071', 'title' => 'ลูกบิด', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00073', 'title' => 'ตะแกรง', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00074', 'title' => 'ซิลิโคลน', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00075', 'title' => 'ตาข่ายดำ', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00076', 'title' => 'ไม้อัด', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00077', 'title' => 'สีสเปรย์ สีดำ', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00078', 'title' => 'ดอกสว่าน', 'unit' => 'ดอก', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00079', 'title' => 'วาล์วทองเหลือง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00080', 'title' => 'ทินเนอร์', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00081', 'title' => 'แปรงทาสี', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00082', 'title' => 'สายยางเขียว', 'unit' => 'เมตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00083', 'title' => 'สกรู ยิงอลูซิงค์', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00084', 'title' => 'หัวบล็อค ยิงอลูซิงค์', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00085', 'title' => 'ใบเลื่อยตัดเหล็ก', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00086', 'title' => 'ข้อลด 4 หุน', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00087', 'title' => 'ข้อลด 6 หุน', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00088', 'title' => 'ก๊อกน้ำ อ่างล้างหน้าแบบปัด', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00089', 'title' => 'ก๊อกน้ำ อ่างล้างหน้าเซรามิค', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00091', 'title' => 'ตะแกรงกันกลิ่น พลาสติก ขนาด 3 นิ้ว', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00094', 'title' => 'ข้องอ 2 นิ้ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00096', 'title' => 'เหล็กกล่อง', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00097', 'title' => 'สายเคเบิ้ล', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00098', 'title' => 'ใบตัด', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00099', 'title' => 'สายน้ำดี', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00100', 'title' => 'ใบเจียร', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00101', 'title' => 'ตาข่ายเหล็ก', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00104', 'title' => 'อุปกรณ์ชักโครก', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00105', 'title' => 'ยาแนว', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00106', 'title' => 'กาวยาง Dunlop', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00111', 'title' => 'ตะไบ', 'unit' => 'เล่ม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00112', 'title' => 'หน้ากากเชื่อม', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00113', 'title' => 'เทปพันสายไฟ', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00114', 'title' => 'ลูกปืน', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00115', 'title' => 'สวิทช์ปั๊มอัตโนมัติ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00117', 'title' => 'ข้อต่อ 2 นิ้ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00120', 'title' => 'อิฐบล็อค', 'unit' => 'ก้อน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00121', 'title' => 'ปูนซีเมนต์', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00123', 'title' => 'โซดาไฟ', 'unit' => 'กิโลกรัม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00124', 'title' => 'กาวยากันรั่วซีม', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00125', 'title' => 'ฝ่าแผ่นเรียบ', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00126', 'title' => 'แม่กุญแจ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00127', 'title' => 'ลูกลอย', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00128', 'title' => 'ชุดโซ่ พร้อม กุญแจล็อค', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00130', 'title' => 'ไส้ไก่พันสายไฟ', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00132', 'title' => 'สายลม', 'unit' => 'เมตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00133', 'title' => 'เลื่อยตัดเหล็ก', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00134', 'title' => 'ท่อดักกลิ่น', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00138', 'title' => 'ประตูน้ำ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00140', 'title' => 'ประตู PVC', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00142', 'title' => 'บานพับ ประตู', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00144', 'title' => 'ทรายคัด', 'unit' => 'คิว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00150', 'title' => 'หินเจียร', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00154', 'title' => 'บอลวาล์ว (Ball Valve)', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00156', 'title' => 'พุกเหล็ก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00157', 'title' => 'ลวดขาว', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00158', 'title' => 'แท่นตัดไฟเบอร์', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00159', 'title' => 'วาล์วลม', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00160', 'title' => 'หัวเติมลม', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00161', 'title' => 'ไขควงตอกกระแทก', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00162', 'title' => 'คาปาซิเตอร์ เครื่องมือแพทย์', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00163', 'title' => 'ล้อรถเข็นตัดหญ้า', 'unit' => 'ล้อ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00164', 'title' => 'ข้อต่อแป๊บ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00165', 'title' => 'คัดเตอร์ตัดแป๊บ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00166', 'title' => 'ท่อ 4 หุน', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00167', 'title' => 'ท่อ 2 นิ้ว', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00168', 'title' => 'สวิทซ์กบ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00169', 'title' => 'ดอกเจียร', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00170', 'title' => 'สีสเปรย์', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00171', 'title' => 'โถสุขภัณฑ์', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00172', 'title' => 'คาร์บอน', 'unit' => 'ลิตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00173', 'title' => 'แมงกานีส', 'unit' => 'ลิตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00174', 'title' => 'เกลียวนอก 2 นิ้ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00175', 'title' => 'เกลียวนอก 6 หุน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00176', 'title' => 'กิ๊บ C3', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00177', 'title' => 'ท่อย่น', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00178', 'title' => 'เส้นใหญ่ท่อย่น', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00179', 'title' => 'เกลียวใน 4 หุน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00180', 'title' => 'ชุดข้อต่อก๊อกน้ำ', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00181', 'title' => 'ลดเหลี่ยม 4 หุน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00182', 'title' => 'ถ่านเครื่องมือ', 'unit' => 'ก้อน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00183', 'title' => 'เหล็กฉาก ขนาด 1 1/2 นิ้ว', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00184', 'title' => 'กาวซีเมนต์', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00185', 'title' => 'กาวทาท่อพีวีซี', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00186', 'title' => 'ชุดอุปกรณ์อ่างล้างหน้า', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00188', 'title' => 'ใบพัดลมมอเตอร์', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00189', 'title' => 'สายยูบานพับ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00190', 'title' => 'แชลง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00191', 'title' => 'ชุดเป่าลม', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00192', 'title' => 'สายรัดเหล็ก', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00193', 'title' => 'ฝาปิด', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00194', 'title' => 'ซีลปั๋มน้ำอัตโนมัติ', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00195', 'title' => 'กาวทรีบอน', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00197', 'title' => 'ชุดสตาร์ทบริ๊ก', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00198', 'title' => 'สายจารบี', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00199', 'title' => 'หัวอัดจารบี', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00200', 'title' => 'วาล์ว PCV 2 นิ้ว', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00201', 'title' => 'สต๊อบวาล์ว 3 ทาง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00202', 'title' => 'ฟองน้ำ (เฟสชิว)', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00203', 'title' => 'ไม้มอบฝ้า 3 เมตร', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00204', 'title' => 'ราวสแตนเลส', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00205', 'title' => 'ถุงมือหนัง', 'unit' => 'คู่', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00206', 'title' => 'ก๊อกน้ำทองเหลือง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00207', 'title' => 'ถ่านหินเจียร', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00208', 'title' => 'อะไหล่รวม (หม้อแปรง+หน้ากากโถเซ็นต์เซอร์)', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00209', 'title' => 'นิเปิ้ล', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00210', 'title' => 'ซีลยาง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00211', 'title' => 'คีมหนีบ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00212', 'title' => 'น้ำยารักษาเนื้อไม้', 'unit' => 'กระป๋อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00213', 'title' => 'ไม้ 1.5*3*6 ศอก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00214', 'title' => 'ล็อคสายยาง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00215', 'title' => 'ข้อต่อเหล็ก 1 นิ้ว', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00216', 'title' => 'คีมตัดสายไฟ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00217', 'title' => 'คีมปลอกสายไฟ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00218', 'title' => 'กาวร้อน', 'unit' => 'หลอด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00219', 'title' => 'วาล์ว 3/4', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00220', 'title' => 'วาล์ว 1/2', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00221', 'title' => 'ประตูไม้อัด', 'unit' => 'บาน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00222', 'title' => 'ใบเลื่อยวงเดือน', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00223', 'title' => 'หินฝุ่น', 'unit' => 'คิว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00224', 'title' => 'ข้อต่อทองเหลือง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00225', 'title' => 'หินลับมีด', 'unit' => 'ก้อน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00226', 'title' => 'ใบพัดปั๊มน้ำรู 16 มิล', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00227', 'title' => 'ใบพัดปั๊มน้ำรู 14 มิล', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00228', 'title' => 'สีทาถนนสะท้อนแสง สีแดง #715', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00229', 'title' => 'สีทาถนนสะท้อนแสง สีดำ #719', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00230', 'title' => 'สีทาถนนสะท้อนแสง สีขาว #717', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00231', 'title' => 'ชุดบล็อค KOCHE', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00232', 'title' => 'กระจกห้องน้ำ', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00233', 'title' => 'เหล็ก 9 มิล', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00234', 'title' => 'ไม้โครง', 'unit' => 'มัด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00235', 'title' => 'ก๊อกฝักบัว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00236', 'title' => 'หัวเทียนเครื่องยนต์', 'unit' => 'หัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00237', 'title' => 'น๊อตหัวแตก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00238', 'title' => 'คอนแดนเซอร์ 2.0-2.6', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00239', 'title' => 'หัวคอปเปอร์', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00240', 'title' => 'ข้อต่อสายลม', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00241', 'title' => 'ซีลาย', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00242', 'title' => 'มอบ PVC', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00243', 'title' => 'ใบเลื่อย', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00244', 'title' => 'ที่แขวนสายยางเหล็ก SPING GN', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00245', 'title' => 'เกรียงอเนกประสงค์ MATALL สีแดง', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00246', 'title' => 'ชุดหัวฉีดน้ำพร้อมข้อต่อ SPING DGH2011', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00247', 'title' => 'สต๊อปวาล์ว 2 ทาง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00248', 'title' => 'หัวบล๊อกสว่าน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00249', 'title' => 'ประแจคอม้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00250', 'title' => 'บันไดพลาสติก 3 ขั้น', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00251', 'title' => 'เหล็ก 12 มิล', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00252', 'title' => 'แปปประปาเหล็ก 1.5 นิ้ว', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00254', 'title' => 'ชุดชักโครก', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00255', 'title' => 'สีน้ำ', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00256', 'title' => 'สายฝักบัว', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00258', 'title' => 'เหล็ก 3 หุน', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00259', 'title' => 'สกรู 2 นิ้ว', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00260', 'title' => 'สกรูดำ', 'unit' => 'กล่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00262', 'title' => 'ชุดไขควง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00264', 'title' => 'กาวยาแนว', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00265', 'title' => 'กาวมหาอุต', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00266', 'title' => 'กาว 2 ตัน', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00267', 'title' => 'ใบพัดปั๊มน้ำ 5 รู', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00268', 'title' => 'น๊อตดำ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00269', 'title' => 'ใบพัดปั๊มน้ำ', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00270', 'title' => 'ท่อระบายน้ำทิ้ง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00271', 'title' => 'บานพับ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00272', 'title' => 'หัวแฉก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00273', 'title' => 'ก๊อกอ่างล้างจาน', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00274', 'title' => 'ก๊อกอ่างล้างหน้า', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00275', 'title' => 'หัวกดชักโครก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00276', 'title' => 'เหล็กกล่อง 3x3', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00277', 'title' => 'เหล็กกล่อง 2x2', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00278', 'title' => 'เพลท 6x6', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00279', 'title' => 'หัวฝักบัว', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00280', 'title' => 'ก้ามปู 1/2', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00281', 'title' => 'ก๊อกอ่างล้างจานใหญ่', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00282', 'title' => 'ก๊อกอ่างล้างจานเล็ก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00283', 'title' => 'ล้อรางเลื่อน', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00284', 'title' => 'กระเบื้อง', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00285', 'title' => 'เหล็กกล่อง 3x3 หนา', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00286', 'title' => 'เพลท 3x3', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00287', 'title' => 'ตะปูคอนกรีต', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00288', 'title' => 'พุกเหล็ก 5/16', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00289', 'title' => 'ลูกยางกระบอกสูบฉีดน้ำ', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00290', 'title' => 'ข้อต่องอ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00291', 'title' => 'ราวช่วยพยุง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00292', 'title' => 'ตาข่ายลวด', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00293', 'title' => 'หูช้างเหล็ก', 'unit' => 'คู่', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00294', 'title' => 'เมทัลชีท 0.29 ม.', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00295', 'title' => 'เมทัลชีท 2.05 ม.', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00296', 'title' => 'เมทัลชีท 2.25 ม.', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00297', 'title' => 'เมทัลชีท 1.95 ม.', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00298', 'title' => 'เมทัลชีท 3.10 ม.', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00299', 'title' => 'เฌอร่า', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00300', 'title' => 'สกรูซิงค์ 2 นิ้ว', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00301', 'title' => 'สกรูซิงค์ 1 นิ้ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00302', 'title' => 'วาล์วฝักบัว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00303', 'title' => 'ก๊อกปิดก้านยาว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00304', 'title' => 'เหล็ก 1X2', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00305', 'title' => 'ก๊อกอ่างล้างหน้าคอยาว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00306', 'title' => 'ฝาดูดสแตนเลส', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00307', 'title' => 'ตาข่ายพลาสติก', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00308', 'title' => 'ลูกรีเวท', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00309', 'title' => 'ก๊อกโถปัสสาวะ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00310', 'title' => 'อะไหล่ชักโครก', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00311', 'title' => 'กลอนขวาง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00312', 'title' => 'กลอนจัมโบ้', 'unit' => 'แพค', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00313', 'title' => 'แคล้มรัดท่อ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00314', 'title' => 'ปากกรองก๊อกเซ็นเซอร์', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00315', 'title' => 'ตะแกรงกันกลิ่นกลม', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00316', 'title' => 'ข้อต่อเกลียวนอก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00317', 'title' => 'วาล์ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00318', 'title' => 'เกลียวนอก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00319', 'title' => 'ชุดหกเหลี่ยม', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00320', 'title' => 'ชุดบล็อก', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00321', 'title' => 'ประแจแหวนข้าง ปากตายข้าง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00322', 'title' => 'กล่องใส่เครื่องมือ', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00323', 'title' => 'คีมปากแหลม', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00324', 'title' => 'เต้าดีด', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00325', 'title' => 'แม่เหล็กจับฉาก', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00326', 'title' => 'แชล็ค', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00327', 'title' => 'หน้าจานกระดาษทราย', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00328', 'title' => 'กระดาษทรายแผ่น', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00329', 'title' => 'ลูกกลิ้ง', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00330', 'title' => 'ประแจทอร์คยาว', 'unit' => 'ด้าม', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00331', 'title' => 'เทปสแตนเลส', 'unit' => 'เมตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00332', 'title' => 'สามทาง PE', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00334', 'title' => 'แลคเกอร์', 'unit' => 'ขวด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00335', 'title' => 'กรรไกรตัดสังกะสี', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00336', 'title' => 'โถปัสสาวะ', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00337', 'title' => 'มือจับประตู', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00338', 'title' => 'เชิงชาย', 'unit' => 'แผ่น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00339', 'title' => 'สกรู', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00340', 'title' => 'ท่อ 80 ซม.', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00341', 'title' => 'ฝา 80 ซม', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00342', 'title' => 'สต๊อปวาล์ว 3 ทาง', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00343', 'title' => 'กันชนประตู', 'unit' => '', 'qty' => '1.00', 'unit_price' => '180.00'],
            ['code' => '04-00344', 'title' => 'สายน้ำทิ้ง', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00345', 'title' => 'ล้อประตูเลื่อน', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00346', 'title' => 'ฝาปิด 1 1/2', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00347', 'title' => 'เทปพันเกลียว', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00348', 'title' => 'รูฟซิล กันซึม', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00351', 'title' => 'ผ้าตาข่าย', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00352', 'title' => 'อ่างล้างจาน', 'unit' => '', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00353', 'title' => 'เครื่องจ่ายสารคลอรีน', 'unit' => 'เครื่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00354', 'title' => 'แผ่นปิดรอยต่อ', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00355', 'title' => 'สิ่วเหล็ก', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00356', 'title' => 'เกียงโป้ว', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00357', 'title' => 'กะโหลกทองเหลือง', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00358', 'title' => 'สีรองพื้น', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00359', 'title' => 'ทราย', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '04-00360', 'title' => 'ประตูกระจก', 'unit' => 'บาน', 'qty' => '0', 'unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M4',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'นำเข้าวัสดุก่อสร้าง',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 202;
                $code = 'IN-680002';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // นำเข้าวัสดุการเกษตร M8
    public static function actionProduct3()
    {
        $data = [
            ['code' => '08-00001', 'title' => 'ชุดสตาร์ทเครื่องตัดหญ้า', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00002', 'title' => 'หัวเทียนตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00004', 'title' => 'เอ็นตัดหญ้า', 'unit' => 'ม้วน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00007', 'title' => 'สายเร่งเครื่องตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00014', 'title' => 'ใบมีดตัดหญ้า', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00015', 'title' => 'ยางกดน้ำมัน', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00016', 'title' => 'คราด', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00018', 'title' => 'ผ้ามุ้งเขียว', 'unit' => 'เมตร', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00020', 'title' => 'หน้ากากตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00021', 'title' => 'โซ่เลื่อย', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00025', 'title' => 'หัวฉีดน้ำทองเหลือง', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00026', 'title' => 'ครัชเครื่องตัดหญ้า', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00027', 'title' => 'ตลับเอ็นเครื่องตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00028', 'title' => 'ไกรเร่งเครื่องตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00029', 'title' => 'คลอรีนผง 70%', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00032', 'title' => 'ฝาครอบหัวเฟือง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00033', 'title' => 'จานไฟเครื่องตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00035', 'title' => 'หัวเชื้อจุลินทรีย์เข้มข้น', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00036', 'title' => 'โครงรถเข็นตัดหญ้า', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00037', 'title' => 'ถังพ่นยาฆ่าแมลง', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00038', 'title' => 'คราดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00039', 'title' => 'กรรไกรตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00040', 'title' => 'กรรไกรตัดกิ่ง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00041', 'title' => 'ใบมีดตัดหญ้ารถเข็น', 'unit' => 'ใบ', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00042', 'title' => 'น๊อตเกลียวซ้ายตัดหญ้า', 'unit' => 'ตัว', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00043', 'title' => 'ล้อรถเข็น', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00044', 'title' => 'สายสะพายเครื่องตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00045', 'title' => 'จุลินทรีย์สำหรับเครื่องกำจัดขยะอินทรีย์', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00047', 'title' => 'แปรงทาสีขนธรรมชาติ (สีดำ)', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00048', 'title' => 'เครื่องวัดค่าน้ำ TDS', 'unit' => 'เครื่อง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00049', 'title' => 'เชือกกระตุกเครื่องยนต์', 'unit' => 'เส้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00050', 'title' => 'ต้นรวงผึ้ง', 'unit' => 'ต้น', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00051', 'title' => 'กระปุกเอ็นตัดหญ้า', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00052', 'title' => 'สปริงครัช', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00053', 'title' => 'ชุดหัวฉีด', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00054', 'title' => 'ข้อต่อทองแดง', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00055', 'title' => 'สวิชปั๊ม', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00056', 'title' => 'โครงรถเข็น', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00057', 'title' => 'ชุดกดน้ำมันเบนซิน', 'unit' => 'อัน', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00058', 'title' => 'คลอรีนผง 75%', 'unit' => 'ถัง', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00059', 'title' => 'สปริงเกอร์', 'unit' => 'ชุด', 'qty' => '0', 'unit_price' => '0'],
            ['code' => '08-00061', 'title' => 'สารเร่งตกตะกอน', 'unit' => 'ถุง', 'qty' => '0', 'unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M8',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'นำเข้าวัสดุการเกษตร',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 499;
                $code = 'IN-680003';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุคอมพิวเตอร์
    public static function actionM12()
    {
        $data = [
            ['code' => '01-00088','title' => 'แผ่น CD (วัสดุสำนักงาน)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '12-00001','title' => 'Battery UPS 5.5Ah 12V','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '12-00002','title' => 'Battery UPS 7.2Ah 12V','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '12-00003','title' => 'Battery UPS 7.8 Ah 12 V','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '12-00006','title' => 'Hard disk SSD 480 GB','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '12-00007','title' => 'Hard disk SSD 240 GB','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '12-00014','title' => 'Toner HP 30A','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00022','title' => 'Toner HP53A','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00023','title' => 'Toner HP78A','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00024','title' => 'Toner HP79A','unit' => 'กล่อง','qty' => '35.00','unit_price' => '450.00000'],
            ['code' => '12-00025','title' => 'Toner HP85A','unit' => 'กล่อง','qty' => '10.00','unit_price' => '450.00000'],
            ['code' => '12-00026','title' => 'Toner SAMSUNG M2T-D1162','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00029','title' => 'Webcam','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '12-00030','title' => 'แผ่น CD (วส.คอมพิวเตอร์)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '12-00031','title' => 'แผ่น DVD (วส.คอมพิวเตอร์)','unit' => 'หลอด','qty' => '2.00','unit_price' => '390.00000'],
            ['code' => '12-00032','title' => 'แผ่นรองเมาส์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00033','title' => 'Toner EPSON T6641 BK','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00034','title' => 'Toner EPSON T6642 C','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00035','title' => 'Toner EPSON T6642 M','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00036','title' => 'Toner EPSON T6642 Y','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00037','title' => 'หัวแลน RJ45 CAT5','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '12-00038','title' => 'Refill Ribbon LQ-300','unit' => 'กล่อง','qty' => '20.00','unit_price' => '130.00000'],
            ['code' => '12-00039','title' => 'keyboard','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00040','title' => 'Mouse','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00052','title' => 'Mouse & Keyboord','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00055','title' => 'สาย LAN LINK 305m.','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00057','title' => 'Toner Samsung MLT-R116','unit' => 'กล่อง','qty' => '4.00','unit_price' => '990.00000'],
            ['code' => '12-00058','title' => 'Toner HP32A-CF232','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00059','title' => 'Toner HP48A-CF248A','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00066','title' => 'เครื่องจ่ายไฟ สำหรับคอมพิวเตอร์','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00069','title' => 'CAT5e UTP Cable (305m./Box) LINK (US-9015M)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00073','title' => 'Toner Samsung MLT-D116L','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '12-00075','title' => 'Adapter 12V','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '12-00086','title' => 'แผ่นบังแสงจอคอม (LCD LED video monitor)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '12-00087','title' => 'ขาตั้งโทรทัศน์ 55 นิ้ว แบบล้อเลื่อน','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00088','title' => 'Adapter NB LENOVO (USB Tip) 20V (65W) 3.25A','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '12-00089','title' => 'สายไมค์ชุดประชุมพร้อมปลั๊ก','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '12-00090','title' => 'SSD M.2 PCle 256.GB','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '12-00091','title' => 'Ram 8 GB DDR4 for Lenovo sever TS150','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00092','title' => '์Network server card card Intel Gigabit Dual port PCle 4X','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '12-00093','title' => 'ซิลิโคน CPU','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00094','title' => 'Rom Upgrade for Raid Card Lenovo','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00095','title' => 'Raid Card Sever Lenovo','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '12-00096','title' => 'สาย Lenovo Raid to Sata','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00097','title' => 'Battery backup ROM','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00099','title' => 'Toner HP107A','unit' => 'กล่อง','qty' => '8.00','unit_price' => '800.00000'],
            ['code' => '12-00100','title' => '128 GB SSD SATA APACER','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '12-00101','title' => 'Smart Card Reader','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '12-00102','title' => 'Connector RJ45','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '12-00103','title' => 'Finger Print Reader','unit' => 'อัน','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M12',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุคอมพิวเตอร์',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 543;
                $code = 'IN-680004';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุไฟฟ้าและวิทยุ IN-680005
    public static function actionM2()
    {
        $data = [
            ['code' => '02-00004','title' => 'ฟิวส์หลอดแก้ว','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00006','title' => 'หลอดไฟ LED ขนาด 18 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00007','title' => 'ปลั๊กกราวด์ 3 ช่อง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00011','title' => 'สะพานไฟ 3 เมตร','unit' => 'อััน','qty' => '1.00','unit_price' => '700.00000'],
            ['code' => '02-00012','title' => 'เทปพันสายไฟ 3M','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00013','title' => 'เทปละลาย','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00014','title' => 'หน้ากาก 3 ช่อง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00015','title' => 'กล่องลอย','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00016','title' => 'รางเก็บสายไฟ','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '02-00018','title' => 'ตะกั่วบัคกรี','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00019','title' => 'ชุดหลอดไฟสำเร็จ 18 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00020','title' => 'น้ำยาประสานบัดกรี','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00022','title' => 'สายไฟอ่อน','unit' => 'เมตร','qty' => '10.00','unit_price' => '13.00000'],
            ['code' => '02-00023','title' => 'พุกผีเสื้อ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00025','title' => 'ขั้วหลอดกระเบื้อง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00026','title' => 'ขั้วหลอดไฟไฮโซเดียม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00027','title' => 'เทอร์มินอลต่อสายไฟ','unit' => 'แผง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00030','title' => 'ตะปูเกลียวขาว 2 นิ้ว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00035','title' => 'เบรคเกอร์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00037','title' => 'สวิทซ์','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00038','title' => 'หน้ากาก 1 ช่อง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00039','title' => 'หลอดไฟ LED ขนาด 15 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00040','title' => 'ฝาครอบ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00041','title' => 'แป้น PVC','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00044','title' => 'เบรคเกอร์ 30 A','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00045','title' => 'ฝาครอบเบรคเกอร์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00046','title' => 'อะแดปเตอร์ 9V','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00050','title' => 'ไขควงวัดไฟ มีเสียง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00054','title' => 'ถ่านชาร์จ 9V','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00057','title' => 'คาปาซิเตอร์','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00059','title' => 'หลอดไฟ LED ขนาด 5 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00060','title' => 'ตะปูเกลียวดำ 1 นิ้ว','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00061','title' => 'แบตเตอร์รี่วิทยุสื่อสาร Ni-MH BP-264','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00065','title' => 'ท่อขาวเดินสายไฟ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00066','title' => 'ข้อต่อโค้ง ท่อขาวเดินสายไฟ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00067','title' => 'แค้มจับท่อขาวเดินสายไฟ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00068','title' => 'ปากกาเช็คไฟ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00070','title' => 'สวิตซ์แสงแดง โฟโต้สวิตซ์','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00071','title' => 'แบตเตอรี่ FB 200A','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00073','title' => 'กระดิ่งไร้สาย','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00074','title' => 'หางปลา','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00075','title' => 'หัวต่อปลั๊กไฟ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00078','title' => 'มอเตอร์พัดลม','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00080','title' => 'หลอดไฟ ขนาด LED 9 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00081','title' => 'ปลั๊กตัวผู้','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00083','title' => 'สายรัดเหล็ก','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00084','title' => 'หลอดไฟนีออน LED ขนาด 30 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00085','title' => 'หลอดไฟ LED ขนาด 9 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00086','title' => 'แท่งกราวด์กลีบมะเฟือง','unit' => 'แท่ง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00087','title' => 'สายดิน','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '02-00093','title' => 'กิ๊ปติดสายไฟ','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '02-00094','title' => 'คอนเนคเตอร์ ท่ออ่อนลูกฟูก','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00099','title' => 'โคมตะแกรงอลูมิเนียม โคมรีเฟล็กซ์สะท้อนแสง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00100','title' => 'ไฟฉุกเฉิน LED ขนาด 12V','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00103','title' => 'หลอดตะเกียบ แบบเกลียว 25 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00104','title' => 'หลอดเกลียว 8 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00105','title' => 'หลอดไฟฮาโลเจน 50 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00106','title' => 'แบตเตอรี่ Adult PAD-PAK','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00107','title' => 'ข้อต่อขาว','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00108','title' => 'สามทางขาว','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00109','title' => 'หลอดตะเกียบ ขนาด 18 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00110','title' => 'สปอร์ตไลท์ LED ขนาด 30 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00111','title' => 'สวิทซ์พัดลม','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00112','title' => 'คีมตัด','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00113','title' => 'คีมย้ำหางปลา','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00114','title' => 'สายไฟ ทองแดง','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00115','title' => 'ลวดแบน','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '02-00116','title' => 'ถุงมือนิรภัย','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '02-00117','title' => 'ปรีฟอร์มเข้าปลายสาย','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '02-00118','title' => 'หลอดไฟ LED ขนาด 26 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00119','title' => 'แบตเตอรี่แห้ง ขนาด 12V','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00120','title' => 'สายไฟอ่อน 2*2.5 มม.','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '02-00121','title' => 'ใบพัดลม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00122','title' => 'แบตเตอรี่ Sealed Acid','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00123','title' => 'ขายึกถังดับเพลิง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00124','title' => 'คัตเตอร์ตัดสายไฟ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00125','title' => 'ท่ออ่อน','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '02-00126','title' => 'เครื่องวัดประสิทธิภาพของแบตเตอรี่','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00127','title' => 'ฮีตเตอร์ 9000 วัตต์','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00128','title' => 'Heater 2000W','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00129','title' => 'แบตเตอรี่ 3.7 V Lithium - Ion','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00130','title' => 'แบตเตอรี่ Patient Monitor Biocare IM12','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00131','title' => 'ขั้วต่อสายไฟ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00132','title' => 'ชุดโคมไฟถนน ขนาด 150 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00133','title' => 'สายรัดพลาสติก','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00134','title' => 'ท่อหด','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '02-00135','title' => 'กล่องเบรคเกอร์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00136','title' => 'ชุดหลอดไฟสำเร็จ LED 9 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00137','title' => 'หลอดไฟ LED ขนาด 7 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00138','title' => 'หลอดไฟกลม LED ขนาด 30 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00139','title' => 'เมนเบรกเกอร์ 32A','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00140','title' => 'แผ่นรองเบรกเกอร์','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00141','title' => 'น้ำยาเอนกประสงค์','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00142','title' => 'กาวแท่งร้อน','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00143','title' => 'ป้ายทางออก Exit','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00144','title' => 'หลอดไฟ สำหรับเครื่องส่องตรวจหู Otoscope Heine','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00145','title' => 'ชุดโคมไฟถนน LED ขนาด 100 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00146','title' => 'ฟุตสวิทซ์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00147','title' => 'หลอดไฟฟลูออเรสเซนต์ ขนาด 18 วัตต์ (สีฟ้า)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00148','title' => 'หลอดไฟฟลูออเรสเซนต์ ขนาด 18 วัตต์ (สีขาว)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00149','title' => 'โซลินอยด์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00150','title' => 'เครื่องปรับแสงแรงดันไฟฟ้าอัตโนมัติ','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00151','title' => 'หลอดตะเกียบ แบบเสียบ 2 ขา ขนาด 100 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00152','title' => 'หลอดไฟฟลูออเรสเซนต์ ขนาด 30 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00153','title' => 'สวิทช์คันโยก','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00154','title' => 'ปลั๊กเมจิก','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00155','title' => 'เบรคเกอร์ 10A','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00156','title' => 'หลอดฆ่าเชื้ออบทารก','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00157','title' => 'ข้องอขาว','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00158','title' => 'สายโทรศัพท์ 4C*0.65','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00159','title' => 'สายสัญญาณเสียง 15 เมตร','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '02-00160','title' => 'แผงต่อสาย','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00161','title' => 'ปั๊มหอยโข่ง 0.5 HP','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00162','title' => 'ดอกยิงหัวแฉก-แบน แบบยาว','unit' => 'ดอก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00163','title' => 'ดอกยิงหัวแฉก-แบน แบบสั้น','unit' => 'ดอก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00164','title' => 'หลอดตะเกียบ 12 V - 50 W','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00165','title' => 'หลอดตะเกียบ 12 V -100 W','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00166','title' => 'เบรคเกอร์ 20 A','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00167','title' => 'แป้นรอง PVC','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00168','title' => 'ท่อต่อตรง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00169','title' => 'ลูกเมนย่อย','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00170','title' => 'หลอดตะเกียบ ขนาด 20 w','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00171','title' => 'พัดลมข้างฝา ติดผนัง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00172','title' => 'พัดลมโคจร','unit' => 'ตัว','qty' => '1.00','unit_price' => '1500.00000'],
            ['code' => '02-00173','title' => 'หลอดไฟฮาโลเจน 100 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00174','title' => 'ชุดปลั๊กกราวด์คู่','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00175','title' => 'ทรานซิสเตอร์สำหรับเครื่องขูดหินปูน','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00176','title' => 'แบตเตอรี่สำรองไฟ ขนาด 12 V','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00177','title' => 'สายไฟขาว','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '02-00178','title' => 'ฟิวส์กระบอก ขนาด 100 A','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00179','title' => 'พัดลมดูดอากาศ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00180','title' => 'ขาโคมไฟถนน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00181','title' => 'ใบพัดปั๊มน้ำพลาสติก 6 รู','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00182','title' => 'ใบพัดปั๊มน้ำพลาสติก 4 รู','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00183','title' => 'นิเปิลทองเหลือง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00184','title' => 'คอนแดนเซอร์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00185','title' => 'แว่นตาเชื่อม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00186','title' => 'สกัด แบน','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00187','title' => 'ปั๊มน้ำอัตโนมัติ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00188','title' => 'ดอกสว่าน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00189','title' => 'หัวคอปเปอร์ปั๊มลม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00190','title' => 'ชุดหลอดไฟ LED 15 W','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00191','title' => 'ถ่าน 23 A','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00192','title' => 'ฮีตเตอร์กลมมีเส้นแดง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00193','title' => 'หน้ากากเบรกเกอร์','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00194','title' => 'เบรกเกอร์ 30A','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00195','title' => 'สายไฟทองแดง','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '02-00196','title' => 'กล่องแยกสาย','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00197','title' => 'ชุดรางหลอดไฟสำเร็จ LED','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00198','title' => 'หัวแร้งบัดกรี','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00199','title' => 'มัลติมิเตอร์ แบบดิจิตอล','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00200','title' => 'ถ่านรีโมท 27 A','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00201','title' => 'ขั้วหลอดสตาร์ทเตอร์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00203','title' => 'ฝาปิด 3 ช่อง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00204','title' => 'หลอด LED 1 W','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00205','title' => 'หลอดไฟ LED','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00206','title' => 'เคเบิ้ลไทร์','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '02-00207','title' => 'หลอดไฟตะเกียบ ขนาด 18 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00208','title' => 'คอนเนคเตอร์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00209','title' => 'ตู้ใส่เบรกเกอร์','unit' => 'ตู้','qty' => '0','unit_price' => '0'],
            ['code' => '02-00210','title' => 'ชุดหลอดไฟ LED 18 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00211','title' => 'ปลั๊ก 3 ตา มีกราวด์คู่','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00212','title' => 'แค้มก้ามปูขาว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00213','title' => 'แบตเตอรี่ N 200 แอมป์','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '02-00214','title' => 'ปลั๊กเดี๋ยว','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00215','title' => 'สายไฟ VCT','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00216','title' => 'ไขควงแฉก','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00217','title' => 'ไขควงวัดไฟ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00218','title' => 'ชุดไขควง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00219','title' => 'หลอดตะเกียบ ขนาด 35 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00220','title' => 'หลอดไฟ LED ขนาด 40 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00221','title' => 'หลอดไฟ ขนาด 50 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00222','title' => 'หลอดไฟ ขนาด 100 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00223','title' => 'หลอดไฟ LED ขนาด 20 วัตต์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00224','title' => 'พุก #7','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00225','title' => 'ตะปูเกลียวดำ 1 "','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00226','title' => 'ชุดโคมไฟ LED ติดผนัง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00227','title' => 'Timer for Koksan Centrifuge','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '02-00228','title' => 'ไส้ไก่','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '02-00229','title' => 'กิ๊ฟ CR','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '02-00230','title' => 'ชุดฟิลส์แบบเสียบ 40A','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00231','title' => 'สวิทซ์ กด-ดับ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00232','title' => 'ชุดฟิลส์กระบอก','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00233','title' => 'โคมไฟถนน ชนิด LED ขนาด 50 วัตต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00234','title' => 'ถังใส่วัสดุมีคมติดเชื้อ สีดำ','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '02-00235','title' => 'แคปสตาร์ท 30 ไมโคร','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '02-00236','title' => 'Timer','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '02-00237','title' => 'สายไฟ 1x2.5 มม.','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '02-00238','title' => 'ไฟโซลาร์เซลล์ 60W','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00240','title' => 'ไฟโซลาร์เซลล์ 500W','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00241','title' => 'เบรกเกอร์ 10 A','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00242','title' => 'หลอดไฟ ดาวไลท์ LED ขนาด 5 W','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00243','title' => 'รีโมทคอนโทรล','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '02-00244','title' => 'ชุดคอยล์ไฟ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00245','title' => 'ชุดขาไมล์ตั้งโต๊ะ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00246','title' => 'วายนัด','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00247','title' => 'คีมปอกสายไฟ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00248','title' => 'หลอดไฟ ดาวไลท์ ขนาด 12 W','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00251','title' => 'เบรกเกอร์ 32 A','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '02-00252','title' => 'หลอดตะเกียบ ขนาด 14 w','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '02-00253','title' => 'หน้ากาก 6 ช่อง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M2',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุไฟฟ้าและวิทยุ',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 544;
                $code = 'IN-680005';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุงานบ้านงานครัว IN-680006
    public static function actionM3()
    {
        $data = [
            ['code' => '02-00017','title' => 'ถ่านกระดุม','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00001','title' => 'กรวยน้ำดื่มกระดาษ','unit' => 'กล่อง','qty' => '9.00','unit_price' => '1350.00000'],
            ['code' => '03-00002','title' => 'กระดาษชำระ','unit' => 'ม้วน','qty' => '354.00','unit_price' => '13.56463'],
            ['code' => '03-00003','title' => 'กระดาษชำระจัมโบ้โรล','unit' => 'ม้วน','qty' => '18.00','unit_price' => '82.50000'],
            ['code' => '03-00004','title' => 'กระดาษเช็ดมือ','unit' => 'ห่อ','qty' => '5.00','unit_price' => '115.00000'],
            ['code' => '03-00006','title' => 'กระเป๋าผ้าสำหรับชุดพิเศษ','unit' => 'ใบ','qty' => '17.00','unit_price' => '70.00000'],
            ['code' => '03-00007','title' => 'แก้วน้ำ ผู้ป่วย','unit' => 'ใบ','qty' => '12.00','unit_price' => '100.00000'],
            ['code' => '03-00008','title' => 'แก๊สหุงต้ม 15 กิโลกรัม (ยกเลิก)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00010','title' => 'ชุดอุปกรณ์ห้องพิเศษ (25ชุด)','unit' => 'ชุด','qty' => '50.00','unit_price' => '70.00000'],
            ['code' => '03-00011','title' => 'ชุดอุปกรณ์ห้องพิเศษ (50ชุด)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00012','title' => 'เชือกฟาง','unit' => 'ม้วน','qty' => '17.00','unit_price' => '21.66588'],
            ['code' => '03-00013','title' => 'แชมพู','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00014','title' => 'น้ำยาทำความสะอาดรถยนต์','unit' => 'แกลลอน','qty' => '7.00','unit_price' => '280.00000'],
            ['code' => '03-00015','title' => 'ด้ามโกนหนวด','unit' => 'ด้าม','qty' => '172.00','unit_price' => '16.18215'],
            ['code' => '03-00016','title' => 'ด้ามโกนหนวด เหล็ก','unit' => 'ด้าม','qty' => '144.00','unit_price' => '51.66667'],
            ['code' => '03-00017','title' => 'ตะกร้าพลาสติก ทรงกลม','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00018','title' => 'ตะกร้าพลาสติก เหลี่ยมทรงสูง','unit' => 'ใบ','qty' => '3.00','unit_price' => '229.00000'],
            ['code' => '03-00019','title' => 'ถ้วยพลาสติก 30 ออน','unit' => 'แถว','qty' => '5.00','unit_price' => '25.00000'],
            ['code' => '03-00020','title' => 'ถังขยะพลาสติก แบบเหยียบ','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00021','title' => 'ถังน้ำพลาสติก ขนาดใหญ่','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00022','title' => 'ถังน้ำพลาสติก ขนาดเล็ก','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00023','title' => 'ถังน้ำพลาสติก ขนาดกลาง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00024','title' => 'ถ่านไฟฉาย 9V','unit' => 'ก้อน','qty' => '9.00','unit_price' => '35.00000'],
            ['code' => '03-00025','title' => 'ถ่านไฟฉาย AA','unit' => 'ก้อน','qty' => '94.00','unit_price' => '27.50000'],
            ['code' => '03-00026','title' => 'ถ่านไฟฉาย AAA','unit' => 'ก้อน','qty' => '60.00','unit_price' => '27.50000'],
            ['code' => '03-00027','title' => 'ถ่านไฟฉาย กลาง','unit' => 'ก้อน','qty' => '18.00','unit_price' => '33.33333'],
            ['code' => '03-00028','title' => 'ถ่านไฟฉาย ใหญ่','unit' => 'ก้อน','qty' => '24.00','unit_price' => '19.00000'],
            ['code' => '03-00029','title' => 'ถุงขยะดำ ขนาด 14*22 นิ้ว','unit' => 'กิโลกรัม','qty' => '487.00','unit_price' => '50.00000'],
            ['code' => '03-00030','title' => 'ถุงขยะดำ ขนาด 20*26 นิ้ว','unit' => 'กิโลกรัม','qty' => '438.00','unit_price' => '50.00000'],
            ['code' => '03-00031','title' => 'ถุงขยะดำ ขนาด 30*40 นิ้ว','unit' => 'กิโลกรัม','qty' => '79.00','unit_price' => '50.00000'],
            ['code' => '03-00033','title' => 'ถุงขยะแดง ขนาด 14*22 นิ้ว','unit' => 'กิโลกรัม','qty' => '309.00','unit_price' => '64.99515'],
            ['code' => '03-00034','title' => 'ถุงขยะแดง ขนาด 20*26 นิ้ว','unit' => 'กิโลกรัม','qty' => '205.00','unit_price' => '65.00000'],
            ['code' => '03-00035','title' => 'ถุงขยะแดง ขนาด 30*40 นิ้ว','unit' => 'กิโลกรัม','qty' => '277.00','unit_price' => '65.14621'],
            ['code' => '03-00037','title' => 'ถุงมือพลาสติก','unit' => 'ห่อ','qty' => '8.00','unit_price' => '20.00000'],
            ['code' => '03-00038','title' => 'ถุงมือยาง สีส้ม','unit' => 'คู่','qty' => '20.00','unit_price' => '23.33400'],
            ['code' => '03-00039','title' => 'ถุงมือยาง สีดำ','unit' => 'คู่','qty' => '54.00','unit_price' => '65.00000'],
            ['code' => '03-00040','title' => 'ถุงหูหิ้ว ขนาด 6*14 นิ้ว','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00041','title' => 'ถุงหูหิ้ว ขนาด 8*16 นิ้ว','unit' => 'ห่อ','qty' => '67.00','unit_price' => '14.54478'],
            ['code' => '03-00042','title' => 'ถุงร้อน ขนาด 3*5 นิ้ว','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00043','title' => 'ถุงร้อน ขนาด 4.5*7 นิ้ว','unit' => 'ห่อ','qty' => '6.00','unit_price' => '42.00000'],
            ['code' => '03-00044','title' => 'ถุงร้อน ขนาด 6*9 นิ้ว','unit' => 'ห่อ','qty' => '4.00','unit_price' => '40.00000'],
            ['code' => '03-00045','title' => 'ถุงร้อน ขนาด 7*11 นิ้ว','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00046','title' => 'ถุงร้อน ขนาด 9*14 นิ้ว','unit' => 'ห่อ','qty' => '12.00','unit_price' => '40.83333'],
            ['code' => '03-00047','title' => 'ถุงร้อน ขนาด 12*18 นิ้ว','unit' => 'ห่อ','qty' => '24.00','unit_price' => '41.25042'],
            ['code' => '03-00048','title' => 'ถุงร้อน ขนาด 16*28 นิ้ว','unit' => 'ห่อ','qty' => '13.00','unit_price' => '39.99923'],
            ['code' => '03-00049','title' => 'ถุงร้อน ขนาด 18*28 นิ้ว','unit' => 'ห่อ','qty' => '20.00','unit_price' => '40.50000'],
            ['code' => '03-00050','title' => 'ที่โกยผง','unit' => 'อัน','qty' => '4.00','unit_price' => '85.00000'],
            ['code' => '03-00051','title' => 'ใบมีดโกน','unit' => 'กล่อง','qty' => '52.00','unit_price' => '18.50000'],
            ['code' => '03-00052','title' => 'ใบมีดโกนไฟฟ้า','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00053','title' => 'ปืนยิงแก๊ส','unit' => 'อัน','qty' => '13.00','unit_price' => '20.38462'],
            ['code' => '03-00054','title' => 'แปรงขัดพื้น ด้ามยาว','unit' => 'อัน','qty' => '1.00','unit_price' => '95.00000'],
            ['code' => '03-00055','title' => 'แปรงซักผ้า','unit' => 'อัน','qty' => '14.00','unit_price' => '29.00071'],
            ['code' => '03-00056','title' => 'แปรงทองเหลือง','unit' => 'อัน','qty' => '66.00','unit_price' => '20.00000'],
            ['code' => '03-00057','title' => 'แปรงล้างห้องน้ำ','unit' => 'ด้าม','qty' => '1.00','unit_price' => '25.00000'],
            ['code' => '03-00058','title' => 'ผงซักฟอก ขนาด 150 กรัม','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00059','title' => 'ผงซักฟอก ขนาด 25 กิโลกรัม','unit' => 'กล่อง','qty' => '4.00','unit_price' => '1500.00000'],
            ['code' => '03-00060','title' => 'ผงซักฟอก บรรจุกิโล','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00061','title' => 'ผ้าถูพื้น สีขาว','unit' => 'ผืน','qty' => '3.00','unit_price' => '100.00000'],
            ['code' => '03-00062','title' => 'ผ้าถูพื้น สีน้ำเงิน','unit' => 'ผืน','qty' => '1.00','unit_price' => '120.00000'],
            ['code' => '03-00063','title' => 'ผ้าถูพื้น สีแดง','unit' => 'ผืน','qty' => '4.00','unit_price' => '120.00000'],
            ['code' => '03-00064','title' => 'ผ้าม๊อบดันฝุ่น','unit' => 'ผืน','qty' => '3.00','unit_price' => '300.00000'],
            ['code' => '03-00065','title' => 'ผ้าอนามัยแบบห่วง','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00066','title' => 'แผ่นขัดพื้น สีแดง','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00067','title' => 'แผ่นขัดพื้น สีดำ','unit' => 'แผ่น','qty' => '6.00','unit_price' => '350.00000'],
            ['code' => '03-00068','title' => 'ฝอยสแตนเลส','unit' => 'ชิ้น','qty' => '94.00','unit_price' => '11.61330'],
            ['code' => '03-00069','title' => 'ไฟแช็ค','unit' => 'อัน','qty' => '12.00','unit_price' => '9.99833'],
            ['code' => '03-00070','title' => 'ไม้กวาด ขนไก่','unit' => 'อัน','qty' => '3.00','unit_price' => '126.33333'],
            ['code' => '03-00071','title' => 'ไม้กวาด ทางมะพร้าวแบบยาว','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00072','title' => 'ไม้กวาด ทางมะพร้าวแบบสั้น','unit' => 'อัน','qty' => '6.00','unit_price' => '35.00000'],
            ['code' => '03-00073','title' => 'ไม้กวาด ดอกหญ้า','unit' => 'ด้าม','qty' => '11.00','unit_price' => '65.00000'],
            ['code' => '03-00074','title' => 'ไม้กวาด หยากไย่','unit' => 'อัน','qty' => '3.00','unit_price' => '60.00000'],
            ['code' => '03-00075','title' => 'ไม้จิ้มฟัน','unit' => 'ห่อ','qty' => '26.00','unit_price' => '5.80077'],
            ['code' => '03-00076','title' => 'ไม้เสียบอาหาร','unit' => 'ห่อ','qty' => '2.00','unit_price' => '18.00000'],
            ['code' => '03-00077','title' => 'ไม้ถูพื้น','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00078','title' => 'ไม้ม๊อบดันพื้น','unit' => 'อัน','qty' => '2.00','unit_price' => '300.00000'],
            ['code' => '03-00079','title' => 'ไม้ปาดน้ำ ขนาด 18 นิ้ว','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00080','title' => 'ไม้ปาดน้ำ ขนาด 24 นิ้ว','unit' => 'อัน','qty' => '3.00','unit_price' => '500.00000'],
            ['code' => '03-00081','title' => 'รองเท้าแตะยาง','unit' => 'คู่','qty' => '31.00','unit_price' => '100.00000'],
            ['code' => '03-00082','title' => 'ลูกโป่ง','unit' => 'ถุง','qty' => '15.00','unit_price' => '53.40933'],
            ['code' => '03-00083','title' => 'สก๊อตไบร์ท','unit' => 'ชิ้น','qty' => '82.00','unit_price' => '10.29305'],
            ['code' => '03-00084','title' => 'สบู่เหลวล้างมือ','unit' => 'ขวด','qty' => '5.00','unit_price' => '75.00200'],
            ['code' => '03-00085','title' => 'สบู่เหลวอนามัยล้างมือ','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00086','title' => 'สเปรย์กันยุง ขนาด 30 มล.','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00087','title' => 'สเปรย์กันยุง ไฟฟ้า','unit' => 'ขวด','qty' => '13.00','unit_price' => '79.00000'],
            ['code' => '03-00088','title' => 'สเปรย์กำจัดแมลง','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00089','title' => 'สเปรย์ปรับอากาศ','unit' => 'ขวด','qty' => '7.00','unit_price' => '59.16714'],
            ['code' => '03-00090','title' => 'สายวัด','unit' => 'เส้น','qty' => '16.00','unit_price' => '11.87500'],
            ['code' => '03-00091','title' => 'หนังยาง','unit' => 'ห่อ','qty' => '9.00','unit_price' => '54.66667'],
            ['code' => '03-00092','title' => 'หลอดงอ','unit' => 'ห่อ','qty' => '8.00','unit_price' => '47.25000'],
            ['code' => '03-00093','title' => 'อะไหล่ยางปาดน้ำ ขนาด 18 นิ้ว','unit' => 'อัน','qty' => '2.00','unit_price' => '400.00000'],
            ['code' => '03-00094','title' => 'อะไหล่ยางปาดน้ำ ขนาด 24 นิ้ว','unit' => 'อัน','qty' => '4.00','unit_price' => '360.00000'],
            ['code' => '03-00095','title' => 'กระบอกฉีดน้ำ','unit' => 'อัน','qty' => '1.00','unit_price' => '100.00000'],
            ['code' => '03-00096','title' => 'ขวดโหลพลาสติก ฝาเกลียว','unit' => 'อัน','qty' => '204.00','unit_price' => '12.50000'],
            ['code' => '03-00097','title' => 'ขันน้ำพลาสติก','unit' => 'ใบ','qty' => '5.00','unit_price' => '28.90000'],
            ['code' => '03-00098','title' => 'ชั้นวางรองเท้าพลาสติก','unit' => 'ชุด','qty' => '5.00','unit_price' => '470.00000'],
            ['code' => '03-00099','title' => 'น้ำยาทำความสะอาดอเนกประสงค์ (F.O.G)','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00100','title' => 'น้ำยาทำความสะอาดและฆ่าเชื้อ (GERM KILLER)','unit' => 'แกลลอน','qty' => '9.00','unit_price' => '2150.00000'],
            ['code' => '03-00101','title' => 'น้ำยาซักผ้าขาว','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00102','title' => 'น้ำยาปรับผ้านุ่ม','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00103','title' => 'น้ำยาล้างจาน','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00104','title' => 'น้ำยาล้างเล็บ','unit' => 'ขวด','qty' => '1.00','unit_price' => '75.00000'],
            ['code' => '03-00105','title' => 'น้ำยาล้างห้องน้ำ สูตร2','unit' => 'แกลลอน','qty' => '9.00','unit_price' => '350.00000'],
            ['code' => '03-00106','title' => 'น้ำยาล้างห้องน้ำ ชนิดเข้มข้น โอเค','unit' => 'แกลลอน','qty' => '5.00','unit_price' => '350.00000'],
            ['code' => '03-00107','title' => 'น้ำยาเช็ดกระจก','unit' => 'แกลลอน','qty' => '4.00','unit_price' => '185.00000'],
            ['code' => '03-00108','title' => 'น้ำยาถูพื้นประจำวัน','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00109','title' => 'น้ำยาทาล้อดำ','unit' => 'แกลลอน','qty' => '4.00','unit_price' => '650.00000'],
            ['code' => '03-00111','title' => 'น้ำยาเคลือบเงาพื้น','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00113','title' => 'ฟองน้ำล้างรถ','unit' => 'ก้อน','qty' => '7.00','unit_price' => '45.00000'],
            ['code' => '03-00114','title' => 'แว่นตานิรภัย','unit' => 'ชิ้น','qty' => '3.00','unit_price' => '70.00000'],
            ['code' => '03-00116','title' => 'น้ำยาสเปรย์บั๊ฟฟ์','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00118','title' => 'ขวดโหล ใบใหญ่','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00119','title' => 'น้ำยาเก็บฝุ่นสูตรน้ำมัน','unit' => 'แกลลอน','qty' => '4.00','unit_price' => '550.00000'],
            ['code' => '03-00121','title' => 'ยาสีฟัน','unit' => 'หลอด','qty' => '7.00','unit_price' => '51.28571'],
            ['code' => '03-00122','title' => 'แป้งฝุ่น','unit' => 'กระป๋อง','qty' => '6.00','unit_price' => '31.66500'],
            ['code' => '03-00123','title' => 'ยางปั๊มห้องน้ำ','unit' => 'อัน','qty' => '3.00','unit_price' => '120.00000'],
            ['code' => '03-00125','title' => 'ด้ายเย็บผ้า','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00126','title' => 'กระสวยเย็บผ้า','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00127','title' => 'กรวย พลาสติก','unit' => 'อัน','qty' => '4.00','unit_price' => '25.00000'],
            ['code' => '03-00128','title' => 'ถุงร้อน 24*40','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00129','title' => 'คัตเตอร์บัต','unit' => 'ห่อ','qty' => '12.00','unit_price' => '17.50000'],
            ['code' => '03-00135','title' => 'กล่องหูหิ้ว','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00136','title' => 'ลิ้นชัก 5 ชั้น','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00137','title' => 'แปรงล้างขวดนม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00140','title' => 'เชือกป่าน','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '03-00141','title' => 'กระติกน้ำร้อน','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00144','title' => 'ผ้าเช็ดรถ','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00145','title' => 'เบกกิ้งโซดา','unit' => 'ถุง','qty' => '15.00','unit_price' => '30.53333'],
            ['code' => '03-00148','title' => 'เก้าอี้หน้าไม้ยางขาเหล็ก','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00149','title' => 'ฟิล์มยืดห่ออาหาร 14 นิ้ว','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '03-00150','title' => 'ฟิล์มยืดห่ออาหาร 18 นิ้ว','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '03-00151','title' => 'ช้อนสแตนเลส','unit' => 'โหล','qty' => '0','unit_price' => '0'],
            ['code' => '03-00153','title' => 'กระทะไฟฟ้า','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00154','title' => 'สบู่อาบน้ำ','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '03-00155','title' => 'แปรงสีฟัน','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
            ['code' => '03-00157','title' => 'ยางยืด','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '03-00158','title' => 'ไฮเตอร์','unit' => 'ขวด','qty' => '6.00','unit_price' => '24.00000'],
            ['code' => '03-00159','title' => 'ขวดโหล ใบกลาง','unit' => 'ใบ','qty' => '21.00','unit_price' => '35.00000'],
            ['code' => '03-00161','title' => 'ที่คว่ำจาน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00162','title' => 'ถังบีบม๊อบ แบบเหยียบ','unit' => 'ชุด','qty' => '1.00','unit_price' => '2550.00000'],
            ['code' => '03-00165','title' => 'ตะกร้าพลาสติก ทรงเหลี่ยม','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00168','title' => 'กล่องใส่อาหาร ทรงกรม ขนาดเล็ก','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00169','title' => 'กล่องใส่อาหาร ทรงกลม ขนาดกลาง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00170','title' => 'กล่องใส่อาหาร ทรงกลม ขนาดใหญ่','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00171','title' => 'กล่องใส่อาหาร สี่เหลี่ยม ขนาดเล็ก','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00172','title' => 'กล่องใส่อาหาร สี่เหลี่ยม ขนาดกลาง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00173','title' => 'กล่องใส่อาหาร สี่เหลี่ยม ขนาดใหญ่','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00174','title' => 'ผ้ากันเปื้อน','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00175','title' => 'เหยือกน้ำ','unit' => 'ใบ','qty' => '12.00','unit_price' => '195.00000'],
            ['code' => '03-00179','title' => 'ผ้าสีขาว','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '03-00180','title' => 'ผ้าสีเหลือง','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '03-00182','title' => 'รองเท้าบูทยาง','unit' => 'คู่','qty' => '1.00','unit_price' => '180.00000'],
            ['code' => '03-00184','title' => 'โลชั่น กันยุง','unit' => 'ซอง','qty' => '624.00','unit_price' => '3.12965'],
            ['code' => '03-00185','title' => 'ตีนตุ๊กแก','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '03-00187','title' => 'ชุดของขวัญเด็กแรกเกิด','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00190','title' => 'สบู่เหลว อาบน้ำ','unit' => 'ขวด','qty' => '42.00','unit_price' => '180.00000'],
            ['code' => '03-00191','title' => 'ผ้าด้ายดิบ','unit' => 'พับ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00198','title' => 'กุญแจคล้อง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00200','title' => 'กะละมัง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00201','title' => 'ผ้ากระสอบสำหรับเช็คเท้า','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00205','title' => 'กล่องพลาสติก มีล้อ ขนาดใหญ่','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00209','title' => 'หัวเตา KB8','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00210','title' => 'ฟิล์มห่ออาหาร 2 เมตร','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00211','title' => 'ไส้กรองน้ำ เมมเบน','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00212','title' => 'น้ำยาล้างพื้นลอกแว๊กซ์','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00213','title' => 'น้ำยารองพื้น','unit' => 'แกลลอน','qty' => '6.00','unit_price' => '550.00000'],
            ['code' => '03-00214','title' => 'เขียงไม้','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00215','title' => 'มีดทำครัว','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
            ['code' => '03-00225','title' => 'ไส้กรองคาร์บอน','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00228','title' => 'เครื่องนึ่งขวดนมไฟฟ้า','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00235','title' => 'ตะกร้าพลาสติก สีขาว 24*22 ซม.','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00237','title' => 'ผ้าขาวบาง','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '03-00238','title' => 'น้ำยาล้างคราบไขมัน TT&T','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00240','title' => 'กล่องพลาสติก มีล้อ ขนาดกลาง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00241','title' => 'กล่องพลาสติก มีล้อ ขนาดเล็ก','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00242','title' => 'น้ำยาขจัดคราบไขมัน','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00243','title' => 'น้ำยาบ้วนปาก','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00244','title' => 'ลวดผ้าม่านพร้อมตะขอ','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '03-00245','title' => 'สารกำจัดยุง','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00246','title' => 'ช้อนพลาสติกสั้น ขาว','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00247','title' => 'ช้อนพลาสติกใหญ่ใส','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00248','title' => 'ส้อมพลาสติกใหญ่ใส','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00249','title' => 'กล่องกระดาษใส่อาหาร','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00250','title' => 'ชามเยื่อธรรมชาติ','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00254','title' => 'ผ้าฟาง','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00255','title' => 'เข็มหมุด','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00256','title' => 'ถังขยะสีดำ มีฝาปิดชนิดหนา ขนาด 66 ลิตร','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00257','title' => 'หัวเตาแก๊ส','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00258','title' => 'เครื่องปั่นอาหาร','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00266','title' => 'ผ้าปูที่นอน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00267','title' => 'ที่นอนสปริง','unit' => 'หลัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00268','title' => 'ไม้แขวนเสื้อ','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '03-00269','title' => 'ราวตากผ้า','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00270','title' => 'แปรงซักพรม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00271','title' => 'หวี','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00272','title' => 'ผ้าอนามัย','unit' => 'ห่อ','qty' => '2.00','unit_price' => '1145.00000'],
            ['code' => '03-00273','title' => 'ส้อมพลาสติกใหญ่ใส','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '03-00274','title' => 'ชามกระดาษ มีฝาปิด','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '03-00275','title' => 'ลิ้นชัก 4 ชั้น 5 ช่อง','unit' => 'อัน','qty' => '1.00','unit_price' => '490.00000'],
            ['code' => '03-00276','title' => 'ชั้นวางของ 4 ชั้น มีล้อ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00277','title' => 'กระติกเก็บน้ำร้อน','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00278','title' => 'ถังขาวมีฝาปิด 3.5 ลิตร','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00279','title' => 'เสื้อกาวน์ตัวยาว แขนสั้น (ใส่ทำอาหาร)','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00280','title' => 'ผ้ากันเปื้อน สีขาว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00281','title' => 'หมวกทำอาหาร','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00282','title' => 'ผ้ากันเปื้อน PVC สีขาว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00283','title' => 'รองเท้าหัวโต','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '03-00284','title' => 'ถังเก็บน้ำ ขนาด 700 ลิตร','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00285','title' => 'เครื่องปั๊มนม','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00286','title' => 'ขวดนม','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00287','title' => 'หวีเด็กอ่อน','unit' => 'อัน','qty' => '2.00','unit_price' => '58.00000'],
            ['code' => '03-00288','title' => 'ถ้วยฝาปิดสแตนเลส 12 ซม.','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00289','title' => 'ถ้วยฝาปิดสแตนเลส 16 ซม.','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00290','title' => 'ถ้วยฝาปิดสแตนเลส 18 ซม.','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00291','title' => 'ปลั๊ก ยาว 3 เมตร','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00292','title' => 'ที่ใส่กระดาษชำระจัมโบ้โรล','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00293','title' => 'ยาสระผม','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00294','title' => 'ผงซักฟอก สูตรเข้มข้น','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00295','title' => 'น้ำยาทำความสะอาดห้องครัว','unit' => 'ขวด','qty' => '9.00','unit_price' => '71.11111'],
            ['code' => '03-00296','title' => 'ที่คีบอาหาร','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00297','title' => 'ไม้กวาดพรม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00298','title' => 'ชั้นวางของ 3 ชั้น แบบมีล้อ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00299','title' => 'ถ้วยหูมีผาปิด','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00300','title' => 'ถาดเมลามีนสีขาว','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00301','title' => 'กล่องใส่กระดาษชำระ','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00302','title' => 'หม้อสแตนเลส เบอร์ 30','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00303','title' => 'หม้อหุงข้าว ขนาด 5 ลิตร','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00304','title' => 'หม้อหุงข้าว ขนาด 7 ลิตร','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00305','title' => 'เครื่องทำน้ำอุ่น','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00306','title' => 'ถาดสแตนเลสมีฝาปิด','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00307','title' => 'ถ้วยอาหารห้องพิเศษ','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00308','title' => 'เครื่องทำน้ำอุ่น ขนาด 4500 วัตต์','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00309','title' => 'แก้วกระดาษ','unit' => 'แถว','qty' => '6.00','unit_price' => '40.00000'],
            ['code' => '03-00310','title' => 'เบบี้ออย','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00311','title' => 'ถุงร้อน ขนาด 30*50 นิ้ว','unit' => 'ห่อ','qty' => '4.00','unit_price' => '86.00000'],
            ['code' => '03-00312','title' => 'น้ำยาลอกแว๊กซ์','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00313','title' => 'ลิ้นชัก 4 ชั้น','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00314','title' => 'ลิ้นชัก 3 ชั้น','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00315','title' => 'ตราชั่ง','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00316','title' => 'ถังขยะแบบเหยียบมีล้อ ขนาด 85 ลิตร','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00317','title' => 'ช้อนส้อมสแตนเลส','unit' => 'โหล','qty' => '0','unit_price' => '0'],
            ['code' => '03-00318','title' => 'ถ้วยเมลามีน 4.5 นิ้ว สีฟ้า','unit' => 'โหล','qty' => '0','unit_price' => '0'],
            ['code' => '03-00319','title' => 'กะทะ 20 นิ้ว','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00320','title' => 'หม้ออลูมิเนียม เบอร์ 34','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00321','title' => 'กรรไกรตัดผ้า','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00322','title' => 'สายพานมอเตอร์จักร','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00323','title' => 'ถ้วยกระดาษใส่อาหาร','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '03-00324','title' => 'ก๊อกน้ำที่เตา','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00325','title' => 'พรมน้ำมัน','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '03-00326','title' => 'หลอด UV','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00327','title' => 'เข็มจักร','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00328','title' => 'ตัวแปลงหลอด UV','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00329','title' => 'ผ้าขนหนู 15*30 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00330','title' => 'ผลิตภัณฑ์ซักผ้าเครื่องลิควิดดีเทอร์เจน (605)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00331','title' => 'ผลิตภัณฑ์ขจัดคราบเลือดสกปรก ผ้าสี (604)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00332','title' => 'ผลิตภัณฑ์เสริมซัก (ด่างเสริมการซัก) (600)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00333','title' => 'ผลิตภัณฑ์ขจัดคราบไขมันบนผ้า (609)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00334','title' => 'ผลิตภัณฑ์ล้างเคมี (ซาวร์) (601)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00335','title' => 'ผลิตภัณฑ์ปรับผ้านุ่ม (606)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00336','title' => 'ผงซักฟอง ขนาด 300 กรัม','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00337','title' => 'ผลิตภัณฑ์ซักผ้าขาว แมกซ์ไวท์ (603)','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00338','title' => 'ด้ายขาว','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00339','title' => 'สบู่เหลว เด็ก','unit' => 'ขวด','qty' => '39.00','unit_price' => '127.49974'],
            ['code' => '03-00340','title' => 'ตาข่ายไก่','unit' => 'มัด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00341','title' => 'สายอ่อน ยาว 2.5 เมตร','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00342','title' => 'ข้องอมือจับ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00343','title' => 'ท่อดูดตรง แบบ 2 ท่อน','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00344','title' => 'หัวดูดฝุ่น','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00345','title' => 'หัวดูดน้ำ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00346','title' => 'หัวดูดฝุ่นปากแบน','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00347','title' => 'หัวดูดฝุ่นแปรงกลม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00348','title' => 'ข้อต่อสำหรับสวมอุปกรณ์','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00350','title' => 'แผ่นปั่นเงา สีแดง','unit' => 'แผ่น','qty' => '6.00','unit_price' => '350.00000'],
            ['code' => '03-00351','title' => 'ผ้าไมโครไฟเบอร์ 40*40 ซม.','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00352','title' => 'ตะกร้าสีขาว 19*28*13 ซม.','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00353','title' => 'ลิ้นชัก 3 ชั้น 6 ช่อง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00355','title' => 'ผ้าห่มนาโน','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00356','title' => 'โอเอชีส','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00357','title' => 'ไหมขัดฟัน','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00358','title' => 'ถุงหูหิ้วชนิดหนา 9*18 นิ้ว','unit' => 'ห่อ','qty' => '9.00','unit_price' => '38.00000'],
            ['code' => '03-00359','title' => 'ไฟฉายแบบธรรมดา','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00360','title' => 'ถุงร้อน ขนาด 24*36','unit' => 'ห่อ','qty' => '6.00','unit_price' => '42.50000'],
            ['code' => '03-00361','title' => 'ผลิตภัณฑ์ทำความสะอาดโถชักโครก','unit' => 'ขวด','qty' => '7.00','unit_price' => '350.00000'],
            ['code' => '03-00362','title' => 'แชมพู','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00364','title' => 'หม้อนึ่ง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00365','title' => 'ผงซักฟอก บรรจุ 1 กก.','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00366','title' => 'ล้อเครื่องขัดพื้น','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00367','title' => 'ถาดอาหารว่าง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00368','title' => 'ช้อนกาแฟเมลามีน','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00369','title' => 'จานขนาด 12 นิ้ว','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00370','title' => 'ผ้าถูพื้นหัวกลม','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00371','title' => 'จาน ขนาด 6','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00372','title' => 'ถ้วย ขนาด 4','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00373','title' => 'บันได 3 ขั้น','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00374','title' => 'ฝาเรียบ 75 มม.TCL','unit' => 'แถว','qty' => '6.00','unit_price' => '25.00000'],
            ['code' => '03-00375','title' => 'ถ้วย TR-5 ออนซ์','unit' => 'แพค','qty' => '6.00','unit_price' => '35.00000'],
            ['code' => '03-00376','title' => 'คัตเตอร์บัดก้านเล็ก','unit' => 'ห่อ','qty' => '36.00','unit_price' => '16.58333'],
            ['code' => '03-00377','title' => 'แผ่นติดแมลงวัน','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00380','title' => 'ฟิล์มยืดห่ออาหาร 12 นิ้ว','unit' => 'ม้วน','qty' => '8.00','unit_price' => '231.66750'],
            ['code' => '03-00381','title' => 'ผ้าเช็ดมือ','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00382','title' => 'กล่องเอนกประสงค์','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00383','title' => 'หม้อ','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00384','title' => 'ชุดช้อนห่วงดวง 9 ชิ้น','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00385','title' => 'ห่วงตากผ้าเหลี่ยม','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00386','title' => 'ที่ลับมีดพลาสติก','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00387','title' => 'หินลับมีดสีดำ','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00388','title' => 'กล่องหูหิ้ว','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00389','title' => 'ผ้าขนหนู นาโน','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00390','title' => 'ผ้าขนหนู 12*12','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00395','title' => 'ถ่านไฟฉาย AA ธรรมดา','unit' => 'ก้อน','qty' => '36.00','unit_price' => '6.25000'],
            ['code' => '03-00396','title' => 'ถุงร้อน ขนาด 24*43','unit' => 'ห่อ','qty' => '2.00','unit_price' => '85.00000'],
            ['code' => '03-00398','title' => 'หม้อต้มน้ำไฟฟ้า','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00399','title' => 'น้ำยาหล่อลื่นเอนกประสงค์ (โซแน็ค)','unit' => 'ขวด','qty' => '4.00','unit_price' => '150.00000'],
            ['code' => '03-00400','title' => 'หัวฉีดฟ๊อกกี้','unit' => 'อัน','qty' => '5.00','unit_price' => '25.00000'],
            ['code' => '03-00401','title' => 'ตะกร้าพลาสติกสี่เหลี่ยมใหญ่ ห้องยา','unit' => 'ใบ','qty' => '30.00','unit_price' => '49.00000'],
            ['code' => '03-00402','title' => 'ตะกร้าพลาสติกสี่เหลี่ยมกลาง ห้องยา','unit' => 'ใบ','qty' => '50.00','unit_price' => '30.00000'],
            ['code' => '03-00403','title' => 'ตะกร้าพลาสติกหูหิ้ว ห้องยา','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00404','title' => 'Solenoid Water Valve','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00405','title' => 'สายพาน เครื่องซักผ้า','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00406','title' => 'วาล์วน้ำทิ้ง เครื่องซักผ้า','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00407','title' => 'บานพับประตูเครื่องอบผ้าด้านซ้าย','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00408','title' => 'บานพับประตูเครื่องอบผ้าด้านขวา','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '03-00409','title' => 'สลายคราบ','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00410','title' => 'โฟมแว็กซ์','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00411','title' => 'ม่านม้วน NICE 100x210 CM','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00412','title' => 'ม่านม้วน NICE 70x210 CM','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00413','title' => 'สายพิกเซลแก๊ส ขนาด 1 เมตร','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00415','title' => 'ถุงมือยาง สีส้ม No. 7.5','unit' => 'คู่','qty' => '92.00','unit_price' => '23.33326'],
            ['code' => '03-00416','title' => 'สายรัดยางยืด','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00417','title' => 'กล่องเก็บของ 42 L','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00418','title' => 'กล่องเก็บของมีล้อ 140 L','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00419','title' => 'กล่องล้อเลื่อนใส 80 L','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00420','title' => 'ชุดม่านพร้อมราง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '03-00421','title' => 'เจลดับกลิ่น','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00422','title' => 'ไฟฉายคาดหัว','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '03-00423','title' => 'สายพานตีนตะขาบเครื่องซิลสายพาน','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00425','title' => 'กล่องอาหาร ชานอ้อย','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00427','title' => 'กาวดักหนู','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00430','title' => 'ผลิตภัณฑ์ลบคราบยางมะตอยและคราบกาว','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00431','title' => 'มุ้งไนล่อน','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00434','title' => 'ตะกร้าในห้องน้ำ','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00435','title' => 'เตาย่างไร้ควัน','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00343','title' => 'ผ้าขนหนู ขนาด 24*48 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00343','title' => 'ผ้าขนหนู ขนาด 24*48 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M3',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุงานบ้านงานครัว',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 545;
                $code = 'IN-680006';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุยานพาหนะและขนส่ง IN-680007
    public static function actionM5()
    {
        $data = [
            ['code' => '05-00001','title' => 'โซ่รถจักรยานยนต์','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00004','title' => 'น๊อต แหวน','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00005','title' => 'น้ำกลั่นแบตเตอรี่','unit' => 'ลิตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00006','title' => 'น้ำกลั่นขาว','unit' => 'ลิตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00007','title' => 'น๊อต 3/8 * 1 1/2','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00009','title' => 'สายพาน','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00010','title' => 'แบตเตอรี่','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '05-00011','title' => 'น้ำมัน 2T','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '05-00012','title' => 'น้ำมันเครื่อง','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '05-00014','title' => 'ลูกปืนเพลา','unit' => 'ตลับ','qty' => '0','unit_price' => '0'],
            ['code' => '05-00015','title' => 'จาระบี','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '05-00017','title' => 'ยางรถ','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00018','title' => 'สลิง','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00019','title' => 'กิ๊บล็อคสลิง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00020','title' => 'โซ่เหล็ก 38 1/25','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00021','title' => 'แหวนรองสปริง 3/4','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00022','title' => 'สายพานแพร','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00023','title' => 'น้ำมัน 4T','unit' => 'ลิตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00024','title' => 'ตะไบกลม','unit' => 'เล่ม','qty' => '0','unit_price' => '0'],
            ['code' => '05-00025','title' => 'อุปกรณ์ล็อคล้อรถยนต์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '05-00026','title' => 'สายอ่อนอัดจารบี','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00027','title' => 'เหล็กรั้งโซ่ 1/2','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00028','title' => 'ลวดแสตนเลส 2.6','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00030','title' => 'ลวดอ่อน 2 มม.','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '05-00031','title' => 'กาวแดง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '05-00032','title' => 'น้ำมันหล่อลื่นเอนกประสงค์','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '05-00033','title' => 'โอริงยาง','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00034','title' => 'สลิง 1/2','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00036','title' => 'สายพาน A','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00037','title' => 'วาล์วน้ำ เหลือง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00038','title' => 'น้ำมันโซ่ 5L','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '05-00039','title' => 'น้ำมันเกียร์','unit' => 'ลิตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00040','title' => 'ตะไบหางหนู','unit' => 'เล่ม','qty' => '0','unit_price' => '0'],
            ['code' => '05-00041','title' => 'ล้อรถเข็น 8"','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '05-00042','title' => 'ตัวเร่งสลิง 1/4','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00043','title' => 'สายไฮโตริค','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00044','title' => 'น้ำมันเครื่องเบนซิน','unit' => 'ลิตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00045','title' => 'สายแก๊ส','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '05-00046','title' => 'เหล็ดรัด','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00047','title' => 'สาแหรก 6"','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00048','title' => 'เหล็กรู','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00049','title' => 'น๊อต 7/16 * 2"','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00052','title' => 'ล้อรถเข็น ขนาด 4 นิ้ว แบบมีเบรค','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '05-00053','title' => 'ล้อรถเข็น','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '05-00054','title' => 'ล้อรถเข็นขนาด 8 นิ้ว แบบแป้นตาย','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '05-00055','title' => 'ล้อรถเข็นขนาด 8 นิ้ว แบบแป้นเป็น มีเบรค','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '05-00056','title' => 'น๊อตขันเกลียวเร่ง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '05-00057','title' => 'โซ่เล็ก','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '05-00058','title' => 'น๊อตตัวดำ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00059','title' => 'สายไฮโดรลิค','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '05-00060','title' => 'น๊อตมิล','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00061','title' => 'น๊อต','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00062','title' => 'โอลิ่ง','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00063','title' => 'น๊อตมิล+แหวน','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '05-00064','title' => 'ล้อยางขนาด 4.10/3.50-5 พร้อมยางใน','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '05-00065','title' => 'ล้อยางนอกพร้อมยางใน ขนาด 3.50-5','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '05-00066','title' => 'กระจกมองข้างรถตู้','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '05-00067','title' => 'น๊อตสตัด','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '05-00068','title' => 'น๊อตล้อแม็กซ์','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '05-00069','title' => 'ใบปัดน้ำฝน','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '05-00070','title' => 'ลูกล้อเกลียวแบบ GNRB ขนาด 3 นิ้ว','unit' => '','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M5',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุยานพาหนะและขนส่ง',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 546;
                $code = 'IN-680007';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุเชื้อเพลิงและหล่อลื่น IN-680008
    public static function actionM6()
    {
        $data = [
            ['code' => '06-00001','title' => 'แก๊สหุงต้ม ขนาด 15 กก.','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '06-00003','title' => 'น้ำมันแก๊สโซฮอล์ 91','unit' => 'ลิตร','qty' => '0','unit_price' => '0'],
            ['code' => '06-00004','title' => 'น้ำมันดีเซล','unit' => 'ลิตร','qty' => '0','unit_price' => '0'],
            ['code' => '06-00005','title' => 'แก๊สหุงต้ม ขนาด 48 กก.','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M6',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุเชื้อเพลิงและหล่อลื่น',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 547;
                $code = 'IN-680008';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุวิทยาศาสตร์หรือการแพทย์ IN-680009
    public static function actionM7()
    {
        $data = [
            ['code' => '03-00259','title' => 'ช็อคคลอรีน','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00260','title' => 'ลองลาสติ้งคลอรีน','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '03-00261','title' => 'ถุงกรอง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '03-00262','title' => 'หัวดูดตะกอนแบบมีล้อ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00263','title' => 'สายดูดตะกอน','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '03-00264','title' => 'ตะแกรงดักใบไม้','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '03-00265','title' => 'เทสต์คิทน้ำ(รีฟิล)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '07-00351','title' => 'ชุดทดสอบโคลิฟอร์มขั้นต้น (SI-2)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00354','title' => 'คลอรีนผง 65%','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00381','title' => 'ชุดทดสอบบอแร็กซ์(ผงกรอบ)ในอาหาร','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00382','title' => 'ชุดทดสอบสารกรดซาลิซิลิคในอาหาร(สารกันรา)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00383','title' => 'ชุดทดสอบโซเตียม ไฮโดรซัลไฟด์(สารฟอกขาว)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00384','title' => 'ชุดทดสอบฟอร์มาลิน(น้ำยาดองศพ)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00385','title' => 'ชุดทดสอบโคลิฟอร์มแบคทีเรียตรวจน้ำบริโภค(อ.11)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00406','title' => 'ชุดทดสอบโคลิฟอร์มในอาหาร','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '07-00407','title' => 'ชุดทดสอบโคลิฟอร์มในน้ำและน้ำแข็ง','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M7',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุวิทยาศาสตร์หรือการแพทย์',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 548;
                $code = 'IN-680009';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // ##
    // วัสดุบริโภค IN-680010
    public static function actionM18()
    {
        $data = [
            ['code' => '18-00003','title' => 'กะทิกล่อง ขนาด 500 มม.','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00005','title' => 'เกลือ','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00007','title' => 'ข้าวเจ้าหอมมะลิ (กิโลกรัม)','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00009','title' => 'ข้าวไรซ์เบอรี่','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00010','title' => 'ข้าวเหนียว','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00011','title' => 'เครื่องตุ๋น','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00012','title' => 'ซอสปรุงรส','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00013','title' => 'ซอสพริก','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00014','title' => 'ซอสมะเขือเทศ','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00015','title' => 'ซอสหอย','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00016','title' => 'ซีอิ้วขาว','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00017','title' => 'ซีอิ้วดำ','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00019','title' => 'นมข้นหวาน','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00020','title' => 'นมจืด','unit' => 'ลัง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00021','title' => 'นมถั่วเหลือง','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00023','title' => 'น้ำดื่มบรรจุถัง-ขนาด 20 ลิตร','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00024','title' => 'น้ำดื่มบรรจุขวด-ขนาดเล็ก','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00025','title' => 'น้ำดื่มบรรจุขวด-ขนาดกลาง','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00026','title' => 'น้ำดื่มบรรจุขวด-ขวดใหญ่','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00028','title' => 'น้ำตาลทราย','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00029','title' => 'น้ำปลา','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00031','title' => 'น้ำมันงา','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00032','title' => 'น้ำมันพืช ขนาดบรรจุ 1 ลิตร','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00033','title' => 'น้ำมันพืช ขนาดบรรจุ 2 ลิตร','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00034','title' => 'น้ำส้มสายชู','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00035','title' => 'น้ำหวาน เฮลบลูบอย','unit' => 'ขวด','qty' => '14.00','unit_price' => '67.85571'],
            ['code' => '18-00037','title' => 'แป้งข้าวโพด','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00039','title' => 'ผงพะโล้','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '18-00040','title' => 'ผงหมูแดง','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '18-00042','title' => 'พริกไทยป่น','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00043','title' => 'พริกไทยเม็ด','unit' => 'ขีด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00044','title' => 'ไม้พะโล้','unit' => 'ซอง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00045','title' => 'วุ้นเส้น','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '18-00048','title' => 'เส้นหมี่','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '18-00050','title' => 'โอวัลติน 3 in 1','unit' => 'ห่อ','qty' => '3.00','unit_price' => '219.00000'],
            ['code' => '18-00051','title' => 'อาหารสด','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00052','title' => 'น้ำจิ้มไก่','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00053','title' => 'ขนม','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00054','title' => 'ไข่ไก่','unit' => 'ฟอง','qty' => '104.00','unit_price' => '60.00000'],
            ['code' => '18-00055','title' => 'นมงาดำ','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00056','title' => 'ข้าวหอมมะลิ (กระสอบ)','unit' => 'กระสอบ','qty' => '0','unit_price' => '0'],
            ['code' => '18-00057','title' => 'ข้าวหอมมะลิผลมข้าวไรซ์เบอรี่','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00060','title' => 'น้ำมันปาล์ม ขนาด 1 ลิตร','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '18-00061','title' => 'กะทิธัญพืช','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00134','title' => 'งาขาว','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00154','title' => 'เส้นก่วยจั๊บ','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00158','title' => 'น้ำพริกเผา','unit' => 'กระปุก','qty' => '0','unit_price' => '0'],
            ['code' => '18-00159','title' => 'นมข้นจีด','unit' => 'กระป๋อง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00180','title' => 'สาหร่ายแห้ง','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00184','title' => 'เส้นก๋วยเตี๋ยว เส้นใหญ่','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00189','title' => 'กระเทียมเจียว','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00190','title' => 'กาแฟ 3 in 1','unit' => 'ซอง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00193','title' => 'แป้งมัน','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00194','title' => 'กาแฟดำ','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '18-00198','title' => 'ข้าวหอมมะลิ','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '18-00201','title' => 'นมวัว','unit' => 'แพค','qty' => '0','unit_price' => '0'],
            ['code' => '18-00203','title' => 'ดอกเก๊กฮอวย','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00204','title' => 'อัญชันอบแห้ง','unit' => 'ถุง','qty' => '0','unit_price' => '0'],
            ['code' => '18-00205','title' => 'ไข่ไก่ เบอร์ 3','unit' => 'แผง','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M18',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุบริโภค',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 549;
                $code = 'IN-680010';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุเครื่องแต่งกาย IN-680011
    public static function actionM10()
    {
        $data = [
            ['code' => '10-00001','title' => 'เสื้อผู้ป่วย','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '10-00002','title' => 'ผ้าถุงผู้ป่วย','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '10-00003','title' => 'ผ้าปูเตียง','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '10-00004','title' => 'ปลอกหมอน','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '10-00005','title' => 'เสื้อแบบให้นมบุตร','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '10-00012','title' => 'เสื้อกาวน์คอฮาวายคลุมทำหัตกรรม','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '10-00013','title' => 'ผ้าสี่เหลี่ยม 2 ชั้น ขนาด 25*25 นิ้ว (ผ้าโทเรสีม่วง)','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '10-00021','title' => 'เสื้อกราวน์กันน้ำ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '10-00022','title' => 'ชุดเจ้าหน้าที่แบบคอวี (เสื้อ+กางเกง)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '10-00023','title' => 'เสื้อกาวน์คอฮาวาย ตัวยาว แขนสั้น','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '10-00024','title' => 'เสื้อกาวน์คอฮาวาย ตัวยาวแขนปลายจั้ม','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '10-00025','title' => 'ชุดดับเพลิงพร้อมหมวก','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M10',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุสำนักงาน',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 550;
                $code = 'IN-6800011';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

    // วัสดุการแพทย์ทั่วไป IN-680012
    public static function actionM22()
    {
        $data = [
            ['code' => '22-00462','title' => 'สติ๊กเกอร์ 3*4 cm.','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00463','title' => 'สติ๊กเกอร์ 5*7.5 cm.','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00157','title' => 'ล้อรถเข็น ขนาด 2 นิ้ว แบบเกลียว','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00158','title' => 'ล้อลูกบอล ขนาด 2 นิ้ว แบบเกลียว','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00159','title' => 'ล้อรถเข็น ขนาด 3 นิ้ว แบบเกลียว','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00160','title' => 'ยางล้อรถเข็น','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00647','title' => 'ลูกล้อ สำหรับ Stand เครื่อง Vital sign monitor','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00011','title' => 'แผ่นให้ความร้อน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00110','title' => 'ซองบรรจุเวชภัณฑ์ชนิดเรียบ ขนาด 2 นิ้ว (5cm*200m)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00111','title' => 'ซองบรรจุเวชภัณฑ์ชนิดเรียบ ขนาด 3 นิ้ว (7.5cm*200m)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00112','title' => 'ซองบรรจุเวชภัณฑ์ชนิดเรียบ ขนาด 4 นิ้ว (10cm*200m)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00113','title' => 'ซองบรรจุเวชภัณฑ์ชนิดเรียบ ขนาด 6 นิ้ว (15cm*200m)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00114','title' => 'ซองบรรจุเวชภัณฑ์ชนิดเรียบ ขนาด 8 นิ้ว (20cm*200m)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00116','title' => 'ซองบรรจุเวชภัณฑ์ชนิดซ้อนขอบ ขนาด 6 นิ้ว (15cm*100m)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00117','title' => 'ซองบรรจุเวชภัณฑ์ชนิดซ้อนขอบ ขนาด 12 นิ้ว (30cm*100m)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00120','title' => 'ผลิตภัณฑ์ทำความสะอาดเครื่องมือแพทย์ (ชนิดไม่มีฟอง)','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00122','title' => 'สายซิลิโคน 7*11 mm','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00126','title' => 'หลอดแก๊สเอทธีลีนออกไซด์ 100 Gms.','unit' => 'หลอด','qty' => '30.00','unit_price' => '295'],
            ['code' => '22-00128','title' => 'สาย Cannolar ผู้ใหญ่','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00131','title' => 'Mask with Bag ผู้ใหญ่','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00139','title' => 'สายออกซิเจน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00140','title' => 'หลอด Spor Test (Biological For EO 4hrs.)','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00141','title' => 'สติ๊กเกอร์ ม้วน HALLO 2HG','unit' => 'ม้วน','qty' => '30.00','unit_price' => '50'],
            ['code' => '22-00142','title' => 'แอลกอฮอล์เจล ขนาด 1.2 ลิตร','unit' => 'ขวด','qty' => '26.00','unit_price' => '1200'],
            ['code' => '22-00143','title' => 'Oxygen Cannula Adult','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00149','title' => 'ล้อเข็นเตียงผู้ป่วย แบบเกลียวแป้น ขนาด 5 นิ้ว','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00150','title' => 'เครื่องเป่าบริหารปอด (Triflow incentive spirometer)','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00151','title' => 'ล้อรถเข็น ไนล่อน ขนาด 2 นิ้ว','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00152','title' => 'flow sensor assembly','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00153','title' => 'Elbow connector','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00153','title' => 'สติ๊กเกอร์ ขนาด 5*2.5 cm.','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00154','title' => 'ล้อรถเข็น แบบเกลียว ขนาด 6 นิ้ว','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00155','title' => 'แผ่นเคลื่อนย้ายผู้ป่วย (Padslide)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00229','title' => 'ออกซิเจน 6 คิว','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00230','title' => 'W1D 15 x 10 mm. Loop Electrode, Disposable','unit' => 'BOX','qty' => '0','unit_price' => '0'],
            ['code' => '22-00231','title' => 'ออกซิเจน 0.5 คิว','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00234','title' => 'ถังใส่วัสดุมีคมติดเชื้อ สีดำ','unit' => 'ใบ','qty' => '112.00','unit_price' => '75'],
            ['code' => '22-00239','title' => 'Spo2 Sensor ผู้ใหญ่','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00240','title' => 'Spo2 Sensor สำหรับเด็ก','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00242','title' => 'กรรไกรผ่าตัด CVD.17 cm.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00243','title' => 'กรรไกรผ่าตัด CVD 20 cm.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00246','title' => 'ผ้าพันแขนขนาด Adult No.11','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00247','title' => 'ผ้าพันแขนขนาด Small Adult No.10','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00248','title' => 'ผ้าพันแขนขนาด Child No.9','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00249','title' => 'กระดาษปริ้นเครื่องนึ่งไอน้ำ','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00252','title' => 'สายท่อลมเดี่ยวสำหรับวัดความดันโลหิต','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00260','title' => 'ผ้ายางสองหน้า เขียว-แดง','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00264','title' => 'ถุงผ้าแดง','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00267','title' => 'ไฟฉาย LED (รุ่น MT06MD)','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00268','title' => 'Battery','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '22-00270','title' => 'Extension (Masimo)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00272','title' => 'แผ่นกรองอากาศ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00273','title' => 'Bacteria filter','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00273','title' => 'Bacteria filter','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00277','title' => 'เครื่องวัดไข้ดิจิตอล (ปรอท)','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00279','title' => 'ถุงบรรจุศพ','unit' => 'ใบ','qty' => '31.00','unit_price' => '460'],
            ['code' => '22-00280','title' => 'แบตเตอรี่','unit' => 'ลูก','qty' => '0','unit_price' => '0'],
            ['code' => '22-00281','title' => 'W2D 20 x 10 mm. Loop Electrode, Disposable','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00282','title' => 'W3D 25 x 10 mm. Loop Electrode, Disposable','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00291','title' => 'สายจี้ไฟฟ้า','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00292','title' => 'Plate ติดจี้ไฟฟ้า','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00293','title' => 'ล้อยางตัน รถเข็น WHEEL CHAIR','unit' => 'ล้อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00296','title' => 'เกย์ออกซิเจน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00299','title' => 'สายวัดค่าความอิ่มตัวของออกซิเจนในเลือด','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00305','title' => 'ผ้าพันแขนขนาด Large Adult No.12','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00318','title' => 'เครื่องวัดอุณหภูมิร่างกาย แบบดิจิตอล','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00319','title' => 'ผ้าปูเตียง','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00321','title' => 'ผ้าถุงผู้ป่วย','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00322','title' => 'เสื้อผู้ป่วย','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '22-00327','title' => 'เสื้อกาวน์คอฮาวายคลุมทำหัตกรรม','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '22-00330','title' => 'เสื้อกาวน์กันน้ำทำคลอด','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00342','title' => 'ชุดเจ้าหน้าที่เสื้อ+กางเกง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00345','title' => 'ชุดอุปกรณ์เผ้าระวังและส่งเสริมพัฒนาการเด็กฯ0-6 ปี','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00357','title' => 'เครื่องวัดอุณหภูมิและความชื้นแบบ IN - OUT','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00402','title' => 'ผ้ายางสองหน้า ฟ้า-ชมพู','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00403','title' => 'เสื้อแบบให้นมบุตร','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '22-00404','title' => 'ผ้าเช็ดเท้าสีขาว','unit' => 'โหล','qty' => '0','unit_price' => '0'],
            ['code' => '22-00405','title' => 'ผ้าเช็ดตัวผู้ป่วย','unit' => 'โหล','qty' => '0','unit_price' => '0'],
            ['code' => '22-00406','title' => 'ถุงผ้าหูรูด','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00407','title' => 'ผ้าขวางเตียง','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00408','title' => 'ผ้าห่อเซ็ท 2 ชั้น ขนาด 20*20 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00410','title' => 'ผ้าห่อเซ็ทเซ็ท 2 ชั้น ขนาด 40*40 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00411','title' => 'ผ้าห่อเซ็ทเซ็ท 2 ชั้น ขนาด 60*60 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00412','title' => 'ผ้า TR 2 ชั้น','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00413','title' => 'ผ้าคลุม Mayo','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00414','title' => 'ปลอกถุงขา','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00415','title' => 'ผ้าขนหนู','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00416','title' => 'ถุงผ้า ขนาด 20*25 นิ้ว','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00417','title' => 'ถุงผ้า ขนาด 30*40 นิ้ว','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00418','title' => 'ผ้าห่อเซ็ท 2 ชั้น ขนาด 25*25 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00436','title' => 'สายแอร์โรเตอร์แบบกลม 4 รู','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00461','title' => 'ไฟฉาย LED แบบชาร์จไฟ','unit' => 'อัน','qty' => '3.00','unit_price' => '1500'],
            ['code' => '22-00468','title' => 'เม็ดสารกันชื้น','unit' => 'กิโลกรัม','qty' => '0','unit_price' => '0'],
            ['code' => '22-00472','title' => 'ผ้าขนหนู ขนาดสีขาว ขนาด 15*30 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00473','title' => 'สายสัญญาณ EKG','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00474','title' => 'กรรไกรตัดไหมตรง SH/BI 14.5 cm.','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00490','title' => 'Mask with Nebulizer Adult','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00491','title' => 'Aliss','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00506','title' => 'Kocker Artery','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00507','title' => 'Long non Tooth Forceps','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00508','title' => 'Long Tooth Forceps','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00509','title' => 'Mayo','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00510','title' => 'Metzenbaum','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00512','title' => 'Needle holder ใหญ่','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00547','title' => 'ผ้าเจาะรูผืนเล็ก','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00565','title' => 'Liner suction 1500cc','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00566','title' => 'Tube connect 1/4" x 6"','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00568','title' => 'สาย SpO2 senser','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00569','title' => 'Oxygen regulator','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00570','title' => 'Inner filetr','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00573','title' => 'ถ้วยใส่น้ำยา','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00595','title' => 'ผ้าสี่เหลี่ยม 2 ชั้น ขนาด 25*25 ซม.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00596','title' => 'ผ้าสี่เหลี่ยม 2 ชั้น ขนาด 15*15 ซม.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00597','title' => 'ผ้าสี่เหลี่ยม 2 ชั้น (เจาะกลาง 4*4 ซม) ขนาด 18*18 ซม.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00598','title' => 'ผ้าจับโคมไฟ 2 ชั้น (ติดกระดุม) ขนาด 5*5 ซม.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00599','title' => 'ถุงผ้า 2 ชั้น ขนาด 65*60 ซม.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00600','title' => 'ผ้าพันสายดูดน้ำลาย (มีเชือกผูก) ขนาด 7*66 ซม.','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00602','title' => 'ผลิตภัณฑ์กำจัดคราบสนิมหินปูน และคราบฝังแน่น','unit' => 'แกลลอน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00604','title' => 'DHC-สติ๊กเกอร์-ไม่มีอินดิเคเตอร์ ดวงเล็ก ขนาด 3*4 ซม.','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00606','title' => 'DHC-สติ๊กเกอร์-มีอินดิเคเตอร์ ดวงเล็ก ขนาด 3*4 ซม.','unit' => 'ม้วน','qty' => '45.00','unit_price' => '750'],
            ['code' => '22-00607','title' => 'DHC-สติ๊กเกอร์-มีอินดิเคเตอร์ ดวงเล็ก ขนาด 5*7.5 ซม.','unit' => 'ม้วน','qty' => '19.00','unit_price' => '1000'],
            ['code' => '22-00608','title' => 'ชุดตะกั่วป้องกันรังสีจากเครื่องเอกซเรย์แบบเต็มตัวพร้อมไทยอยด์ซิลด์ Size L รุ่น Classic','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00609','title' => 'ชุดตะกั่วป้องกันรังสีจากเครื่องเอกซเรย์แบบเต็มตัวพร้อมไทยอยด์ซิลด์ Size S รุ่น Innomax','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00610','title' => 'ไม้แขวนสแตนเลสชุดตะกั่วแบบติดผนัง','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00620','title' => 'สายวัดออกซิเจนในเลือดสำหรับเด็ก','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00621','title' => 'ผ้าพันแขนสำหรับวัดความดัน (Blood Pressure Cuff)','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00622','title' => 'สติ๊กเกอร์ พิมพ์วันหมดอายุ ขนาด 2.4*1.55 ซม.','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00635','title' => 'ผ้าเซ็ทในคลอด 2 ชั้น','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00644','title' => 'ปรอทวัดไข้ Digital','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00662','title' => 'Surgicel','unit' => 'pcs','qty' => '0','unit_price' => '0'],
            ['code' => '22-00666','title' => 'Grave Vaginal Speculum No.S','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00667','title' => 'Grave Vaginal Speculum No.M','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00668','title' => 'Grave Vaginal Speculum No.L','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00669','title' => 'Suction หู No.3','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00670','title' => 'Suction หู No.5','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00676','title' => 'แก๊สเอทธิลีนออกไซด์ 100% (บรรจุท่อ 6 กก.)','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00679','title' => 'PLAT 8(200mm*70m) TYVEK','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00680','title' => 'PLAT 10(250mm*70m) TYVEK','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00681','title' => 'PLAT 16(400mm*70m) TYVEK','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00682','title' => 'Cheamical Strip for Plasrna','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00683','title' => 'Biological indicator for Plasrna 30 mins.(BT96)','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00685','title' => 'สายพานเทปลอน 750 มม.','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00686','title' => 'ผ้ายางเอนกประสงค์สีฟ้า','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00692','title' => 'โมโนฟิลาเมนต์ แบบปากกา','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00700','title' => 'กระดาษผิวมัน (paper thermal roll) ขนาด 58 กรับ*57มม.*38มม.','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00710','title' => 'ชุดช่วยหายใจมือบีบ (Ambubag Silicone Adult)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00711','title' => 'Tube&Chamber Kit','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00712','title' => 'Mask with Nebulizer Child','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00714','title' => 'กระป๋อง Oxygen PVC','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00715','title' => 'Mask wiyh Bag Adult','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00717','title' => 'ขวด suction พร้อมฝาขนาด 1000 cc','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00718','title' => 'สาย Extensinon masimo','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00719','title' => 'กระโหลกราวกั้นพับซ้าย','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00720','title' => 'กระโหลกราวกั้นพับขวา','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00721','title' => 'แก๊สเอทธิลีนออกไซด์ 100% (บรรจุท่อ 4.8 กก.)','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00722','title' => 'ฝาครอบเครื่องI nfusion pump สำหรับล็อคเสาน้ำเกลือ','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00727','title' => 'น้ำยาไฮโดรเจนเพอร์ออกไซด์แก๊สพลาสมา','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00728','title' => 'TRIFLO II Incentive Deep Breathing Exerciser','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00729','title' => 'บอลลูนห้ามเลือด','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00732','title' => 'ผ้าสี่เหลี่ยม 2 ชั้น ขนาด 65x65','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00733','title' => 'ถุงผ้า ขนาด 50x50','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00734','title' => 'เสื้อกาวน์แขนยาวฝ่ายทันตกรรม','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00736','title' => 'ผ้าสี่เหลี่ยมเจาะกลาง 2 ชั้น ขนาด 18*18 เจาะกลาง 4*4','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00741','title' => 'สเก็ตบอร์ดมือ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00742','title' => 'ม้วนหมึก HALLO 2 HG','unit' => 'ลูก','qty' => '12.00','unit_price' => '150'],
            ['code' => '22-00743','title' => 'เสื้อผู้ป่วยเด็กเล็ก 1-3 ปี แบบผ่าข้างซ้าย ไม่มีแขน','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '22-00744','title' => 'เสื้อผู้ป่วยเด็กเล็ก 1-3 ปี แบบผ่าข้างซ้าย มีแขนในตัว','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '22-00745','title' => 'กางเกงผู้ป่วยเด็ก 1-3 ปี เอวรูด','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '22-00747','title' => 'Infant Resuscitator T-Piece Circuit','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00752','title' => 'ชุดสาย OMRON CUFF HOSES','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00756','title' => 'SpO2 srnsor probe for Zoll Rseries','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00771','title' => 'ซิลิโคนเสียบสายเครื่องเป่าแห้ง 52*34*7 mm หนา 9.5 mm','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '22-00772','title' => 'ท่อส่งลม (ฺBreathing Tube)','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00773','title' => 'กระป๋องน้ำ (Water Chamber)','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00774','title' => 'สายออกซิเจนแคนนูล่า (Oxygen Cannula)','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00775','title' => 'หน้ากากช่วยหายใจ (Full Face Mask) Size L','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00776','title' => 'หน้ากากช่วยหายใจ (Full Face Mask) Size M','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00779','title' => 'ซองกันน้ำลาย เบอร์ 4','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00781','title' => 'ข้อต่อสามทาง','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00782','title' => 'Disposable Swivel Elbow w/suction','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00784','title' => 'หมึกริบบอน 60 มม.X 300 ม.','unit' => 'ม้วน','qty' => '4.00','unit_price' => '320'],
            ['code' => '22-00785','title' => 'EKG 3 Lead wire for Patient monitor Mediana','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00786','title' => 'Reusable Finger Clip Sensor Rad-G','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00787','title' => 'Battery for Infusion pump Ampall 9.6 V.2000mAh','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '22-00788','title' => 'ชุดทดสอบยาฆ่าแมลง (OC-KIT)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00789','title' => 'ชุดทดสอบโคลิฟอร์มในอาหาร SI-2','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00790','title' => 'สายรัดตัวผู้ป่วยกับเตียง','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00791','title' => 'สายรัดข้อเท้าผู้ใหญ่','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00793','title' => 'กระดาษสำหรับเครื่อง EKG','unit' => 'พับ','qty' => '0','unit_price' => '0'],
            ['code' => '22-00794','title' => 'Spo2 sensor for patient monitor Drager Vista 120','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00795','title' => 'หมึกริบบอน 40 มม.X 300 ม.','unit' => 'ม้วน','qty' => '10.00','unit_price' => '350'],
            ['code' => '22-00796','title' => 'Safety trap for suction pipeline system','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00797','title' => 'อะไหล่สำหรับเครื่องอบฆ่าเชื้อด้วยแก๊สเอทธิลีนออกไซด์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00801','title' => 'อะไหล่เครื่อง infusion pump','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00802','title' => 'เครื่องวัดออกซิเจนในอากาศ','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '22-00807','title' => 'Battery Ni-MH 7.2V 4.0Ah','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '22-00808','title' => 'เครื่องวิเคราะห์ค่าออกซิเจนสำหรับเครื่องผลิตออกซิเจน','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '22-00809','title' => 'ใบเลื่อยตัดเฝือก 120-65','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '22-00814','title' => 'ชุดดึงเอว','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '22-00850','title' => 'Multisite Probe SpO2 (Masimo)','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00853','title' => 'อะไหล่เครื่องกระตุกหัวใจ','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '22-00860','title' => 'ตัวตรวจสอบประสิทธิภาพของเครื่องล้าง','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '22-00861','title' => 'ตัวตรวจสอบการฆ่าเชื้อทางเคมี สำหรับการฆ่าเชื้อแบบหม้อนึ่งไอน้ำ','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '22-00222','title' => 'รองเท้าหนังสำหรับผู้ป่วยเบาหวานเบอร์ 39','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '22-00223','title' => 'รองเท้าหนังสำหรับผู้ป่วยเบาหวานเบอร์ 40','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '22-00224','title' => 'รองเท้าหนังสำหรับผู้ป่วยเบาหวานเบอร์ 41','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '22-00251','title' => 'แอลกอฮอล์เจล 450 ml','unit' => 'ขวด','qty' => '0','unit_price' => '0'],
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
                // echo $value['code'] . "\n";
                // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                if (!$asetItem) {
                    $newItem = new Categorise([
                        'name' => 'asset_item',
                        'group_id' => 4,
                        'category_id' => 'M22',
                        'code' => $value['code'],
                        'title' => $value['title'],
                        'data_json' => [
                            'unit' => $value['unit'],
                            'sub_title' => '',
                            'price_name' => '',
                            'category_name' => 'วัสดุการแพทย์ทั่วไป',
                            'asset_type_name' => '',
                        ],
                    ]);
                    $newItem->save(false);
                }

                $qty = (int) explode('.', $value['qty'])[0];

                $category_id = 551;
                $code = 'IN-680012';
                $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                $model = new StockEvent([
                    'ref' => $ref,
                    'lot_number' => $lot,
                    'name' => 'order_item',
                    'code' => $code,
                    'category_id' => $category_id,
                    'transaction_type' => 'IN',
                    'asset_item' => $value['code'],
                    'warehouse_id' => 7,
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
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                } else {
                    echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                }
                $sum = $qty * (int) $value['unit_price'];
                $total += $sum;
            }
            echo $total;
        }
    }

        // วัสดุการแพทย์ออกซิเจน IN-680016
        public static function actionM26()
        {
            $data = [
                ['code' => '23-00001','title' => 'ออกซิเจนเหลวทางการแพทย์ (วส. ออกซิเจน)','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
                ['code' => '23-00002','title' => 'ออกซิเจน 0.5 คิว (วส. ออกซิเจน)','unit' => 'ท่อ','qty' => '1669.20','unit_price' => '0'],
                ['code' => '23-00003','title' => 'ออกซิเจน 2 คิว (วส. ออกซิเจน)','unit' => 'ท่อ','qty' => '898.80','unit_price' => '0'],
                ['code' => '23-00004','title' => 'ออกซิเจน 6 คิว (วส. ออกซิเจน)','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
                ['code' => '23-00005','title' => 'ไนตรัสออกไซด์ 20 กก. (วส. ออกซิเจน)','unit' => 'ท่อ','qty' => '8560.00','unit_price' => '0'],
                ['code' => '64-00001','title' => 'ท่อออกซิเจนอลูมิเนียม V30','unit' => 'ท่อ','qty' => '0','unit_price' => '0'],
                ['code' => '64-00002','title' => 'ออกซิเจน 5 คิว (วส. ออกซิเจน)','unit' => 'ท่อ','qty' => '171.20','unit_price' => '0'],
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
                    // echo $value['code'] . "\n";
                    // ถ้าไม่มีประวัสดุใฟ้สร้างมห่
                    if (!$asetItem) {
                        $newItem = new Categorise([
                            'name' => 'asset_item',
                            'group_id' => 4,
                            'category_id' => 'M26',
                            'code' => $value['code'],
                            'title' => $value['title'],
                            'data_json' => [
                                'unit' => $value['unit'],
                                'sub_title' => '',
                                'price_name' => '',
                                'category_name' => 'วัสดุการแพทย์ ออกซิเจน',
                                'asset_type_name' => '',
                            ],
                        ]);
                        $newItem->save(false);
                    }
    
                    $qty = (int) explode('.', $value['qty'])[0];
    
                    $category_id = 1230;
                    $code = 'IN-680016';
                    $lot = \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
                    $ref = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
                    $model = new StockEvent([
                        'ref' => $ref,
                        'lot_number' => $lot,
                        'name' => 'order_item',
                        'code' => $code,
                        'category_id' => $category_id,
                        'transaction_type' => 'IN',
                        'asset_item' => $value['code'],
                        'warehouse_id' => 7,
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
                        echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."สำเร็จ! \n";
                    } else {
                        echo 'นำเข้า '.$value['code'].' รหัส : '.$value['code']."ผิดพลาด! \n";
                    }
                    $sum = $qty * (int) $value['unit_price'];
                    $total += $sum;
                }
                echo $total;
            }
        }
}
