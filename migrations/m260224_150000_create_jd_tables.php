<?php

use yii\db\Migration;

/**
 * สร้างตารางสำหรับโมดูล Job Description (JD)
 * - jd_template: template ต่อตำแหน่งงาน (position)
 * - jd_template_section: หัวข้อใน template (หน้าที่, คุณสมบัติ, KPI ฯลฯ)
 * - jd_employee: JD ของแต่ละพนักงาน (โหลดจาก template ได้)
 * - jd_employee_section: หัวข้อใน JD พนักงาน (แก้ไข/เพิ่มได้)
 */
class m260224_150000_create_jd_tables extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%jd_template}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('ชื่อ template'),
            'position_code' => $this->string(64)->notNull()->comment('รหัสตำแหน่ง (categorise name=position_name)'),
            'is_active' => $this->tinyInteger(1)->notNull()->defaultValue(1)->comment('1=ใช้งาน'),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx-jd_template-position_code', '{{%jd_template}}', 'position_code');
        $this->createIndex('idx-jd_template-is_active', '{{%jd_template}}', 'is_active');

        $this->createTable('{{%jd_template_section}}', [
            'id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull()->comment('หัวข้อ เช่น หน้าที่ความรับผิดชอบ'),
            'content' => $this->text()->null()->comment('เนื้อหา'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('idx-jd_template_section-template_id', '{{%jd_template_section}}', 'template_id');
        $this->addForeignKey(
            'fk-jd_template_section-template',
            '{{%jd_template_section}}',
            'template_id',
            '{{%jd_template}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable('{{%jd_employee}}', [
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull()->comment('พนักงาน'),
            'template_id' => $this->integer()->null()->comment('template ที่โหลดมา (ถ้ามี)'),
            'created_at' => $this->datetime()->notNull(),
            'updated_at' => $this->datetime()->null(),
        ], $tableOptions);

        $this->createIndex('idx-jd_employee-emp_id', '{{%jd_employee}}', 'emp_id');
        $this->createIndex('uq-jd_employee-emp_id', '{{%jd_employee}}', 'emp_id', true);
        $this->addForeignKey(
            'fk-jd_employee-emp',
            '{{%jd_employee}}',
            'emp_id',
            '{{%employees}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-jd_employee-template',
            '{{%jd_employee}}',
            'template_id',
            '{{%jd_template}}',
            'id',
            'SET NULL',
            'SET NULL'
        );

        $this->createTable('{{%jd_employee_section}}', [
            'id' => $this->primaryKey(),
            'jd_employee_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull()->comment('หัวข้อ'),
            'content' => $this->text()->null()->comment('เนื้อหา'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('idx-jd_employee_section-jd_employee_id', '{{%jd_employee_section}}', 'jd_employee_id');
        $this->addForeignKey(
            'fk-jd_employee_section-jd_employee',
            '{{%jd_employee_section}}',
            'jd_employee_id',
            '{{%jd_employee}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropTable('{{%jd_employee_section}}');
        $this->dropTable('{{%jd_employee}}');
        $this->dropTable('{{%jd_template_section}}');
        $this->dropTable('{{%jd_template}}');
    }
}
