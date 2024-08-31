<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%helpdesk}}`.
 */
class m240510_123044_create_helpdesk_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%helpdesk}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'code' => $this->string(255),
            'date_start' => $this->date(),
            'date_end' => $this->date(),
            'name' => $this->string(255)->comment('ชื่อการเก็บข้อมูล'),
            'title' => $this->string(255)->comment('รายการ'),
            'data_json' => $this->json()->comment('การเก็บข้อมูลชนิด JSON'),
            'status' => $this->string(255)->comment('สถานะ'),
            'rating' => $this->string(255)->comment('คะแนน'),
            'move_out' => $this->boolean()->comment('จำหน่าย'),
            'repair_group' => $this->string(255)->comment('หน่วยงานที่ส่งซ่่อม'),
            // 'thai_year' => $this->integer(4)->comment('ปีงบประมาณ'),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);

        $sqlUrgency = Yii::$app->db->createCommand("select * from categorise where name = 'urgency'")->queryAll();
        if (count($sqlUrgency) < 1) {
            // ความเร่งด่วน
            $this->insert('categorise', ['category_id' => '', 'code' => '1', 'name' => 'urgency', 'title' => 'ปกติ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '2', 'name' => 'urgency', 'title' => 'ด่วน', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '3', 'name' => 'urgency', 'title' => 'ด่วนมาก', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '4', 'name' => 'urgency', 'title' => 'ด่วนที่สุด', 'active' => 1]);
        }
        $sqlStatus = Yii::$app->db->createCommand("select * from categorise where name = 'repair_status'")->queryAll();
        if (count($sqlStatus) < 1) {
            // สถานะงานซ่อม
            $this->insert('categorise', ['category_id' => '', 'code' => '1', 'name' => 'repair_status', 'title' => 'ร้องขอ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '2', 'name' => 'repair_status', 'title' => 'รับเรื่อง', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '3', 'name' => 'repair_status', 'title' => 'ดำเนินการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '4', 'name' => 'repair_status', 'title' => 'เสร็จสิ้น', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '5', 'name' => 'repair_status', 'title' => 'ยกเลิก', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '6', 'name' => 'repair_status', 'title' => 'จำหน่าย', 'active' => 1]);
        }
        $sqlSendType = Yii::$app->db->createCommand("select * from categorise where name = 'send_type'")->queryAll();
        if (count($sqlSendType) < 1) {
            // ประเภทการแจ้งซ่อม
            $this->insert('categorise', ['category_id' => '', 'code' => 'general', 'name' => 'send_type', 'title' => 'ซ่อมทั่วไป', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 'asset', 'name' => 'send_type', 'title' => 'ซ่อมครุภัณฑ์', 'active' => 1]);
        }
        $sqlRating = Yii::$app->db->createCommand("select * from categorise where name = 'rating'")->queryAll();
        if (count($sqlRating) < 1) {
            // การให้คะแนน
            $this->insert('categorise', ['category_id' => '', 'code' => 1, 'name' => 'rating', 'title' => 'ควรปรับปรุง', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 2, 'name' => 'rating', 'title' => 'พอใช้', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 3, 'name' => 'rating', 'title' => 'ปานกลาง', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 4, 'name' => 'rating', 'title' => 'ดี', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 5, 'name' => 'rating', 'title' => 'ดีมาก', 'active' => 1]);
        }

        $RepairGroupWork = Yii::$app->db->createCommand("select * from categorise where name = 'repair_group'")->queryAll();
        if (count($RepairGroupWork) < 1) {
            // หน่วยงานซ่อม
            $this->insert('categorise', ['category_id' => '', 'code' => 1, 'name' => 'repair_group', 'title' => 'งานซ่อมบำรุง', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 2, 'name' => 'repair_group', 'title' => 'ศูนย์คอมพิวเตอร์', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 3, 'name' => 'repair_group', 'title' => 'ศูนย์เครื่องมือแพทย์', 'active' => 1]);
        }

        $LineGroup = Yii::$app->db->createCommand("select * from categorise where name = 'line_group'")->queryAll();
        if (count($LineGroup) < 1) {
            // หน่วยงานซ่อม
            $this->insert('categorise', ['category_id' => '', 'code' => 1, 'name' => 'line_group', 'title' => 'งานซ่อมบำรุง', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 2, 'name' => 'line_group', 'title' => 'ศูนย์คอมพิวเตอร์', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 3, 'name' => 'line_group', 'title' => 'ศูนย์เครื่องมือแพทย์', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 4, 'name' => 'line_group', 'title' => 'ระบบลา', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 5, 'name' => 'line_group', 'title' => 'ระบบความเสี่ยง', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 6, 'name' => 'line_group', 'title' => 'ระบบห้องประชุม', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 7, 'name' => 'line_group', 'title' => 'ระบบขอใช้รถยนต์', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 8, 'name' => 'line_group', 'title' => 'ระบบจองรถพยาบาล', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 9, 'name' => 'line_group', 'title' => 'ระบบ รปภ.', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => 10, 'name' => 'line_group', 'title' => 'ระบบบ้านพัก', 'active' => 1]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%helpdesk}}');
    }
}
