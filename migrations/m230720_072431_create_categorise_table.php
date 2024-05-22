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
            'ma_items' => $this->json()->comment('รายการบำรุงรักษา'),
            'active' => $this->boolean()->defaultValue(true),
        ]);

        $this->insert('categorise', ['name' => 'site', 'title' => 'ตั้งค่าระบบ', 'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'data_json' => [
                'company_name' => 'ระบุชื่อองค์กร'
            ]]);

        // ระดับ
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

        // ========= กำหนดตำแหน่ง ========
        // PT1 กลุ่มข้าราชการ
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PT1PG1', 'title' => 'บริหาร', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PT1PG2', 'title' => 'อำนวยการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PT1PG3', 'title' => 'วิชาการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT1', 'code' => 'PT1PG4', 'title' => 'ทั่วไป', 'active' => 1]);
        // PT1กำหนดตำแหน่งข้าราชการ
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG1', 'code' => 'PT1PG1PN1', 'title' => 'นักบริหาร', 'data_json' => ['code' => '1-1-2001', 'title_name' => 'บริหาร', 'level' => 'ต้น - สูง'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG1', 'code' => 'PT1PG1PN2', 'title' => 'ผู้ตรวจราชการกระทรวง', 'data_json' => ['code' => '1-1-2004', 'title_name' => 'ตรวจราชการกระทรวง', 'level' => 'สูง'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG2', 'code' => 'PT1PG2PN1', 'title' => 'ผู้อำนวยการ', 'data_json' => ['code' => '2-1-2001', 'title_name' => 'อำนวยการ', 'level' => 'ต้น - สูง'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG2', 'code' => 'PT1PG2PN2', 'title' => 'ผู้อำนวยการเฉพาะด้าน (ระบุชื่อสายงาน)', 'data_json' => ['code' => '2-1-2002', 'title_name' => 'อำนวยการเฉพาะด้าน', 'level' => 'ต้น - สูง'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN1', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['code' => '3-1-2004', 'title_name' => 'จัดการงานทั่วไป', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN2', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['code' => '3-1-2006', 'title_name' => 'ทรัพยากรบุคคล', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN3', 'title' => 'นิติกร', 'data_json' => ['code' => '3-1-2008', 'title_name' => 'นิติการ', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN4', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['code' => '3-1-2012', 'title_name' => 'นักวิเคราะห์นโยบายและแผน', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN5', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['code' => '3-1-2013', 'title_name' => 'วิชาการคอมพิวเตอร์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN6', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['code' => '3-1-2015', 'title_name' => 'วิชาการเทคโนโลยีสารสนเทศ', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN7', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['code' => '3-1-2016', 'title_name' => 'วิชาการพัสดุ', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN8', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['code' => '3-1-2019', 'title_name' => 'วิชาการสถิติ', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN9', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['code' => '3-1-2021', 'title_name' => 'วิเทศสัมพันธ์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN10', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['code' => '3-2-2006', 'title_name' => 'วิชาการเงินและบัญชี', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN11', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['code' => '3-2-2009', 'title_name' => 'วิชาการตรวจสอบภายใน', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN12', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['code' => '3-3-2005', 'title_name' => 'ประชาสัมพันธ์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN13', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['code' => '3-3-2007', 'title_name' => 'วิชาการเผยแพร่', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN14', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['code' => '3-3-2008', 'title_name' => 'วิชาการโสตทัศนศึกษา', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN15', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['code' => '3-6-2001', 'title_name' => 'กายภาพบำบัด', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN16', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['code' => '3-6-2002', 'title_name' => 'กิจกรรมบำบัด', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN17', 'title' => 'นักจิตวิทยา', 'data_json' => ['code' => '3-6-2003', 'title_name' => 'จิตวิทยา', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN18', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['code' => '3-6-2004', 'title_name' => 'จิตวิทยาคลีนิก', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN19', 'title' => 'ทันตแพทย์', 'data_json' => ['code' => '3-6-2005', 'title_name' => 'ทันตแพทย์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN20', 'title' => 'นักเทคนิคการแพทย์', 'data_json' => ['code' => '3-6-2006', 'title_name' => 'เทคนิคการแพทย์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN21', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['code' => '3-6-2008', 'title_name' => 'พยาบาลวิชาชีพ', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN22', 'title' => 'นายแพทย์', 'data_json' => ['code' => '3-6-2009', 'title_name' => 'แพทย์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN23', 'title' => 'แพทย์แผนไทย', 'data_json' => ['code' => '3-6-2010', 'title_name' => 'แพทย์แผนไทย', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN24', 'title' => 'เภสัชกร', 'data_json' => ['code' => '3-6-2011', 'title_name' => 'เภสัชกรรม', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN25', 'title' => 'นักโภชนาการ', 'data_json' => ['code' => '3-6-2012', 'title_name' => 'โภชนาการ', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN26', 'title' => 'นักรังสีการแพทย์', 'data_json' => ['code' => '3-6-2013', 'title_name' => 'รังสีการแพทย์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN27', 'title' => 'นักวิชาการพยาบาล', 'data_json' => ['code' => '3-6-2014', 'title_name' => 'วิชาการพยาบาล', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN28', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['code' => '3-6-2015', 'title_name' => 'วิชาการสาธารณสุข', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN29', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['code' => '3-6-2017', 'title_name' => 'วิชาการอาหารและยา', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN30', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '3-6-2018', 'title_name' => 'วิทยาศาสตร์การแพทย์', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN31', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['code' => '3-6-2019', 'title_name' => 'เวชศาสตร์การสื่อความหมาย', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN32', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['code' => '3-6-2020', 'title_name' => 'เทคโนโลยีหัวใจและทรวงอก', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN33', 'title' => 'นักสาธารณสุข', 'data_json' => ['code' => '3-6-2022', 'title_name' => 'สาธารณสุข', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN34', 'title' => 'นักกายอุปกรณ์', 'data_json' => ['code' => '3-7-2001', 'title_name' => 'กายอุปกรณ์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN35', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['code' => '3-7-2004', 'title_name' => 'ช่างภาพการแพทย์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN36', 'title' => 'บรรณารักษ์', 'data_json' => ['code' => '3-8-2003', 'title_name' => 'บรรณารักษ์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN37', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['code' => '3-8-2021', 'title_name' => 'วิชาการศึกษา', 'level' => 'ปฏิบัติการ - ทรงคุณวุฒิ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN38', 'title' => 'วิทยาจารย์', 'data_json' => ['code' => '3-8-2025', 'title_name' => 'วิทยาจารย์', 'level' => 'ปฏิบัติการ - ชำนาญการพิเศษ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG3', 'code' => 'PT1PG3PN39', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['code' => '3-8-2026', 'title_name' => 'สังคมสงเคราะห์', 'level' => 'ปฏิบัติการ - เชี่ยวชาญ'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN1', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['code' => '4-1-2001', 'title_name' => 'ปฏิบัติงานธุรการ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN2', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['code' => '4-1-2002', 'title_name' => 'ปฏิบัติงานพัสดุ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN3', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['code' => '4-1-2004', 'title_name' => 'เจ้าพนักงานเวชสถิติ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN4', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['code' => '4-1-2005', 'title_name' => 'ปฏิบัติงานสถิติ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN5', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['code' => '4-2-2002', 'title_name' => 'ปฏิบัติงานการเงินและบัญชี', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN6', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์', 'data_json' => ['code' => '4-3-2004', 'title_name' => 'ปฏิบัติงานเผยแพร่ประชาสัมพันธ์', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN7', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['code' => '4-3-2007', 'title_name' => 'ปฏิบัติงานโสตทัศนศึกษา', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN8', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['code' => '4-6-2001', 'title_name' => 'ปฏิบัติงานทันตสาธารณสุข', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN9', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['code' => '4-6-2002', 'title_name' => 'ปฏิบัติงานเภสัชกรรม', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN10', 'title' => 'โภชนากร', 'data_json' => ['code' => '4-6-2003', 'title_name' => 'โภชนาการ', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN11', 'title' => 'เจ้าพนักงานรังสีการแพทย์', 'data_json' => ['code' => '4-6-2004', 'title_name' => 'ปฏิบัติงานรังสีการแพทย์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN12', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '4-6-2005', 'title_name' => 'ปฏิบัติงานวิทยาศาสตร์การแพทย์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN13', 'title' => 'เจ้าพนักงานเวชกรรมฟื้นฟู', 'data_json' => ['code' => '4-6-2006', 'title_name' => 'ปฏิบัติงานเวชกรรมฟื้นฟู', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN14', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['code' => '4-6-2007', 'title_name' => 'ปฏิบัติงานสาธารณสุข', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN15', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['code' => '4-6-2008', 'title_name' => 'ปฏิบัติงานอาชีวบำบัด', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN16', 'title' => 'พยาบาลเทคนิค', 'data_json' => ['code' => '4-6-2009', 'title_name' => 'พยาบาลเทคนิค', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN17', 'title' => 'นายช่างศิลป์', 'data_json' => ['code' => '4-7-2003', 'title_name' => 'ปฏิบัติงานช่างศิลป์', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN18', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['code' => '4-7-2006', 'title_name' => 'ปฏิบัติงานกายอุปกรณ์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN19', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['code' => '4-7-2007', 'title_name' => 'ปฏิบัติงานเครื่องคอมพิวเตอร์', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN20', 'title' => 'ช่างทันตกรรม', 'data_json' => ['code' => '4-7-2012', 'title_name' => 'ปฏิบัติงานช่างทันตกรรม', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN21', 'title' => 'นายช่างเทคนิค', 'data_json' => ['code' => '4-7-2013', 'title_name' => 'ปฏิบัติงานช่างเทคนิค', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN22', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['code' => '4-7-2014', 'title_name' => 'ปฏิบัติงานช่างไฟฟ้า', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN23', 'title' => 'นายช่างโยธา', 'data_json' => ['code' => '4-7-2016', 'title_name' => 'ปฏิบัติงานโยธา', 'level' => 'ปฏิบัติงาน - อาวุโส'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT1PG4', 'code' => 'PT1PG4PN24', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['code' => '4-8-2015', 'title_name' => 'ปฏิบัติงานห้องสมุด', 'level' => 'ปฏิบัติงาน - ชำนาญงาน'], 'active' => 1]);

        // PT2 กลุ่มพนักงานราชการ ===================
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PT2PG1', 'title' => 'บริการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PT2PG2', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PT2PG3', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT2', 'code' => 'PT2PG4', 'title' => 'วิชาชีพเฉพาะ', 'active' => 1]);
        // ** กำหนดตำแหน่งพนักงานราชการ
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN1', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['title_name' => 'ปฏิบัติงานช่วยเหลือคนไข้'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN2', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['title_name' => 'ผู้ช่วยพยาบาล'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN3', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['title_name' => 'ผู้ช่วยทันตแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN4', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['title_name' => 'ปฏิบัติงานธุรการ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN5', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['title_name' => 'ปฏิบัติงานพัสดุ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN6', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['title_name' => 'ปฏิบัติงานสถิติ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN7', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['title_name' => 'ปฏิบัติงานการเงินและบัญชี'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN8', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์', 'data_json' => ['title_name' => 'ปฏิบัติงานเผยแพร่ประชาสัมพันธ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG1', 'code' => 'PT2PG1PN9', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['title_name' => 'ปฏิบัติงานห้องสมุด'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN1', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['title_name' => 'ปฏิบัติงานโสตทัศนศึกษา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN2', 'title' => 'นายช่างเทคนิค', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างเทคนิค'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN3', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['title_name' => 'ปฏิบัติงานเครื่องคอมพิวเตอร์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN4', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างไฟฟ้า'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN5', 'title' => 'นายช่างโยธา', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างโยธา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN6', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['title_name' => 'ปฏิบัติงานอาชีวบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN7', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['title_name' => 'ปฏิบัติงานเวชสถิติ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN8', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['title_name' => 'ปฏิบัติงานทันตสาธารณสุข'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN9', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['title_name' => 'ปฏิบัติงานเภสัชกรรม'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN10', 'title' => 'โภชนากร', 'data_json' => ['title_name' => 'ปฏิบัติงานโภชนากร'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN11', 'title' => 'เจ้าพนักงานรังสีการแพทย์', 'data_json' => ['title_name' => 'ปฏิบัติงานรังสีการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN12', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['title_name' => 'ปฏิบัติงานวิทยาศาสตร์การแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN13', 'title' => 'เจ้าพนักงานเวชกรรมฟื้นฟู', 'data_json' => ['title_name' => 'ปฏิบัติงานเวชกรรมฟื้นฟู'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN14', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['title_name' => 'ปฏิบัติงานสาธารณสุข'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN15', 'title' => 'นายช่างศิลป์', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างศิลป์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN16', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างกายอุปกรณ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN17', 'title' => 'ช่างทันตกรรม', 'data_json' => ['title_name' => 'ปฏิบัติงานช่างทันตกรรม'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG2', 'code' => 'PT2PG2PN18', 'title' => 'พยาบาลเทคนิค', 'data_json' => ['title_name' => 'ปฏิบัติงานพยาบาลเทคนิค'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN1', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['title_name' => 'จัดการงานทั่วไป'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN2', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['title_name' => 'ทรัพยากรบุคคล'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN3', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['title_name' => 'ประชาสัมพันธ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN4', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['title_name' => 'วิชาการเผยแพร่'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN5', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['title_name' => 'วิเคราะห์นโยบายและแผน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN6', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['title_name' => 'วิชาการพัสดุ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN7', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['title_name' => 'วิชาการศึกษา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN8', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['title_name' => 'วิชาการตรวจสอบภายใน'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN9', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['title_name' => 'วิทยาศาสตร์การแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN10', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['title_name' => 'สังคมสงเคราะห์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN11', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['title_name' => 'อาชีวบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN12', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['title_name' => 'วิชาการสาธารณสุข'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN13', 'title' => 'นักโภชนาการ', 'data_json' => ['title_name' => 'โภชนาการ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN14', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['title_name' => 'วิชาการเงินและบัญชี'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN15', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['title_name' => 'วิชาการโสตทัศนศึกษา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN16', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['title_name' => 'ช่างภาพการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN17', 'title' => 'บรรณารักษ์', 'data_json' => ['title_name' => 'บรรณารักษ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN18', 'title' => 'นิติกร', 'data_json' => ['title_name' => 'นิติการ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN19', 'title' => 'วิชาการสถิติ', 'data_json' => ['title_name' => 'วิชาการสถิติ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN20', 'title' => 'นักจิตวิทยา', 'data_json' => ['title_name' => 'จิตวิทยา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN21', 'title' => 'เศรษฐกร', 'data_json' => ['title_name' => 'เศรษฐกร'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG3', 'code' => 'PT2PG3PN22', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['title_name' => 'วิเทศสัมพันธ์'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN1', 'title' => 'นักกายอุปกรณ์', 'data_json' => ['title_name' => 'กายอุปกรณ์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN2', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['title_name' => 'กายภาพบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN3', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['title_name' => 'กิจกรรมบำบัด'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN4', 'title' => 'แพทย์แผนไทย', 'data_json' => ['title_name' => 'การแพทย์แผนไทย'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN5', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['title_name' => 'จิตวิทยาคลินิก'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN6', 'title' => 'นักเทคนิคการแพทย์', 'data_json' => ['title_name' => 'เทคนิคการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN7', 'title' => 'นักรังสีการแพทย์', 'data_json' => ['title_name' => 'รังสีการแพทย์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN8', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['title_name' => 'วิชาการคอมพิวเตอร์'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN9', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['title_name' => 'เวชศาสตร์การสื่อความหมาย'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN10', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['title_name' => 'พยาบาลวิชาชีพ'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN11', 'title' => 'เภสัชกร', 'data_json' => ['title_name' => 'เภสัชกรรม'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN12', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['title_name' => 'เทคโนโลยีหัวใจและทรวงอก'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN13', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['title_name' => 'วิศวกรไฟฟ้า'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN14', 'title' => 'วิศวกรโยธา', 'data_json' => ['title_name' => 'วิศวกรโยธา'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT2PG4', 'code' => 'PT2PG4PN15', 'title' => 'วิศวกรเครื่องกล', 'data_json' => ['title_name' => 'วิศวกรเครื่องกล'], 'active' => 1]);

        // PT3 กลุ่มพนักงานกระทรวง (พกส.) ========================
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PT3PG1', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PT3PG2', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PT3PG3', 'title' => 'วิชาชีพเฉพาะ ค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PT3PG4', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PT3PG5', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT3', 'code' => 'PT3PG6', 'title' => 'บริการ', 'active' => 1]);
        // *** กำหนดตำแหน่งพนักงานกระทรวงสาธารณสุข
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN1', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['code' => '3-6-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN2', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['code' => '3-6-2002', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN3', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['code' => '3-6-2004', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN4', 'title' => 'ทันตแพทย์', 'data_json' => ['code' => '3-6-2005', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN5', 'title' => 'นักเทคนิคการแพทย', 'data_json' => ['code' => '3-6-2006', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN6', 'title' => 'นายสัตวแพทย', 'data_json' => ['code' => '3-6-2007', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN7', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['code' => '3-6-2008', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN8', 'title' => 'นายแพทย์', 'data_json' => ['code' => '3-6-2009', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN9', 'title' => 'แพทย์แผนไทย', 'data_json' => ['code' => '3-6-2010', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN10', 'title' => 'เภสัชกร', 'data_json' => ['code' => '3-6-2011', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN11', 'title' => 'นักรังสีการแพทย', 'data_json' => ['code' => '3-6-2013', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN12', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['code' => '3-6-2019', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN13', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['code' => '3-6-2020', 'note' => 'นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN14', 'title' => 'นักฟิสิกส์การแพทย', 'data_json' => ['code' => '3-6-2021', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN15', 'title' => 'นักทัศนมาตร', 'data_json' => ['code' => '3-6-2022', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN16', 'title' => 'นักกายอุปกรณ', 'data_json' => ['code' => '3-7-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG1PN17', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['code' => '3-7-2020', 'note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN1', 'title' => 'นักวิชาการศึกษาพิเศษ', 'data_json' => ['code' => '3-8-2022', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN2', 'title' => 'นักฟิสิกส์รังสี', 'data_json' => ['code' => '3-5-2007', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN3', 'title' => 'นักวิทยาศาสตร์', 'data_json' => ['code' => '3-5-2010', 'note' => 'นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN4', 'title' => 'นักจิตวิทยา', 'data_json' => ['code' => '3-6-2003', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN5', 'title' => 'นักโภชนาการ', 'data_json' => ['code' => '3-6-2012', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN6', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['code' => '3-6-2015', 'note' => 'นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN7', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['code' => '3-6-2016', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN8', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['code' => '3-6-2017', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN9', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '3-6-2018', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN10', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['code' => '3-7-2004', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG2PN11', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['code' => '3-8-2026', 'note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG3', 'code' => 'PT3PG3PN1', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['code' => '3-1-2013', 'note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN1', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['code' => '3-1-2004', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN2', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['code' => '3-1-2006', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN3', 'title' => 'นิติกร', 'data_json' => ['code' => '3-1-2008', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN4', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['code' => '3-1-2012', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN5', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['code' => '3-1-2015', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN6', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['code' => '3-1-2016', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN7', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['code' => '3-1-2019', 'note' => 'นักวิชาการสถิติ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN8', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['code' => '3-1-2021', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN9', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['code' => '3-2-2006', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN10', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['code' => '3-2-2009', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN11', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['code' => '3-3-2005', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN12', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['code' => '3-3-2007', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN13', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['code' => '3-3-2008', 'note' => 'นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN14', 'title' => 'นักวิชาการเกษตร', 'data_json' => ['code' => '3-4-2001', 'note' => 'นักวิชาการเกษตร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN15', 'title' => 'วิศวกร', 'data_json' => ['code' => '3-7-2015', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN16', 'title' => 'บรรณารักษ์', 'data_json' => ['code' => '3-8-2003', 'note' => 'บรรณารักษ์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN17', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['code' => '3-8-2021', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG4', 'code' => 'PT3PG4PN18', 'title' => 'วิทยาจารย์', 'data_json' => ['code' => '3-8-2025', 'note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN1', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['code' => '4-1-2001', 'note' => 'เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN2', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['code' => '4-1-2002', 'note' => 'เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN3', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['code' => '4-1-2004', 'note' => 'เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN4', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['code' => '4-1-2005', 'note' => 'เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN5', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['code' => '4-2-2002', 'note' => 'เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN6', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', 'data_json' => ['code' => '4-3-2004', 'note' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN7', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['code' => '4-3-2007', 'note' => 'เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN8', 'title' => 'เจ้าพนักงานการเกษตร', 'data_json' => ['code' => '4-4-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN9', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['code' => '4-6-2001', 'note' => 'เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN10', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['code' => '4-6-2002', 'note' => 'เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN11', 'title' => 'โภชนากร', 'data_json' => ['code' => '4-6-2003', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN12', 'title' => 'เจ้าพนักงานรังสีการแพทย', 'data_json' => ['code' => '4-6-2004', 'note' => 'เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN13', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['code' => '4-6-2005', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN14', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['code' => '4-6-2007', 'note' => 'เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN15', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['code' => '4-6-2008', 'note' => 'เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN16', 'title' => 'เจ้าพนักงานเวชกิจฉุกเฉิน', 'data_json' => ['code' => '4-6-2011', 'note' => 'เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN17', 'title' => 'เจ้าพนักงานการแพทย์แผนไทย', 'data_json' => ['code' => '4-6-2012', 'note' => 'เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN18', 'title' => 'นายช่างศิลป์', 'data_json' => ['code' => '4-7-2003', 'note' => 'นายช่างศิลป์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN19', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['code' => '4-7-2006', 'note' => 'ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN20', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['code' => '4-7-2007', 'note' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN21', 'title' => 'ช่างทันตกรรม', 'data_json' => ['code' => '4-7-2012', 'note' => 'ช่างทันตกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN22', 'title' => 'นายช่างเทคนิค', 'data_json' => ['code' => '4-7-2013', 'note' => 'นายช่างเทคนิค เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN23', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['code' => '4-7-2014', 'note' => 'นายช่างไฟฟ้า เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN24', 'title' => 'นายช่างโยธา', 'data_json' => ['code' => '4-7-2016', 'note' => 'นายช่างโยธา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN25', 'title' => 'ครูการศึกษาพิเศษ', 'data_json' => ['code' => '4-8-2001', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG5', 'code' => 'PT3PG5PN26', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['code' => '7-8-2015', 'note' => 'เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN1', 'title' => 'พนักงานประจำตึก', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN2', 'title' => 'พนักงานเปล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN3', 'title' => 'พนักงานซักฟอก', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN4', 'title' => 'พนักงานบริการ', 'data_json' => ['code' => '', 'note' => 'พนักงานบริการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN5', 'title' => 'พนักงานรับโทรศัพท์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN6', 'title' => 'พนักงานเกษตรพื้นฐาน', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN7', 'title' => 'พนักงานเรือยนต', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN8', 'title' => 'พนักงานบริการเอกสารทั่วไป', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN9', 'title' => 'พนักงานเก็บเอกสาร', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN10', 'title' => 'พนักงานบริการสื่ออุปกรณ์การสอน', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN11', 'title' => 'พนักงานเก็บเงิน', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN12', 'title' => 'พนักงานโสตทัศนศึกษา', 'data_json' => ['code' => '', 'note' => 'พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN13', 'title' => 'พนักงานผลิตน้ำประปา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN14', 'title' => 'พนักงานการเงินและบัญชี', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN15', 'title' => 'พนักงานพัสดุ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN16', 'title' => 'พนักงานธุรการ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN17', 'title' => 'พนักงานพิมพ์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN18', 'title' => 'พนักงานประเมินผล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN19', 'title' => 'พนักงานการศึกษา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN20', 'title' => 'พนักงานห้องสมุด', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN21', 'title' => 'พนักงานสื่อสาร', 'data_json' => ['code' => '', 'note' => 'พนักงานสื่อสาร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN22', 'title' => 'ล่ามภาษาต่างประเทศ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN23', 'title' => 'ครูพี่เลี้ยง', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN24', 'title' => 'พี่เลี้ยง', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN25', 'title' => 'พนักงานช่วยการพยาบาล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN26', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['code' => '', 'note' => 'พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN27', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN28', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['code' => '', 'note' => 'ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN29', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN30', 'title' => 'พนักงานประจำห้องยา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN31', 'title' => 'ผู้ช่วยพนักงานสุขศึกษา', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN32', 'title' => 'ผู้ช่วยเจ้าหน้าที่อนามัย', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN33', 'title' => 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN34', 'title' => 'พนักงานการแพทย์และรังสีเทคนิค', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN35', 'title' => 'พนักงานจุลทัศนกร', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN36', 'title' => 'พนักงานประกอบอาหาร', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN37', 'title' => 'พนักงานห้องผ่าตัด', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN38', 'title' => 'พนักงานผ่าและรักษาศพ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN39', 'title' => 'พนักงานบัตรรายงานโรค', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN40', 'title' => 'พนักงานปฏิบัติการทดลองพาหะนำโรค', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN41', 'title' => 'ผู้ช่วยนักกายภาพบำบัด', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN42', 'title' => 'พนักงานกู้ชีพ', 'data_json' => ['code' => '', 'note' => 'พนักงานกู้ชีพเริ่มใช้ 9/06/2566'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN43', 'title' => 'พนักงานประจำห้องทดลอง', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN44', 'title' => 'พนักงานวิทยาศาสตร์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN45', 'title' => 'พนักงานพิธีสงฆ์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN46', 'title' => 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN47', 'title' => 'ช่างเหล็ก', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN48', 'title' => 'ช่างฝีมือทั่วไป', 'data_json' => ['code' => '', 'note' => 'ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN49', 'title' => 'ช่างต่อท่อ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN50', 'title' => 'ช่างศิลป์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN51', 'title' => 'ช่างตัดเย็บผ้า', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN52', 'title' => 'ช่างตัดผม', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN53', 'title' => 'ช่างซ่อมเครื่องทำความเย็น', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN54', 'title' => 'ช่างเครื่องช่วยคนพิการ', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN55', 'title' => 'ผู้ช่วยช่างทั่วไป', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG6PN56', 'title' => 'นักปฏิบัติการฉุกเฉินการแพทย์', 'data_json' => ['code' => '3-6-2023', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG6PN57', 'title' => 'นักกำหนดอาหาร', 'data_json' => ['code' => '3-6-2024', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG6', 'code' => 'PT3PG6PN58', 'title' => 'พนักงานขับรถยนต์', 'data_json' => ['code' => '', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG2', 'code' => 'PT3PG6PN59', 'title' => 'นักนิติวิทยาศาสตร์', 'data_json' => ['code' => '3-5-004-1', 'note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT3PG1', 'code' => 'PT3PG6PN60', 'title' => 'นักสาธารณสุข', 'data_json' => ['code' => '3-6-022-1', 'note' => ''], 'active' => 1]);

        // PT4 กลุ่มลูกจ้างชั่วคราวรายเดือน =========================
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PT4PG1', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PT4PG2', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PT4PG3', 'title' => 'วิชาชีพเฉพาะ ค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PT4PG4', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PT4PG5', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT4', 'code' => 'PT4PG6', 'title' => 'บริการ', 'active' => 1]);
        // *** ตำแหน่งลูกจ้างชั่วคราวรายเดือน
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN1', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN2', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN3', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN4', 'title' => 'ทันตแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN5', 'title' => 'นักเทคนิคการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN6', 'title' => 'นายสัตวแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN7', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN8', 'title' => 'นายแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN9', 'title' => 'แพทย์แผนไทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN10', 'title' => 'เภสัชกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN11', 'title' => 'นักรังสีการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN12', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN13', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['note' => 'นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN14', 'title' => 'นักฟิสิกส์การแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN15', 'title' => 'นักทัศนมาตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN16', 'title' => 'นักกายอุปกรณ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN17', 'title' => 'นักสาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG1', 'code' => 'PT4PG1PN18', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN1', 'title' => 'นักวิชาการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN2', 'title' => 'นักฟิสิกส์รังสี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN3', 'title' => 'นักวิทยาศาสตร์', 'data_json' => ['note' => 'นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN4', 'title' => 'นักจิตวิทยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN5', 'title' => 'นักโภชนาการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN6', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['note' => 'นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN7', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN8', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN9', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN10', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN11', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN12', 'title' => 'นักปฏิบัติการฉุกเฉินการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN13', 'title' => 'นักกำหนดอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG2', 'code' => 'PT4PG2PN14', 'title' => 'นักนิติวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG3', 'code' => 'PT4PG3PN1', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN2', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN3', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN4', 'title' => 'นิติกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN5', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN6', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN7', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN8', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['note' => 'นักวิชาการสถิติ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN9', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN10', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN11', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN12', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN13', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN14', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['note' => 'นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN15', 'title' => 'นักวิชาการเกษตร', 'data_json' => ['note' => 'นักวิชาการเกษตร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN16', 'title' => 'วิศวกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN17', 'title' => 'บรรณารักษ์', 'data_json' => ['note' => 'บรรณารักษ์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN18', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG4', 'code' => 'PT4PG3PN19', 'title' => 'วิทยาจารย์', 'data_json' => ['note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN1', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['note' => 'เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN2', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['note' => 'เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN3', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN4', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN5', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['note' => 'เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN6', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', 'data_json' => ['note' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN7', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN8', 'title' => 'เจ้าพนักงานการเกษตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN9', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN10', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['note' => 'เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN11', 'title' => 'โภชนากร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN12', 'title' => 'เจ้าพนักงานรังสีการแพทย', 'data_json' => ['note' => 'เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN13', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN14', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN15', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['note' => 'เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN16', 'title' => 'เจ้าพนักงานเวชกิจฉุกเฉิน', 'data_json' => ['note' => 'เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN17', 'title' => 'เจ้าพนักงานการแพทย์แผนไทย', 'data_json' => ['note' => 'เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN18', 'title' => 'นายช่างศิลป์', 'data_json' => ['note' => 'นายช่างศิลป์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN19', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['note' => 'ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN20', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['note' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN21', 'title' => 'ช่างทันตกรรม', 'data_json' => ['note' => 'ช่างทันตกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN22', 'title' => 'นายช่างเทคนิค', 'data_json' => ['note' => 'นายช่างเทคนิค เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN23', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['note' => 'นายช่างไฟฟ้า เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN24', 'title' => 'นายช่างโยธา', 'data_json' => ['note' => 'นายช่างโยธา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN25', 'title' => 'ครูการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG5', 'code' => 'PT4PG5PN26', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['note' => 'เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN1', 'title' => 'พนักงานประจำตึก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN2', 'title' => 'พนักงานเปล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN3', 'title' => 'พนักงานซักฟอก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN4', 'title' => 'พนักงานบริการ', 'data_json' => ['note' => 'พนักงานบริการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN5', 'title' => 'พนักงานรับโทรศัพท์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN6', 'title' => 'พนักงานเกษตรพื้นฐาน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN7', 'title' => 'พนักงานเรือยนต', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN8', 'title' => 'พนักงานบริการเอกสารทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN9', 'title' => 'พนักงานเก็บเอกสาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN10', 'title' => 'พนักงานบริการสื่ออุปกรณ์การสอน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN11', 'title' => 'พนักงานเก็บเงิน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN12', 'title' => 'พนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN13', 'title' => 'พนักงานผลิตน้ำประปา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN14', 'title' => 'พนักงานการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN15', 'title' => 'พนักงานพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN16', 'title' => 'พนักงานธุรการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN17', 'title' => 'พนักงานพิมพ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN18', 'title' => 'พนักงานประเมินผล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN19', 'title' => 'พนักงานการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN20', 'title' => 'พนักงานห้องสมุด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN21', 'title' => 'พนักงานสื่อสาร', 'data_json' => ['note' => 'พนักงานสื่อสาร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN22', 'title' => 'ล่ามภาษาต่างประเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN23', 'title' => 'ครูพี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN24', 'title' => 'พี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN25', 'title' => 'พนักงานช่วยการพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN26', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['note' => 'พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN27', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN28', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['note' => 'ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN29', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN30', 'title' => 'พนักงานประจำห้องยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN31', 'title' => 'ผู้ช่วยพนักงานสุขศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN32', 'title' => 'ผู้ช่วยเจ้าหน้าที่อนามัย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN33', 'title' => 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN34', 'title' => 'พนักงานการแพทย์และรังสีเทคนิค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN35', 'title' => 'พนักงานจุลทัศนกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN36', 'title' => 'พนักงานประกอบอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN37', 'title' => 'พนักงานห้องผ่าตัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN38', 'title' => 'พนักงานผ่าและรักษาศพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN39', 'title' => 'พนักงานบัตรรายงานโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN40', 'title' => 'พนักงานปฏิบัติการทดลองพาหะนำโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN41', 'title' => 'ผู้ช่วยนักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN42', 'title' => 'พนักงานกู้ชีพ', 'data_json' => ['note' => 'พนักงานกู้ชีพเริ่มใช้ 9/06/2566'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN43', 'title' => 'พนักงานประจำห้องทดลอง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN44', 'title' => 'พนักงานวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN45', 'title' => 'พนักงานพิธีสงฆ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN46', 'title' => 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN47', 'title' => 'ช่างเหล็ก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN48', 'title' => 'ช่างฝีมือทั่วไป', 'data_json' => ['note' => 'ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN49', 'title' => 'ช่างต่อท่อ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN50', 'title' => 'ช่างศิลป์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN52', 'title' => 'ช่างตัดเย็บผ้า', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN52', 'title' => 'ช่างตัดผม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN53', 'title' => 'ช่างซ่อมเครื่องทำความเย็น', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN54', 'title' => 'ช่างเครื่องช่วยคนพิการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN55', 'title' => 'ผู้ช่วยช่างทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT4PG6', 'code' => 'PT4PG6PN56', 'title' => 'พนักงานขับรถยนต์', 'data_json' => ['note' => ''], 'active' => 1]);

        // PT5 กลุ่มลูกจ้างชั่วคราวรายวัน
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PT5PG1', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PT5PG2', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PT5PG3', 'title' => 'วิชาชีพเฉพาะ ค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PT5PG4', 'title' => 'บริหารทั่วไป', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PT5PG5', 'title' => 'เทคนิค', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT5', 'code' => 'PT5PG6', 'title' => 'บริการ', 'active' => 1]);
        // ** ตำแหน่งลูกจ้างชั่วคราวรายวัน
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN1', 'title' => 'นักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN2', 'title' => 'นักกิจกรรมบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN3', 'title' => 'นักจิตวิทยาคลินิก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN4', 'title' => 'ทันตแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN5', 'title' => 'นักเทคนิคการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN6', 'title' => 'นายสัตวแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN7', 'title' => 'พยาบาลวิชาชีพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN8', 'title' => 'นายแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN9', 'title' => 'แพทย์แผนไทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN10', 'title' => 'เภสัชกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN11', 'title' => 'นักรังสีการแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN12', 'title' => 'นักเวชศาสตร์การสื่อความหมาย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN13', 'title' => 'นักเทคโนโลยีหัวใจและทรวงอก', 'data_json' => ['note' => 'นักเทคโนโลยีหัวใจและทรวงอก เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN14', 'title' => 'นักฟิสิกส์การแพทย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN15', 'title' => 'นักทัศนมาตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN16', 'title' => 'นักกายอุปกรณ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN27', 'title' => 'วิศวกรไฟฟ้า', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG1', 'code' => 'PT5PG1PN28', 'title' => 'นักสาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN1', 'title' => 'นักวิชาการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN2', 'title' => 'นักฟิสิกส์รังสี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2P3', 'title' => 'นักวิทยาศาสตร์', 'data_json' => ['note' => 'นักวิทยาศาสตร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN4', 'title' => 'นักจิตวิทยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN5', 'title' => 'นักโภชนาการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN6', 'title' => 'นักวิชาการสาธารณสุข', 'data_json' => ['note' => 'นักวิชาการสาธารณสุข เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN7', 'title' => 'นักอาชีวบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN8', 'title' => 'นักวิชาการอาหารและยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN9', 'title' => 'นักวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN10', 'title' => 'ช่างภาพการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN11', 'title' => 'นักสังคมสงเคราะห์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN12', 'title' => 'นักปฏิบัติการฉุกเฉินการแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN13', 'title' => 'นักกำหนดอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG2', 'code' => 'PT5PG2PN14', 'title' => 'นักนิติวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG3', 'code' => 'PT5PG3PN1', 'title' => 'นักวิชาการคอมพิวเตอร์', 'data_json' => ['note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN1', 'title' => 'นักจัดการงานทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN2', 'title' => 'นักทรัพยากรบุคคล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN3', 'title' => 'นิติกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN4', 'title' => 'นักวิเคราะห์นโยบายและแผน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN5', 'title' => 'นักเทคโนโลยีสารสนเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN6', 'title' => 'นักวิชาการพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN7', 'title' => 'นักวิชาการสถิติ', 'data_json' => ['note' => 'นักวิชาการสถิติ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN8', 'title' => 'นักวิเทศสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN9', 'title' => 'นักวิชาการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN10', 'title' => 'นักวิชาการตรวจสอบภายใน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN11', 'title' => 'นักประชาสัมพันธ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN12', 'title' => 'นักวิชาการเผยแพร่', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN13', 'title' => 'นักวิชาการโสตทัศนศึกษา', 'data_json' => ['note' => 'นักวิชาการโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN14', 'title' => 'นักวิชาการเกษตร', 'data_json' => ['note' => 'นักวิชาการเกษตร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN15', 'title' => 'วิศวกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN16', 'title' => 'บรรณารักษ์', 'data_json' => ['note' => 'บรรณารักษ์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN17', 'title' => 'นักวิชาการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG4', 'code' => 'PT5PG4PN18', 'title' => 'วิทยาจารย์', 'data_json' => ['note' => ''], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN1', 'title' => 'เจ้าพนักงานธุรการ', 'data_json' => ['note' => 'เจ้าพนักงานธุรการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN2', 'title' => 'เจ้าพนักงานพัสดุ', 'data_json' => ['note' => 'เจ้าพนักงานพัสดุ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN3', 'title' => 'เจ้าพนักงานเวชสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานเวชสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN4', 'title' => 'เจ้าพนักงานสถิติ', 'data_json' => ['note' => 'เจ้าพนักงานสถิติ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN5', 'title' => 'เจ้าพนักงานการเงินและบัญชี', 'data_json' => ['note' => 'เจ้าพนักงานการเงินและบัญชี เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN6', 'title' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ', 'data_json' => ['note' => 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN7', 'title' => 'เจ้าพนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'เจ้าพนักงานโสตทัศนศึกษา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN8', 'title' => 'เจ้าพนักงานการเกษตร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN9', 'title' => 'เจ้าพนักงานทันตสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานทันตสาธารณสุข เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN10', 'title' => 'เจ้าพนักงานเภสัชกรรม', 'data_json' => ['note' => 'เจ้าพนักงานเภสัชกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN11', 'title' => 'โภชนากร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN12', 'title' => 'เจ้าพนักงานรังสีการแพทย', 'data_json' => ['note' => 'เจ้าพนักงานรังสีการแพทย์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN13', 'title' => 'เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN14', 'title' => 'เจ้าพนักงานสาธารณสุข', 'data_json' => ['note' => 'เจ้าพนักงานสาธารณสุขเ ริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN15', 'title' => 'เจ้าพนักงานอาชีวบำบัด', 'data_json' => ['note' => 'เจ้าพนักงานอาชีวบำบัด เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN16', 'title' => 'เจ้าพนักงานเวชกิจฉุกเฉิน', 'data_json' => ['note' => 'เจ้าพนักงานเวชกิจฉุกเฉิน เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN17', 'title' => 'เจ้าพนักงานการแพทย์แผนไทย', 'data_json' => ['note' => 'เจ้าพนักงานการแพทย์แผนไทย เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN18', 'title' => 'นายช่างศิลป์', 'data_json' => ['note' => 'นายช่างศิลป์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN19', 'title' => 'ช่างกายอุปกรณ์', 'data_json' => ['note' => 'ช่างกายอุปกรณ์ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN20', 'title' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์', 'data_json' => ['note' => 'เจ้าพนักงานเครื่องคอมพิวเตอร์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN21', 'title' => 'ช่างทันตกรรม', 'data_json' => ['note' => 'ช่างทันตกรรม เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN22', 'title' => 'นายช่างเทคนิค', 'data_json' => ['note' => 'นายช่างเทคนิค เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN23', 'title' => 'นายช่างไฟฟ้า', 'data_json' => ['note' => 'นายช่างไฟฟ้า เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN24', 'title' => 'นายช่างโยธา', 'data_json' => ['note' => 'นายช่างโยธา เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN25', 'title' => 'ครูการศึกษาพิเศษ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG5', 'code' => 'PT5PG5PN26', 'title' => 'เจ้าพนักงานห้องสมุด', 'data_json' => ['note' => 'เจ้าพนักงานห้องสมุด เริ่มใช้ 24/12/2562'], 'active' => 1]);

        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN1', 'title' => 'พนักงานประจำตึก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN2', 'title' => 'พนักงานเปล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN3', 'title' => 'พนักงานซักฟอก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN4', 'title' => 'พนักงานบริการ', 'data_json' => ['note' => 'พนักงานบริการ เริ่มใช้ 24/12/2562'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN5', 'title' => 'พนักงานรับโทรศัพท์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN6', 'title' => 'พนักงานเกษตรพื้นฐาน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN7', 'title' => 'พนักงานเรือยนต', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN8', 'title' => 'พนักงานบริการเอกสารทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN9', 'title' => 'พนักงานเก็บเอกสาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN10', 'title' => 'พนักงานบริการสื่ออุปกรณ์การสอน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN11', 'title' => 'พนักงานเก็บเงิน', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN12', 'title' => 'พนักงานโสตทัศนศึกษา', 'data_json' => ['note' => 'พนักงานโสตทัศนศึกษา เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN13', 'title' => 'พนักงานผลิตน้ำประปา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN14', 'title' => 'พนักงานการเงินและบัญชี', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN15', 'title' => 'พนักงานพัสดุ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN16', 'title' => 'พนักงานธุรการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN17', 'title' => 'พนักงานพิมพ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN18', 'title' => 'พนักงานประเมินผล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN19', 'title' => 'พนักงานการศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN20', 'title' => 'พนักงานห้องสมุด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN21', 'title' => 'พนักงานสื่อสาร', 'data_json' => ['note' => 'พนักงานสื่อสาร เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN22', 'title' => 'ล่ามภาษาต่างประเทศ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN23', 'title' => 'ครูพี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN24', 'title' => 'พี่เลี้ยง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN25', 'title' => 'พนักงานช่วยการพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN26', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['note' => 'พนักงานช่วยเหลือคนไข้ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN27', 'title' => 'ผู้ช่วยพยาบาล', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN28', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['note' => 'ผู้ช่วยทันตแพทย์ เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN29', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN30', 'title' => 'พนักงานประจำห้องยา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN31', 'title' => 'ผู้ช่วยพนักงานสุขศึกษา', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN32', 'title' => 'ผู้ช่วยเจ้าหน้าที่อนามัย', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN33', 'title' => 'ผู้ช่วยเจ้าหน้าที่สาธารณสุข', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN34', 'title' => 'พนักงานการแพทย์และรังสีเทคนิค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN35', 'title' => 'พนักงานจุลทัศนกร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN36', 'title' => 'พนักงานประกอบอาหาร', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN37', 'title' => 'พนักงานห้องผ่าตัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN38', 'title' => 'พนักงานผ่าและรักษาศพ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN39', 'title' => 'พนักงานบัตรรายงานโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN40', 'title' => 'พนักงานปฏิบัติการทดลองพาหะนำโรค', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN41', 'title' => 'ผู้ช่วยนักกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN42', 'title' => 'พนักงานกู้ชีพ', 'data_json' => ['note' => 'พนักงานกู้ชีพเริ่มใช้ 9/06/2566'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN43', 'title' => 'พนักงานประจำห้องทดลอง', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN44', 'title' => 'พนักงานวิทยาศาสตร์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN45', 'title' => 'พนักงานพิธีสงฆ์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN46', 'title' => 'ช่างไฟฟ้าและอิเล็กทรอนิกส์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN47', 'title' => 'ช่างเหล็ก', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN48', 'title' => 'ช่างฝีมือทั่วไป', 'data_json' => ['note' => 'ช่างฝีมือทั่วไป เริ่มใช้ 31/05/2560'], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN49', 'title' => 'ช่างต่อท่อ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN50', 'title' => 'ช่างศิลป์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN51', 'title' => 'ช่างตัดเย็บผ้า', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN52', 'title' => 'ช่างตัดผม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN53', 'title' => 'ช่างซ่อมเครื่องทำความเย็น', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN54', 'title' => 'ช่างเครื่องช่วยคนพิการ', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN55', 'title' => 'ผู้ช่วยช่างทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT5PG6', 'code' => 'PT5PG6PN56', 'title' => 'พนักงานขับรถยนต์', 'data_json' => ['note' => ''], 'active' => 1]);

        // PT6 กลุ่มลูกจ้างประจำ ================================= (ดำเนินการเรียบร้อย)
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT6', 'code' => 'PT6PG1', 'title' => 'วิชาชีพเฉพาะ ก', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT6', 'code' => 'PT6PG2', 'title' => 'วิชาชีพเฉพาะ ข', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT6', 'code' => 'PT6PG3', 'title' => 'บริการ', 'active' => 1]);
        // **** */ ตำแหน่งลูกจ้างประจำ
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT6PG3', 'code' => 'PT6PG3PN1', 'title' => 'พนักงานกายภาพบำบัด', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT6PG3', 'code' => 'PT6PG3PN2', 'title' => 'พนักงานช่วยเหลือคนไข้', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT6PG3', 'code' => 'PT6PG3PN3', 'title' => 'ผู้ช่วยทันตแพทย์', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT6PG3', 'code' => 'PT6PG3PN4', 'title' => 'พนักงานเภสัชกรรม', 'data_json' => ['note' => ''], 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT6PG3', 'code' => 'PT6PG3PN5', 'title' => 'พนักงานธุรการ', 'data_json' => ['note' => ''], 'active' => 1]);

        // PT7 จ้างเหมาบริการรายวัน ============================= (ดำเนินการเรียบร้อย)
        $this->insert('categorise', ['name' => 'position_group', 'category_id' => 'PT7', 'code' => 'PT7PG1', 'title' => 'บริการ', 'active' => 1]);
        $this->insert('categorise', ['name' => 'position_name', 'category_id' => 'PT7PG1', 'code' => 'PT7PG1PN1', 'title' => 'พนักงานทั่วไป', 'data_json' => ['note' => ''], 'active' => 1]);

        // ตำแหน่งลูกจ้างประจำ

        // ครอบครัว
        $this->insert('categorise', ['code' => 1, 'name' => 'family_relation', 'title' => 'บิดา']);
        $this->insert('categorise', ['code' => 2, 'name' => 'family_relation', 'title' => 'มารดา']);
        $this->insert('categorise', ['code' => 3, 'name' => 'family_relation', 'title' => 'สามี']);
        $this->insert('categorise', ['code' => 4, 'name' => 'family_relation', 'title' => 'ภรรยา']);
        $this->insert('categorise', ['code' => 5, 'name' => 'family_relation', 'title' => 'บุตรชาย']);
        $this->insert('categorise', ['code' => 6, 'name' => 'family_relation', 'title' => 'บุตรสาว']);
        $this->insert('categorise', ['code' => 7, 'name' => 'family_relation', 'title' => 'พี่ชาย']);
        $this->insert('categorise', ['code' => 8, 'name' => 'family_relation', 'title' => 'พี่สาว']);

        // เพศ
        $this->insert('categorise', ['name' => 'gender', 'title' => 'ชาย']);
        $this->insert('categorise', ['name' => 'gender', 'title' => 'หญิง']);

        // กรุ๊บเลือด
        $this->insert('categorise', ['code' => 'A', 'name' => 'blood', 'title' => 'A']);
        $this->insert('categorise', ['code' => 'B', 'name' => 'blood', 'title' => 'B']);
        $this->insert('categorise', ['code' => 'AB', 'name' => 'blood', 'title' => 'AB']);
        $this->insert('categorise', ['code' => 'O', 'name' => 'blood', 'title' => 'O']);

        //  คำนำหน้าชื่อ
        $this->insert('categorise', ['name' => 'prefix_th', 'title' => 'นาย']);
        $this->insert('categorise', ['name' => 'prefix_th', 'title' => 'นาง']);
        $this->insert('categorise', ['name' => 'prefix_th', 'title' => 'น.ส.']);
        $this->insert('categorise', ['name' => 'prefix_en', 'title' => 'Mr.']);
        $this->insert('categorise', ['name' => 'prefix_en', 'title' => 'Mrs.']);
        $this->insert('categorise', ['name' => 'prefix_en', 'title' => 'Ms.']);

        // สถานะ
        $this->insert('categorise', ['code' => 1, 'name' => 'marry', 'title' => 'โสด']);
        $this->insert('categorise', ['code' => 2, 'name' => 'marry', 'title' => 'สมรส']);
        $this->insert('categorise', ['code' => 3, 'name' => 'marry', 'title' => 'หม้าย/หย่าร้าง']);

        // ศาสนา
        $this->insert('categorise', ['code' => 1, 'name' => 'religion', 'title' => 'พุทธ']);
        $this->insert('categorise', ['code' => 2, 'name' => 'religion', 'title' => 'คริศต์']);
        $this->insert('categorise', ['code' => 3, 'name' => 'religion', 'title' => 'อิสลาม']);
        $this->insert('categorise', ['code' => 4, 'name' => 'religion', 'title' => 'ฮินดู']);

        // สัญชาติ
        $this->insert('categorise', ['code' => 1, 'name' => 'nationality', 'title' => 'ไทย']);
        $this->insert('categorise', ['code' => 2, 'name' => 'nationality', 'title' => 'ลาว']);
        $this->insert('categorise', ['code' => 3, 'name' => 'nationality', 'title' => 'จีน']);
        $this->insert('categorise', ['code' => 4, 'name' => 'nationality', 'title' => 'อเมริกัน']);
        $this->insert('categorise', ['code' => 5, 'name' => 'nationality', 'title' => 'พม่า']);
        $this->insert('categorise', ['code' => 6, 'name' => 'nationality', 'title' => 'กัมพูชา']);
        $this->insert('categorise', ['code' => 7, 'name' => 'nationality', 'title' => 'อินโดนีเซีย']);
        $this->insert('categorise', ['code' => 8, 'name' => 'nationality', 'title' => 'ฟิลิปปินส์']);
        $this->insert('categorise', ['code' => 9, 'name' => 'nationality', 'title' => 'มาเลเซีย']);

        // สถานะของพนักงาน
        $this->insert('categorise', ['code' => 1, 'name' => 'emp_status', 'title' => 'ปฏิบัติราชการ']);
        $this->insert('categorise', ['code' => 2, 'name' => 'emp_status', 'title' => 'ลาออก']);
        $this->insert('categorise', ['code' => 3, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการ']);
        $this->insert('categorise', ['code' => 4, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการรับบำนาญ']);
        $this->insert('categorise', ['code' => 5, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการรับบำเหน็จ']);
        $this->insert('categorise', ['code' => 6, 'name' => 'emp_status', 'title' => 'เกษียณอายุราชการรับบำเหน็จรายเดือน']);
        $this->insert('categorise', ['code' => 7, 'name' => 'emp_status', 'title' => 'ถึงแก่กรรม']);
        $this->insert('categorise', ['code' => 8, 'name' => 'emp_status', 'title' => 'ถึงแก่กรรมรับบำเหน็จ']);
        $this->insert('categorise', ['code' => 9, 'name' => 'emp_status', 'title' => 'ปลดออก']);
        $this->insert('categorise', ['code' => 10, 'name' => 'emp_status', 'title' => 'ปลดออกรับบำเหน็จ']);
        $this->insert('categorise', ['code' => 11, 'name' => 'emp_status', 'title' => 'ไปปฏิบัติการวิจัย ณ ต่างประเทศ']);
        $this->insert('categorise', ['code' => 12, 'name' => 'emp_status', 'title' => 'ไปปฏิบัติการวิจัยในประเทศ']);
        $this->insert('categorise', ['code' => 13, 'name' => 'emp_status', 'title' => 'ย้าย']);
        $this->insert('categorise', ['code' => 14, 'name' => 'emp_status', 'title' => 'ไปปฏิบัติงานเพื่อเพิ่มพูนความรู้ทางวิชาการ']);
        $this->insert('categorise', ['code' => 15, 'name' => 'emp_status', 'title' => 'ไปราชการ ณ ต่างประเทศ']);
        $this->insert('categorise', ['code' => 16, 'name' => 'emp_status', 'title' => 'ไปราชการภายในประเทศ']);
        $this->insert('categorise', ['code' => 17, 'name' => 'emp_status', 'title' => 'ฝึกอบรม ณ ต่างประเทศ']);
        $this->insert('categorise', ['code' => 18, 'name' => 'emp_status', 'title' => 'ฝึกอบรมในประเทศ']);
        $this->insert('categorise', ['code' => 19, 'name' => 'emp_status', 'title' => 'ยกเลิกคำสั่งบรรจุ']);
        $this->insert('categorise', ['code' => 20, 'name' => 'emp_status', 'title' => 'ลาศึกษา ณ ต่างประเทศ']);
        $this->insert('categorise', ['code' => 21, 'name' => 'emp_status', 'title' => 'ลาศึกษาในประเทศ']);
        $this->insert('categorise', ['code' => 22, 'name' => 'emp_status', 'title' => 'ลาออกรับบำนาญ']);
        $this->insert('categorise', ['code' => 23, 'name' => 'emp_status', 'title' => 'ลาออกรับบำเหน็จ']);
        $this->insert('categorise', ['code' => 24, 'name' => 'emp_status', 'title' => 'เลิกจ้าง']);
        $this->insert('categorise', ['code' => 25, 'name' => 'emp_status', 'title' => 'ไล่ออก']);
        $this->insert('categorise', ['code' => 26, 'name' => 'emp_status', 'title' => 'หมดสัญญาจ้าง']);
        $this->insert('categorise', ['code' => 27, 'name' => 'emp_status', 'title' => 'ให้ออก']);
        $this->insert('categorise', ['code' => 28, 'name' => 'emp_status', 'title' => 'ให้ออกจากราชการ']);
        $this->insert('categorise', ['code' => 29, 'name' => 'emp_status', 'title' => 'ให้ออกรับบำนาญ']);
        $this->insert('categorise', ['code' => 30, 'name' => 'emp_status', 'title' => 'ให้ออกรับบำเหน็จ']);
        $this->insert('categorise', ['code' => 31, 'name' => 'emp_status', 'title' => 'ให้โอน']);
        $this->insert('categorise', ['code' => 32, 'name' => 'emp_status', 'title' => 'ไปฏิบัติงานในองค์การระหว่างประเทศ']);
        $this->insert('categorise', ['code' => 33, 'name' => 'emp_status', 'title' => 'ข้าราชการบำนาญถึงแก่กรรม']);
        $this->insert('categorise', ['code' => 34, 'name' => 'emp_status', 'title' => 'ลาออกรับบำเหน็จรายเดือน']);
        $this->insert('categorise', ['code' => 35, 'name' => 'emp_status', 'title' => 'ข้าราชการบำเหน็จถึงแก่กรรม']);
        $this->insert('categorise', ['code' => 36, 'name' => 'emp_status', 'title' => 'ลูกจ้างประจำบำเหน็จรายเดือน']);
        $this->insert('categorise', ['code' => 37, 'name' => 'emp_status', 'title' => 'ลูกจ้างประจำบำเหน็จรายเดือนถึงแก่กรรม']);

        // ประเภทการเปลี่ยนชื่อ
        $this->insert('categorise', ['code' => 1, 'name' => 'rename_type', 'title' => 'เปลี่ยนคำนำหน้า']);
        $this->insert('categorise', ['code' => 2, 'name' => 'rename_type', 'title' => 'เปลี่ยนชื่อ']);
        $this->insert('categorise', ['code' => 3, 'name' => 'rename_type', 'title' => 'เปลี่ยนสกุล']);
        $this->insert('categorise', ['code' => 4, 'name' => 'rename_type', 'title' => 'เปลี่ยนคำนำหน้าและสกุล']);
        $this->insert('categorise', ['code' => 5, 'name' => 'rename_type', 'title' => 'เปลี่ยนชื่อและเปลี่ยนสกุล']);
        $this->insert('categorise', ['code' => 6, 'name' => 'rename_type', 'title' => 'เปลี่ยนคำนำหน้าเปลี่ยนชื่อและเปลี่ยนสกุล']);

        // รายการ สัมมนา ฝึกอบรม ดูงาน ศึกษาต่อ และข้อมูลรายงาน
        $this->insert('categorise', ['code' => 1, 'name' => 'develop', 'title' => 'ประชุม']);
        $this->insert('categorise', ['code' => 2, 'name' => 'develop', 'title' => 'ประชุมวิชาการ']);
        $this->insert('categorise', ['code' => 3, 'name' => 'develop', 'title' => 'ประชุมเชิงปฏิบัติการ']);
        $this->insert('categorise', ['code' => 4, 'name' => 'develop', 'title' => 'ศึกษาดูงาน']);
        $this->insert('categorise', ['code' => 5, 'name' => 'develop', 'title' => 'สัมมนา']);

        // ประเภทของเงิน
        $this->insert('categorise', ['code' => 1, 'name' => 'money_type', 'title' => 'งบประมาณแผ่นดิน']);
        $this->insert('categorise', ['code' => 2, 'name' => 'money_type', 'title' => 'เงินรายได้']);

        // ลักษณะการไป
        $this->insert('categorise', ['code' => 1, 'name' => 'followby', 'title' => 'ได้รับเชิญ']);
        $this->insert('categorise', ['code' => 2, 'name' => 'followby', 'title' => 'คณะ/หน่วยงานส่งเข้าร่วมเป็นตัวแทน']);
        $this->insert('categorise', ['code' => 3, 'name' => 'followby', 'title' => 'เจ้าตัวสมัครไป']);
        // กำหนด FSN

        $this->insert('categorise', ['code' => '00', 'name' => 'vendor', 'title' => 'บริษัทตัวอย่างทดสอบ']);

        // ประเภททรัพย์สิน
        $this->insert('categorise', ['category_id' => '2', 'code' => '1', 'name' => 'asset_type', 'title' => 'อาคารถาวร', 'data_json' => ['depreciation' => '4', 'service_life' => '25'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '2', 'code' => '2.1', 'name' => 'asset_type', 'title' => 'สิ่งปลูกสร้าง อาคารชั่วคราว/โรงเรือน', 'data_json' => ['depreciation' => '10', 'service_life' => '10'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '2', 'code' => '2.2', 'name' => 'asset_type', 'title' => 'สิ่งปลูกสร้าง ใช้คอนกรีตเสริมเหล็กหรือโครงเหล็กเป็นส่วนประกอบหลัก', 'data_json' => ['depreciation' => '6.66', 'service_life' => '15'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '2', 'code' => '2.3', 'name' => 'asset_type', 'title' => 'สิ่งปลูกสร้าง ใช้ไม้หรือวัสดุอื่นๆ เป็นส่วนประกอบหลัก', 'data_json' => ['depreciation' => '20', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '3', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์สำนักงาน', 'data_json' => ['depreciation' => '33.33', 'service_life' => '3'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '4', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์ยานพาหนะและขนส่ง', 'data_json' => ['depreciation' => '20.00', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '5.1', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์ไฟฟ้าและวิทยุ ', 'data_json' => ['depreciation' => '20.00', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '5.2', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์ไฟฟ้าและวิทยุ (เครื่องกำเนิดไฟฟ้า)', 'data_json' => ['depreciation' => '6.5', 'service_life' => '15'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '6', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์โฆษณาและเผยแพร่', 'data_json' => ['depreciation' => '20.00', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '7.1', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์การเกษตร (เครื่องมือและอุปกรณ์)', 'data_json' => ['depreciation' => '50.00', 'service_life' => '2'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '7.2', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์การเกษตร (เครื่องจักรกล)', 'data_json' => ['depreciation' => '50', 'service_life' => '2'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '8.1', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์โรงงาน (เครื่องมือและอุปกรณ์)', 'data_json' => ['depreciation' => '50.00', 'service_life' => '2'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '8.2', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์โรงงาน (เครื่องจักรกล)', 'data_json' => ['depreciation' => '20', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '9.1', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์ก่อสร้าง (เครื่องมือและอุปกรณ์)', 'data_json' => ['depreciation' => '50', 'service_life' => '2'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '9.2', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์ก่อสร้าง (เครื่องจักกล)', 'data_json' => ['depreciation' => '20', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '10', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์สำรวจ', 'data_json' => ['depreciation' => '12.5', 'service_life' => '8'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '11', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์วิทยาศาสตร์และการแพทย์', 'data_json' => ['depreciation' => '20.00', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '12', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์คอมพิวเตอร์', 'data_json' => ['depreciation' => '33.33', 'service_life' => '3'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '13', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์การศึกษา', 'data_json' => ['depreciation' => '33.33', 'service_life' => '3'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '14', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์งานบ้านงานครัว', 'data_json' => ['depreciation' => '33.33', 'service_life' => '3'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '15', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์กีฬา/กายภาพ', 'data_json' => ['depreciation' => '20', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '16', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์ดนตรี/นาฎศิลป์', 'data_json' => ['depreciation' => '20.0', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '17', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์อาวุธ', 'data_json' => ['depreciation' => '10', 'service_life' => '10'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '3', 'code' => '18', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์สนาม', 'data_json' => ['depreciation' => '50', 'service_life' => '2'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '', 'code' => '19', 'name' => 'asset_type', 'title' => 'ครุภัณฑ์อื่นๆ', 'data_json' => ['depreciation' => '20', 'service_life' => '5'], 'active' => 1]);
        $this->insert('categorise', ['category_id' => '', 'code' => '20', 'name' => 'asset_type', 'title' => 'สินทรัพย์ไม่มีตัวตัน', 'data_json' => ['depreciation' => '33.3', 'service_life' => '3'], 'active' => 1]);

        // ประเถทวัสดุ
        $this->insert('categorise', ['category_id' => '4', 'code' => '1', 'name' => 'product_type', 'title' => 'วัสดุสำนักงาน', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '2', 'name' => 'product_type', 'title' => 'วัสดุไฟฟูาและวิทยุ', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '3', 'name' => 'product_type', 'title' => 'วัสดุงานบ้านงานครัว', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '4', 'name' => 'product_type', 'title' => 'วัสดุก่อสร้าง', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '5', 'name' => 'product_type', 'title' => 'วัสดุยานพาหนะและขนส่ง', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '6', 'name' => 'product_type', 'title' => 'วัสดุเชื้อเพลิงและหล่อลื่น', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '7', 'name' => 'product_type', 'title' => 'วัสดุวิทยาศาสตร์หรือการแพทย์', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '8', 'name' => 'product_type', 'title' => 'วัสดุการเกษตร', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '9', 'name' => 'product_type', 'title' => 'วัสดุโฆษณาและเผยแพร่', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '10', 'name' => 'product_type', 'title' => 'วัสดุเครื่องแต่งกาย', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '11', 'name' => 'product_type', 'title' => 'วัสดุกีฬา', 'active' => 1]);
        $this->insert('categorise', ['category_id' => '4', 'code' => '12', 'name' => 'product_type', 'title' => 'วัสดุคอมพิวเตอร์', 'active' => 1]);

        // Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_type.sql'));
        // Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_group.sql'));
        // Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_name.sql'));

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
