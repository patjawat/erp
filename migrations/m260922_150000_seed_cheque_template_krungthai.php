<?php

use yii\db\Migration;
use yii\helpers\Json;

/**
 * Seed แม่แบบเช็คกรุงไทย (พิกัดตั้งต้น — ปรับจริงบนหน้า calibrate)
 * idempotent: ข้ามถ้ามีแม่แบบชื่อเดียวกันแล้ว
 */
class m260922_150000_seed_cheque_template_krungthai extends Migration
{
    public function safeUp()
    {
        $name = 'เช็คกรุงไทย (ตั้งต้น)';
        $exists = (new \yii\db\Query())
            ->from('{{%finance_cheque_template}}')
            ->where(['name' => $name])
            ->exists($this->db);
        if ($exists) {
            echo "    > ข้าม: มีแม่แบบ \"$name\" อยู่แล้ว\n";
            return;
        }

        // พิกัด % ของแผ่นเช็ค (178x82 มม.) — ค่าเริ่มต้น ต้องปรับกับเช็คจริง
        $layout = [
            ['key' => 'cheque_date',   'x' => 78, 'y' => 14, 'font_size' => 16, 'align' => 'L', 'bold' => 0, 'enabled' => 1],
            ['key' => 'payee',         'x' => 22, 'y' => 33, 'font_size' => 16, 'align' => 'L', 'bold' => 0, 'enabled' => 1],
            ['key' => 'amount_text',   'x' => 17, 'y' => 47, 'font_size' => 16, 'align' => 'L', 'bold' => 0, 'enabled' => 1],
            ['key' => 'amount_number', 'x' => 90, 'y' => 47, 'font_size' => 18, 'align' => 'R', 'bold' => 1, 'enabled' => 1],
            ['key' => 'ac_payee',      'x' => 40, 'y' => 60, 'font_size' => 14, 'align' => 'L', 'bold' => 0, 'enabled' => 0],
        ];

        $this->insert('{{%finance_cheque_template}}', [
            'bank_code' => 'KTB',
            'bank_name' => 'ธนาคารกรุงไทย',
            'name' => $name,
            'page_width_mm' => 178,
            'page_height_mm' => 82,
            'layout_json' => Json::encode($layout),
            'calibrate_offset_x' => 0,
            'calibrate_offset_y' => 0,
            'is_active' => 1,
            'note' => 'ค่าพิกัดตั้งต้น ปรับให้ตรงเช็คจริงที่หน้าปรับตำแหน่ง',
            'created_at' => time(),
        ]);
        echo "    > seed แม่แบบ \"$name\" แล้ว\n";
    }

    public function safeDown()
    {
        $this->delete('{{%finance_cheque_template}}', ['name' => 'เช็คกรุงไทย (ตั้งต้น)']);
    }
}
