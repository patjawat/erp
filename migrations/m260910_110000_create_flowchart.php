<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ตารางหลักของโมดูล flowchart — 1 แถว = 1 ผังกระบวนการ
 * ขั้นตอนย่อยเก็บใน flowchart_step (ปลายทางเงื่อนไข yes/no ของ decision เก็บบน step เอง ไม่มีตาราง edge)
 * png_path เตรียมไว้สำหรับฝังรูปลงเอกสารโมดูลอื่น (medsop/qms/pm) ในเฟสถัดไป — เฟส 1 ยังไม่ใช้
 */
final class m260910_110000_create_flowchart extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%flowchart}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(30)->null(),            // FC-2569-0001
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'category' => $this->string(30)->null(),        // sop | process | service | other
            'status' => $this->string(20)->notNull()->defaultValue('draft'), // draft | published
            'diagram_dir' => $this->string(4)->notNull()->defaultValue('TD'), // TD | LR
            'owner_id' => $this->integer()->null(),         // user id ผู้จัดทำ
            'org_unit_id' => $this->integer()->null(),      // หน่วยงานเจ้าของ
            'budget_year' => $this->smallInteger()->null(), // ปีงบ พ.ศ.
            'png_path' => $this->string(255)->null(),       // cache รูปสำหรับฝังเอกสาร (เฟสถัดไป)
            'revision_no' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-flowchart-owner', '{{%flowchart}}', 'owner_id');
        $this->createIndex('idx-flowchart-status', '{{%flowchart}}', ['status', 'budget_year']);
        $this->createIndex('idx-flowchart-org', '{{%flowchart}}', 'org_unit_id');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%flowchart}}');
    }
}
