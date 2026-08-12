<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ลบตัวชี้วัดหลักแล้วให้ตัวชี้วัดรองถูกลบตามไปด้วย
 *
 * เดิม FK ของ parent_id เป็น SET NULL ทำให้ตัวชี้วัดรองกลายเป็นตัวชี้วัดหลักลอย
 * พร้อมกลยุทธ์ที่ผูกอยู่ ซึ่งไม่ตรงกับโครงสร้างที่ตัวชี้วัดรองเป็นส่วนหนึ่งของตัวหลัก
 * และไม่ตรงกับข้อความยืนยันตอนลบ ชั้นอื่นในต้นไม้ใช้ CASCADE อยู่แล้ว
 */
final class m260808_000008_cascade_delete_sub_indicator extends Migration
{
    public function safeUp(): void
    {
        $this->dropForeignKey('fk-pm_strategy_indicator-parent', '{{%pm_strategy_indicator}}');
        $this->addForeignKey('fk-pm_strategy_indicator-parent', '{{%pm_strategy_indicator}}', 'parent_id', '{{%pm_strategy_indicator}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-pm_strategy_indicator-parent', '{{%pm_strategy_indicator}}');
        $this->addForeignKey('fk-pm_strategy_indicator-parent', '{{%pm_strategy_indicator}}', 'parent_id', '{{%pm_strategy_indicator}}', 'id', 'SET NULL', 'CASCADE');
    }
}
