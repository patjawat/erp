<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * กำหนด OT ที่ระดับเวรของหน่วยงาน ไม่อนุมานจากหมวดเวรหรือประเภทวัน
 *
 * เดิมหมวดบ่าย/ดึกถูก seed เป็น is_ot=1 ทำให้ทุกเวรบ่ายและดึกถูกนับ OT
 * แม้หน่วยงานไม่ได้กำหนดให้เป็น OT โดยตรง ค่าเริ่มต้นใหม่จึงเป็น 0 และให้หัวหน้า
 * ติ๊กเฉพาะนิยามเวรที่ต้องนับในคอลัมน์ OT
 */
final class m260901_120000_roster_unit_shift_ot_flag extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%roster_unit_shift}}', 'is_ot',
            $this->tinyInteger(1)->notNull()->defaultValue(0)
                ->after('is_standby')
                ->comment('1 = นับเป็น OT ตามนิยามเวรของหน่วยงาน'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%roster_unit_shift}}', 'is_ot');
    }
}
