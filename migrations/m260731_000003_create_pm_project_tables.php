<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * โมดูล pm — แบบเสนอโครงการ (เฟส 1)
 *
 * ตาราง projects เก็บหัวโครงการ + 13 หัวข้อของแบบเสนอโครงการ
 * ตารางลูก:
 *   - project_objectives   วัตถุประสงค์ (ข้อ 2)
 *   - project_indicators   เป้าหมาย/ตัวชี้วัด + ร้อยละ (ข้อ 3)
 *   - project_responsibles ผู้รับผิดชอบโครงการ (ข้อ 11)
 *
 * ตารางลูกสำหรับ กำหนดการอบรม / ผังกำกับ (Gantt) และแบบรายงานผล
 * จะเพิ่มในเฟสถัดไป
 */
final class m260731_000003_create_pm_project_tables extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        // ---- projects : หัวโครงการ + แบบเสนอโครงการ ----
        $this->createTable('{{%projects}}', [
            'id' => $this->primaryKey()->comment('รหัสโครงการ'),
            'code' => $this->string(50)->null()->comment('เลขที่โครงการ'),
            'name' => $this->string(255)->notNull()->comment('ชื่อโครงการ'),
            'thai_year' => $this->integer()->null()->comment('ปีงบประมาณ (พ.ศ.)'),
            'department_id' => $this->integer()->null()->comment('หน่วยงานเจ้าของโครงการ (tree.id)'),
            'strategy_type' => $this->string(20)->null()->comment('ใน/นอกยุทธศาสตร์ (in/out)'),

            'rationale' => $this->text()->null()->comment('1. หลักการและเหตุผล'),
            'target_group' => $this->text()->null()->comment('4. กลุ่มเป้าหมาย'),
            'method' => $this->text()->null()->comment('5. วิธีดำเนินการ (งานและกิจกรรม)'),

            'start_date' => $this->date()->null()->comment('6. วันที่เริ่มดำเนินการ'),
            'end_date' => $this->date()->null()->comment('6. วันที่สิ้นสุดดำเนินการ'),
            'dead_line_date' => $this->date()->null()->comment('วันครบกำหนด'),
            'duration_text' => $this->string(255)->null()->comment('6. ระยะเวลา (ข้อความ)'),

            'location' => $this->string(255)->null()->comment('7. สถานที่ดำเนินโครงการ'),
            'lecturer' => $this->text()->null()->comment('8. วิทยากร'),
            'evaluation' => $this->text()->null()->comment('9. การประเมินผลโครงการ'),
            'expected_result' => $this->text()->null()->comment('10. ผลที่คาดว่าจะได้รับ'),

            'budget_total' => $this->decimal(14, 2)->notNull()->defaultValue(0)->comment('12. งบประมาณรวม (บาท)'),
            'budget_source' => $this->string(255)->null()->comment('แหล่งงบประมาณ'),
            'budget_detail' => $this->text()->null()->comment('12. รายละเอียดงบประมาณ'),

            'status' => $this->string(30)->notNull()->defaultValue('draft')->comment('สถานะ: draft/proposed/approved/rejected/done'),
            'data_json' => $this->json()->null()->comment('ข้อมูลเสริม'),

            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_at' => $this->dateTime()->null(),
            'deleted_by' => $this->integer()->null(),
        ], $tableOptions);

        $this->createIndex('idx-projects-thai_year', '{{%projects}}', 'thai_year');
        $this->createIndex('idx-projects-department_id', '{{%projects}}', 'department_id');
        $this->createIndex('idx-projects-status', '{{%projects}}', 'status');

        // ---- project_objectives : วัตถุประสงค์ (ข้อ 2) ----
        $this->createTable('{{%project_objectives}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull()->comment('projects.id'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'detail' => $this->text()->notNull()->comment('รายละเอียดวัตถุประสงค์'),
        ], $tableOptions);
        $this->createIndex('idx-project_objectives-project_id', '{{%project_objectives}}', 'project_id');
        $this->addForeignKey(
            'fk-project_objectives-project',
            '{{%project_objectives}}',
            'project_id',
            '{{%projects}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // ---- project_indicators : เป้าหมาย/ตัวชี้วัด + ร้อยละ (ข้อ 3) ----
        $this->createTable('{{%project_indicators}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull()->comment('projects.id'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'detail' => $this->text()->notNull()->comment('ตัวชี้วัดผลสำเร็จ'),
            'target_percent' => $this->decimal(6, 2)->null()->comment('ค่าเป้าหมาย (ร้อยละ)'),
        ], $tableOptions);
        $this->createIndex('idx-project_indicators-project_id', '{{%project_indicators}}', 'project_id');
        $this->addForeignKey(
            'fk-project_indicators-project',
            '{{%project_indicators}}',
            'project_id',
            '{{%projects}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // ---- project_responsibles : ผู้รับผิดชอบโครงการ (ข้อ 11) ----
        $this->createTable('{{%project_responsibles}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->integer()->notNull()->comment('projects.id'),
            'sort' => $this->integer()->notNull()->defaultValue(0),
            'role' => $this->string(20)->null()->comment('owner=ผู้รับผิดชอบ / director=ผู้บังคับบัญชา'),
            'emp_id' => $this->integer()->null()->comment('employees.id (ถ้าเลือกจากระบบ)'),
            'fullname' => $this->string(255)->null()->comment('ชื่อ-สกุล'),
            'position' => $this->string(255)->null()->comment('ตำแหน่ง'),
            'phone' => $this->string(30)->null()->comment('เบอร์โทรศัพท์'),
        ], $tableOptions);
        $this->createIndex('idx-project_responsibles-project_id', '{{%project_responsibles}}', 'project_id');
        $this->addForeignKey(
            'fk-project_responsibles-project',
            '{{%project_responsibles}}',
            'project_id',
            '{{%projects}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%project_responsibles}}');
        $this->dropTable('{{%project_indicators}}');
        $this->dropTable('{{%project_objectives}}');
        $this->dropTable('{{%projects}}');
    }
}
