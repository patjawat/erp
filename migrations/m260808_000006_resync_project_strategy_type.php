<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ปรับ strategy_type ให้ตรงกับการผูกกลยุทธ์จริง
 *
 * การลบกลยุทธ์ทำให้ tactic_id ถูกล้างด้วย FK (SET NULL) โดยไม่ผ่าน beforeSave
 * คอลัมน์จึงค้างเป็น in ได้ ทั้งที่โครงการไม่สังกัดกลยุทธ์ใดแล้ว
 */
final class m260808_000006_resync_project_strategy_type extends Migration
{
    public function safeUp(): void
    {
        $this->update('{{%projects}}', ['strategy_type' => 'out'], ['tactic_id' => null]);
        $this->update('{{%projects}}', ['strategy_type' => 'in'], ['not', ['tactic_id' => null]]);
    }

    public function safeDown(): void
    {
        echo "    > ไม่ต้องย้อนกลับ — เป็นการปรับข้อมูลให้ตรงกับความจริง\n";
    }
}
