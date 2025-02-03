<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%asset_detail}}`.
 */
class m231203_123323_create_asset_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'asset_group' => $this->string(255)->comment('แยกประเภทพัสดุ/ครุภัณฑ์'),
            'asset_item' => $this->string(255)->comment('หมายเลขรายการ'),
            'license_plate' => $this->string()->comment('เลขทะเบียน (ถ้าเป็นรถยนต์)'),
            'car_type' => $this->string()->comment('ประเภทของรถ general หรือ ambulance'),
            'code' => $this->string(255)->comment('หมายเลขครุภัณฑ์'),
            'order_number' => $this->string(255)->comment('หมายเลขใบสั่งซื้อ'),
            'receive_date' => $this->date()->comment('วันที่รับเข้า'),
            'price' => $this->double()->comment('ราคา'),
            'purchase' => $this->integer()->comment('การจัดซื้อ'),
            'method_get' => $this->double()->comment('วิธีได้มา'),
            'life' => $this->integer()->comment('อายุการใช้งาน'),
            'department' => $this->integer()->comment('ประจำอยู่หน่วยงาน'),
            'owner' => $this->string(255)->comment('ผู้รับผิดชอบ'),
            'depre_type' => $this->integer()->comment('ประเภทค่าเสื่อมราคา'),
            'on_year' => $this->integer()->comment('งบประมาณ'),
            'budget_type' => $this->integer()->comment('ประเภทเงิน'),
            'asset_status' => $this->string(255)->comment('สถานะ'),
            'data_json' => $this->json(),
            'device_items' => $this->json()->comment('ครุภัณฑ์ภายใน'),
            'updated_at' => $this->dateTime()->comment('วันเวลาแก้ไข'),
            'created_at' => $this->dateTime()->comment('วันเวลาสร้าง'), 
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%asset}}');
    }
}
