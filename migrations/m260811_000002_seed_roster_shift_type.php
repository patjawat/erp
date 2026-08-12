<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ตั้งต้นประเภทเวร ช/บ/ด/ควบ
 *
 * seed เฉพาะ "ความหมาย" ของเวร — ไม่ seed เวลาเข้า-ออก เพราะแต่ละหน่วยงานใช้เวลาต่างกัน
 * เวลาจริงกรอกที่ roster_unit_shift รายหน่วย
 */
final class m260811_000002_seed_roster_shift_type extends Migration
{
    // color = ชื่อสี Bootstrap ไม่ใช่ hex — เพื่อให้ bg-*-subtle เปลี่ยนตามธีมสว่าง/มืดเอง
    private const ROWS = [
        // code, short_name, title,     is_night, is_ot, is_extra, color,     sort
        ['M', 'ช', 'เวรเช้า', 0, 0, 0, 'warning', 1],
        ['A', 'บ', 'เวรบ่าย', 0, 1, 0, 'info',    2],
        ['N', 'ด', 'เวรดึก',  1, 1, 0, 'primary', 3],
        ['X', 'ค', 'ควบเวร',  0, 1, 1, 'danger',  4],
    ];

    public function safeUp(): void
    {
        $now = date('Y-m-d H:i:s');
        foreach (self::ROWS as [$code, $short, $title, $isNight, $isOt, $isExtra, $color, $sort]) {
            $exists = (new \yii\db\Query())->from('{{%roster_shift_type}}')->where(['code' => $code])->exists();
            if ($exists) {
                continue;
            }
            $this->insert('{{%roster_shift_type}}', [
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                'code' => $code,
                'short_name' => $short,
                'title' => $title,
                'is_night' => $isNight,
                'is_ot' => $isOt,
                'is_extra' => $isExtra,
                'color' => $color,
                'sort_order' => $sort,
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%roster_shift_type}}', ['code' => array_column(self::ROWS, 0)]);
    }
}
