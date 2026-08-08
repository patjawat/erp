<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * เพิ่มชั้น "กลยุทธ์" ระหว่างเป้าประสงค์กับมาตรการ
 * ลำดับตามหลักวิชาการ: ประเด็นยุทธศาสตร์ → เป้าประสงค์ → กลยุทธ์ → มาตรการ → แผนงาน/โครงการ
 * เป้าประสงค์คือปลายทาง กลยุทธ์คือวิธีไปให้ถึง จึงต้องกำหนดเป้าประสงค์ก่อนเสมอ
 */
final class m260808_000003_create_pm_strategy_tactic extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%pm_strategy_tactic}}', [
            'id' => $this->primaryKey(),
            'goal_id' => $this->integer()->notNull(),
            'code' => $this->string(50)->null(),
            'name' => $this->text()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'ref' => $this->string(64)->notNull()->unique(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);
        $this->createIndex('idx-pm_strategy_tactic-goal_id', '{{%pm_strategy_tactic}}', 'goal_id');
        $this->addForeignKey('fk-pm_strategy_tactic-goal', '{{%pm_strategy_tactic}}', 'goal_id', '{{%pm_strategy_goal}}', 'id', 'CASCADE', 'CASCADE');

        // มาตรการผูกกับกลยุทธ์ ปล่อยว่างได้สำหรับข้อมูลที่นำเข้าจาก Excel ก่อนจัดกลุ่ม
        $this->addColumn('{{%pm_strategy_measure}}', 'tactic_id', $this->integer()->null());
        $this->createIndex('idx-pm_strategy_measure-tactic_id', '{{%pm_strategy_measure}}', 'tactic_id');
        $this->addForeignKey('fk-pm_strategy_measure-tactic', '{{%pm_strategy_measure}}', 'tactic_id', '{{%pm_strategy_tactic}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-pm_strategy_measure-tactic', '{{%pm_strategy_measure}}');
        $this->dropIndex('idx-pm_strategy_measure-tactic_id', '{{%pm_strategy_measure}}');
        $this->dropColumn('{{%pm_strategy_measure}}', 'tactic_id');
        $this->dropTable('{{%pm_strategy_tactic}}');
    }
}
