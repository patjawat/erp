<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ผูกโครงการเข้ากับกลยุทธ์ในแผนยุทธศาสตร์
 *
 * โครงการที่สร้างจากหน้าแผนยุทธศาสตร์ = "โครงการในแผนยุทธศาสตร์" (ผูกกลยุทธ์)
 * โครงการที่สร้างจากหน้าโครงการโดยตรง = "โครงการนอกแผนยุทธศาสตร์" (ไม่ผูกกลยุทธ์)
 * ใช้คอลัมน์ strategy_type ที่มีอยู่แล้วแต่ยังไม่เคยถูกใช้งานเป็นตัวบอกประเภท
 */
final class m260808_000005_link_projects_to_strategy extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%projects}}', 'tactic_id', $this->integer()->null()->after('strategy_type'));
        $this->createIndex('idx-projects-tactic_id', '{{%projects}}', 'tactic_id');
        $this->addForeignKey('fk-projects-tactic', '{{%projects}}', 'tactic_id', '{{%pm_strategy_tactic}}', 'id', 'SET NULL', 'CASCADE');

        // โครงการเดิมทั้งหมดยังไม่ผูกยุทธศาสตร์ จึงเป็นโครงการนอกแผน
        $this->update('{{%projects}}', ['strategy_type' => 'out'], ['strategy_type' => null]);
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-projects-tactic', '{{%projects}}');
        $this->dropIndex('idx-projects-tactic_id', '{{%projects}}');
        $this->dropColumn('{{%projects}}', 'tactic_id');
    }
}
