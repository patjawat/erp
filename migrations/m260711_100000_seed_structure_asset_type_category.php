<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * แปลง taxonomy "สิ่งปลูกสร้าง" จากชุด categorise(name='structure_type') 2 ชั้น
 * ให้อยู่ในรูปแบบเดียวกับครุภัณฑ์ (equip): asset_type + asset_category
 *
 *   structure_type ราก (category_id IS NULL)  → asset_type   (group_id='STRUCT')
 *   structure_type leaf (category_id = รากโค้ด) → asset_category (category_id = โค้ดรากเดิม)
 *
 * คงค่า code/category_id เดิมทุกตัว จึงไม่ต้อง remap และ "สร้างแถวใหม่" ไม่แตะ structure_type เดิม
 * เพื่อให้ rollback ง่ายและฟอร์ม cascade เก่ายังทำงานได้ระหว่างช่วงเปลี่ยนผ่าน
 *
 * ค่าเสื่อม default (useful_life/depreciation_rate) ของรากจะถูกสำเนาลงทุก leaf ด้วย
 * เพื่อให้ auto-fill ตอนเลือก "หมวด" ในฟอร์มแบบ equip มีค่าใช้ทันที (+ คง allow_other_note)
 */
class m260711_100000_seed_structure_asset_type_category extends Migration
{
    private const SRC_NAME   = 'structure_type';
    private const TYPE_NAME  = 'asset_type';
    private const CAT_NAME   = 'asset_category';
    private const GROUP_ID   = 'STRUCT';

    public function safeUp()
    {
        $rows = (new Query())
            ->from('categorise')
            ->where(['name' => self::SRC_NAME])
            ->all($this->db);

        // แยกราก/leaf และทำ index ค่า default ของรากไว้สำเนาให้ leaf
        $roots = [];
        $leaves = [];
        foreach ($rows as $r) {
            if ($r['category_id'] === null || $r['category_id'] === '') {
                $roots[$r['code']] = $r;
            } else {
                $leaves[] = $r;
            }
        }

        foreach ($roots as $code => $r) {
            if ($this->existsRow(self::TYPE_NAME, $code)) {
                continue;
            }
            $this->insert('categorise', [
                'group_id'    => self::GROUP_ID,
                'category_id' => null,
                'code'        => $code,
                'name'        => self::TYPE_NAME,
                'title'       => $r['title'],
                'data_json'   => $this->cleanJson($this->decode($r['data_json'])),
                'active'      => 1,
            ]);
        }

        foreach ($leaves as $r) {
            $code = $r['code'];
            if ($this->existsRow(self::CAT_NAME, $code)) {
                continue;
            }
            $parent = $roots[$r['category_id']] ?? null;
            $parentJson = $parent ? $this->decode($parent['data_json']) : [];
            $leafJson   = $this->decode($r['data_json']);

            // leaf สืบ default ค่าเสื่อมจากราก (ถ้า leaf ไม่ได้กำหนดเอง) + คง allow_other_note
            $merged = [
                'useful_life'       => $leafJson['useful_life']       ?? $parentJson['useful_life']       ?? null,
                'depreciation_rate' => $leafJson['depreciation_rate'] ?? $parentJson['depreciation_rate'] ?? null,
            ];
            if (!empty($leafJson['allow_other_note'])) {
                $merged['allow_other_note'] = true;
            }

            $this->insert('categorise', [
                'group_id'    => self::GROUP_ID,
                'category_id' => $r['category_id'],
                'code'        => $code,
                'name'        => self::CAT_NAME,
                'title'       => $r['title'],
                'data_json'   => $this->cleanJson($merged),
                'active'      => 1,
            ]);
        }
    }

    public function safeDown()
    {
        // ลบเฉพาะแถวที่ derive มาจากชุดนี้ (group_id='STRUCT')
        $this->delete('categorise', ['name' => self::TYPE_NAME, 'group_id' => self::GROUP_ID]);
        $this->delete('categorise', ['name' => self::CAT_NAME, 'group_id' => self::GROUP_ID]);
    }

    private function existsRow(string $name, string $code): bool
    {
        return (new Query())
            ->from('categorise')
            ->where(['name' => $name, 'code' => $code])
            ->exists($this->db);
    }

    private function decode($json): array
    {
        if (is_array($json)) {
            return $json;
        }
        if (is_string($json) && $json !== '') {
            return json_decode($json, true) ?: [];
        }
        return [];
    }

    private function cleanJson(array $data): array
    {
        // ตัด key ที่ค่าเป็น null ออก ให้ json สะอาด แล้วส่ง array ให้ Yii encode เอง (กัน double-encode)
        return array_filter($data, static fn ($v) => $v !== null);
    }
}
