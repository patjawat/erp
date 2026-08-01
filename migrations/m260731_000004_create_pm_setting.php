<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ตารางตั้งค่าโมดูล pm (key-value) — มิเรอร์ convention ของ medsop_setting
 * ใช้เก็บรูปแบบรหัสโครงการ (code_pattern) และค่าตั้งค่าอื่นในอนาคต
 */
final class m260731_000004_create_pm_setting extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%pm_setting}}', [
            'setting_key' => $this->string(100)->notNull(),
            'setting_value' => $this->text()->null(),
            'updated_by' => $this->integer()->null(),
            'updated_at' => $this->dateTime()->notNull(),
            'PRIMARY KEY(setting_key)',
        ], $tableOptions);

        // ค่าเริ่มต้น: รูปแบบรหัสโครงการ เช่น P-MAN-690001
        $this->insert('{{%pm_setting}}', [
            'setting_key' => 'code_pattern',
            'setting_value' => 'P-{org}-{yy}{sequence}',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%pm_setting}}');
    }
}
