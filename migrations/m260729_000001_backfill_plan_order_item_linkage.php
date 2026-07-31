<?php

use yii\db\Migration;

/**
 * Backfill รายการ plan_order ปีงบ 2569 ที่ plan_item_id หลุด (NULL หรือ dangling)
 * ให้ผูกกับ plan_item ที่ถูกต้อง เพื่อให้หน้า "ติดตามแผนรายจ่าย" (/plan/overview)
 * รวมยอดผ่านสาย item -> plan_item.category_id -> plan_category -> plan_type ได้ครบ
 *
 * ที่มา: คอลัมน์ plan_type_id / plan_category_id บน plan_order ปนเปื้อน
 * (เก็บรหัส plan_budget_type เช่น PE/OE หรือ sentinel PE1 แทนรหัสจริง)
 * source of truth ที่เชื่อถือได้คือ plan_item_id เท่านั้น
 *
 * mapping (ยืนยันโดยผู้ใช้):
 *   - เงินเดือน/ค่าจ้างบุคลากร (id 7,13-21) -> P2 (ลูกจ้างชั่วคราว, หมวด PER_01)
 *   - ค่าสาธารณูปโภค          (id 8)        -> P94 (ค่าไฟฟ้า, หมวด OPS_04)
 *   - ค่าวัสดุ (ยา/เวชภัณฑ์)    (id 9,10)     -> P92 (ยา และเวชภัณฑ์ที่มิใช่ยา, หมวด OPS_03)
 */
class m260729_000001_backfill_plan_order_item_linkage extends Migration
{
    /** id ของ plan_order => รหัส plan_item เป้าหมาย */
    private $map = [
        7  => 'P2',
        13 => 'P2',
        14 => 'P2',
        15 => 'P2',
        16 => 'P2',
        17 => 'P2',
        18 => 'P2',
        19 => 'P2',
        20 => 'P2',
        21 => 'P2',
        8  => 'P94',
        9  => 'P92',
        10 => 'P92',
    ];

    /** ค่าเดิมก่อนแก้ (สำหรับ safeDown) => [id => [plan_type_id, plan_category_id, plan_item_id]] */
    private $original = [
        7  => ['PE1', 'PER_01', 'PER_01_01'],
        8  => ['OPS', 'OE', null],
        9  => ['OPS', 'OE', null],
        10 => ['OPS', 'OE', null],
        13 => ['PE1', 'PE', null],
        14 => ['PE1', 'PE', null],
        15 => ['PE1', 'PE', null],
        16 => ['PE1', 'PE', null],
        17 => ['PE1', 'PE', null],
        18 => ['PE1', 'PE', null],
        19 => ['PE1', 'PE', null],
        20 => ['PE1', 'PE', null],
        21 => ['PE1', 'PE', null],
    ];

    public function safeUp()
    {
        foreach ($this->map as $orderId => $itemCode) {
            // ดึงหมวด (category) และประเภท (type) จากสาย item เพื่อให้ 3 คอลัมน์สอดคล้องกัน
            $chain = (new \yii\db\Query())
                ->select([
                    'cat_code'  => 'i.category_id',
                    'type_code' => 'c.category_id',
                ])
                ->from(['i' => 'categorise'])
                ->leftJoin(['c' => 'categorise'], 'c.code = i.category_id AND c.name = :cat', [':cat' => 'plan_category'])
                ->where(['i.name' => 'plan_item', 'i.code' => $itemCode])
                ->one();

            if (!$chain) {
                echo "  ! ข้าม order #$orderId: ไม่พบ plan_item '$itemCode'\n";
                continue;
            }

            $this->update('plan_order', [
                'plan_item_id'     => $itemCode,
                'plan_category_id' => $chain['cat_code'],
                'plan_type_id'     => $chain['type_code'],
            ], ['id' => $orderId]);

            echo "  ✓ order #$orderId -> item $itemCode (cat {$chain['cat_code']}, type {$chain['type_code']})\n";
        }
    }

    public function safeDown()
    {
        foreach ($this->original as $orderId => $vals) {
            $this->update('plan_order', [
                'plan_type_id'     => $vals[0],
                'plan_category_id' => $vals[1],
                'plan_item_id'     => $vals[2],
            ], ['id' => $orderId]);
        }
    }
}
