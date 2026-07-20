<?php

use yii\db\Migration;

/**
 * เกณฑ์ค่าเสื่อม (depreciation profiles) — ต้นแบบ/เกณฑ์ที่ทรัพย์สินอ้างอิง
 *
 * หลักการ: ไม่แก้ไขเกณฑ์เดิมจนกระทบทรัพย์สินเก่า ควรสร้าง version/profile ใหม่แทน
 * (asset จะเก็บ snapshot ของค่าที่ใช้จริง ณ วันขึ้นทะเบียนไว้ในตัวเอง)
 *
 * ชื่อตารางไม่มี prefix am_ ตามที่กำหนด
 */
class m260712_100000_create_depreciation_profiles_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%depreciation_profiles}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(50)->notNull()->comment('รหัสเกณฑ์ (unique)'),
            'name' => $this->string(255)->notNull()->comment('ชื่อเกณฑ์'),
            'method' => $this->string(30)->notNull()->defaultValue('straight_line')->comment('วิธีคำนวณ: straight_line'),
            'useful_life_months' => $this->integer()->null()->comment('อายุการใช้งาน (เดือน) รองรับอายุไม่เต็มปี'),
            'annual_rate' => $this->decimal(8, 4)->null()->comment('อัตราค่าเสื่อมต่อปี (%) — nullable'),
            'salvage_value_type' => $this->string(20)->notNull()->defaultValue('amount')->comment('ชนิดมูลค่าซาก: amount|percent|one_baht'),
            'salvage_value' => $this->decimal(20, 4)->notNull()->defaultValue(0)->comment('มูลค่าซาก (ตามชนิดที่ระบุ)'),
            'calculation_basis' => $this->string(20)->notNull()->defaultValue('monthly')->comment('ฐานคำนวณ: monthly|daily|full_period'),
            'start_rule' => $this->string(30)->notNull()->defaultValue('ready_date')->comment('กฎเริ่มคิด: ready_date|ready_month|next_month'),
            'rounding_scale' => $this->tinyInteger()->notNull()->defaultValue(2)->comment('จำนวนทศนิยมที่ปัดเศษ'),
            'effective_from' => $this->date()->null()->comment('เริ่มมีผลบังคับใช้'),
            'effective_to' => $this->date()->null()->comment('สิ้นสุดมีผลบังคับใช้'),
            'status' => $this->string(20)->notNull()->defaultValue('active')->comment('สถานะ: active|inactive|draft'),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('uq_depreciation_profiles_code', '{{%depreciation_profiles}}', 'code', true);
        $this->createIndex('idx_depreciation_profiles_status', '{{%depreciation_profiles}}', 'status');
        $this->createIndex('idx_depreciation_profiles_effective', '{{%depreciation_profiles}}', ['effective_from', 'effective_to']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%depreciation_profiles}}');
    }
}
