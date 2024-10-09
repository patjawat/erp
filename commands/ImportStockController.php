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
 */
class ImportStockController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
    }

    //นำเข้าวัสดุสำนักงาน
    public static function actionProduct1()
    {
        $data = [
            ['code' => '01-00001','title' => 'กระดาษถ่ายเอกสาร ขนาด A4','unit' => 'รีม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00002','title' => 'กระดาษถ่ายเอกสาร ขนาด A3','unit' => 'รีม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00003','title' => 'กระดาษถ่ายเอกสาร ขนาด F4','unit' => 'รีม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00004','title' => 'กระดาษถ่ายเอกสาร ขนาด A5','unit' => 'รีม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00005','title' => 'กระดาษเทอร์มอล 58','unit' => 'ม้วน','qty' => '30','unit_price' => '48.15000'],
            ['code' => '01-00006','title' => 'กระดาษโฟโต้ ขนาด A4','unit' => 'รีม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00007','title' => 'กระดาษการ์ดหอม','unit' => 'รีม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00009','title' => 'กระดาษคาร์บอน','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00010','title' => 'กระดาษทำปก สีเขียว','unit' => 'รีม','qty' => '27','unit_price' => '124.44444'],
            ['code' => '01-00011','title' => 'กระดาษทำปก สีชมพู','unit' => 'รีม','qty' => '32','unit_price' => '130'],
            ['code' => '01-00012','title' => 'กระดาษทำปก สีฟ้า','unit' => 'รีม','qty' => '20','unit_price' => '130'],
            ['code' => '01-00013','title' => 'กระดาษทำปก สีเหลือง','unit' => 'รีม','qty' => '22','unit_price' => '130'],
            ['code' => '01-00014','title' => 'กระดาษทำปก สีม่วง','unit' => 'รีม','qty' => '4','unit_price' => '130'],
            ['code' => '01-00015','title' => 'กระดาษทำปก สีส้ม','unit' => 'รีม','qty' => '3','unit_price' => '120'],
            ['code' => '01-00019','title' => 'กระดาษกาวย่น ขนาด 1 นิ้ว','unit' => 'ม้วน','qty' => '204','unit_price' => '39.75289'],
            ['code' => '01-00020','title' => 'กระดาษกาวย่น ขนาด 2 นิ้ว','unit' => 'ม้วน','qty' => '128','unit_price' => '75.00000'],
            ['code' => '01-00021','title' => 'กระดาษกาวสองหน้า แบบบาง','unit' => 'ม้วน','qty' => '21','unit_price' => '30'],
            ['code' => '01-00022','title' => 'กระดาษกาวสองหน้า แบบหนา','unit' => 'ม้วน','qty' => '5','unit_price' => '59.00000'],
            ['code' => '01-00023','title' => 'กระดาษกาวสองหน้า 3M','unit' => 'ม้วน','qty' => '7','unit_price' => '89.28571'],
            ['code' => '01-00024','title' => 'กระดาษห่อของขวัญ','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00025','title' => 'กาวช้าง','unit' => 'หลอด','qty' => '11','unit_price' => '25.00000'],
            ['code' => '01-00026','title' => 'กาวน้ำ','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00027','title' => 'กาวสติ๊ก','unit' => 'หลอด','qty' => '55','unit_price' => '17.20000'],
            ['code' => '01-00028','title' => 'กรรไกร','unit' => 'ด้าม','qty' => '19','unit_price' => '47.63158'],
            ['code' => '01-00029','title' => 'ครีมนับเงิน','unit' => 'ตลับ','qty' => '5','unit_price' => '35.00000'],
            ['code' => '01-00030','title' => 'ครีมล้างแห้ง','unit' => 'กระปุก','qty' => '5','unit_price' => '380'],
            ['code' => '01-00031','title' => 'คลิปบอร์ด A4','unit' => 'อัน','qty' => '26','unit_price' => '45.00000'],
            ['code' => '01-00032','title' => 'คลิปบอร์ด A5','unit' => 'ตัว','qty' => '8','unit_price' => '35.00000'],
            ['code' => '01-00033','title' => 'คลิปหนีบกระดาษ','unit' => 'โหล','qty' => '0','unit_price' => '0'],
            ['code' => '01-00034','title' => 'ลวดเสียบกระดาษ','unit' => 'กล่อง','qty' => '55','unit_price' => '19.27273'],
            ['code' => '01-00035','title' => 'คัตเตอร์เล็ก','unit' => 'ด้าม','qty' => '12','unit_price' => '35.00000'],
            ['code' => '01-00036','title' => 'คัตเตอร์ใหญ่','unit' => 'ด้าม','qty' => '13','unit_price' => '58.00000'],
            ['code' => '01-00037','title' => 'ใบมีดคัตเตอร์เล็ก','unit' => 'ห่อ','qty' => '25','unit_price' => '23.00000'],
            ['code' => '01-00038','title' => 'ใบมีดคัตเตอร์ใหญ่','unit' => 'ห่อ','qty' => '15','unit_price' => '38.00000'],
            ['code' => '01-00039','title' => 'เครื่องคิดเลข','unit' => 'เครื่อง','qty' => '3','unit_price' => '370'],
            ['code' => '01-00041','title' => 'เครื่องเย็บกระดาษ No.10','unit' => 'ตัว','qty' => '24','unit_price' => '99.00000'],
            ['code' => '01-00042','title' => 'เครื่องเย็บกระดาษ No.8','unit' => 'ตัว','qty' => '7','unit_price' => '333.42857'],
            ['code' => '01-00043','title' => 'ลวดเย็บกระดาษ No.10','unit' => 'ชิ้น','qty' => '86','unit_price' => '11.44174'],
            ['code' => '01-00044','title' => 'ลวดเย็บกระดาษ No.8','unit' => 'กล่อง','qty' => '16','unit_price' => '18.00000'],
            ['code' => '01-00045','title' => 'ลวดเย็บกระดาษ No.3','unit' => 'กล่อง','qty' => '18','unit_price' => '29.00000'],
            ['code' => '01-00046','title' => 'ลวดเย็บกระดาษ No.23/15','unit' => 'กล่อง','qty' => '12','unit_price' => '175.00000'],
            ['code' => '01-00047','title' => 'ลวดเย็บกระดาษ No.23/17','unit' => 'กล่อง','qty' => '11','unit_price' => '189.00000'],
            ['code' => '01-00048','title' => 'ลวดเย็บกระดาษ No.23/8','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00049','title' => 'สมุด No.2','unit' => 'เล่ม','qty' => '8','unit_price' => '50'],
            ['code' => '01-00051','title' => 'ไส้แฟ้ม 11 รู','unit' => 'ห่อ','qty' => '13','unit_price' => '20'],
            ['code' => '01-00052','title' => 'เหล็กยึดแฟ้ม','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00053','title' => 'หมึกเติมแท่นประทับ สีแดง','unit' => 'ขวด','qty' => '4','unit_price' => '12.00000'],
            ['code' => '01-00054','title' => 'หมึกเติมแท่นประทับ สีน้ำเงิน','unit' => 'ขวด','qty' => '6','unit_price' => '27.66667'],
            ['code' => '01-00056','title' => 'แท่นตัดเทปใส','unit' => 'อัน','qty' => '2','unit_price' => '98.00000'],
            ['code' => '01-00057','title' => 'แท่นประทับ สีแดง','unit' => 'อัน','qty' => '1','unit_price' => '32.66000'],
            ['code' => '01-00058','title' => 'แท่นประทับ สีน้ำเงิน','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00059','title' => 'แท่นประทับ สีดำ','unit' => 'อัน','qty' => '1','unit_price' => '38.00000'],
            ['code' => '01-00060','title' => 'ซองเอกสารน้ำตาล A4 ขยายข้าง','unit' => 'ซอง','qty' => '150','unit_price' => '5.00000'],
            ['code' => '01-00061','title' => 'ซองเอกสารน้ำตาล A4','unit' => 'ซอง','qty' => '50','unit_price' => '5.00000'],
            ['code' => '01-00062','title' => 'ซองพับ 4 สีขาว','unit' => 'ซอง','qty' => '1000','unit_price' => '0.80000'],
            ['code' => '01-00065','title' => 'ซองพับ 2 สีน้ำตาล','unit' => 'ซอง','qty' => '200','unit_price' => '4.00000'],
            ['code' => '01-00066','title' => 'ซองฟิล์ม x-ray ฟัน ขนาด 7*10 นิ้ว','unit' => 'ซอง','qty' => '500','unit_price' => '3.20000'],
            ['code' => '01-00067','title' => 'ดินน้ำมัน','unit' => 'ก้อน','qty' => '3','unit_price' => '23.00000'],
            ['code' => '01-00068','title' => 'ดินสอ','unit' => 'แท่ง','qty' => '109','unit_price' => '3.62688'],
            ['code' => '01-00070','title' => 'ยางลบ','unit' => 'ก้อน','qty' => '59','unit_price' => '6.00000'],
            ['code' => '01-00071','title' => 'ปากกาลบคำผิด','unit' => 'แท่ง','qty' => '24','unit_price' => '15.00000'],
            ['code' => '01-00072','title' => 'ปากกาเพ้นท์','unit' => 'แท่ง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00073','title' => 'ปากกามารค์เกอร์ 2 หัว สีน้ำเงิน','unit' => 'ด้าม','qty' => '41','unit_price' => '13.00000'],
            ['code' => '01-00074','title' => 'ปากกามาร์คเกอร์ 2 หัว สีแดง','unit' => 'แท่ง','qty' => '18','unit_price' => '13.00000'],
            ['code' => '01-00075','title' => 'ปากกามาร์คเกอร์ 2 หัว สีดำ','unit' => 'ด้าม','qty' => '49','unit_price' => '13.00000'],
            ['code' => '01-00076','title' => 'ปากกาไวท์บอร์ด สีน้ำเงิน','unit' => 'ด้าม','qty' => '10','unit_price' => '25.00000'],
            ['code' => '01-00077','title' => 'ปากกาไวท์บอร์ด สีดำ','unit' => 'ด้าม','qty' => '11','unit_price' => '25.00000'],
            ['code' => '01-00078','title' => 'ปากกาไวท์บอร์ด สีแดง','unit' => 'ด้าม','qty' => '10','unit_price' => '22.22800'],
            ['code' => '01-00079','title' => 'ธงชาติ ขนาด 90*135 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00080','title' => 'ธงประจำพระองค์ รัชกาลที่ 10','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00081','title' => 'เชือกขาวแดง','unit' => 'ม้วน','qty' => '4','unit_price' => '52.00000'],
            ['code' => '01-00082','title' => 'ตรายาง','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00083','title' => 'ตรายางวันที่','unit' => 'อัน','qty' => '3','unit_price' => '55.00000'],
            ['code' => '01-00084','title' => 'ที่ถอนลวดเย็บกระดาษ','unit' => 'อัน','qty' => '10','unit_price' => '68.00000'],
            ['code' => '01-00085','title' => 'เทปผ้ากาว ขนาด 2 นิ้ว','unit' => 'ม้วน','qty' => '4','unit_price' => '50'],
            ['code' => '01-00086','title' => 'เทปผ้ากาว ขนาด 1.5 นิ้ว','unit' => 'ม้วน','qty' => '7','unit_price' => '40'],
            ['code' => '01-00087','title' => 'เทปใสแกนใหญ่','unit' => 'ม้วน','qty' => '19','unit_price' => '20'],
            ['code' => '01-00089','title' => 'แผ่นเคลือบบัตร A4','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00090','title' => 'หมึกพิมพ์สำเนา เครื่องก๊อปปี้ปรินท์','unit' => 'หลอด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00091','title' => 'กระดาษพิมพ์สำเนา เครื่องก๊อปปี้ปรินท์','unit' => 'ม้วน','qty' => '1','unit_price' => '2600'],
            ['code' => '01-00092','title' => 'หมึกเครื่องถ่ายเอกสาร','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00094','title' => 'สติ๊กเกอร์ ต่อเนื่อง ขนาด 3*7 ซม.','unit' => 'ห่อ','qty' => '6','unit_price' => '1750'],
            ['code' => '01-00095','title' => 'สติ๊กเกอร์ ไดเร็คเทอร์มอล ขนาด 5*2 ซม.','unit' => 'ดวง','qty' => '58000','unit_price' => '0.15000'],
            ['code' => '01-00096','title' => 'หนังสือ คู่มือผู้ป่วยโรคความดันโลหิตสูง','unit' => 'เล่ม','qty' => '156','unit_price' => '50'],
            ['code' => '01-00097','title' => 'หนังสือ คู่มือผู้ป่วยโรคเบาหวาน','unit' => 'เล่ม','qty' => '310','unit_price' => '50'],
            ['code' => '01-00098','title' => 'ใบเสร็จรับเงิน (เล่มเขียว)','unit' => 'เล่ม','qty' => '60','unit_price' => '110'],
            ['code' => '01-00099','title' => 'แบบฟอร์ม ใบเสร็จรับเงินต่อเนื่อง','unit' => 'กล่อง','qty' => '57','unit_price' => '1043.85965'],
            ['code' => '01-00100','title' => 'แบบฟอร์ม ใบคำสั่งการรักษาของแพทย์','unit' => 'ห่อ','qty' => '11','unit_price' => '302.45000'],
            ['code' => '01-00101','title' => 'แบบฟอร์ม ใบรับรองแพทย์เพื่อการรักษา','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '01-00103','title' => 'แบบฟอร์ม แบบการเตรียมสารน้ำที่ให้ทางหลอดเลือดดำ','unit' => 'เล่ม','qty' => '159','unit_price' => '19.00000'],
            ['code' => '01-00104','title' => 'แบบฟอร์ม แบบบันทึกการตรวจอัลตร้าซาวน์','unit' => 'เล่ม','qty' => '80','unit_price' => '108.00000'],
            ['code' => '01-00105','title' => 'แบบฟอร์ม แบบบันทึกการให้ข้อมูลจำหน่าย','unit' => 'ห่อ','qty' => '8','unit_price' => '450'],
            ['code' => '01-00106','title' => 'แบบฟอร์ม แบบบันทึกทางการพยาบาล','unit' => 'ห่อ','qty' => '4','unit_price' => '450'],
            ['code' => '01-00107','title' => 'แบบฟอร์ม แบบบันทึกประวัติผู้ป่วย','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '01-00108','title' => 'แบบฟอร์ม Admission Note','unit' => 'ห่อ','qty' => '18','unit_price' => '250'],
            ['code' => '01-00109','title' => 'แบบฟอร์ม Anesthesia record','unit' => 'เล่ม','qty' => '120','unit_price' => '99.33333'],
            ['code' => '01-00111','title' => 'แบบฟอร์ม ปรอท','unit' => 'ห่อ','qty' => '11','unit_price' => '440'],
            ['code' => '01-00112','title' => 'แบบฟอร์ม ใบลงนามยินยอมรับการรักษา','unit' => 'ห่อ','qty' => '8','unit_price' => '350'],
            ['code' => '01-00113','title' => 'แบบฟอร์ม ใบส่งต่อผู้ป่วย (รพ)','unit' => 'เล่ม','qty' => '125','unit_price' => '78.40000'],
            ['code' => '01-00114','title' => 'แบบฟอร์ม ใบ ORDER','unit' => 'เล่ม','qty' => '196','unit_price' => '33.00000'],
            ['code' => '01-00117','title' => 'เครื่องเจาะกระดาษ (เล็ก)','unit' => 'ตัว','qty' => '1','unit_price' => '75.00000'],
            ['code' => '01-00118','title' => 'แบบฟอร์ม ใบรับรองแพทย์','unit' => 'ห่อ','qty' => '26','unit_price' => '37.54000'],
            ['code' => '01-00120','title' => 'แบบฟอร์ม บัตรคิวต่อเนื่อง 5*6 นิ้ว','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '01-00126','title' => 'โทรศัพท์ ธรรมดา','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '01-00127','title' => 'แฟ้มสอดไส้','unit' => 'แฟ้ม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00128','title' => 'ปากกาเน้นข้อความ','unit' => 'ด้าม','qty' => '29','unit_price' => '25.00000'],
            ['code' => '01-00130','title' => 'กระดาษเทอร์มอล 58 แกรมขาว','unit' => 'ม้วน','qty' => '690','unit_price' => '15.28913'],
            ['code' => '01-00136','title' => 'กระดาษทำปก สีแดง','unit' => 'รีม','qty' => '4','unit_price' => '130'],
            ['code' => '01-00138','title' => 'สายคล้องบัตร','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00140','title' => 'กระดาษสีน้ำตาล','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '01-00144','title' => 'เทปกาวใส OPP 2 นิ้ว','unit' => 'ม้วน','qty' => '8','unit_price' => '55.00000'],
            ['code' => '01-00145','title' => 'กรวยจราจร','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00149','title' => 'พานดอกไม้','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '01-00150','title' => 'พานเงิน - พานทอง','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '01-00160','title' => 'ไม้บรรทัด','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00161','title' => 'เคมีดับเพลิง ชนิดผงแห้ง ขนาด 15 ปอนด์','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00163','title' => 'เคมีดับเพลิง ชนิดไนโตรเจน ขนาด 10 ปอนด์','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00166','title' => 'เครื่องเย็บกระดาษ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '01-00167','title' => 'ลวดเย็บกระดาษ No.35-1','unit' => 'กล่อง','qty' => '25','unit_price' => '15.00000'],
            ['code' => '01-00169','title' => 'แฟ้มเสนอเซ็น','unit' => 'เล่ม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00170','title' => 'เทปกาวสองหน้า 3M','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00171','title' => 'แผ่นพลาสติกใส','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00175','title' => 'ธงประจำพระองค์ พระราชินี รัชกาลที่ 10','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00176','title' => 'ธงชาติ ขนาด 60*90 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00177','title' => 'พานพุ่ม เงิน-ทอง','unit' => 'คู่','qty' => '0','unit_price' => '0'],
            ['code' => '01-00180','title' => 'พู่กัน','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '01-00187','title' => 'ป้ายทางหนีไฟฉุกเฉิน','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00188','title' => 'ถ่าน ขนาด 3V','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00189','title' => 'เครื่องเหลาดินสอ','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00192','title' => 'โทรศัพท์ ไร้สาย','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00193','title' => 'ชั้นลิ้นชัก 3 ลิ้น','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00194','title' => 'ป้ายอะคริลิคติดผนัง','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00195','title' => 'ขอบขางตู้เก็บถังดับเพลิง','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '01-00197','title' => 'กบเหลาดินสอ','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00198','title' => 'แผ่นเคลือบบัตร','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00199','title' => 'พลาสติกลูกฟูก (ฟิวเจอร์บอร์ด)','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00200','title' => 'สมุดบันทึกสุขภาพผู้ป่วยคลีนิกโรคปอดอุดกลั้นเรื้อรัง','unit' => 'เล่ม','qty' => '300','unit_price' => '26.00000'],
            ['code' => '01-00201','title' => 'ปากกาลงนาม','unit' => 'ด้าม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00202','title' => 'เทปตีเส้น (สีแดง)','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00203','title' => 'โอเอซิส','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00204','title' => 'ปกพลาสติกใส','unit' => 'ห่อ','qty' => '0','unit_price' => '0'],
            ['code' => '01-00205','title' => 'สายคล้องบัตร (สีน้ำเงิน)','unit' => 'เส้น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00207','title' => 'หนังสือ คู่มือผู้ป่วยโรคไตเสื่อมเรื้อรัง','unit' => 'เล่ม','qty' => '600','unit_price' => '26.00000'],
            ['code' => '01-00208','title' => 'พัดลมเพดาน','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '01-00209','title' => 'สติ๊กเกอร์ 4.5*1.5 cm.','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00210','title' => 'สติ๊กเกอร์ใสหลังเหลือง','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00211','title' => 'ป้ายจราจร','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00212','title' => 'สติ๊กเกอร์ PVC','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00213','title' => 'เครื่องยิงสติ๊กเกอร์ 2HG','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00214','title' => 'สติ๊กเกอร์สะท้อนแสง','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00216','title' => 'แปรงลบกระดาน','unit' => 'อัน','qty' => '1','unit_price' => '15.00000'],
            ['code' => '01-00220','title' => 'ซองพลาสติกใส่เอกสาร','unit' => 'ซอง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00221','title' => 'แฟ้ม 2 นิ้ว','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00222','title' => 'ธงชาติ ขนาด 150*225 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00223','title' => 'แท่นตัดกระดาษ','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '01-00225','title' => 'ชุดเครื่องทองน้อย','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00226','title' => 'ผ้าลูกไม้','unit' => 'เมตร','qty' => '0','unit_price' => '0'],
            ['code' => '01-00227','title' => 'ลวดเย็บกระดาษ No.T3-10MB','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00228','title' => 'แฟ้มหนีบ','unit' => 'แฟ้ม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00229','title' => 'แผงกั้นจราจรบรรจุน้ำ','unit' => 'ชิ้น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00230','title' => 'รางลิ้นชักรับใต้','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00231','title' => 'พัดลมปีกเพดาน ขนาด 48 นิ้ว','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00232','title' => 'ริบบิ้นห่อเหรียญ','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00233','title' => 'ดอกริบบิ้นมัด','unit' => 'ดอก','qty' => '0','unit_price' => '0'],
            ['code' => '01-00234','title' => 'หนังสือ คู่มือการประเมินและส่งเสริมพัฒนาการเด็กแรกเกิด-5 ปี','unit' => 'เล่ม','qty' => '0','unit_price' => '0'],
            ['code' => '01-00239','title' => 'ป้ายบ่งชี้','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00242','title' => 'สันรูด','unit' => 'อัน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00243','title' => 'โทรศัพท์ภายในไร้สาย','unit' => '','qty' => '0','unit_price' => '0'],
            ['code' => '01-00244','title' => 'นาฬิกาแขวนผนัง','unit' => 'เรือน','qty' => '2','unit_price' => '1300'],
            ['code' => '01-00245','title' => 'เทปกั้นเขต ขาวแดง','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00246','title' => 'ธงชาติ ขนาด 120x180 นิ้ว','unit' => 'ผืน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00247','title' => 'สติกเกอร์สีเทา แผ่นใหญ่','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00248','title' => 'ธูปไฟฟ้า','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00249','title' => 'เทียนไฟฟ้า','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00250','title' => 'เก้าอี้พลาสติก','unit' => 'ตัว','qty' => '0','unit_price' => '0'],
            ['code' => '01-00259','title' => 'ชุดลูกยางเครื่องสแกนเนอร์','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00260','title' => 'สติกเกอร์สี','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00261','title' => 'เครื่องยิงบอร์ด','unit' => 'เครื่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00263','title' => 'เข็มหมุดหัวกลม','unit' => 'กล่อง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00265','title' => 'ใบ บันทึกข้อความ','unit' => 'เล่ม','qty' => '50','unit_price' => '61.00000'],
            ['code' => '01-00267','title' => 'แผ่นยาง EVA rubber รองกันกระแทก','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00268','title' => 'ถ่านแบตเตอรี่ ลิเธียม ขนาด 3 V','unit' => 'ก้อน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00277','title' => 'กล่องกระดาษ ขนาด M+','unit' => 'ใบ','qty' => '0','unit_price' => '0'],
            ['code' => '01-00278','title' => 'สติ๊กเกอร์ตัวเลข','unit' => 'ชิ้้น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00279','title' => 'พานดอกไม้','unit' => 'ชุด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00280','title' => 'ลวดขด','unit' => 'มัด','qty' => '0','unit_price' => '0'],
            ['code' => '01-00281','title' => 'ภาพพระฉายาลักษณ์ ร.10','unit' => 'รายการ','qty' => '0','unit_price' => '0'],
            ['code' => '01-00282','title' => 'พาเลทพลาสติก','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00283','title' => 'เทปกันลื่น','unit' => 'ม้วน','qty' => '0','unit_price' => '0'],
            ['code' => '01-00284','title' => 'แผ่นสติ๊กเกอร์กันลื่น','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00285','title' => 'เหล็กเสียบกระดาษ','unit' => 'อัน','qty' => '2','unit_price' => '35.00000'],
            ['code' => '01-00286','title' => 'สติ๊กเกอร์ ขยะติดเชื้อ ขนาด A4','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00287','title' => 'สติ๊กเกอร์ ขยะติดเชื้อ ขนาด 11x17 ซม','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00288','title' => 'สติ๊กเกอร์ ถังดับเพลิง ขนาด A4','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00289','title' => 'พลาสติกการ์ด','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00290','title' => 'เครื่องดับเพลิงชนิดน้ำยาเหลวระเหย','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00296','title' => 'เคมีดับเพลิง ชนิดไนโตรเจน ขนาด 5 ปอนด์','unit' => 'ถัง','qty' => '0','unit_price' => '0'],
            ['code' => '01-00299','title' => 'สติ๊กเกอร์ ขั้นตอนการล้างมือ','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
            ['code' => '01-00300','title' => 'สติ๊กเกอร์ การทำความสะอาดพื้นผิวที่เปื้อนสิ่งคัดหลั่ง','unit' => 'แผ่น','qty' => '0','unit_price' => '0'],
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
                $lot =  \mdm\autonumber\AutoNumber::generate('LOT'.substr(AppHelper::YearBudget(), 2).'-?????');
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
                    'unit_price' => (double)$value['unit_price'],
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
}
