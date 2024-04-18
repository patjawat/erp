<?php

use yii\db\Migration;
use yii\helpers\Json;

/**
 * Handles the creation of table `{{%categorise}}`.
 */
class m230720_072431_create_categorise_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%categorise}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'category_id' => $this->string(),
            'code' => $this->string()->comment('รหัส'),
            'emp_id' => $this->string()->comment('พนักงาน'),
            'name' => $this->string()->notNull()->comment('ชนิดข้อมูล'),
            'title' => $this->string()->comment('ชื่อ'),
            'description' => $this->string()->comment('รายละเอียดเพิ่มเติม'),
            'data_json' => $this->json(),
            'active' => $this->boolean()->defaultValue(true),
        ]);

        $this->insert('categorise', ['name' => 'site', 'title' => 'ตั้งค่าระบบ', 'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10)]);

        //ระดับ
        $this->insert('categorise', ['code' => 1, 'name' => 'position_level', 'title' => 'ปฏิบัติการ']);
        $this->insert('categorise', ['code' => 2, 'name' => 'position_level', 'title' => 'ชำนาญการ']);
        $this->insert('categorise', ['code' => 3, 'name' => 'position_level', 'title' => 'ชำนาญการพิเศษ']);
        $this->insert('categorise', ['code' => 4, 'name' => 'position_level', 'title' => 'ปฏิบัติงาน']);
        $this->insert('categorise', ['code' => 5, 'name' => 'position_level', 'title' => 'ชำนาญงาน']);

        //  ประเภทบุคลากร
        $this->insert('categorise', ['name' => 'position_type', 'code' => 'PT1', 'title' => 'ข้าราชการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_type', 'code' => 'PT2', 'title' => 'พนักงานราชการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_type', 'code' => 'PT3', 'title' => 'พนักงานกระทรวง (พกส.)', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_type', 'code' => 'PT4', 'title' => 'ลูกจ้างชั่วคราวรายเดือน', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_type', 'code' => 'PT5', 'title' => 'ลูกจ้างชั่วคราวรายวัน', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_type', 'code' => 'PT6', 'title' => 'ลูกจ้างประจำ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_type', 'code' => 'PT7', 'title' => 'จ้างเหมาบริการรายวัน', 'active' => 1]);

        // กลุ่มงาน
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PG1', 'title' => 'บริหาร', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PG2', 'title' => 'อำนวยการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PG3', 'title' => 'วิชาการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PG4', 'title' => 'ทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PG5', 'title' => 'บริการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PG6', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PG7', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PG8', 'title' => 'วิชาชีพเฉพาะ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PG9', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PG10', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PG11', 'title' => 'วิชาชีพเฉพาะ ค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PG12', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PG13', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PG14', 'title' => 'บริการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PG15', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PG16', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PG17', 'title' => 'วิชาชีพเฉพาะ ค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PG18', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PG19', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PG20', 'title' => 'บริการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PG21', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PG22', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PG23', 'title' => 'วิชาชีพเฉพาะ ค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PG24', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PG25', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PG26', 'title' => 'บริการ', 'active' => 1]);
    // PT6 กลุ่มลูกจ้างประจำ 
    $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT6', 'code' => 'PG27', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
    $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT6', 'code' => 'PG28', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
    $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT6', 'code' => 'PG29', 'title' => 'บริการ', 'active' => 1]);
    // PT7 จ้างเหมาบริการรายวัน
    $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT7', 'code' => 'PG230', 'title' => 'บริการ', 'active' => 1]);
    
        //ตำแหน่งข้าราชการ
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG1', 'code' => 'PG1PN1', 'title' => 'นักบริหาร', 'data_json' => ['code' => '1-1-2001', 'title_name' => 'บริหาร', 'level' => 'ต้น - สูง'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG1', 'code' => 'PG1PN2', 'title' => 'ผู้ตรวจราชการกระทรวง', 'data_json' => ['code' => '1-1-2004', 'title_name' => 'ตรวจราชการกระทรวง', 'level' => 'สูง'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG2', 'code' => 'PG2PN3', 'title' => 'ผู้อำนวยการ', 'data_json' => ['code' => '2-1-2001', 'title_name' => 'อำนวยการ', 'level' => 'ต้น - สูง'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG2', 'code' => 'PG2PN4', 'title' => 'ผู้อำนวยการเฉพาะด้าน (ระบุชื่อสายงาน)', 'data_json' => ['code' => '2-1-2002', 'title_name' => 'อำนวยการเฉพาะด้าน', 'level' => 'ต้น - สูง'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN5', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['code' => '3-1-2004', 'title_name' => 'จัดการงานทั่วไป', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN6', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['code' => '3-1-2006', 'title_name' => 'ทรัพยากรบุคคล', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN7', 'title' => 'นิติกร', 'data_json' => ['code' => '3-1-2008', 'title_name' => 'นิติการ', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN8', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['code' => '3-1-2012', 'title_name' => 'นักวิเคราะห์นโยบายและแผน', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN9', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['code' => '3-1-2013', 'title_name' => 'วิชาการคอมพิวเตอร์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN10', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['code' => '3-1-2015', 'title_name' => 'วิชาการเทคโนโลยีสารสนเทศ', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN11', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['code' => '3-1-2016', 'title_name' => 'วิชาการพัสดุ', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN12', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['code' => '3-1-2019', 'title_name' => 'วิชาการสถิติ', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN13', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['code' => '3-1-2021', 'title_name' => 'วิเทศสัมพันธ์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN14', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['code' => '3-2-2006', 'title_name' => 'วิชาการเงินและบัญชี', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN15', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['code' => '3-2-2009', 'title_name' => 'วิชาการตรวจสอบภายใน', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN16', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['code' => '3-3-2005', 'title_name' => 'ประชาสัมพันธ์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN17', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['code' => '3-3-2007', 'title_name' => 'วิชาการเผยแพร่', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN18', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['code' => '3-3-2008', 'title_name' => 'วิชาการโสตทัศนศึกษา', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN19', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['code' => '3-6-2001', 'title_name' => 'กายภาพบำบัด', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN20', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['code' => '3-6-2002', 'title_name' => 'กิจกรรมบำบัด', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN21', 'title' => 'นักจิตวิทยา', 'data_json' => ['code' => '3-6-2003', 'title_name' => 'จิตวิทยา', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN22', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['code' => '3-6-2004', 'title_name' => 'จิตวิทยาคลีนิก', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN23', 'title' => 'ทันตแพทย์', 'data_json' => ['code' => '3-6-2005', 'title_name' => 'ทันตแพทย์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN24', 'title' => 'นักเทคนิคการแพทย์', 'data_json' => ['code' => '3-6-2006', 'title_name' => 'เทคนิคการแพทย์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN25', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['code' => '3-6-2008', 'title_name' => 'พยาบาลวิชาชีพ', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN26', 'title' => 'นายแพทย์', 'data_json' => ['code' => '3-6-2009', 'title_name' => 'แพทย์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN27', 'title' => 'แพทย์แผนไทย', 'data_json' => ['code' => '3-6-2010', 'title_name' => 'แพทย์แผนไทย', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN28', 'title' => 'เภสัชกร', 'data_json' => ['code' => '3-6-2011', 'title_name' => 'เภสัชกรรม', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN29', 'title' => 'นักโภชนาการ', 'data_json' => ['code' => '3-6-2012', 'title_name' => 'โภชนาการ', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN30', 'title' => 'นักรังสีการแพทย์', 'data_json' => ['code' => '3-6-2013', 'title_name' => 'รังสีการแพทย์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN31', 'title' => 'นักวิชาการพยาบาล', 'data_json' => ['code' => '3-6-2014', 'title_name' => 'วิชาการพยาบาล', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN32', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['code' => '3-6-2015', 'title_name' => 'วิชาการสาธารณสุข', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN33', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['code' => '3-6-2017', 'title_name' => 'วิชาการอาหารและยา', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN34', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '3-6-2018', 'title_name' => 'วิทยาศาสตร์การแพทย์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN35', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['code' => '3-6-2019', 'title_name' => 'เวชศาสตร์การสื่อความหมาย', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN36', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['code' => '3-6-2020', 'title_name' => 'เทคโนโลยีหัวใจและทรวงอก', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN37', 'title' => 'นักสาธารณสุข', 'data_json' => ['code' => '3-6-2022', 'title_name' => 'สาธารณสุข', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN38', 'title' => 'นักกายอุปกรณ์', 'data_json' => ['code' => '3-7-2001', 'title_name' => 'กายอุปกรณ์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN39', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['code' => '3-7-2004', 'title_name' => 'ช่างภาพการแพทย์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN40', 'title' => 'บรรณารักษ์', 'data_json' => ['code' => '3-8-2003', 'title_name' => 'บรรณารักษ์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN41', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['code' => '3-8-2021', 'title_name' => 'วิชาการศึกษา', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN42', 'title' => 'วิทยาจารย์', 'data_json' => ['code' => '3-8-2025', 'title_name' => 'วิทยาจารย์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG3', 'code' => 'PG3PN43', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['code' => '3-8-2026', 'title_name' => 'สังคมสงเคราะห์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN44', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['code' => '4-1-2001', 'title_name' => 'ปฏิบัติงานธุรการ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN45', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['code' => '4-1-2002', 'title_name' => 'ปฏิบัติงานพัสดุ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN46', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['code' => '4-1-2004', 'title_name' => 'เจ้าพนักงานเวชสถิติ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN47', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['code' => '4-1-2005', 'title_name' => 'ปฏิบัติงานสถิติ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN48', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['code' => '4-2-2002', 'title_name' => 'ปฏิบัติงานการเงินและบัญชี', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN49', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์', 'data_json' => ['code' => '4-3-2004', 'title_name' => 'ปฏิบัติงานเผยแพร่ประชาสัมพันธ์', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN50', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['code' => '4-3-2007', 'title_name' => 'ปฏิบัติงานโสตทัศนศึกษา', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN51', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['code' => '4-6-2001', 'title_name' => 'ปฏิบัติงานทันตสาธารณสุข', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN52', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['code' => '4-6-2002', 'title_name' => 'ปฏิบัติงานเภสัชกรรม', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN53', 'title' => 'โภชนากร', 'data_json' => ['code' => '4-6-2003', 'title_name' => 'โภชนาการ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN54', 'title' => 'เจ้าพนักงานรังสีการแพทย์', 'data_json' => ['code' => '4-6-2004', 'title_name' => 'ปฏิบัติงานรังสีการแพทย์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN55', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '4-6-2005', 'title_name' => 'ปฏิบัติงานวิทยาศาสตร์การแพทย์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN56', 'title' => 'เจ้าพนักงานเวชกรรมฟื้นฟู', 'data_json' => ['code' => '4-6-2006', 'title_name' => 'ปฏิบัติงานเวชกรรมฟื้นฟู', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN57', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['code' => '4-6-2007', 'title_name' => 'ปฏิบัติงานสาธารณสุข', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN58', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['code' => '4-6-2008', 'title_name' => 'ปฏิบัติงานอาชีวบำบัด', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN59', 'title' => 'พยาบาลเทคนิค', 'data_json' => ['code' => '4-6-2009', 'title_name' => 'พยาบาลเทคนิค', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN60', 'title' => 'นายช่างศิลป์', 'data_json' => ['code' => '4-7-2003', 'title_name' => 'ปฏิบัติงานช่างศิลป์', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN61', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['code' => '4-7-2006', 'title_name' => 'ปฏิบัติงานกายอุปกรณ์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN62', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['code' => '4-7-2007', 'title_name' => 'ปฏิบัติงานเครื่องคอมพิวเตอร์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN63', 'title' => 'ช่างทันตกรรม', 'data_json' => ['code' => '4-7-2012', 'title_name' => 'ปฏิบัติงานช่างทันตกรรม', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN64', 'title' => 'นายช่างเทคนิค', 'data_json' => ['code' => '4-7-2013', 'title_name' => 'ปฏิบัติงานช่างเทคนิค', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN65', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['code' => '4-7-2014', 'title_name' => 'ปฏิบัติงานช่างไฟฟ้า', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN66', 'title' => 'นายช่างโยธา', 'data_json' => ['code' => '4-7-2016', 'title_name' => 'ปฏิบัติงานโยธา', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG4', 'code' => 'PG4PN67', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['code' => '4-8-2015', 'title_name' => 'ปฏิบัติงานห้องสมุด', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);
        // กำหนดตำแหน่งพนักงานราชการ
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN1', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['title_name' => 'ปฏิบัติงานช่วยเหลือคนไข้'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN2', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['title_name' => 'ผู้ช่วยพยาบาล'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN3', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['title_name' => 'ผู้ช่วยทันตแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN4', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['title_name' => 'ปฏิบัติงานธุรการ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN5', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['title_name' => 'ปฏิบัติงานพัสดุ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN6', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['title_name' => 'ปฏิบัติงานสถิติ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN7', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['title_name' => 'ปฏิบัติงานการเงินและบัญชี'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN8', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์', 'data_json' => ['title_name' => 'ปฏิบัติงานเผยแพร่ประชาสัมพันธ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG5', 'code' => 'PG5PN9', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['title_name' => 'ปฏิบัติงานห้องสมุด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN10', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['title_name' => 'ปฏิบัติงานโสตทัศนศึกษา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN11', 'title' => 'นายช่างเทคนิค', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างเทคนิค'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN12', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['title_name' => 'ปฏิบัติงานเครื่องคอมพิวเตอร์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN13', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างไฟฟ้า'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN14', 'title' => 'นายช่างโยธา', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างโยธา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN15', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['title_name' => 'ปฏิบัติงานอาชีวบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN16', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['title_name' => 'ปฏิบัติงานเวชสถิติ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN17', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['title_name' => 'ปฏิบัติงานทันตสาธารณสุข'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN18', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['title_name' => 'ปฏิบัติงานเภสัชกรรม'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN19', 'title' => 'โภชนากร', 'data_json' => ['title_name' => 'ปฏิบัติงานโภชนากร'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN20', 'title' => 'เจ้าพนักงานรังสีการแพทย์', 'data_json' => ['title_name' => 'ปฏิบัติงานรังสีการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN21', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['title_name' => 'ปฏิบัติงานวิทยาศาสตร์การแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN22', 'title' => 'เจ้าพนักงานเวชกรรมฟื้นฟู', 'data_json' => ['title_name' => 'ปฏิบัติงานเวชกรรมฟื้นฟู'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN23', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['title_name' => 'ปฏิบัติงานสาธารณสุข'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN24', 'title' => 'นายช่างศิลป์', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างศิลป์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN25', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างกายอุปกรณ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN26', 'title' => 'ช่างทันตกรรม', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างทันตกรรม'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN27', 'title' => 'พยาบาลเทคนิค', 'data_json' => ['title_name' => 'ปฏิบัติงานพยาบาลเทคนิค'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN28', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['title_name' => 'จัดการงานทั่วไป'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN29', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['title_name' => 'ทรัพยากรบุคคล'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN30', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['title_name' => 'ประชาสัมพันธ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN31', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['title_name' => 'วิชาการเผยแพร่'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN32', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['title_name' => 'วิเคราะห์นโยบายและแผน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN33', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['title_name' => 'วิชาการพัสดุ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN34', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['title_name' => 'วิชาการศึกษา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN35', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['title_name' => 'วิชาการตรวจสอบภายใน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN36', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['title_name' => 'วิทยาศาสตร์การแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN37', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['title_name' => 'สังคมสงเคราะห์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN38', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['title_name' => 'อาชีวบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN39', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['title_name' => 'วิชาการสาธารณสุข'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN40', 'title' => 'นักโภชนาการ', 'data_json' => ['title_name' => 'โภชนาการ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN41', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['title_name' => 'วิชาการเงินและบัญชี'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN42', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['title_name' => 'วิชาการโสตทัศนศึกษา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN43', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['title_name' => 'ช่างภาพการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN44', 'title' => 'บรรณารักษ์', 'data_json' => ['title_name' => 'บรรณารักษ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN45', 'title' => 'นิติกร', 'data_json' => ['title_name' => 'นิติการ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN46', 'title' => 'วิชาการสถิติ', 'data_json' => ['title_name' => 'วิชาการสถิติ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN47', 'title' => 'นักจิตวิทยา', 'data_json' => ['title_name' => 'จิตวิทยา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN48', 'title' => 'เศรษฐกร', 'data_json' => ['title_name' => 'เศรษฐกร'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG7', 'code' => 'PG7PN49', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['title_name' => 'วิเทศสัมพันธ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN50', 'title' => 'นักกายอุปกรณ์', 'data_json' => ['title_name' => 'กายอุปกรณ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN51', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['title_name' => 'กายภาพบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN52', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['title_name' => 'กิจกรรมบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN53', 'title' => 'แพทย์แผนไทย', 'data_json' => ['title_name' => 'การแพทย์แผนไทย'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN54', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['title_name' => 'จิตวิทยาคลินิก'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN55', 'title' => 'นักเทคนิคการแพทย์', 'data_json' => ['title_name' => 'เทคนิคการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN56', 'title' => 'นักรังสีการแพทย์', 'data_json' => ['title_name' => 'รังสีการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN57', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['title_name' => 'วิชาการคอมพิวเตอร์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN58', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['title_name' => 'เวชศาสตร์การสื่อความหมาย'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN59', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['title_name' => 'พยาบาลวิชาชีพ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN60', 'title' => 'เภสัชกร', 'data_json' => ['title_name' => 'เภสัชกรรม'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN61', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['title_name' => 'เทคโนโลยีหัวใจและทรวงอก'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN62', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['title_name' => 'วิศวกรไฟฟ้า'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN63', 'title' => 'วิศวกรโยธา', 'data_json' => ['title_name' => 'วิศวกรโยธา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG8', 'code' => 'PG8PN64', 'title' => 'วิศวกรเครื่องกล', 'data_json' => ['title_name' => 'วิศวกรเครื่องกล'], 'active' => 1]);

        // กำหนดตำแหน่งพนักงานกระทรวงสาธารณสุข
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN1', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['code' => '3-6-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN2', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['code' => '3-6-2002', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN3', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['code' => '3-6-2004', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN4', 'title' => 'ทันตแพทย์', 'data_json' => ['code' => '3-6-2005', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN5', 'title' => 'นักเทคนิคการแพทย', 'data_json' => ['code' => '3-6-2006', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN6', 'title' => 'นายสัตวแพทย', 'data_json' => ['code' => '3-6-2007', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN7', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['code' => '3-6-2008', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN8', 'title' => 'นายแพทย์', 'data_json' => ['code' => '3-6-2009', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN9', 'title' => 'แพทย์แผนไทย', 'data_json' => ['code' => '3-6-2010', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN10', 'title' => 'เภสัชกร', 'data_json' => ['code' => '3-6-2011', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN11', 'title' => 'นักรังสีการแพทย', 'data_json' => ['code' => '3-6-2013', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN12', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['code' => '3-6-2019', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN13', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['code' => '3-6-2020', 'note' => 'นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN14', 'title' => 'นักฟิสิกส์การแพทย', 'data_json' => ['code' => '3-6-2021', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN15', 'title' => 'นักทัศนมาตร', 'data_json' => ['code' => '3-6-2022', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN16', 'title' => 'นักกายอุปกรณ', 'data_json' => ['code' => '3-7-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN17', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['code' => '3-7-2020', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN18', 'title' => 'นักวิชาการศึกษาพิเศษ', 'data_json' => ['code' => '3-8-2022', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN19', 'title' => 'นักฟิสิกส์รังสี', 'data_json' => ['code' => '3-5-2007', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN20', 'title' => 'นักวิทยาศาสตร์', 'data_json' => ['code' => '3-5-2010', 'note' => 'นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN21', 'title' => 'นักจิตวิทยา', 'data_json' => ['code' => '3-6-2003', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN22', 'title' => 'นักโภชนาการ', 'data_json' => ['code' => '3-6-2012', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN23', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['code' => '3-6-2015', 'note' => 'นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN24', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['code' => '3-6-2016', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN25', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['code' => '3-6-2017', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN26', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '3-6-2018', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN27', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['code' => '3-7-2004', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN28', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['code' => '3-8-2026', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG11', 'code' => 'PG11PN29', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['code' => '3-1-2013', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN30', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['code' => '3-1-2004', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN31', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['code' => '3-1-2006', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN32', 'title' => 'นิติกร', 'data_json' => ['code' => '3-1-2008', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN33', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['code' => '3-1-2012', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN34', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['code' => '3-1-2015', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN35', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['code' => '3-1-2016', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN36', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['code' => '3-1-2019', 'note' => 'นักวิชาการสถิติ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN37', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['code' => '3-1-2021', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN38', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['code' => '3-2-2006', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN39', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['code' => '3-2-2009', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN40', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['code' => '3-3-2005', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN41', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['code' => '3-3-2007', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN42', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['code' => '3-3-2008', 'note' => 'นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN43', 'title' => 'นักวิชาการเกษตร', 'data_json' => ['code' => '3-4-2001', 'note' => 'นักวิชาการเกษตร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN44', 'title' => 'วิศวกร', 'data_json' => ['code' => '3-7-2015', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN45', 'title' => 'บรรณารักษ์', 'data_json' => ['code' => '3-8-2003', 'note' => 'บรรณารักษ์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN46', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['code' => '3-8-2021', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG12', 'code' => 'PG12PN47', 'title' => 'วิทยาจารย์', 'data_json' => ['code' => '3-8-2025', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN48', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['code' => '4-1-2001', 'note' => 'เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN49', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['code' => '4-1-2002', 'note' => 'เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN50', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['code' => '4-1-2004', 'note' => 'เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN51', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['code' => '4-1-2005', 'note' => 'เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN52', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['code' => '4-2-2002', 'note' => 'เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN53', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', 'data_json' => ['code' => '4-3-2004', 'note' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN54', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['code' => '4-3-2007', 'note' => 'เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN55', 'title' => 'เจ้าพนักงานการเกษตร', 'data_json' => ['code' => '4-4-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN56', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['code' => '4-6-2001', 'note' => 'เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN57', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['code' => '4-6-2002', 'note' => 'เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN58', 'title' => 'โภชนากร', 'data_json' => ['code' => '4-6-2003', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN59', 'title' => 'เจ้าพนักงานรังสีการแพทย', 'data_json' => ['code' => '4-6-2004', 'note' => 'เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN60', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '4-6-2005', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN61', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['code' => '4-6-2007', 'note' => 'เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN62', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['code' => '4-6-2008', 'note' => 'เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN63', 'title' => 'เจ้าพนักงานเวชกิจฉุกเฉิน', 'data_json' => ['code' => '4-6-2011', 'note' => 'เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN64', 'title' => 'เจ้าพนักงานการแพทย์แผนไทย', 'data_json' => ['code' => '4-6-2012', 'note' => 'เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN65', 'title' => 'นายช่างศิลป์', 'data_json' => ['code' => '4-7-2003', 'note' => 'นายช่างศิลป์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN66', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['code' => '4-7-2006', 'note' => 'ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN67', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['code' => '4-7-2007', 'note' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN68', 'title' => 'ช่างทันตกรรม', 'data_json' => ['code' => '4-7-2012', 'note' => 'ช่างทันตกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN69', 'title' => 'นายช่างเทคนิค', 'data_json' => ['code' => '4-7-2013', 'note' => 'นายช่างเทคนิค เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN70', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['code' => '4-7-2014', 'note' => 'นายช่างไฟฟ้า เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN71', 'title' => 'นายช่างโยธา', 'data_json' => ['code' => '4-7-2016', 'note' => 'นายช่างโยธา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN72', 'title' => 'ครูการศึกษาพิเศษ', 'data_json' => ['code' => '4-8-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG13', 'code' => 'PG13PN73', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['code' => '7-8-2015', 'note' => 'เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN74', 'title' => 'พนักงานประจำตึก', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN75', 'title' => 'พนักงานเปล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN76', 'title' => 'พนักงานซักฟอก', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN77', 'title' => 'พนักงานบริการ', 'data_json' => ['code' => '', 'note' => 'พนักงานบริการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN78', 'title' => 'พนักงานรับโทรศัพท์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN79', 'title' => 'พนักงานเกษตรพื้นฐาน', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN80', 'title' => 'พนักงานเรือยนต', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN81', 'title' => 'พนักงานบริการเอกสารทั่วไป', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN82', 'title' => 'พนักงานเก็บเอกสาร', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN83', 'title' => 'พนักงานบริการสื่ออุปกรณ์การสอน', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN84', 'title' => 'พนักงานเก็บเงิน', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN85', 'title' => 'พนักงานโสตทัศนศึกษา', 'data_json' => ['code' => '', 'note' => 'พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN86', 'title' => 'พนักงานผลิตน้ำประปา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN87', 'title' => 'พนักงานการเงินและบัญชี', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN88', 'title' => 'พนักงานพัสดุ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN89', 'title' => 'พนักงานธุรการ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN90', 'title' => 'พนักงานพิมพ์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN91', 'title' => 'พนักงานประเมินผล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN92', 'title' => 'พนักงานการศึกษา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN93', 'title' => 'พนักงานห้องสมุด', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN94', 'title' => 'พนักงานสื่อสาร', 'data_json' => ['code' => '', 'note' => 'พนักงานสื่อสาร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN95', 'title' => 'ล่ามภาษาต่างประเทศ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN96', 'title' => 'ครูพี่เลี้ยง', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN97', 'title' => 'พี่เลี้ยง', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN98', 'title' => 'พนักงานช่วยการพยาบาล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN99', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['code' => '', 'note' => 'พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN100', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN101', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['code' => '', 'note' => 'ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN102', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN103', 'title' => 'พนักงานประจำห้องยา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN104', 'title' => 'ผู้ช่วยพนักงานสุขศึกษา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN105', 'title' => 'ผู้ช่วยเจ้าหน้าที่อนามัย', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN106', 'title' => 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN107', 'title' => 'พนักงานการแพทย์และรังสีเทคนิค', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN108', 'title' => 'พนักงานจุลทัศนกร', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN109', 'title' => 'พนักงานประกอบอาหาร', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN110', 'title' => 'พนักงานห้องผ่าตัด', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN111', 'title' => 'พนักงานผ่าและรักษาศพ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN112', 'title' => 'พนักงานบัตรรายงานโรค', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN113', 'title' => 'พนักงานปฏิบัติการทดลองพาหะนำโรค', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN114', 'title' => 'ผู้ช่วยนักกายภาพบำบัด', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN115', 'title' => 'พนักงานกู้ชีพ', 'data_json' => ['code' => '', 'note' => 'พนักงานกู้ชีพเริ่มใช้ 9/06/2566'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN116', 'title' => 'พนักงานประจำห้องทดลอง', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN117', 'title' => 'พนักงานวิทยาศาสตร์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN118', 'title' => 'พนักงานพิธีสงฆ์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN119', 'title' => 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN120', 'title' => 'ช่างเหล็ก', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN121', 'title' => 'ช่างฝีมือทั่วไป', 'data_json' => ['code' => '', 'note' => 'ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN122', 'title' => 'ช่างต่อท่อ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN123', 'title' => 'ช่างศิลป์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN124', 'title' => 'ช่างตัดเย็บผ้า', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN125', 'title' => 'ช่างตัดผม', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN126', 'title' => 'ช่างซ่อมเครื่องทำความเย็น', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN127', 'title' => 'ช่างเครื่องช่วยคนพิการ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN128', 'title' => 'ผู้ช่วยช่างทั่วไป', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN129', 'title' => 'นักปฏิบัติการฉุกเฉินการแพทย์', 'data_json' => ['code' => '3-6-2023', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN130', 'title' => 'นักกำหนดอาหาร', 'data_json' => ['code' => '3-6-2024', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG14', 'code' => 'PG14PN131', 'title' => 'พนักงานขับรถยนต์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG10', 'code' => 'PG10PN132', 'title' => 'นักนิติวิทยาศาสตร์', 'data_json' => ['code' => '3-5-004-1', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG9', 'code' => 'PG9PN133', 'title' => 'นักสาธารณสุข', 'data_json' => ['code' => '3-6-022-1', 'note' => ''], 'active' => 1]);
// ลูกจ้างชั่วคราวรายเดือน
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN1', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN2', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN3', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN4', 'title' => 'ทันตแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN5', 'title' => 'นักเทคนิคการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN6', 'title' => 'นายสัตวแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN7', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN8', 'title' => 'นายแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN9', 'title' => 'แพทย์แผนไทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN10', 'title' => 'เภสัชกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN11', 'title' => 'นักรังสีการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN12', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN13', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['note' => 'นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN14', 'title' => 'นักฟิสิกส์การแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN15', 'title' => 'นักทัศนมาตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN16', 'title' => 'นักกายอุปกรณ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN17', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN18', 'title' => 'นักวิชาการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN19', 'title' => 'นักฟิสิกส์รังสี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN20', 'title' => 'นักวิทยาศาสตร์', 'data_json' => ['note' => 'นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN21', 'title' => 'นักจิตวิทยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN22', 'title' => 'นักโภชนาการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN23', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['note' => 'นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN24', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN25', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN26', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN27', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN28', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG17', 'code' => 'PG17PN29', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN30', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN31', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN32', 'title' => 'นิติกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN33', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN34', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN35', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN36', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['note' => 'นักวิชาการสถิติ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN37', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN38', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN39', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN40', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN41', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN42', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['note' => 'นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN43', 'title' => 'นักวิชาการเกษตร', 'data_json' => ['note' => 'นักวิชาการเกษตร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN44', 'title' => 'วิศวกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN45', 'title' => 'บรรณารักษ์', 'data_json' => ['note' => 'บรรณารักษ์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN46', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG18', 'code' => 'PG18PN47', 'title' => 'วิทยาจารย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN48', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['note' => 'เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN49', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['note' => 'เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN50', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN51', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN52', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['note' => 'เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN53', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', 'data_json' => ['note' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN54', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN55', 'title' => 'เจ้าพนักงานการเกษตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN56', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN57', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['note' => 'เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN58', 'title' => 'โภชนากร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN59', 'title' => 'เจ้าพนักงานรังสีการแพทย', 'data_json' => ['note' => 'เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN60', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN61', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN62', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['note' => 'เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN63', 'title' => 'เจ้าพนักงานเวชกิจฉุกเฉิน', 'data_json' => ['note' => 'เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN64', 'title' => 'เจ้าพนักงานการแพทย์แผนไทย', 'data_json' => ['note' => 'เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN65', 'title' => 'นายช่างศิลป์', 'data_json' => ['note' => 'นายช่างศิลป์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN66', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['note' => 'ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN67', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['note' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN68', 'title' => 'ช่างทันตกรรม', 'data_json' => ['note' => 'ช่างทันตกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN69', 'title' => 'นายช่างเทคนิค', 'data_json' => ['note' => 'นายช่างเทคนิค เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN70', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['note' => 'นายช่างไฟฟ้า เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN71', 'title' => 'นายช่างโยธา', 'data_json' => ['note' => 'นายช่างโยธา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN72', 'title' => 'ครูการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG19', 'code' => 'PG19PN73', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['note' => 'เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN74', 'title' => 'พนักงานประจำตึก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN75', 'title' => 'พนักงานเปล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN76', 'title' => 'พนักงานซักฟอก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN77', 'title' => 'พนักงานบริการ', 'data_json' => ['note' => 'พนักงานบริการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN78', 'title' => 'พนักงานรับโทรศัพท์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN79', 'title' => 'พนักงานเกษตรพื้นฐาน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN80', 'title' => 'พนักงานเรือยนต', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN81', 'title' => 'พนักงานบริการเอกสารทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN82', 'title' => 'พนักงานเก็บเอกสาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN83', 'title' => 'พนักงานบริการสื่ออุปกรณ์การสอน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN84', 'title' => 'พนักงานเก็บเงิน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN85', 'title' => 'พนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN86', 'title' => 'พนักงานผลิตน้ำประปา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN87', 'title' => 'พนักงานการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN88', 'title' => 'พนักงานพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN89', 'title' => 'พนักงานธุรการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN90', 'title' => 'พนักงานพิมพ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN91', 'title' => 'พนักงานประเมินผล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN92', 'title' => 'พนักงานการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN93', 'title' => 'พนักงานห้องสมุด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN94', 'title' => 'พนักงานสื่อสาร', 'data_json' => ['note' => 'พนักงานสื่อสาร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN95', 'title' => 'ล่ามภาษาต่างประเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN96', 'title' => 'ครูพี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN97', 'title' => 'พี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN98', 'title' => 'พนักงานช่วยการพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN99', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['note' => 'พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN100', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN101', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['note' => 'ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN102', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN103', 'title' => 'พนักงานประจำห้องยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN104', 'title' => 'ผู้ช่วยพนักงานสุขศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN105', 'title' => 'ผู้ช่วยเจ้าหน้าที่อนามัย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN106', 'title' => 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN107', 'title' => 'พนักงานการแพทย์และรังสีเทคนิค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN108', 'title' => 'พนักงานจุลทัศนกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN109', 'title' => 'พนักงานประกอบอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN110', 'title' => 'พนักงานห้องผ่าตัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN111', 'title' => 'พนักงานผ่าและรักษาศพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN112', 'title' => 'พนักงานบัตรรายงานโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN113', 'title' => 'พนักงานปฏิบัติการทดลองพาหะนำโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN114', 'title' => 'ผู้ช่วยนักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN115', 'title' => 'พนักงานกู้ชีพ', 'data_json' => ['note' => 'พนักงานกู้ชีพเริ่มใช้ 9/06/2566'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN116', 'title' => 'พนักงานประจำห้องทดลอง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN117', 'title' => 'พนักงานวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN118', 'title' => 'พนักงานพิธีสงฆ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN119', 'title' => 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN120', 'title' => 'ช่างเหล็ก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN121', 'title' => 'ช่างฝีมือทั่วไป', 'data_json' => ['note' => 'ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN122', 'title' => 'ช่างต่อท่อ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN123', 'title' => 'ช่างศิลป์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN124', 'title' => 'ช่างตัดเย็บผ้า', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN125', 'title' => 'ช่างตัดผม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN126', 'title' => 'ช่างซ่อมเครื่องทำความเย็น', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN127', 'title' => 'ช่างเครื่องช่วยคนพิการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN128', 'title' => 'ผู้ช่วยช่างทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN129', 'title' => 'นักปฏิบัติการฉุกเฉินการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN130', 'title' => 'นักกำหนดอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG20', 'code' => 'PG20PN131', 'title' => 'พนักงานขับรถยนต์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG16', 'code' => 'PG16PN132', 'title' => 'นักนิติวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG15', 'code' => 'PG15PN133', 'title' => 'นักสาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);
// ลูกจ้างชั่วคราวรายวัน
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN1', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN2', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN3', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN4', 'title' => 'ทันตแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN5', 'title' => 'นักเทคนิคการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN6', 'title' => 'นายสัตวแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN7', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN8', 'title' => 'นายแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN9', 'title' => 'แพทย์แผนไทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN10', 'title' => 'เภสัชกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN11', 'title' => 'นักรังสีการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN12', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN13', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['note' => 'นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN14', 'title' => 'นักฟิสิกส์การแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN15', 'title' => 'นักทัศนมาตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN16', 'title' => 'นักกายอุปกรณ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN17', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN18', 'title' => 'นักวิชาการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN19', 'title' => 'นักฟิสิกส์รังสี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN20', 'title' => 'นักวิทยาศาสตร์', 'data_json' => ['note' => 'นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN21', 'title' => 'นักจิตวิทยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN22', 'title' => 'นักโภชนาการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN23', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['note' => 'นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN24', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN25', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN26', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN27', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN28', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG23', 'code' => 'PG23PN29', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN30', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN31', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN32', 'title' => 'นิติกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN33', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN34', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN35', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN36', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['note' => 'นักวิชาการสถิติ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN37', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN38', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN39', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN40', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN41', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN42', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['note' => 'นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN43', 'title' => 'นักวิชาการเกษตร', 'data_json' => ['note' => 'นักวิชาการเกษตร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN44', 'title' => 'วิศวกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN45', 'title' => 'บรรณารักษ์', 'data_json' => ['note' => 'บรรณารักษ์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN46', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG24', 'code' => 'PG24PN47', 'title' => 'วิทยาจารย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN48', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['note' => 'เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN49', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['note' => 'เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN50', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN51', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN52', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['note' => 'เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN53', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', 'data_json' => ['note' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN54', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN55', 'title' => 'เจ้าพนักงานการเกษตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN56', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN57', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['note' => 'เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN58', 'title' => 'โภชนากร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN59', 'title' => 'เจ้าพนักงานรังสีการแพทย', 'data_json' => ['note' => 'เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN60', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN61', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN62', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['note' => 'เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN63', 'title' => 'เจ้าพนักงานเวชกิจฉุกเฉิน', 'data_json' => ['note' => 'เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN64', 'title' => 'เจ้าพนักงานการแพทย์แผนไทย', 'data_json' => ['note' => 'เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN65', 'title' => 'นายช่างศิลป์', 'data_json' => ['note' => 'นายช่างศิลป์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN66', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['note' => 'ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN67', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['note' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN68', 'title' => 'ช่างทันตกรรม', 'data_json' => ['note' => 'ช่างทันตกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN69', 'title' => 'นายช่างเทคนิค', 'data_json' => ['note' => 'นายช่างเทคนิค เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN70', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['note' => 'นายช่างไฟฟ้า เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN71', 'title' => 'นายช่างโยธา', 'data_json' => ['note' => 'นายช่างโยธา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN72', 'title' => 'ครูการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG25', 'code' => 'PG25PN73', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['note' => 'เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN74', 'title' => 'พนักงานประจำตึก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN75', 'title' => 'พนักงานเปล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN76', 'title' => 'พนักงานซักฟอก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN77', 'title' => 'พนักงานบริการ', 'data_json' => ['note' => 'พนักงานบริการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN78', 'title' => 'พนักงานรับโทรศัพท์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN79', 'title' => 'พนักงานเกษตรพื้นฐาน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN80', 'title' => 'พนักงานเรือยนต', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN81', 'title' => 'พนักงานบริการเอกสารทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN82', 'title' => 'พนักงานเก็บเอกสาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN83', 'title' => 'พนักงานบริการสื่ออุปกรณ์การสอน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN84', 'title' => 'พนักงานเก็บเงิน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN85', 'title' => 'พนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN86', 'title' => 'พนักงานผลิตน้ำประปา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN87', 'title' => 'พนักงานการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN88', 'title' => 'พนักงานพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN89', 'title' => 'พนักงานธุรการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN90', 'title' => 'พนักงานพิมพ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN91', 'title' => 'พนักงานประเมินผล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN92', 'title' => 'พนักงานการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN93', 'title' => 'พนักงานห้องสมุด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN94', 'title' => 'พนักงานสื่อสาร', 'data_json' => ['note' => 'พนักงานสื่อสาร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN95', 'title' => 'ล่ามภาษาต่างประเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN96', 'title' => 'ครูพี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN97', 'title' => 'พี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN98', 'title' => 'พนักงานช่วยการพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN99', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['note' => 'พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN100', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN101', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['note' => 'ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN102', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN103', 'title' => 'พนักงานประจำห้องยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN104', 'title' => 'ผู้ช่วยพนักงานสุขศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN105', 'title' => 'ผู้ช่วยเจ้าหน้าที่อนามัย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN106', 'title' => 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN107', 'title' => 'พนักงานการแพทย์และรังสีเทคนิค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN108', 'title' => 'พนักงานจุลทัศนกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN109', 'title' => 'พนักงานประกอบอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN110', 'title' => 'พนักงานห้องผ่าตัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN111', 'title' => 'พนักงานผ่าและรักษาศพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN112', 'title' => 'พนักงานบัตรรายงานโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN113', 'title' => 'พนักงานปฏิบัติการทดลองพาหะนำโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN114', 'title' => 'ผู้ช่วยนักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN115', 'title' => 'พนักงานกู้ชีพ', 'data_json' => ['note' => 'พนักงานกู้ชีพเริ่มใช้ 9/06/2566'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN116', 'title' => 'พนักงานประจำห้องทดลอง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN117', 'title' => 'พนักงานวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN118', 'title' => 'พนักงานพิธีสงฆ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN119', 'title' => 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN120', 'title' => 'ช่างเหล็ก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN121', 'title' => 'ช่างฝีมือทั่วไป', 'data_json' => ['note' => 'ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN122', 'title' => 'ช่างต่อท่อ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN123', 'title' => 'ช่างศิลป์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN124', 'title' => 'ช่างตัดเย็บผ้า', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN125', 'title' => 'ช่างตัดผม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN126', 'title' => 'ช่างซ่อมเครื่องทำความเย็น', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN127', 'title' => 'ช่างเครื่องช่วยคนพิการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN128', 'title' => 'ผู้ช่วยช่างทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN129', 'title' => 'นักปฏิบัติการฉุกเฉินการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN130', 'title' => 'นักกำหนดอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG26', 'code' => 'PG26PN131', 'title' => 'พนักงานขับรถยนต์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG22', 'code' => 'PG22PN132', 'title' => 'นักนิติวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG21', 'code' => 'PG21PN133', 'title' => 'นักสาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);
        
        // ตำแหน่งลูกจ้างประจำ
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN134', 'title' => 'พนักงานกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN135', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN136', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN137', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PG6', 'code' => 'PG6PN138', 'title' => 'พนักงานธุรการ', 'data_json' => ['note' => ''], 'active' => 1]);
        // ตำแหน่งลูกจ้างประจำ

        // ครอบครัว
//         $this->insert('categorise', ['code' => 1, 'name' => 'family_relation', 'title' => 'บิดา']);
//         $this->insert('categorise', ['code' => 2, 'name' => 'family_relation', 'title' => 'มารดา']);
//         $this->insert('categorise', ['code' => 3, 'name' => 'family_relation', 'title' => 'สามี']);
//         $this->insert('categorise', ['code' => 4, 'name' => 'family_relation', 'title' => 'ภรรยา']);
//         $this->insert('categorise', ['code' => 5, 'name' => 'family_relation', 'title' => 'บุตรชาย']);
//         $this->insert('categorise', ['code' => 6, 'name' => 'family_relation', 'title' => 'บุตรสาว']);
//         $this->insert('categorise', ['code' => 7, 'name' => 'family_relation', 'title' => 'พี่ชาย']);
//         $this->insert('categorise', ['code' => 8, 'name' => 'family_relation', 'title' => 'พี่สาว']);

//         // เพศ
//         $this->insert('categorise', ['name' => 'gender', 'title' => 'ชาย']);
//         $this->insert('categorise', ['name' => 'gender', 'title' => 'หญิง']);

//         //กรุ๊บเลือด
//         $this->insert('categorise', ['code' => 'A', 'name' => 'blood', 'title' => 'A']);
//         $this->insert('categorise', ['code' => 'B', 'name' => 'blood', 'title' => 'B']);
//         $this->insert('categorise', ['code' => 'AB', 'name' => 'blood', 'title' => 'AB']);
//         $this->insert('categorise', ['code' => 'O', 'name' => 'blood', 'title' => 'O']);

//         //  คำนำหน้าชื่อ
//         $this->insert('categorise', ['name' => 'prefix_th', 'title' => 'นาย']);
//         $this->insert('categorise', ['name' => 'prefix_th', 'title' => 'นาง']);
//         $this->insert('categorise', ['name' => 'prefix_th', 'title' => 'น.ส.']);
//         $this->insert('categorise', ['name' => 'prefix_en', 'title' => 'Mr.']);
//         $this->insert('categorise', ['name' => 'prefix_en', 'title' => 'Mrs.']);
//         $this->insert('categorise', ['name' => 'prefix_en', 'title' => 'Ms.']);

//         // สถานะ
//         $this->insert('categorise', ['code' => 1, 'name' => 'marry', 'title' => 'โสด']);
//         $this->insert('categorise', ['code' => 2, 'name' => 'marry', 'title' => 'สมรส']);
//         $this->insert('categorise', ['code' => 3, 'name' => 'marry', 'title' => 'หม้าย/หย่าร้าง']);

//         // ศาสนา
//         $this->insert('categorise', ['code' => 1, 'name' => 'religion', 'title' => 'พุทธ']);
//         $this->insert('categorise', ['code' => 2, 'name' => 'religion', 'title' => 'คริศต์']);
//         $this->insert('categorise', ['code' => 3, 'name' => 'religion', 'title' => 'อิสลาม']);
//         $this->insert('categorise', ['code' => 4, 'name' => 'religion', 'title' => 'ฮินดู']);

//         // สัญชาติ
//         $this->insert('categorise', ['code' => 1, 'name' => 'nationality', 'title' => 'ไทย']);
//         $this->insert('categorise', ['code' => 2, 'name' => 'nationality', 'title' => 'ลาว']);
//         $this->insert('categorise', ['code' => 3, 'name' => 'nationality', 'title' => 'จีน']);
//         $this->insert('categorise', ['code' => 4, 'name' => 'nationality', 'title' => 'อเมริกัน']);
//         $this->insert('categorise', ['code' => 5, 'name' => 'nationality', 'title' => 'พม่า']);
//         $this->insert('categorise', ['code' => 6, 'name' => 'nationality', 'title' => 'กัมพูชา']);
//         $this->insert('categorise', ['code' => 7, 'name' => 'nationality', 'title' => 'อินโดนีเซีย']);
//         $this->insert('categorise', ['code' => 8, 'name' => 'nationality', 'title' => 'ฟิลิปปินส์']);
//         $this->insert('categorise', ['code' => 9, 'name' => 'nationality', 'title' => 'มาเลเซีย']);

//         //สถานะของพนักงาน
//         $this->insert('categorise', ['code' => 1, 'name' => 'emp_status', 'title' => 'ปฏิบัติราชการ']);
//         $this->insert('categorise', ['code' => 2, 'name' => 'emp_status', 'title' => 'ลาออก']);
//         $this->insert('categorise', ['code' => 3, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการ']);
//         $this->insert('categorise', ['code' => 4, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการรับบำนาญ']);
//         $this->insert('categorise', ['code' => 5, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการรับบำเหน็จ']);
//         $this->insert('categorise', ['code' => 6, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการรับบำเหน็จรายเดือน']);
//         $this->insert('categorise', ['code' => 7, 'name' => 'emp_status', 'title' => 'ถึงแก่กรรม']);
//         $this->insert('categorise', ['code' => 8, 'name' => 'emp_status', 'title' => 'ถึงแก่กรรมรับบำเหน็จ']);
//         $this->insert('categorise', ['code' => 9, 'name' => 'emp_status', 'title' => 'ปลดออก']);
//         $this->insert('categorise', ['code' => 10, 'name' => 'emp_status', 'title' => 'ปลดออกรับบำเหน็จ']);
//         $this->insert('categorise', ['code' => 11, 'name' => 'emp_status', 'title' => 'ไปปฏิบัติการวิจัย ณ ต่างประเทศ']);
//         $this->insert('categorise', ['code' => 12, 'name' => 'emp_status', 'title' => 'ไปปฏิบัติการวิจัยในประเทศ']);
//         $this->insert('categorise', ['code' => 13, 'name' => 'emp_status', 'title' => 'ย้าย']);
//         $this->insert('categorise', ['code' => 14, 'name' => 'emp_status', 'title' => 'ไปปฏิบัติงานเพื่อเพิ่มพูนความรู้ทางวิชาการ']);
//         $this->insert('categorise', ['code' => 15, 'name' => 'emp_status', 'title' => 'ไปราชการ ณ ต่างประเทศ']);
//         $this->insert('categorise', ['code' => 16, 'name' => 'emp_status', 'title' => 'ไปราชการภายในประเทศ']);
//         $this->insert('categorise', ['code' => 17, 'name' => 'emp_status', 'title' => 'ฝึกอบรม ณ ต่างประเทศ']);
//         $this->insert('categorise', ['code' => 18, 'name' => 'emp_status', 'title' => 'ฝึกอบรมในประเทศ']);
//         $this->insert('categorise', ['code' => 19, 'name' => 'emp_status', 'title' => 'ยกเลิกคำสั่งบรรจุ']);
//         $this->insert('categorise', ['code' => 20, 'name' => 'emp_status', 'title' => 'ลาศึกษา ณ ต่างประเทศ']);
//         $this->insert('categorise', ['code' => 21, 'name' => 'emp_status', 'title' => 'ลาศึกษาในประเทศ']);
//         $this->insert('categorise', ['code' => 22, 'name' => 'emp_status', 'title' => 'ลาออกรับบำนาญ']);
//         $this->insert('categorise', ['code' => 23, 'name' => 'emp_status', 'title' => 'ลาออกรับบำเหน็จ']);
//         $this->insert('categorise', ['code' => 24, 'name' => 'emp_status', 'title' => 'เลิกจ้าง']);
//         $this->insert('categorise', ['code' => 25, 'name' => 'emp_status', 'title' => 'ไล่ออก']);
//         $this->insert('categorise', ['code' => 26, 'name' => 'emp_status', 'title' => 'หมดสัญญาจ้าง']);
//         $this->insert('categorise', ['code' => 27, 'name' => 'emp_status', 'title' => 'ให้ออก']);
//         $this->insert('categorise', ['code' => 28, 'name' => 'emp_status', 'title' => 'ให้ออกจากราชการ']);
//         $this->insert('categorise', ['code' => 29, 'name' => 'emp_status', 'title' => 'ให้ออกรับบำนาญ']);
//         $this->insert('categorise', ['code' => 30, 'name' => 'emp_status', 'title' => 'ให้ออกรับบำเหน็จ']);
//         $this->insert('categorise', ['code' => 31, 'name' => 'emp_status', 'title' => 'ให้โอน']);
//         $this->insert('categorise', ['code' => 32, 'name' => 'emp_status', 'title' => 'ไปฏิบัติงานในองค์การระหว่างประเทศ']);
//         $this->insert('categorise', ['code' => 33, 'name' => 'emp_status', 'title' => 'ข้าราชการบำนาญถึงแก่กรรม']);
//         $this->insert('categorise', ['code' => 34, 'name' => 'emp_status', 'title' => 'ลาออกรับบำเหน็จรายเดือน']);
//         $this->insert('categorise', ['code' => 35, 'name' => 'emp_status', 'title' => 'ข้าราชการบำเหน็จถึงแก่กรรม']);
//         $this->insert('categorise', ['code' => 36, 'name' => 'emp_status', 'title' => 'ลูกจ้างประจำบำเหน็จรายเดือน']);
//         $this->insert('categorise', ['code' => 37, 'name' => 'emp_status', 'title' => 'ลูกจ้างประจำบำเหน็จรายเดือนถึงแก่กรรม']);

        //ประเภทการเปลี่ยนชื่อ
        $this->insert('categorise', ['code' => 1, 'name' => 'rename_type', 'title' => 'เปลี่ยนคำนำหน้า']);
        $this->insert('categorise', ['code' => 2, 'name' => 'rename_type', 'title' => 'เปลี่ยนชื่อ']);
        $this->insert('categorise', ['code' => 3, 'name' => 'rename_type', 'title' => 'เปลี่ยนสกุล']);
        $this->insert('categorise', ['code' => 4, 'name' => 'rename_type', 'title' => 'เปลี่ยนคำนำหน้าและสกุล']);
        $this->insert('categorise', ['code' => 5, 'name' => 'rename_type', 'title' => 'เปลี่ยนชื่อและเปลี่ยนสกุล']);
        $this->insert('categorise', ['code' => 6, 'name' => 'rename_type', 'title' => 'เปลี่ยนคำนำหน้าเปลี่ยนชื่อและเปลี่ยนสกุล']);

        //รายการ สัมมนา ฝึกอบรม ดูงาน ศึกษาต่อ และข้อมูลรายงาน
        $this->insert('categorise', ['code' => 1, 'name' => 'develop', 'title' => 'ประชุม']);
        $this->insert('categorise', ['code' => 2, 'name' => 'develop', 'title' => 'ประชุมวิชาการ']);
        $this->insert('categorise', ['code' => 3, 'name' => 'develop', 'title' => 'ประชุมเชิงปฏิบัติการ']);
        $this->insert('categorise', ['code' => 4, 'name' => 'develop', 'title' => 'ศึกษาดูงาน']);
        $this->insert('categorise', ['code' => 5, 'name' => 'develop', 'title' => 'สัมมนา']);

        //ประเภทของเงิน
        $this->insert('categorise', ['code' => 1, 'name' => 'money_type', 'title' => 'งบประมาณแผ่นดิน']);
        $this->insert('categorise', ['code' => 2, 'name' => 'money_type', 'title' => 'เงินรายได้']);

        //ลักษณะการไป
        $this->insert('categorise', ['code' => 1, 'name' => 'followby', 'title' => 'ได้รับเชิญ']);
        $this->insert('categorise', ['code' => 2, 'name' => 'followby', 'title' => 'คณะ/หน่วยงานส่งเข้าร่วมเป็นตัวแทน']);
        $this->insert('categorise', ['code' => 3, 'name' => 'followby', 'title' => 'เจ้าตัวสมัครไป']);

        Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_type.sql'));
        Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_group.sql'));
        Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_name.sql'));

        // ระดับการศึกษา
        $sqlEducation = file_get_contents(__DIR__ . '/education.sql');

        Yii::$app->db->pdo->exec($sqlEducation);

        // สถาบันการศึกษา
        // $sqlInstitute = file_get_contents(__DIR__ . '/institute.sql');

        // Yii::$app->db->pdo->exec($sqlInstitute);
        // วิชาเอก
        // $sqlMajor = file_get_contents(__DIR__ . '/major.sql');
        // Yii::$app->db->pdo->exec($sqlMajor);
        // ประเทศ
        $sqlContry = file_get_contents(__DIR__ . '/contry.sql');
        Yii::$app->db->pdo->exec($sqlContry);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%categorise}}');

    }
}
