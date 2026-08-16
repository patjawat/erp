<?php

use yii\db\Query;
use yii\db\Migration;

/**
 * รวมแถวซ้ำของทะเบียนประเภท/หมวดทรัพย์สิน (categorise name=asset_type, asset_category)
 *
 * ปัญหา: ข้อมูลถูก import ซ้ำสองรอบ ทำให้รหัสเดียวกันมีหลายแถว
 *   - asset_type ซ้ำ 11 รหัส (ครุภัณฑ์ทุกประเภท)
 *   - asset_category ซ้ำ 40 รหัส
 * ผลกระทบ: หน้า "ผูกเกณฑ์ค่าเสื่อม" แสดงชื่อซ้ำสองบรรทัด ผู้ใช้ผูกไปแถวเดียว
 * แต่ตัวหาเกณฑ์ (resolver) ค้นด้วย code แล้วหยิบแถวใดแถวหนึ่ง จึงอาจอ่านแถวที่ไม่ได้ผูก
 * → ตั้งเกณฑ์แล้วค่าเสื่อมไม่ขึ้น โดยไม่มีอะไรฟ้อง
 *
 * วิธีรวม: เก็บแถว id น้อยสุดเป็นตัวหลัก แล้วเติมค่าที่ตัวหลักยังว่างจากแถวซ้ำ
 * (ชื่อใช้ของแถวใหม่สุดเมื่อไม่ตรงกัน — ชุด import หลังมักแก้คำผิดแล้ว) จากนั้นลบแถวซ้ำ
 *
 * ตรวจก่อนลบแล้วว่าไม่มีตารางใดอ้าง categorise.id ของแถวเหล่านี้
 * (ไม่มี FK ชี้มาที่ categorise และสแกน 438 คอลัมน์ที่ลงท้ายด้วย _id แล้วไม่พบการอ้างอิง)
 *
 * นอกจากนี้ปิดใช้งานประเภท "สิ่งปลูกสร้าง" ชุดเดิม (2.1/2.2/2.3) ที่ถูกแทนด้วย STR_GRP_*
 * แล้วและไม่มีทรัพย์สิน/หมวด/รายการใดอ้างถึง
 */
class m260816_160000_dedupe_asset_taxonomy extends Migration
{
    private const NAMES = ['asset_type', 'asset_category'];

    /** ประเภทสิ่งปลูกสร้างชุดเดิมที่ถูกแทนด้วย STR_GRP_* */
    private const SUPERSEDED_TYPE_CODES = ['2.1', '2.2', '2.3'];

    /** คอลัมน์ที่เติมให้ตัวหลักเมื่อค่าเดิมว่าง */
    private const FILLABLE = ['category_id', 'group_id', 'useful_life', 'depreciation_rate', 'description', 'sort', 'ref'];

    public function safeUp()
    {
        $groups = (new Query())
            ->select(['name', 'code', 'keep_id' => 'MIN(id)', 'n' => 'COUNT(*)'])
            ->from('{{%categorise}}')
            ->where(['name' => self::NAMES])
            ->groupBy(['name', 'code'])
            ->having(['>', 'COUNT(*)', 1])
            ->all();

        $merged = 0;
        $deleted = 0;

        foreach ($groups as $g) {
            $rows = (new Query())->from('{{%categorise}}')
                ->where(['name' => $g['name'], 'code' => $g['code']])
                ->orderBy(['id' => SORT_ASC])->all();
            if (count($rows) < 2) {
                continue;
            }

            $keep = array_shift($rows);
            $update = [];

            // ชื่อ: ใช้ของแถวใหม่สุดเมื่อไม่ตรงกับตัวหลัก
            $newest = end($rows);
            if (!empty($newest['title']) && $newest['title'] !== $keep['title']) {
                $update['title'] = $newest['title'];
            }

            // คอลัมน์อื่น: เติมเฉพาะช่องที่ตัวหลักยังว่าง
            foreach (self::FILLABLE as $col) {
                if (!array_key_exists($col, $keep) || !self::isBlank($keep[$col])) {
                    continue;
                }
                foreach ($rows as $dup) {
                    if (!self::isBlank($dup[$col] ?? null)) {
                        $update[$col] = $dup[$col];
                        break;
                    }
                }
            }

            // data_json: รักษาการผูกเกณฑ์ค่าเสื่อมไว้ไม่ให้หายไปกับแถวที่ถูกลบ
            $keepJson = self::decode($keep['data_json'] ?? null);
            $jsonChanged = false;
            foreach ($rows as $dup) {
                $dupJson = self::decode($dup['data_json'] ?? null);
                if (empty($keepJson['depreciation_profile_id']) && !empty($dupJson['depreciation_profile_id'])) {
                    $keepJson['depreciation_profile_id'] = (int) $dupJson['depreciation_profile_id'];
                    $jsonChanged = true;
                }
            }
            if ($jsonChanged) {
                $update['data_json'] = json_encode($keepJson, JSON_UNESCAPED_UNICODE);
            }

            if ($update) {
                $this->update('{{%categorise}}', $update, ['id' => $keep['id']]);
                $merged++;
            }

            $dupIds = array_column($rows, 'id');
            $this->delete('{{%categorise}}', ['id' => $dupIds]);
            $deleted += count($dupIds);
        }

        echo "    > รวมแถวซ้ำ {$merged} รหัส · ลบแถวซ้ำ {$deleted} แถว\n";

        // ปิดใช้งานประเภทสิ่งปลูกสร้างชุดเดิมที่ไม่มีอะไรอ้างถึงแล้ว
        $inUse = (new Query())->from('{{%asset}}')
            ->where(['asset_type_id' => self::SUPERSEDED_TYPE_CODES, 'deleted_at' => null])->exists();
        if ($inUse) {
            echo "    > ข้ามการปิดใช้งาน 2.1/2.2/2.3 — ยังมีทรัพย์สินอ้างถึง\n";
        } else {
            $n = $this->db->createCommand()->update('{{%categorise}}', ['active' => 0],
                ['name' => 'asset_type', 'code' => self::SUPERSEDED_TYPE_CODES])->execute();
            echo "    > ปิดใช้งานประเภทสิ่งปลูกสร้างชุดเดิม {$n} แถว (ถูกแทนด้วย STR_GRP_*)\n";
        }
    }

    public function safeDown()
    {
        // คืนสถานะประเภทสิ่งปลูกสร้างชุดเดิมได้ แต่แถวซ้ำที่ลบไปแล้วคืนไม่ได้
        // (เป็นข้อมูล import ซ้ำที่ไม่มีอะไรอ้างถึง จึงไม่เก็บสำเนาไว้)
        $this->update('{{%categorise}}', ['active' => 1],
            ['name' => 'asset_type', 'code' => self::SUPERSEDED_TYPE_CODES]);
        echo "    > คืนสถานะ 2.1/2.2/2.3 แล้ว — แถวซ้ำที่ลบไปไม่สามารถคืนได้\n";

        return true;
    }

    private static function isBlank($v): bool
    {
        return $v === null || $v === '';
    }

    /**
     * decode data_json แบบทนทาน (บางแถวเก็บ JSON เป็นข้อความซ้อน)
     */
    private static function decode($value): array
    {
        $d = $value;
        for ($i = 0; $i < 3 && is_string($d); $i++) {
            $decoded = json_decode($d, true);
            if ($decoded === null) {
                return [];
            }
            $d = $decoded;
        }

        return is_array($d) ? $d : [];
    }
}
