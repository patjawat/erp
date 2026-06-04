<?php

namespace app\modules\inventoryV2\components;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;

/**
 * Unified read-only "movement" bridge across V1 (`stock_events`) and V2
 * (`stock_order` + `stock_detail`).
 *
 * Reports must stay continuous when an organisation migrates from V1 to V2 —
 * both layers can co-exist, and any movement (รับเข้า/จ่ายออก) that occurred in
 * either layer must show up in:
 *   - Stock card (timeline per item)
 *   - Per-item aggregate (เริ่ม/รับ/จ่าย/ปลาย)
 *   - Order-detail listing (each line)
 *   - Monthly closing (aggregate to stock_monthly_report)
 *
 * Each method emits PHP arrays with a stable shape regardless of source.
 *
 * Common conventions:
 *   - Movement direction is based on the MAIN warehouse perspective.
 *     "IN"  = พัสดุเข้าคลังหลัก (PO / รับเข้า / ปรับเพิ่ม)
 *     "OUT" = พัสดุออกจากคลังหลัก (จ่ายให้คลังย่อย / ใช้งาน)
 *   - V1 row qualifies when `e.name='order'`, `i.asset_item IS NOT NULL`,
 *     statuses are 'success', and the order's `warehouse_id` (i.e. คลังหลัก
 *     ของเหตุการณ์) is the MAIN warehouse perspective.
 *   - V2 row qualifies when `so.status='CONFIRMED'` and `so.main_warehouse_id`
 *     matches.
 */
class MovementBridge
{
    /** Default "main" warehouse type code in `warehouses` table. */
    const MAIN_WAREHOUSE_TYPE = 'MAIN';

    /**
     * Normalised in-out movement lines (one row per item line).
     *
     * @param array $opt
     *   - dateFrom (string Y-m-d, inclusive) — optional
     *   - dateTo   (string Y-m-d, inclusive) — optional
     *   - warehouseId (int) — MAIN warehouse to scope on; null = all main warehouses
     *   - itemCode (string) — restrict to a single item
     *   - transactionType ('IN'|'OUT'|null)
     *   - assetTypeCode (string) — categorise.code under name='asset_type'
     *   - orderBy ('ASC'|'DESC') by movement_date
     *   - limit (int)
     *
     * @return array<int, array{
     *   source: string, movement_date: string, order_no: string, order_type: string,
     *   warehouse_id: int|null, warehouse_name: string|null, warehouse_type: string|null,
     *   counterparty_id: string|null, counterparty_name: string|null, counterparty_type: string|null,
     *   item_code: string, item_name: string, asset_type_code: string|null, asset_type_name: string|null,
     *   unit_name: string|null, qty: float, unit_price: float, total_price: float, lot_number: string|null
     * }>
     */
    public static function movements(array $opt = [])
    {
        $rowsV1 = self::movementsV1($opt);
        $rowsV2 = self::movementsV2($opt);
        $rows   = array_merge($rowsV1, $rowsV2);

        $orderBy = strtoupper($opt['orderBy'] ?? 'ASC');
        usort($rows, function ($a, $b) use ($orderBy) {
            $cmp = strcmp((string) $a['movement_date'], (string) $b['movement_date']);
            return $orderBy === 'DESC' ? -$cmp : $cmp;
        });

        $limit = (int) ($opt['limit'] ?? 0);
        if ($limit > 0 && count($rows) > $limit) {
            $rows = array_slice($rows, 0, $limit);
        }
        return $rows;
    }

