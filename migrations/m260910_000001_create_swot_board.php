<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ตารางหลักของโมดูล SWOT/SOAR — 1 แถว = 1 เรื่องวิเคราะห์ (กระดาน)
 * ช่อง JSON tows_matrix/soar_matrix/ai_analysis เตรียมไว้ล่วงหน้าสำหรับเฟส 2-5 (เฟส 1 ยังไม่ใช้)
 */
final class m260910_000001_create_swot_board extends Migration
{
    public function safeUp(): void
    {
        $options = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%swot_board}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'framework' => $this->string(10)->notNull()->defaultValue('swot'), // swot | soar
            'objective' => $this->text()->null(),
            'owner_id' => $this->integer()->null(),        // user id ผู้จัดทำ
            'org_unit_id' => $this->integer()->null(),     // หน่วยงาน (เผื่อกรอง/รายงาน)
            'budget_year' => $this->smallInteger()->null(), // ปีงบ พ.ศ.
            'status' => $this->string(20)->notNull()->defaultValue('active'), // active | archived
            'custom_categories' => $this->json()->null(),
            'tows_matrix' => $this->json()->null(),  // เฟส 3
            'soar_matrix' => $this->json()->null(),  // เฟส 3
            'ai_analysis' => $this->json()->null(),  // เฟส 5
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $options);

        $this->createIndex('idx-swot_board-owner', '{{%swot_board}}', 'owner_id');
        $this->createIndex('idx-swot_board-status', '{{%swot_board}}', ['status', 'budget_year']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%swot_board}}');
    }
}
