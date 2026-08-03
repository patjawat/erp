<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * เพิ่มอัตราตั้งต้น (เช่น ราคาต่อคน) ลงในทะเบียนประเภทค่าใช้จ่าย
 * เพื่อใช้เป็นค่าเริ่มต้นเวลาลงค่าใช้จ่ายรายเดือน (ลดการบันทึกซ้ำ)
 */
final class m260802_000001_add_default_rate_to_housing_charge_type extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn(
            '{{%housing_charge_type}}',
            'default_rate',
            $this->decimal(12, 2)->null()->after('unit_name')
        );
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%housing_charge_type}}', 'default_rate');
    }
}