    /**
     * Aggregate per item between dateFrom and dateTo from BOTH V1 + V2.
     *
     * Returns one row per item with begin / in / out / end qty + value.
     * "Begin" = sum of net movements before dateFrom (bootstrap).
     * "End"   = begin + in - out.
     *
     * @return array<int, array{
     *   item_code: string, asset_name: string, asset_type_code: string|null,
     *   asset_type_name: string|null, unit_name: string|null,
     *   begin_qty: float, begin_price: float,
     *   qty_in: float, price_in: float,
     *   qty_out: float, price_out: float,
     *   end_qty: float, end_price: float
     * }>
     */
    public static function aggregateByItem(array $opt = [])
    {
        $dateFrom = $opt['dateFrom'] ?? null;
        $dateTo   = $opt['dateTo']   ?? null;
        $assetTypeCode = $opt['assetTypeCode'] ?? null;
        $warehouseId   = $opt['warehouseId']   ?? null;

        // 1) Pull all movement lines in the period (and before, for bootstrap).
        $opt['orderBy'] = 'ASC';
        $opt['limit'] = 0;

        $optBefore = $opt;
        $optBefore['dateFrom'] = null;
        if ($dateFrom) {
            $optBefore['dateTo'] = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
        }
        $before = $dateFrom ? self::movements($optBefore) : [];

        $optIn = $opt;
        $optIn['dateFrom'] = $dateFrom;
        $optIn['dateTo']   = $dateTo;
        $within = self::movements($optIn);

        $itemsMap = [];
        $accumulate = function (&$row, $movement, $bucket) {
            $row['item_code']        = $movement['item_code'];
            $row['asset_name']       = $row['asset_name']       ?? $movement['item_name'];
            $row['asset_type_code']  = $row['asset_type_code']  ?? $movement['asset_type_code'];
            $row['asset_type_name']  = $row['asset_type_name']  ?? $movement['asset_type_name'];
            $row['unit_name']        = $row['unit_name']        ?? $movement['unit_name'];
            $q = (float) $movement['qty'];
            $v = (float) $movement['total_price'];
            if ($bucket === 'before') {
                if ($movement['order_type'] === 'IN') {
                    $row['begin_qty']   += $q;
                    $row['begin_price'] += $v;
                } else {
                    $row['begin_qty']   -= $q;
                    $row['begin_price'] -= $v;
                }
            } elseif ($bucket === 'within_in') {
                $row['qty_in']   += $q;
                $row['price_in'] += $v;
            } else { // within_out
                $row['qty_out']   += $q;
                $row['price_out'] += $v;
            }
        };

        $blank = function () {
            return [
                'item_code' => '',
                'asset_name' => '',
                'asset_type_code' => null,
                'asset_type_name' => null,
                'unit_name' => null,
                'begin_qty' => 0.0, 'begin_price' => 0.0,
                'qty_in'    => 0.0, 'price_in'    => 0.0,
                'qty_out'   => 0.0, 'price_out'   => 0.0,
                'end_qty'   => 0.0, 'end_price'   => 0.0,
            ];
        };

        foreach ($before as $m) {
            $code = $m['item_code'];
            if (!isset($itemsMap[$code])) {
                $itemsMap[$code] = $blank();
            }
            $accumulate($itemsMap[$code], $m, 'before');
        }
        foreach ($within as $m) {
            $code = $m['item_code'];
            if (!isset($itemsMap[$code])) {
                $itemsMap[$code] = $blank();
            }
            if ($m['order_type'] === 'IN') {
                $accumulate($itemsMap[$code], $m, 'within_in');
            } else {
                $accumulate($itemsMap[$code], $m, 'within_out');
            }
        }

        foreach ($itemsMap as &$row) {
            $row['end_qty']   = $row['begin_qty']   + $row['qty_in']   - $row['qty_out'];
            $row['end_price'] = $row['begin_price'] + $row['price_in'] - $row['price_out'];
        }
        unset($row);

        // Filter by asset type if not already done at SQL level
        if ($assetTypeCode !== null && $assetTypeCode !== '') {
            $itemsMap = array_filter($itemsMap, function ($r) use ($assetTypeCode) {
                return (string) ($r['asset_type_code'] ?? '') === (string) $assetTypeCode;
            });
        }

        $list = array_values($itemsMap);
        usort($list, function ($a, $b) {
            return strcmp($a['item_code'], $b['item_code']);
        });
        return $list;
    }

