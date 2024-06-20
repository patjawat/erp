<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m240522_073153_create_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'name' => $this->string(255)->comment('ชื่อตารางเก็บข้อมูล'),
            'category_id' => $this->string(255)->comment('หมวดหมูหลักที่เก็บ'),
            'code' => $this->string(255)->comment('รหัส'),
            'pr_number' => $this->string(255)->comment('เลขที่ขอซื้อ'),
            'pq_number' => $this->string(255)->comment('เลขทะเบียนคุม'),
            'po_number' => $this->string(255)->comment('ที่ที่สั่งซื้อ'),
            'item_id' => $this->integer(255)->comment('รายการที่เก็บ'),
            'price' => $this->double(255)->comment('ราคา'),
            'amount' => $this->integer(255)->comment('จำนวน'),
            'status' => $this->integer(255)->comment('สถานะ'),
            'approve' => $this->string(1)->comment('อนุมัติ'),
            'data_json' => $this->json(),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);

        $sqlBoardStatus = Yii::$app->db->createCommand("select * from categorise where name = 'board'")->queryAll();
        if (count($sqlBoardStatus) < 1) {
            // คณะกรรมการ
            $this->insert('categorise', ['category_id' => '', 'code' => '1', 'name' => 'board', 'title' => 'ประธานกรรมการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '2', 'name' => 'board', 'title' => 'กรรมการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '3', 'name' => 'board', 'title' => 'กรรมการและเลขานุการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '4', 'name' => 'board', 'title' => 'เลขานุการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '5', 'name' => 'board', 'title' => 'ผู้ควบคุมงาน', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '6', 'name' => 'board', 'title' => 'ผู้ตรวจรับพัสดุหรืองานจ้าง', 'active' => 1]);
        }

        $sqlOrderStatus = Yii::$app->db->createCommand("select * from categorise where name = 'order_status'")->queryAll();
        if (count($sqlOrderStatus) < 1) {
            // สถานะคำสั่งซื้อ
            $this->insert('categorise', ['category_id' => '', 'code' => '1', 'name' => 'order_status', 'title' => 'ขอซื้อ-ขอจ้าง(PR)', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '2', 'name' => 'order_status', 'title' => 'รอเห็นชอบ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '3', 'name' => 'order_status', 'title' => 'พัสดุตรวจสอบ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '4', 'name' => 'order_status', 'title' => 'ผู้อำนวยการตรวจสอบ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '5', 'name' => 'order_status', 'title' => 'ลงทะเบียนคุม', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '6', 'name' => 'order_status', 'title' => 'ออกใบสั่งซื้อ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '7', 'name' => 'order_status', 'title' => 'ตรวจรับวัสดุ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '8', 'name' => 'order_status', 'title' => 'ยืนยันตรวจรับ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '9', 'name' => 'order_status', 'title' => 'วัสดุเข้าคลัง', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '10', 'name' => 'order_status', 'title' => 'ส่งบัญชี', 'active' => 1]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%order}}');
    }
}
