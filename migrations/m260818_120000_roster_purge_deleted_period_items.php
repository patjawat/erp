<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ล้างช่องเวรที่ค้างจากแผ่นตารางเวรที่ถูกลบไปแล้ว
 *
 * การลบแผ่นเป็น soft delete (ตั้ง deleted_at) แต่เดิมไม่ได้ลบ roster_item ตามไปด้วย
 * เวรที่ค้างยังกินคีย์ uq-roster_item-emp_date_unit_shift (emp_id, work_date, unit_shift_id)
 * ผลคือแผ่นอื่นจัดเวรคนเดิมวันเดิมไม่ได้ และระบบแจ้งว่า "ถูกจัดไว้ในอีกแผ่นแล้ว"
 * โดยชี้ไปยังแผ่นที่ผู้ใช้มองไม่เห็นและเปิดไม่ได้ — แก้เองไม่ได้เลย
 *
 * ต้นตอแก้ที่ PeriodController::actionDelete() ให้ลบ item พร้อมแผ่นแล้ว
 * migration นี้เก็บกวาดของเดิมที่ค้างอยู่ก่อนหน้า
 *
 * ย้อนกลับไม่ได้ เพราะข้อมูลที่ลบเป็นของแผ่นที่ผู้ใช้ตั้งใจลบไปแล้ว
 */
final class m260818_120000_roster_purge_deleted_period_items extends Migration
{
    public function safeUp(): void
    {
        $ids = (new \yii\db\Query())
            ->select('id')
            ->from('{{%roster_period}}')
            ->where(['not', ['deleted_at' => null]])
            ->column();

        if (empty($ids)) {
            echo "    ไม่มีแผ่นที่ถูกลบ ไม่ต้องเก็บกวาด\n";
            return;
        }

        // ใบเปลี่ยนตัวก่อน เพราะอ้างถึงเวรที่กำลังจะลบ (แผ่นร่างไม่ควรมี แต่กันไว้)
        $swaps = $this->db->createCommand()
            ->delete('{{%roster_swap}}', ['period_id' => $ids])->execute();
        $items = $this->db->createCommand()
            ->delete('{{%roster_item}}', ['period_id' => $ids])->execute();

        echo "    แผ่นที่ถูกลบ " . count($ids) . " แผ่น · ล้างเวร $items ช่อง · ใบเปลี่ยนตัว $swaps ใบ\n";
    }

    public function safeDown(): bool
    {
        echo "m260818_120000_roster_purge_deleted_period_items ย้อนกลับไม่ได้ — เป็นการล้างข้อมูลค้าง\n";
        return false;
    }
}
