<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Seed taxonomy "ประเภทสิ่งปลูกสร้าง" ใน categorise (name='structure_type')
 *
 * โครงสร้าง 2 ระดับ — หมวด (3 ตัว) → leaf (17 รายการ)
 *  - หมวดเก็บ data_json = { depreciation_rate, useful_life } เพื่อ auto-fill ค่าเสื่อม
 *  - leaf เก็บ data_json = {} เพื่อ inherit จากหมวด (override ได้เมื่อจำเป็น)
 *  - flag allow_other_note=true บน STR_TEMP_OTHER เปิด text input "ระบุเพิ่มเติม"
 */
class m260625_150000_insert_structure_type_categorise extends Migration
{
    private const NAME = 'structure_type';

    public function safeUp()
    {
        foreach ($this->rows() as $row) {
            $exists = (new Query())
                ->from('categorise')
                ->where(['name' => self::NAME, 'code' => $row['code']])
                ->exists($this->db);

            if ($exists) {
                continue;
            }

            $this->insert('categorise', [
                'category_id' => $row['category_id'],
                'code'        => $row['code'],
                'name'        => self::NAME,
                'title'       => $row['title'],
                'data_json'   => $row['data_json'],
                'active'      => 1,
            ]);
        }
    }

    public function safeDown()
    {
        $codes = array_column($this->rows(), 'code');
        $this->delete('categorise', ['name' => self::NAME, 'code' => $codes]);
    }

    /**
     * @return array<int,array{category_id:?string,code:string,title:string,data_json:array}>
     */
    private function rows(): array
    {
        return [
            // ── หมวด (level 1) ─────────────────────────────────────────────
            [
                'category_id' => null,
                'code'        => 'STR_GRP_TEMP',
                'title'       => 'อาคารชั่วคราว/โรงเรือน',
                'data_json'   => ['depreciation_rate' => 10.00, 'useful_life' => 10],
            ],
            [
                'category_id' => null,
                'code'        => 'STR_GRP_CONCRETE',
                'title'       => 'สิ่งก่อสร้าง — คอนกรีต/เหล็ก',
                'data_json'   => ['depreciation_rate' => 6.66, 'useful_life' => 15],
            ],
            [
                'category_id' => null,
                'code'        => 'STR_GRP_WOOD',
                'title'       => 'สิ่งก่อสร้าง — ไม้/วัสดุอื่น',
                'data_json'   => ['depreciation_rate' => 20.00, 'useful_life' => 5],
            ],

            // ── leaf ใต้ STR_GRP_TEMP ─────────────────────────────────────
            ['category_id' => 'STR_GRP_TEMP', 'code' => 'STR_GARAGE',     'title' => 'โรงเก็บรถ',      'data_json' => []],
            ['category_id' => 'STR_GRP_TEMP', 'code' => 'STR_SHELTER',    'title' => 'ที่พักชั่วคราว', 'data_json' => []],
            ['category_id' => 'STR_GRP_TEMP', 'code' => 'STR_NURSERY',    'title' => 'เรือนเพาะชำ',   'data_json' => []],
            ['category_id' => 'STR_GRP_TEMP', 'code' => 'STR_TEMP_OTHER', 'title' => 'อื่น ๆ (รื้อถอนได้)', 'data_json' => ['allow_other_note' => true]],

            // ── leaf ใต้ STR_GRP_CONCRETE ─────────────────────────────────
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_FENCE_CONCRETE',  'title' => 'รั้วคอนกรีต / ผนังอิฐ',     'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_POOL',            'title' => 'สระว่ายน้ำ',                 'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_YARD_CONCRETE',   'title' => 'ลานคอนกรีต',                 'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_ROAD_CONCRETE',   'title' => 'ถนนคอนกรีต',                  'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_ROAD_ASPHALT',    'title' => 'ถนนลาดยาง',                   'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_BRIDGE_CONCRETE', 'title' => 'สะพานคอนกรีตเสริมเหล็ก',     'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_DAM_EARTH',       'title' => 'เขื่อนดิน',                   'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_DAM_CONCRETE',    'title' => 'เขื่อนปูน',                   'data_json' => []],
            ['category_id' => 'STR_GRP_CONCRETE', 'code' => 'STR_RESERVOIR',       'title' => 'อ่างเก็บน้ำ',                 'data_json' => []],

            // ── leaf ใต้ STR_GRP_WOOD ─────────────────────────────────────
            ['category_id' => 'STR_GRP_WOOD', 'code' => 'STR_FENCE_BARBED', 'title' => 'รั้วลวดหนาม', 'data_json' => []],
            ['category_id' => 'STR_GRP_WOOD', 'code' => 'STR_FENCE_ZINC',   'title' => 'รั้วสังกะสี', 'data_json' => []],
            ['category_id' => 'STR_GRP_WOOD', 'code' => 'STR_FENCE_MESH',   'title' => 'รั้วตาข่าย',  'data_json' => []],
            ['category_id' => 'STR_GRP_WOOD', 'code' => 'STR_FENCE_WOOD',   'title' => 'รั้วไม้',     'data_json' => []],
        ];
    }
}
