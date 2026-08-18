<?php

use yii\db\Migration;

/**
 * แผนวัสดุประจำปีของงานพัสดุ — ฉบับที่ปิดค่าแล้วเพื่อส่ง สสจ.
 *
 * หน้าคำนวณเดิม (/inventory-v2/material-plan) คิดสดทุกครั้ง ตัวเลขจึงขยับตามวันที่กด
 * ตารางนี้เก็บผลที่พัสดุตัดสินใจแล้วไว้เป็นฉบับอ้างอิง ทั้งของเอกสารที่ส่งออกไป
 * และของหน่วยงานที่ดึงอัตราเผื่อไปใช้ตั้งงบ
 */
class m260818_100000_create_material_plan extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%material_plan}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null()->comment('token ต่อ record (ใช้ผูกไฟล์ในระบบ filemanager)'),
            'fiscal_year' => $this->integer()->notNull()->comment('ปีงบประมาณที่จะจัดซื้อ (พ.ศ.)'),
            'base_year' => $this->integer()->notNull()->comment('ปีงบที่ใช้ยอดจริงเป็นฐาน'),
            'warehouse_id' => $this->integer()->null()->comment('คลังหลักที่จ่าย null = ทุกคลังหลัก'),
            'growth_pct' => $this->decimal(6, 2)->notNull()->defaultValue(5.00)->comment('อัตราปรับเพิ่ม/ลด (%) ที่พัสดุกำหนดให้ทั้งระบบใช้'),
            'months_covered' => $this->tinyInteger()->notNull()->defaultValue(12)->comment('จำนวนเดือนที่ปีฐานมีข้อมูลจริง'),
            'annual_factor' => $this->decimal(8, 4)->notNull()->defaultValue(1)->comment('ตัวคูณปรับเป็นเต็มปี'),
            'data_cutoff_date' => $this->dateTime()->null()->comment('วันที่ทำรายการล่าสุดที่ใช้คำนวณ'),
            'balance_source' => $this->string(30)->null()->comment('ที่มายอดคงคลัง: closed_month | rolled_back'),
            'item_count' => $this->integer()->notNull()->defaultValue(0)->comment('จำนวนรายการในแผน'),
            'plan_value' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('มูลค่าประมาณการรวม'),
            'status' => $this->string(20)->notNull()->defaultValue('draft')->comment('draft = แก้ได้, locked = ปิดค่าแล้ว'),
            'locked_at' => $this->dateTime()->null()->comment('วันที่ปิดค่า'),
            'locked_by' => $this->integer()->null()->comment('ผู้ปิดค่า'),
            'note' => $this->text()->null()->comment('บันทึกของผู้จัดทำ'),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        // 1 ปีงบ ต่อ 1 ขอบเขตคลัง มีแผนได้ฉบับเดียว — รองรับทั้งแบบรวมทุกคลัง (null) และแยกรายคลัง
        $this->createIndex('ux-material-plan-year-warehouse', '{{%material_plan}}', ['fiscal_year', 'warehouse_id'], true);

        $this->createTable('{{%material_plan_item}}', [
            'id' => $this->primaryKey(),
            'material_plan_id' => $this->integer()->notNull(),
            'item_code' => $this->string(50)->notNull()->comment('รหัสวัสดุ'),
            'item_name' => $this->string(255)->null(),
            'category_id' => $this->string(50)->null()->comment('หมวดวัสดุ'),
            'category_title' => $this->string(255)->null(),
            'unit_name' => $this->string(100)->null(),
            'actual_usage' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดใช้จริงเท่าที่เก็บได้ในปีฐาน'),
            'annual_usage' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดใช้ปรับเป็นเต็มปีแล้ว'),
            'forecast_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ประมาณการใช้ปีที่จะจัดซื้อ'),
            'opening_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดคงคลัง ณ สิ้นปีฐาน'),
            'plan_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ประมาณการจัดซื้อ'),
            'unit_price' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'price_source' => $this->string(20)->null()->comment('average | latest | manual | none'),
            'plan_value' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'q1_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'q2_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'q3_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'q4_qty' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'is_manual' => $this->boolean()->notNull()->defaultValue(false)->comment('ผู้ใช้เพิ่มรายการนี้เอง (ไม่มีความเคลื่อนไหวในปีฐาน)'),
            'is_adjusted' => $this->boolean()->notNull()->defaultValue(false)->comment('ผู้ใช้แก้ตัวเลขจากที่ระบบคำนวณ'),
            'data_json' => $this->json()->null()->comment('ยอดใช้ย้อนหลังรายปีและข้อมูลประกอบอื่น'),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx-material-plan-item-plan', '{{%material_plan_item}}', 'material_plan_id');
        $this->createIndex('ux-material-plan-item-code', '{{%material_plan_item}}', ['material_plan_id', 'item_code'], true);
        $this->addForeignKey(
            'fk-material-plan-item-plan',
            '{{%material_plan_item}}',
            'material_plan_id',
            '{{%material_plan}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-material-plan-item-plan', '{{%material_plan_item}}');
        $this->dropTable('{{%material_plan_item}}');
        $this->dropTable('{{%material_plan}}');
    }
}
