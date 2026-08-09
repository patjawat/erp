<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * แยกงานใต้กลยุทธ์เป็น 2 ชนิด: โครงการ (ใช้งบประมาณ) และแผนงาน/กิจกรรม (อาจไม่ใช้งบ)
 *
 * เก็บในตารางเดียวกันเพราะอยู่ตำแหน่งเดียวกันในต้นไม้ และกิจกรรมที่ได้งบภายหลัง
 * ยกระดับเป็นโครงการได้ด้วยการแก้ฟิลด์เดียว ไม่ต้องย้ายข้อมูลข้ามตาราง
 */
final class m260808_000009_add_work_type_to_projects extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%projects}}', 'work_type', $this->string(20)->notNull()->defaultValue('project')->after('name'));
        $this->createIndex('idx-projects-work_type', '{{%projects}}', ['work_type', 'thai_year']);
        $this->update('{{%projects}}', ['work_type' => 'project']);
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-projects-work_type', '{{%projects}}');
        $this->dropColumn('{{%projects}}', 'work_type');
    }
}