    /**
     * Movements pulled from V1 `stock_events` (legacy).
     */
    protected static function movementsV1(array $opt = [])
    {
        if (!self::tableExists('stock_events')) {
            return [];
        }
        $params = [];
        $conds  = [
            "e.name = 'order'",
            "i.asset_item IS NOT NULL",
            "i.name = 'order_item'",
            "e.order_status = 'success'",
            "i.order_status = 'success'",
        ];

        if (!empty($opt['dateFrom'])) {
            $conds[] = 'e.movement_date >= :dateFrom';
            $params[':dateFrom'] = $opt['dateFrom'] . ' 00:00:00';
        }
        if (!empty($opt['dateTo'])) {
            $conds[] = 'e.movement_date <= :dateTo';
            $params[':dateTo'] = $opt['dateTo'] . ' 23:59:59';
        }
        if (!empty($opt['itemCode'])) {
            $conds[] = 'i.asset_item = :itemCode';
            $params[':itemCode'] = $opt['itemCode'];
        }
        if (!empty($opt['transactionType'])) {
            $conds[] = 'i.transaction_type = :ttype';
            $params[':ttype'] = $opt['transactionType'];
        }
        if (!empty($opt['warehouseId'])) {
            // for IN: items arrive at e.warehouse_id (main)
            // for OUT: items leave from e.warehouse_id (main as source)
            $conds[] = 'e.warehouse_id = :whId';
            $params[':whId'] = (int) $opt['warehouseId'];
        }
        if (!empty($opt['assetTypeCode'])) {
            $conds[] = 't.code = :assetType';
            $params[':assetType'] = $opt['assetTypeCode'];
        }

        // Require warehouse_type = MAIN for main perspective (skip pure sub<->sub)
        $conds[] = "COALESCE(wi.warehouse_type, wo.warehouse_type) = 'MAIN'";

        $where = implode(' AND ', $conds);
        $sql = "SELECT
            'V1' AS source,
            e.movement_date,
            e.code AS order_no,
            i.transaction_type AS order_type,
            e.warehouse_id AS warehouse_id,
            wi.warehouse_name AS warehouse_name,
            wi.warehouse_type AS warehouse_type,
            CASE WHEN i.transaction_type = 'IN' THEN e.vendor_id ELSE NULL END AS counterparty_id_in,
            wo.id   AS counterparty_id_out,
            wo.warehouse_name AS counterparty_name_out,
            wo.warehouse_type AS counterparty_type_out,
            i.asset_item AS item_code,
            a.title AS item_name,
            t.code AS asset_type_code,
            t.title AS asset_type_name,
            a.data_json->>'$.unit' AS unit_name,
            CAST(i.qty AS DECIMAL(20,5)) AS qty,
            CAST(i.unit_price AS DECIMAL(20,5)) AS unit_price,
            CAST(i.qty * i.unit_price AS DECIMAL(20,5)) AS total_price,
            i.lot_number,
            v.title AS vendor_name
        FROM stock_events i
        LEFT JOIN stock_events e ON e.id = i.category_id AND e.name = 'order'
        LEFT JOIN warehouses wi ON wi.id = e.warehouse_id
        LEFT JOIN warehouses wo ON wo.id = e.from_warehouse_id
        LEFT JOIN categorise a ON a.code = i.asset_item AND a.name = 'asset_item'
        LEFT JOIN categorise t ON t.code = a.category_id AND t.name = 'asset_type'
        LEFT JOIN (
            SELECT code, title FROM (
                SELECT *, ROW_NUMBER() OVER(PARTITION BY code ORDER BY id) AS rn
                FROM categorise WHERE name = 'vendor'
            ) v WHERE rn = 1
        ) v ON v.code = e.vendor_id
        WHERE $where";

        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();
        $out = [];
        foreach ($rows as $r) {
            $isIn = strtoupper((string) $r['order_type']) === 'IN';
            $out[] = [
                'source' => 'V1',
                'movement_date' => (string) $r['movement_date'],
                'order_no' => (string) $r['order_no'],
                'order_type' => $isIn ? 'IN' : 'OUT',
                'warehouse_id' => $r['warehouse_id'] !== null ? (int) $r['warehouse_id'] : null,
                'warehouse_name' => $r['warehouse_name'],
                'warehouse_type' => $r['warehouse_type'],
                'counterparty_id'   => $isIn ? ($r['counterparty_id_in'] ?: null) : ($r['counterparty_id_out'] !== null ? (int) $r['counterparty_id_out'] : null),
                'counterparty_name' => $isIn ? ($r['vendor_name'] ?: null) : ($r['counterparty_name_out'] ?: null),
                'counterparty_type' => $isIn ? 'VENDOR' : ($r['counterparty_type_out'] ?: null),
                'item_code' => (string) $r['item_code'],
                'item_name' => (string) ($r['item_name'] ?: $r['item_code']),
                'asset_type_code' => $r['asset_type_code'] !== null ? (string) $r['asset_type_code'] : null,
                'asset_type_name' => $r['asset_type_name'],
                'unit_name' => $r['unit_name'],
                'qty' => (float) $r['qty'],
                'unit_price' => (float) $r['unit_price'],
                'total_price' => (float) $r['total_price'],
                'lot_number' => $r['lot_number'],
            ];
        }
        return $out;
    }

