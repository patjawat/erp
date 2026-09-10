<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ขั้นตอนย่อยของผังกระบวนการ (1 แถว = 1 กล่องในผัง / 1 บรรทัดในตารางกระบวนการ)
 *
 * การไหลของเส้น derive จากลำดับ (seq) + ปลายทางเงื่อนไขของ decision:
 *  - step ทั่วไป  -> ไหลลงขั้นถัดไปอัตโนมัติ
 *  - step decision -> ใช้ branch_yes / branch_no (อ้าง seq ของขั้นปลายทาง) เป็น 2 เส้น
 * จึงไม่ต้องมีตาราง edge แยก
 */
final class m260910_110001_create_flowchart_step extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%flowchart_step}}', [
            'id' => $this->primaryKey(),
            'flowchart_id' => $this->integer()->notNull(),
            'seq' => $this->integer()->notNull()->defaultValue(1), // ลำดับ 1..n
            'type' => $this->string(20)->notNull()->defaultValue('process'), // start|process|decision|document|subprocess|end
            'title' => $this->text()->null(),          // ข้อความในกล่อง
            'actor' => $this->string(255)->null(),     // ผู้รับผิดชอบ
            'related_doc' => $this->string(255)->null(), // เอกสารที่เกี่ยวข้อง
            'duration' => $this->string(120)->null(),  // ระยะเวลา
            'note' => $this->text()->null(),           // หมายเหตุ
            'branch_yes' => $this->integer()->null(),  // seq ปลายทางเมื่อ "ใช่" (เฉพาะ decision)
            'branch_no' => $this->integer()->null(),   // seq ปลายทางเมื่อ "ไม่" (เฉพาะ decision)
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ], $options);

        $this->createIndex('idx-flowchart_step-fc', '{{%flowchart_step}}', ['flowchart_id', 'seq']);
        $this->addForeignKey(
            'fk-flowchart_step-fc',
            '{{%flowchart_step}}',
            'flowchart_id',
            '{{%flowchart}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-flowchart_step-fc', '{{%flowchart_step}}');
        $this->dropTable('{{%flowchart_step}}');
    }
}
