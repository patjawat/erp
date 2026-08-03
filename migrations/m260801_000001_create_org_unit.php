<?php

use yii\db\Migration;

/**
 * ทะเบียนหน่วยงานกลาง (org_unit) — เวอร์ชันตามปีงบประมาณ
 * รวมหน่วยในโครงสร้าง (ดึงจาก tree) + หน่วยเพิ่มเอง (ทีมประสาน/สสจ./CUP)
 */
class m260801_000001_create_org_unit extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('org_unit', [
            'id' => $this->bigPrimaryKey(),
            'thai_year' => $this->integer()->notNull()->comment('ปีงบประมาณ (พ.ศ.)'),
            'source' => $this->string(20)->notNull()->defaultValue('structure')->comment('structure=ดึงจากผัง, manual=เพิ่มเอง'),
            'ref_id' => $this->bigInteger()->null()->comment('tree.id เมื่อ source=structure'),
            'unit_type' => $this->string(50)->null()->comment('ประเภท (categorise code name=org_unit_type)'),
            'code' => $this->string(20)->null()->comment('อักษรย่อ'),
            'name' => $this->string(255)->notNull()->comment('ชื่อหน่วยงาน'),
            'leader_emp_id' => $this->integer()->null()->comment('หัวหน้า/ผู้รับผิดชอบ (emp_id ภายใน)'),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_at' => $this->dateTime()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx_org_unit_year_active', 'org_unit', ['thai_year', 'active']);
        $this->createIndex('idx_org_unit_type', 'org_unit', 'unit_type');
        $this->createIndex('idx_org_unit_leader', 'org_unit', 'leader_emp_id');
        // หน่วยในโครงสร้าง: 1 tree node ต่อ 1 row ต่อปี (ref_id=null ของ manual ซ้ำได้)
        $this->createIndex('uq_org_unit_year_ref', 'org_unit', ['thai_year', 'ref_id'], true);
        // อักษรย่อห้ามซ้ำในปีเดียวกัน (code=null ซ้ำได้)
        $this->createIndex('uq_org_unit_year_code', 'org_unit', ['thai_year', 'code'], true);
    }

    public function safeDown()
    {
        $this->dropTable('org_unit');
    }
}
