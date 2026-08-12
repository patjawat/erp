<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * 1 หน่วยงาน 1 เดือน มีตารางเวรได้หลาย "แผ่น"
 *
 * หน้างานจริงของหอผู้ป่วยแยกแผ่นตามชนิดเวร เช่น เดือนกันยายนมี
 *   ตารางเวรหลัก (เช้า/บ่าย/ดึก) · ตารางเวร Refer · ตารางเวร On call · ตารางเวรบ่ายดึก
 * แต่ละแผ่นเวียนอนุมัติแยกกัน แผ่นหนึ่งอาจอนุมัติแล้วขณะที่อีกแผ่นยังแก้อยู่
 *
 * เดิมบังคับ UNIQUE(unit_id, year_ce, month) = เดือนละรอบเดียว จึงทำแบบนี้ไม่ได้
 * เปลี่ยนเป็นกันชื่อซ้ำภายในเดือนเดียวกันแทน
 *
 * ขอบเขตเวรของแต่ละแผ่นเก็บใน roster_period.data_json.unit_shift_ids
 * (เป็นรายการสั้นๆ ไว้กรองคอลัมน์ที่แสดง ไม่ใช่ข้อมูลเชิงสัมพันธ์ที่ต้อง join)
 */
final class m260811_000006_roster_multi_sheet extends Migration
{
    public function safeUp(): void
    {
        $this->dropIndex('uq-roster_period-unit_month', '{{%roster_period}}');

        // ชื่อแผ่นกลายเป็นตัวแยกรอบ จึงต้องมีเสมอ
        $this->execute("UPDATE {{%roster_period}} SET title = CONCAT('ตารางเวร ', month, '/', year_ce) WHERE title IS NULL OR title = ''");
        $this->alterColumn('{{%roster_period}}', 'title', $this->string(255)->notNull()->comment('ชื่อแผ่น เช่น ตารางเวรหลัก / ตารางเวร Refer'));

        $this->createIndex('uq-roster_period-unit_month_title', '{{%roster_period}}',
            ['unit_id', 'year_ce', 'month', 'title'], true);
        $this->createIndex('idx-roster_period-unit_month', '{{%roster_period}}', ['unit_id', 'year_ce', 'month']);
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx-roster_period-unit_month', '{{%roster_period}}');
        $this->dropIndex('uq-roster_period-unit_month_title', '{{%roster_period}}');
        $this->alterColumn('{{%roster_period}}', 'title', $this->string(255)->null());
        $this->createIndex('uq-roster_period-unit_month', '{{%roster_period}}', ['unit_id', 'year_ce', 'month'], true);
    }
}
