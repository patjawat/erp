<?php

use yii\db\Migration;

/**
 * Phase 0+1 ของแผน "ยุบ stock_item ไปรวมกับ categorise(asset_item)"
 *
 * เป้าหมาย:
 *   - Phase 0 — VERIFY ข้อมูลก่อน: นับ stock_item ที่ไม่มี categorise record คู่กัน
 *   - Phase 1 — MERGE ข้อมูลจาก stock_item เข้า categorise(name=asset_item, group_id=MATER)
 *               โดยไม่แตะ schema (table stock_item + FK ยังคงอยู่)
 *
 * Mapping (จะ degrade อัตโนมัติถ้าคอลัมน์เป้าหมายไม่มีจริงใน DB):
 *   stock_item.item_code            -> categorise.code
 *   stock_item.item_name            -> categorise.title
 *   stock_item.category_id          -> categorise.category_id   (parent = asset_type code)
 *   stock_item.min_qty              -> categorise.qty_min (หรือ min_stock) | fallback: data_json.min_qty
 *   stock_item.max_qty              -> categorise.qty_max (หรือ max_stock) | fallback: data_json.max_qty
 *   stock_item.is_active            -> categorise.active        | fallback: data_json.is_active
 *   stock_item.ref                  -> categorise.ref
 *   stock_item.is_asset             -> categorise.data_json.is_asset
 *   stock_item.is_innovation        -> categorise.data_json.is_innovation
 *   stock_item.data_json.unit_name  -> categorise.data_json.unit_name
 *
 * safeDown:
 *   ลบเฉพาะ categorise record ที่ migration นี้ "สร้างใหม่" (track ผ่าน ref marker)
 *   ไม่ unmerge data_json ของ record เดิม
 */
class m260622_120000_migrate_stock_item_to_categorise extends Migration
{
    const NAME_ASSET_ITEM = 'asset_item';
    const GROUP_MATER = 'MATER';
    const REF_MARKER = '__migrated_from_stock_item__';

