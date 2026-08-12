<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ย้าย "นิยามเวร" ลงมาอยู่ที่ระดับหน่วยงาน
 *
 * เดิมชื่อเวรเป็นของกลางทั้งโรงพยาบาล (ช/บ/ด) แต่ละหน่วยตั้งได้แค่เวลา
 * ซึ่งรับรูปแบบเวรจริงของพยาบาลไม่ได้ เช่น
 *   เวรบ่ายดึก   16 ชม.รวดข้ามเที่ยงคืน — เป็นเวรเดียว ไม่ใช่บ่าย+ดึกต่อกัน
 *   เวร Refer    ออกนอกหน่วย ซ้อนกับเวรอื่นในวันเดียวกันได้
 *   เวร On call  อยู่บ้านรอเรียก ไม่ใช่ชั่วโมงทำงานจริง
 *
 * roster_shift_type ยังอยู่ในฐานะ "หมวด" (ช/บ/ด/ควบ) เพื่อให้รายงานรวมข้ามหน่วยทำได้
 * ส่วนชื่อ/เวลา/อัตราค่าตอบแทนที่ผู้ใช้เห็น มาจาก roster_unit_shift
 *
 * is_standby สำคัญ: On call ถ้านับเป็นเวรทำงานปกติ คนที่รับทุกคืนจะโดนกฎ
 * "ทำงานติดต่อกันเกิน N วัน" และ "พักน้อยกว่า N ชม." เตือนรัวทั้งเดือนทั้งที่ไม่ได้ทำงานจริง
 * (แต่ยังนับใน required_staff ตามปกติ — ผู้ใช้ต้องการให้เตือนเมื่อไม่มีคนรับ on-call)
 */
final class m260811_000005_roster_unit_shift_detail extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%roster_unit_shift}}', 'name', $this->string(100)->null()
            ->after('shift_type_id')->comment('ชื่อเวรที่หน่วยนี้เรียก เช่น บ่ายดึก / Refer / On call'));
        $this->addColumn('{{%roster_unit_shift}}', 'short_name', $this->string(10)->null()
            ->after('name')->comment('อักษรย่อในกริด เช่น บด / R / OC'));
        $this->addColumn('{{%roster_unit_shift}}', 'is_standby', $this->tinyInteger(1)->notNull()->defaultValue(0)
            ->after('required_staff')->comment('1 = รอเรียก/ออกนอกหน่วย → ยกเว้นจากกฎเวลาพักและวันทำงานติดกัน'));
        $this->addColumn('{{%roster_unit_shift}}', 'pay_rate', $this->decimal(10, 2)->null()
            ->after('is_standby')->comment('อัตราค่าตอบแทน (บาท)'));
        $this->addColumn('{{%roster_unit_shift}}', 'pay_unit', $this->string(10)->notNull()->defaultValue('shift')
            ->after('pay_rate')->comment('shift = ต่อเวร | hour = ต่อชั่วโมง'));
        $this->addColumn('{{%roster_unit_shift}}', 'sort_order', $this->integer()->notNull()->defaultValue(0)
            ->after('pay_unit'));

        // หน่วยเดียวมีเวรหลายแบบในหมวดเดียวกันได้แล้ว (เช่น "Refer เช้า" กับ "Refer บ่าย")
        $this->dropIndex('uq-roster_unit_shift-unit_type', '{{%roster_unit_shift}}');
        $this->createIndex('idx-roster_unit_shift-unit', '{{%roster_unit_shift}}', ['unit_id', 'active', 'sort_order']);

        // เวรที่จัดต้องชี้มาที่นิยามของหน่วย ไม่ใช่หมวดกลาง
        // เก็บ shift_type_id ไว้ในแถวด้วย (denormalise) เพื่อให้รายงานรวมข้ามหน่วยไม่ต้อง join
        $this->addColumn('{{%roster_item}}', 'unit_shift_id', $this->integer()->null()->after('shift_type_id'));
        $this->createIndex('idx-roster_item-unit_shift', '{{%roster_item}}', 'unit_shift_id');
        $this->dropIndex('uq-roster_item-emp_date_shift', '{{%roster_item}}');
        $this->createIndex('uq-roster_item-emp_date_unit_shift', '{{%roster_item}}',
            ['emp_id', 'work_date', 'unit_shift_id'], true);
        $this->addForeignKey('fk-roster_item-unit_shift', '{{%roster_item}}', 'unit_shift_id',
            '{{%roster_unit_shift}}', 'id', 'RESTRICT', 'CASCADE');

        // ตั้งชื่อเริ่มต้นให้แถวเดิมจากหมวดที่ผูกอยู่ เพื่อไม่ให้ชื่อว่าง
        $this->execute("
            UPDATE {{%roster_unit_shift}} us
            JOIN {{%roster_shift_type}} st ON st.id = us.shift_type_id
            SET us.name = COALESCE(us.name, st.title),
                us.short_name = COALESCE(us.short_name, st.short_name),
                us.sort_order = st.sort_order
        ");
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-roster_item-unit_shift', '{{%roster_item}}');
        $this->dropIndex('uq-roster_item-emp_date_unit_shift', '{{%roster_item}}');
        $this->dropIndex('idx-roster_item-unit_shift', '{{%roster_item}}');
        $this->dropColumn('{{%roster_item}}', 'unit_shift_id');
        $this->createIndex('uq-roster_item-emp_date_shift', '{{%roster_item}}',
            ['emp_id', 'work_date', 'shift_type_id'], true);

        $this->dropIndex('idx-roster_unit_shift-unit', '{{%roster_unit_shift}}');
        $this->createIndex('uq-roster_unit_shift-unit_type', '{{%roster_unit_shift}}', ['unit_id', 'shift_type_id'], true);
        foreach (['sort_order', 'pay_unit', 'pay_rate', 'is_standby', 'short_name', 'name'] as $col) {
            $this->dropColumn('{{%roster_unit_shift}}', $col);
        }
    }
}
