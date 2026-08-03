<?php

use yii\db\Migration;

/**
 * เพิ่มฟิลด์ reference (เอกสาร/ข้อมูลอ้างอิง) ต่อจาก description (วัตถุประสงค์)
 * เช่น "ทะเบียนครุภัณฑ์เลข 11111111" เพื่อใช้อ้างอิงประกอบแผน
 */
class m260731_000004_add_reference_to_plan_order extends Migration
{
    public function safeUp()
    {
        $this->addColumn('plan_order', 'reference', $this->text()->null()->comment('เอกสาร/ข้อมูลอ้างอิง')->after('description'));
    }

    public function safeDown()
    {
        $this->dropColumn('plan_order', 'reference');
    }
}