    /**
     * Movements pulled from V2 `stock_order` + `stock_detail`.
     */
    protected static function movementsV2(array $opt = [])
    {
        $q = (new Query())
            ->select([
                "source" => new Expression("'V2'"),
                'sd.id',
                'so.order_date AS movement_date',
                'so.order_no',
                'so.order_type',
                'so.main_warehouse_id AS warehouse_id',
                'wmain.warehouse_name AS warehouse_name',
                'wmain.warehouse_type AS warehouse_type',
                'so.sub_warehouse_id AS counterparty_id',
                'wsub.warehouse_name AS counterparty_name',
                'wsub.warehouse_type AS counterparty_type',
                'sd.item_code',
                'si.item_name',
                'si.category_id AS asset_type_code',
                't.title AS asset_type_name',
                'sd.qty',
                'sd.unit_price',
                'sd.lot_number',
                'so.contact_id',
                'so.source_type',
            ])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->leftJoin(['si' => 'stock_item'], 'si.item_code = sd.item_code')
            ->leftJoin(['wmain' => 'warehouses'], 'wmain.id = so.main_warehouse_id')
            ->leftJoin(['wsub' => 'warehouses'],  'wsub.id  = so.sub_warehouse_id')
            ->leftJoin(['t' => 'categorise'], "t.code = si.category_id AND t.name = 'asset_type'")
            ->where(['so.status' => StockOrder::STATUS_CONFIRMED]);

        if (!empty($opt['dateFrom'])) {
            $q->andWhere(['>=', 'so.order_date', $opt['dateFrom'] . ' 00:00:00']);
        }
        if (!empty($opt['dateTo'])) {
            $q->andWhere(['<=', 'so.order_date', $opt['dateTo'] . ' 23:59:59']);
        }
        if (!empty($opt['itemCode'])) {
            $q->andWhere(['sd.item_code' => $opt['itemCode']]);
        }
        if (!empty($opt['transactionType'])) {
            $q->andWhere(['so.order_type' => $opt['transactionType']]);
        }
        if (!empty($opt['warehouseId'])) {
            $q->andWhere(['so.main_warehouse_id' => (int) $opt['warehouseId']]);
        }
        if (!empty($opt['assetTypeCode'])) {
            $q->andWhere(['si.category_id' => $opt['assetTypeCode']]);
        }

        $rows = $q->all();
        $out = [];
        foreach ($rows as $r) {
            $qty = (float) $r['qty'];
            $price = (float) ($r['unit_price'] ?? 0);
            $isIn = strtoupper((string) $r['order_type']) === 'IN';
            // For V2 IN orders, counterparty = vendor (contact_id) — name is fetched lazily.
            $cpId = $isIn ? ($r['contact_id'] ?? null) : ($r['counterparty_id'] ?? null);
            $cpName = $isIn ? null : ($r['counterparty_name'] ?? null);
            $cpType = $isIn ? 'VENDOR' : ($r['counterparty_type'] ?? null);
            $out[] = [
                'source' => 'V2',
                'movement_date' => (string) $r['movement_date'],
                'order_no' => (string) $r['order_no'],
                'order_type' => $isIn ? 'IN' : 'OUT',
                'warehouse_id' => $r['warehouse_id'] !== null ? (int) $r['warehouse_id'] : null,
                'warehouse_name' => $r['warehouse_name'],
                'warehouse_type' => $r['warehouse_type'],
                'counterparty_id'   => $cpId !== null ? (is_numeric($cpId) ? (int) $cpId : (string) $cpId) : null,
                'counterparty_name' => $cpName,
                'counterparty_type' => $cpType,
                'item_code' => (string) $r['item_code'],
                'item_name' => (string) ($r['item_name'] ?: $r['item_code']),
                'asset_type_code' => $r['asset_type_code'] !== null ? (string) $r['asset_type_code'] : null,
                'asset_type_name' => $r['asset_type_name'],
                'unit_name' => self::unitNameForItem($r['item_code']),
                'qty' => $qty,
                'unit_price' => $price,
                'total_price' => $qty * $price,
                'lot_number' => $r['lot_number'],
            ];
        }
        return $out;
    }

    /** memo cache of unit_name per item_code */
    protected static $unitCache = [];

    protected static function unitNameForItem($itemCode)
    {
        $key = (string) $itemCode;
        if ($key === '') return null;
        if (array_key_exists($key, self::$unitCache)) {
            return self::$unitCache[$key];
        }
        $item = \app\modules\inventoryV2\models\StockItem::findOne($itemCode);
        $unit = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;
        return self::$unitCache[$key] = ($unit ?: null);
    }

    /** lightweight table-exists check (so V1 environments without `stock_events` don't blow up) */
    protected static function tableExists($table)
    {
        try {
            return Yii::$app->db->getSchema()->getTableSchema($table, true) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
