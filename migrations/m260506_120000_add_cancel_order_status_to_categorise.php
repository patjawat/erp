<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * เพิ่มสถานะคำสั่งซื้อ "ยกเลิก" ลงใน categorise
 */
class m260506_120000_add_cancel_order_status_to_categorise extends Migration
{
    public function safeUp()
    {
        $exists = (new Query())
            ->from('categorise')
            ->where([
                'name' => 'order_status',
                'code' => '8',
            ])
            ->exists($this->db);

        $row = [
            'category_id' => '',
            'name' => 'order_status',
            'code' => '8',
            'title' => 'ยกเลิก',
            'active' => 1,
            'data_json' => ['color' => 'secondary'],
        ];

        if ($exists) {
            $this->update('categorise', $row, [
                'name' => 'order_status',
                'code' => '8',
            ]);
            return;
        }

        $this->insert('categorise', $row);
    }

    public function safeDown()
    {
        $this->delete('categorise', [
            'name' => 'order_status',
            'code' => '8',
        ]);
    }
}