    public function safeUp()
    {
        // --- Phase 0: pre-check ---
        if (!$this->tableExistsByName('stock_item')) {
            echo "  [SKIP] table stock_item ไม่มีอยู่ — ข้าม migration นี้\n";
            return true;
        }
        if (!$this->tableExistsByName('categorise')) {
            throw new \Exception('ตาราง categorise ไม่มีอยู่ — ต้องรัน migration categorise ก่อน');
        }

        // ตรวจ schema จริงของ categorise — รองรับชื่อคอลัมน์ qty_min/qty_max หรือ min_stock/max_stock
        $catCols = array_keys($this->db->getTableSchema('categorise', true)->columns);
        $minCol = in_array('qty_min', $catCols, true) ? 'qty_min'
                : (in_array('min_stock', $catCols, true) ? 'min_stock' : null);
        $maxCol = in_array('qty_max', $catCols, true) ? 'qty_max'
                : (in_array('max_stock', $catCols, true) ? 'max_stock' : null);
        $hasActive     = in_array('active', $catCols, true);
        $hasRef        = in_array('ref', $catCols, true);
        $hasGroupId    = in_array('group_id', $catCols, true);
        $hasCategoryId = in_array('category_id', $catCols, true);

        echo "  [Phase 0] categorise columns: min=" . ($minCol ?: 'N')
            . " max=" . ($maxCol ?: 'N')
            . " active=" . ($hasActive?'Y':'N')
            . " ref=" . ($hasRef?'Y':'N')
            . " group_id=" . ($hasGroupId?'Y':'N')
            . " category_id=" . ($hasCategoryId?'Y':'N') . "\n";

        if (!$hasGroupId) {
            throw new \Exception('categorise ไม่มีคอลัมน์ group_id — schema เก่าเกินไปสำหรับ migration นี้');
        }

        $totalStockItem = (int) (new \yii\db\Query())->from('stock_item')->count('*', $this->db);
        echo "  [Phase 0] stock_item ทั้งหมด: {$totalStockItem} record\n";

        if ($totalStockItem === 0) {
            echo "  [SKIP] ไม่มีข้อมูลใน stock_item — ไม่มีอะไรต้อง migrate\n";
            return true;
        }

        $missing = (new \yii\db\Query())
            ->from(['si' => 'stock_item'])
            ->leftJoin(['c' => 'categorise'], "c.code = CONVERT(si.item_code USING utf8mb4) COLLATE utf8mb4_general_ci AND c.name = :n AND c.group_id = :g", [
                ':n' => self::NAME_ASSET_ITEM,
                ':g' => self::GROUP_MATER,
            ])
            ->where(['c.id' => null])
            ->count('*', $this->db);
        echo "  [Phase 0] stock_item ที่ไม่มี categorise(asset_item, MATER) คู่กัน: {$missing} record\n";
        echo "             -> migration จะ INSERT categorise ใหม่ให้ทั้งหมดอัตโนมัติ\n";

        // --- Phase 1: merge ทีละ record (ใช้ transaction) ---
        $stats = ['updated' => 0, 'inserted' => 0, 'skipped' => 0, 'errors' => 0];

        $transaction = $this->db->beginTransaction();
        try {
            $rows = (new \yii\db\Query())
                ->from('stock_item')
                ->all($this->db);

            foreach ($rows as $si) {
                try {
                    $code = (string) $si['item_code'];
                    if ($code === '') {
                        $stats['skipped']++;
                        continue;
                    }

                    $siJson = $this->parseJson($si['data_json'] ?? null);
                    $unitName = $siJson['unit_name'] ?? ($siJson['unit'] ?? null);

                    $existing = (new \yii\db\Query())
                        ->from('categorise')
                        ->where(['code' => $code, 'name' => self::NAME_ASSET_ITEM, 'group_id' => self::GROUP_MATER])
                        ->one($this->db);

                    // merge data_json — เก็บ existing keys + เพิ่ม/override จาก stock_item
                    $existingJson = $existing ? $this->parseJson($existing['data_json'] ?? null) : [];
                    $mergedJson = $existingJson;
                    if ($unitName !== null && $unitName !== '') {
                        $mergedJson['unit_name'] = $unitName;
                    }
                    if (isset($si['is_asset'])) {
                        $mergedJson['is_asset'] = (int) $si['is_asset'];
                    }
                    if (isset($si['is_innovation'])) {
                        $mergedJson['is_innovation'] = (int) $si['is_innovation'];
                    }
                    // ถ้า categorise ไม่มีคอลัมน์ min/max/active — fallback เก็บใน data_json
                    if (!$minCol && isset($si['min_qty'])) {
                        $mergedJson['min_qty'] = (float) $si['min_qty'];
                    }
                    if (!$maxCol && isset($si['max_qty'])) {
                        $mergedJson['max_qty'] = (float) $si['max_qty'];
                    }
                    if (!$hasActive && isset($si['is_active'])) {
                        $mergedJson['is_active'] = (int) $si['is_active'];
                    }

                    // build payload — ใส่เฉพาะคอลัมน์ที่มีจริง
                    if ($existing) {
                        $update = [
                            'title' => ($si['item_name'] !== null && $si['item_name'] !== '')
                                ? $si['item_name']
                                : $existing['title'],
                        ];
                        if ($hasCategoryId && !empty($si['category_id'])) {
                            $update['category_id'] = $si['category_id'];
                        }
                        if ($minCol) {
                            $update[$minCol] = $si['min_qty'] !== null
                                ? (float) $si['min_qty']
                                : ($existing[$minCol] ?? null);
                        }
                        if ($maxCol) {
                            $update[$maxCol] = $si['max_qty'] !== null
                                ? (float) $si['max_qty']
                                : ($existing[$maxCol] ?? null);
                        }
                        if ($hasActive) {
                            $update['active'] = isset($si['is_active'])
                                ? (int) $si['is_active']
                                : $existing['active'];
                        }
                        if ($hasRef && !empty($si['ref'])) {
                            $update['ref'] = $si['ref'];
                        }
                        // ส่ง array ตรงๆ ให้ Yii encode JSON เอง (อย่า pre-encode)
                        $update['data_json'] = !empty($mergedJson) ? $mergedJson : null;

                        $this->db->createCommand()
                            ->update('categorise', $update, ['id' => $existing['id']])
                            ->execute();
                        $stats['updated']++;
                    } else {
                        $insert = [
                            'code'  => $code,
                            'name'  => self::NAME_ASSET_ITEM,
                            'group_id' => self::GROUP_MATER,
                            'title' => $si['item_name'] ?? $code,
                        ];
                        if ($hasCategoryId) {
                            $insert['category_id'] = $si['category_id'] ?? null;
                        }
                        if ($minCol) {
                            $insert[$minCol] = $si['min_qty'] !== null
                                ? (float) $si['min_qty'] : null;
                        }
                        if ($maxCol) {
                            $insert[$maxCol] = $si['max_qty'] !== null
                                ? (float) $si['max_qty'] : 0;
                        }
                        if ($hasActive) {
                            $insert['active'] = isset($si['is_active']) ? (int) $si['is_active'] : 1;
                        }
                        if ($hasRef) {
                            $insert['ref'] = $this->buildRef($si['ref'] ?? null);
                        }
                        $insert['data_json'] = !empty($mergedJson) ? $mergedJson : null;

                        $this->db->createCommand()->insert('categorise', $insert)->execute();
                        $stats['inserted']++;
                    }
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    echo "  [ERROR] item_code={$si['item_code']}: " . $e->getMessage() . "\n";
                    throw $e;
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        echo "  [Phase 1] DONE — updated={$stats['updated']}, inserted={$stats['inserted']}, skipped={$stats['skipped']}, errors={$stats['errors']}\n";
        echo "  [NOTE] ตาราง stock_item + FK ยังไม่ถูกแตะ — รัน Phase 2 เพื่อ refactor models/controllers ต่อ\n";
        return true;
    }

    public function safeDown()
    {
        if (!$this->tableExistsByName('categorise')) {
            return true;
        }
        $catCols = array_keys($this->db->getTableSchema('categorise', true)->columns);
        if (!in_array('ref', $catCols, true)) {
            echo "  [safeDown] categorise ไม่มีคอลัมน์ ref — ไม่สามารถระบุ record ที่ migration นี้สร้างได้\n";
            return true;
        }
        $deleted = $this->db->createCommand()->delete('categorise', [
            'and',
            ['name' => self::NAME_ASSET_ITEM, 'group_id' => self::GROUP_MATER],
            ['like', 'ref', self::REF_MARKER . '%', false],
        ])->execute();
        echo "  [safeDown] ลบ categorise record ที่ migration นี้สร้าง: {$deleted} record\n";
        echo "  [WARN] ไม่ได้ unmerge data_json ของ record เดิม — โปรดตรวจสอบเองหากต้องการ rollback เต็มที่\n";
        return true;
    }

    private function tableExistsByName($table)
    {
        return $this->db->getTableSchema($table, true) !== null;
    }

    private function parseJson($val)
    {
        if (is_array($val)) return $val;
        if (is_string($val) && $val !== '') {
            $d = json_decode($val, true);
            // กรณี double-encoded ('{"...}' ที่ถูก wrap เป็น string)
            if (is_string($d)) {
                $d2 = json_decode($d, true);
                return is_array($d2) ? $d2 : [];
            }
            return is_array($d) ? $d : [];
        }
        return [];
    }

    private function buildRef($existingRef)
    {
        if ($existingRef !== null && $existingRef !== '') {
            return self::REF_MARKER . '|' . $existingRef;
        }
        return self::REF_MARKER;
    }
}
