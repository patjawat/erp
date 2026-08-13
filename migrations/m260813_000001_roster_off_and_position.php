<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * วันหยุด (OFF) + ตำแหน่งประจำเวร
 *
 * OFF: หน่วยงานต้องทำเครื่องหมาย "x" ในตาราง (แบบเดียวกับที่ทำใน Excel) เพื่อแยก
 * "ตั้งใจให้หยุด" ออกจาก "ยังไม่ได้จัด" — แต่ OFF ไม่ใช่การทำงาน จึงต้องมีธงกำกับ
 * ไม่งั้นทุกที่ที่นับเวรจะนับ OFF เป็นเวรทำงานด้วย ทำให้คอลัมน์รวมและรายงาน
 * ความเป็นธรรมอ่านกลับด้าน (คนที่ "หยุด" วันเสาร์จะถูกนับว่า "มาทำงาน" วันเสาร์)
 *
 * ตำแหน่ง: อัตราค่าตอบแทนต่างกันตามวิชาชีพ พยาบาลวิชาชีพกับพนักงานเปลขึ้นเวรเช้า
 * เหมือนกันแต่ได้ไม่เท่ากัน หน่วยงานจึงแยกเวรตามวิชาชีพ (ชพ/ชป/ชผ) แล้วผูกตำแหน่ง
 * ไว้กับนิยามเวร ทำให้ระบบเตือนได้เมื่อจัดคนผิดวิชาชีพลงช่อง
 */
final class m260813_000001_roster_off_and_position extends Migration
{
    public function safeUp(): void
    {
        // ธงระดับหมวด — หมวด "หยุด" ทั้งหมวดไม่ใช่การทำงาน
        $this->addColumn('{{%roster_shift_type}}', 'is_off', $this->tinyInteger(1)->notNull()->defaultValue(0)
            ->after('is_extra')->comment('1 = วันหยุด ไม่นับเป็นเวรทำงาน ไม่คิดค่าตอบแทน'));

        // ตำแหน่งที่เวรนี้กำหนดไว้ — ใช้ตรวจว่าจัดคนตรงวิชาชีพหรือไม่
        $this->addColumn('{{%roster_unit_shift}}', 'position_id', $this->integer()->null()
            ->after('shift_type_id')->comment('employee_position.id — NULL = ไม่จำกัดวิชาชีพ'));
        $this->createIndex('idx-roster_unit_shift-position', '{{%roster_unit_shift}}', 'position_id');

        $exists = (new \yii\db\Query())->from('{{%roster_shift_type}}')->where(['code' => 'O'])->exists();
        if (!$exists) {
            $now = date('Y-m-d H:i:s');
            $this->insert('{{%roster_shift_type}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                'code' => 'O',
                'short_name' => 'x',
                'title' => 'วันหยุด',
                'is_night' => 0,
                'is_ot' => 0,
                'is_extra' => 0,
                'is_off' => 1,
                'color' => 'secondary',
                'sort_order' => 9,
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%roster_shift_type}}', ['code' => 'O']);
        $this->dropIndex('idx-roster_unit_shift-position', '{{%roster_unit_shift}}');
        $this->dropColumn('{{%roster_unit_shift}}', 'position_id');
        $this->dropColumn('{{%roster_shift_type}}', 'is_off');
    }
}
