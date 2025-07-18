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
        $this->createTable('{{%orders}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'name' => $this->string(255)->comment('ชื่อตารางเก็บข้อมูล'),
            'group_id' => $this->string(255)->comment('หมวดหมูหลักที่เก็บ'),
            'category_id' => $this->string(255)->comment('Key หลักของ order หลัก'),
            'asset_type' => $this->string(255)->comment('ประภทของรายการ'),
            'asset_item' => $this->string(255)->comment('รายการที่เก็บ'),
            'vendor_id' => $this->string(255)->comment('ผู้ขาย'),
            'code' => $this->string(255)->comment('รหัส'),
            'pr_number' => $this->string(255)->comment('เลขที่ขอซื้อ'),
            'pq_number' => $this->string(255)->comment('เลขทะเบียนคุม'),
            'po_number' => $this->string(255)->comment('ที่ที่สั่งซื้อ'),
            'gr_number' => $this->string(255)->comment('เลขที่รับสินค้า'),
            'price' => $this->double(255)->comment('ราคา'),
            'discount_price' => $this->double(255)->comment('ราคาส่ววนลด'),
            'qty' => $this->float(255)->comment('จำนวน'),
            'to_stock' => $this->integer(255)->comment('จำนวนที่รับเข้าคลังแล้ว'),
            'status' => $this->integer(255)->comment('สถานะ'),
            'approve' => $this->string(1)->comment('อนุมัติ'),
            'data_json' => $this->json(),
            'thai_year' => $this->integer(255)->comment('ปีงบประมาณ'),
            'created_at' => $this->dateTime()->comment('วันที่สร้าง'),
            'updated_at' => $this->dateTime()->comment('วันที่แก้ไข'),
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข'),
            'deleted_at' => $this->dateTime()->comment('วันที่ลบ'),
            'deleted_by' => $this->integer()->comment('ผู้ลบ')
        ]);

        // เงื่อนไขการจัดซื้อ
        $sqlPurchaseCondition = Yii::$app->db->createCommand("select * from categorise where name = 'purchase_condition'")->queryAll();
        if (count($sqlPurchaseCondition) < 1) {
            // คณะกรรมการ
            $this->insert('categorise', ['category_id' => '', 'code' => '1', 'name' => 'purchase_condition', 'title' => 'ก.ดำเนินการด้วยวิธีประกาศเชิญชวนทั่วไปและวิธีคัดเลือก', 'data_json' => ['comment' => '-'], 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '2', 'name' => 'purchase_condition', 'title' => 'ข.ไม่เกินวงเงินที่กำหนดในกฏกระทรวง', 'data_json' => ['comment' => 'เนื่องจากการจัดซื้อจัดจ้างพัสดุ ที่มีการผลิต จำหน่าย ก่อสร้าง หรือให้บริการทั่วไป และมีวงเงินในการจัดซื้อจัดจ้างบครั้งหนึ่งไม่เกินวงเงินตามที่กำหนดในกฏกระทรวง'], 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '3', 'name' => 'purchase_condition', 'title' => 'ค.มีผู้ประกอบการที่มีคุณสมบัติเพียงรายเดียว', 'data_json' => ['comment' => '-'], 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '4', 'name' => 'purchase_condition', 'title' => 'ง.มีความจำเป็นต้องใช้พัสดุโดยฉุกเฉิน', 'data_json' => ['comment' => '-'], 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '5', 'name' => 'purchase_condition', 'title' => 'จ.เกี่ยวพันกับพัสดุที่ชื่อไว้ก่อนหน้า', 'data_json' => ['comment' => '-'], 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '6', 'name' => 'purchase_condition', 'title' => 'ฉ.เป็นพัสดุจะขายทอดตลาดโดยหน่วยงานของรัฐ', 'data_json' => ['comment' => '-'], 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '7', 'name' => 'purchase_condition', 'title' => 'ช.ที่ดิน/สิ่งปลูกสร้างที่ต้องซื้อเฉพาะแห่ง', 'data_json' => ['comment' => '-'], 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '8', 'name' => 'purchase_condition', 'title' => 'ซ.กรณีอื่นตามที่กำหนดในกฏกระทรวง', 'data_json' => ['comment' => '-'], 'active' => 1]);
        }

        $sqlBoardStatus = Yii::$app->db->createCommand("select * from categorise where name = 'committee'")->queryAll();
        if (count($sqlBoardStatus) < 1) {
            // คณะกรรมการ
            $this->insert('categorise', ['category_id' => '', 'code' => '1', 'name' => 'committee', 'title' => 'ประธานกรรมการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '2', 'name' => 'committee', 'title' => 'กรรมการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '3', 'name' => 'committee', 'title' => 'กรรมการและเลขานุการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '4', 'name' => 'committee', 'title' => 'เลขานุการ', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '5', 'name' => 'committee', 'title' => 'ผู้ควบคุมงาน', 'active' => 1]);
            $this->insert('categorise', ['category_id' => '', 'code' => '6', 'name' => 'committee', 'title' => 'ผู้ตรวจรับพัสดุหรืองานจ้าง', 'active' => 1]);
        }

        $sqlOrderStatus = Yii::$app->db->createCommand("select * from categorise where name = 'order_status'")->queryAll();
        if (count($sqlOrderStatus) < 1) {
            // สถานะคำสั่งซื้อ
            $this->insert('categorise', ['category_id' => '', 'code' => '1', 'name' => 'order_status', 'title' => 'ขอซื้อ-ขอจ้าง(PR)', 'active' => 1,'data_json' => ['color' => 'danger']]);
            $this->insert('categorise', ['category_id' => '', 'code' => '2', 'name' => 'order_status', 'title' => 'ผอ.อนุมัติ', 'active' => 1,'data_json' => ['color' => 'warning']]);
            $this->insert('categorise', ['category_id' => '', 'code' => '3', 'name' => 'order_status', 'title' => 'ลงทะเบียนคุม', 'active' => 1,'data_json' => ['color' => 'warning']]);
            $this->insert('categorise', ['category_id' => '', 'code' => '4', 'name' => 'order_status', 'title' => 'ออกใบสั่งซื้อ', 'active' => 1,'data_json' => ['color' => 'primary']]);
            $this->insert('categorise', ['category_id' => '', 'code' => '5', 'name' => 'order_status', 'title' => 'ตรวจรับวัสดุ', 'active' => 1,'data_json' => ['color' => 'info']]);
            $this->insert('categorise', ['category_id' => '', 'code' => '6', 'name' => 'order_status', 'title' => 'วัสดุเข้าคลัง', 'active' => 1,'data_json' => ['color' => 'info']]);
            $this->insert('categorise', ['category_id' => '', 'code' => '7', 'name' => 'order_status', 'title' => 'ส่งบัญชี', 'active' => 1,'data_json' => ['color' => 'success']]);
            $this->insert('categorise', ['category_id' => '', 'code' => '8', 'name' => 'order_status', 'title' => 'ยกเลิก', 'active' => 1,'data_json' => ['color' => 'secondary']]);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%orders}}');
    }
}
