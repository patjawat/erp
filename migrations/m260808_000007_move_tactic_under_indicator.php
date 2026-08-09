<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ย้ายกลยุทธ์ไปอยู่ใต้ตัวชี้วัด (หลักหรือรอง) แทนที่จะอยู่ใต้เป้าประสงค์โดยตรง
 *
 * ลำดับใหม่: เป้าประสงค์ → ตัวชี้วัดหลัก → ตัวชี้วัดรอง
 *                                    ↘ กลยุทธ์ → มาตรการ / โครงการ
 *
 * goal_id ยังเก็บไว้เป็นค่าที่ derive จากตัวชี้วัด เพื่อให้ query เดิมที่กรองด้วย
 * เป้าประสงค์ยังทำงานต่อได้ และรองรับกลยุทธ์เก่าที่ยังไม่ได้ผูกตัวชี้วัด
 */
final class m260808_000007_move_tactic_under_indicator extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%pm_strategy_tactic}}', 'indicator_id', $this->integer()->null()->after('goal_id'));
        $this->createIndex('idx-pm_strategy_tactic-indicator_id', '{{%pm_strategy_tactic}}', 'indicator_id');
        $this->addForeignKey('fk-pm_strategy_tactic-indicator', '{{%pm_strategy_tactic}}', 'indicator_id', '{{%pm_strategy_indicator}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-pm_strategy_tactic-indicator', '{{%pm_strategy_tactic}}');
        $this->dropIndex('idx-pm_strategy_tactic-indicator_id', '{{%pm_strategy_tactic}}');
        $this->dropColumn('{{%pm_strategy_tactic}}', 'indicator_id');
    }
}
