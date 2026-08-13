<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * อัตรากำลังแยกตามประเภทวัน + กฎเพดานชั่วโมงต่อสัปดาห์
 *
 * หอผู้ป่วยใช้คนวันเสาร์-อาทิตย์-นักขัตฤกษ์ไม่เท่าวันธรรมดา การมีตัวเลขเดียว
 * ทำให้ตัวนับความครบและ heatmap อ่านผิดทุกวันหยุด (ขึ้นแดงทั้งที่จัดครบตามจริง)
 *
 * required_staff เดิมกลายเป็น "วันธรรมดา" ส่วนอีก 3 ค่าเป็น override
 * ปล่อย NULL = ใช้ค่าวันธรรมดา จึงไม่กระทบหน่วยที่ตั้งไว้แล้ว
 *
 * เพดานชั่วโมง/สัปดาห์จำเป็นเพราะระบบรองรับเวร 16 ชม. (บ่ายดึก) แล้ว
 * กฎ "วันทำงานติดต่อกัน" ที่มีอยู่จับไม่ได้ — อยู่บ่ายดึก 3 วันติด = 48 ชม.
 * ใน 3 วัน ซึ่งผ่านกฎวันติดกันแต่เกินเพดานชั่วโมงชัดเจน
 */
final class m260813_000002_roster_daytype_and_hours extends Migration
{
    public function safeUp(): void
    {
        foreach ([
            'required_sat' => 'จำนวนคนที่ต้องการวันเสาร์ (NULL = ใช้ค่าวันธรรมดา)',
            'required_sun' => 'จำนวนคนที่ต้องการวันอาทิตย์ (NULL = ใช้ค่าวันธรรมดา)',
            'required_holiday' => 'จำนวนคนที่ต้องการวันหยุดนักขัตฤกษ์ (NULL = ใช้ค่าวันธรรมดา)',
        ] as $col => $comment) {
            $this->addColumn('{{%roster_unit_shift}}', $col,
                $this->integer()->null()->after('required_staff')->comment($comment));
        }
    }

    public function safeDown(): void
    {
        foreach (['required_holiday', 'required_sun', 'required_sat'] as $col) {
            $this->dropColumn('{{%roster_unit_shift}}', $col);
        }
    }
}
