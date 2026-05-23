<?php

namespace app\modules\inventoryV2\components;

use Yii;
use yii\db\Query;
use yii\db\Expression;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\Warehouse;

/**
 * Sync ข้อมูลจาก V1 (stock_events) → V2 (stock_order + stock_detail)
 *
 * Idempotency:
 *   - ใช้ stock_order.ref = 'V1-EVENT-{v1_id}' เก็บ V1 event id
 *   - ใช้ stock_detail.ref = 'V1-EVENT-{v1_item_id}' เก็บ V1 item id
 *   - sync ซ้ำได้ — รายการเดิม update ตาม V1, รายการใหม่ insert
 *
 * Workflow:
 *   1. preview($from, $to, $warehouseId)  → คืนรายการที่จะ sync (ไม่บันทึก)
 *   2. syncRange($from, $to, $warehouseId) → ทำ sync จริง
 *   3. verify($from, $to, $warehouseId)   → เทียบยอด V1 vs V2 แยกตาม item
 */
class InventorySyncService
{
    const REF_PREFIX = 'V1-EVENT-';

    public $logs = [];   // เก็บ log ระหว่าง sync เพื่อแสดงผล
    public $errors = []; // เก็บ error
    public $stats = [
        'orders_inserted' => 0,
        'orders_updated'  => 0,
        'details_inserted'=> 0,
        'details_updated' => 0,
        'items_skipped'   => 0, // ไม่มีใน stock_item ของ V2
    ];

    // ===================================================================
    // PREVIEW — แสดงรายการที่จะ sync (ไม่บันทึก)
    // ===================================================================
    public function preview($dateFrom, $dateTo, $warehouseId = null)
    {
        $q = (new Query())
            ->select([
                'e.id',
                'e.code',
                'e.movement_date',
                'e.transaction_type',
                'e.warehouse_id',
                'e.from_warehouse_id',
                'e.order_status',
                'e.vendor_id',
                'e.po_number',
                'wh_name' => 'wi.warehouse_name',
                'wh_from' => 'wo.warehouse_name',
                'item_count' => new Expression(
                    "(SELECT COUNT(*) FROM stock_events ei
                       WHERE ei.category_id = e.id
                         AND ei.name = 'order_item'
                         AND ei.order_status = 'success')"
                ),
                'has_ref' => new Expression(
                    "(SELECT COUNT(*) FROM stock_order so
                       WHERE so.ref = CONCAT('" . self::REF_PREFIX . "', e.id))"
                ),
            ])
            ->from(['e' => 'stock_events'])
            ->leftJoin(['wi' => 'warehouses'], 'wi.id = e.warehouse_id')
            ->leftJoin(['wo' => 'warehouses'], 'wo.id = e.from_warehouse_id')
            ->where(['e.name' => 'order'])
            ->andWhere(['e.order_status' => 'success'])
            ->andWhere(['between', 'e.movement_date',
                $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderBy(['e.movement_date' => SORT_ASC, 'e.id' => SORT_ASC]);

        if ($warehouseId) {
            $q->andWhere(['e.warehouse_id' => (int) $warehouseId]);
        }

        return $q->all();
    }

    // ===================================================================
    // SYNC — ทำงานจริง
    // ===================================================================
    public function syncRange($dateFrom, $dateTo, $warehouseId = null)
    {
        $headers = $this->preview($dateFrom, $dateTo, $warehouseId);
        $affectedItemWarehouses = []; // เก็บ {item_code, warehouse_id} เพื่อ recalc balance

        $db = Yii::$app->db;
        foreach ($headers as $h) {
            $tx = $db->beginTransaction();
            try {
                $order = $this->syncOrderHeader($h);
                if ($order === null) continue;

                $detailResults = $this->syncOrderItems($order, (int) $h['id']);
                foreach ($detailResults as $itemCode) {
                    $whId = (int) ($order->main_warehouse_id ?? $order->sub_warehouse_id ?? 0);
                    if ($whId && $itemCode) {
                        $affectedItemWarehouses["{$itemCode}|{$whId}"] = [$itemCode, $whId];
                    }
                }
                $tx->commit();
            } catch (\Throwable $e) {
                $tx->rollBack();
                $this->errors[] = "Order {$h['code']} (V1 id={$h['id']}): " . $e->getMessage();
            }
        }

        // recalc stock_balance (1 ครั้งต่อ item × warehouse)
        foreach ($affectedItemWarehouses as $pair) {
            list($code, $whId) = $pair;
            $this->recalcBalance($code, $whId);
        }

        return [
            'processed' => count($headers),
            'stats'     => $this->stats,
            'errors'    => $this->errors,
        ];
    }

    /**
     * Insert/Update stock_order จาก V1 event header
     */
    protected function syncOrderHeader(array $h)
    {
        $ref = self::REF_PREFIX . $h['id'];
        $order = StockOrder::findOne(['ref' => $ref]);
        $isNew = ($order === null);
        if ($isNew) {
            $order = new StockOrder();
            $order->ref = $ref;
            // order_no: ถ้ามีใน V1 ใช้เลย, ไม่งั้นออกใหม่
            $order->order_no = $h['code'] ?: ('V1-' . $h['id']);
        }

        // map ฟิลด์ V1 → V2
        $orderType = ($h['transaction_type'] === 'IN') ? StockOrder::ORDER_TYPE_IN
                   : (($h['transaction_type'] === 'OUT') ? StockOrder::ORDER_TYPE_OUT : null);
        if (!$orderType) {
            return null; // ไม่ใช่ IN/OUT — ข้าม
        }
        $order->order_type = $orderType;
        $order->order_date = $h['movement_date'];
        $order->status     = StockOrder::STATUS_CONFIRMED;

        if ($orderType === StockOrder::ORDER_TYPE_IN) {
            // รับเข้า: V1 warehouse_id = คลังปลายทาง = main_warehouse_id ของ V2
            $order->main_warehouse_id = $h['warehouse_id'] ?: null;
            $order->sub_warehouse_id  = null;
            $order->source_type       = StockOrder::SOURCE_NORMAL;
        } else {
            // จ่ายออก: V1 from_warehouse_id = คลังต้นทาง (main), warehouse_id = ปลายทาง (sub)
            $order->main_warehouse_id = $h['from_warehouse_id'] ?: $h['warehouse_id'] ?: null;
            $order->sub_warehouse_id  = $h['warehouse_id'] ?: null;
            $order->source_type       = 'REQUEST';
        }

        $order->contact_id = is_numeric($h['vendor_id']) ? (int) $h['vendor_id'] : null;

        // data_json ใส่ metadata จาก V1
        $order->data_json = json_encode([
            'v1_id'           => (int) $h['id'],
            'v1_po_number'    => $h['po_number'],
            'v1_vendor_id'    => $h['vendor_id'],
            'synced_at'       => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE);

        if (!$order->save()) {
            throw new \RuntimeException('save order failed: ' . implode(', ', $order->getFirstErrors()));
        }

        if ($isNew) $this->stats['orders_inserted']++;
        else $this->stats['orders_updated']++;

        return $order;
    }

    /**
     * Insert/Update stock_detail สำหรับ items ของ order นี้
     */
    protected function syncOrderItems(StockOrder $order, $v1HeaderId)
    {
        $items = (new Query())
            ->select([
                'i.id',
                'i.asset_item',
                'i.qty',
                'i.unit_price',
                'i.lot_number',
                'exp_date' => new Expression("i.data_json->>'$.exp_date'"),
            ])
            ->from(['i' => 'stock_events'])
            ->where([
                'i.category_id'  => $v1HeaderId,
                'i.name'         => 'order_item',
                'i.order_status' => 'success',
            ])
            ->andWhere(['IS NOT', 'i.asset_item', null])
            ->all();

        $itemCodes = [];
        foreach ($items as $it) {
            $code = trim((string) $it['asset_item']);
            if ($code === '') continue;

            // ตรวจว่ามีใน stock_item ไหม (FK constraint)
            if (!$this->ensureStockItem($code)) {
                $this->stats['items_skipped']++;
                $this->logs[] = "skip item {$code} (no stock_item record)";
                continue;
            }

            $detailRef = self::REF_PREFIX . $it['id'];
            $detail = StockDetail::findOne(['ref' => $detailRef]);
            $isNew = ($detail === null);
            if ($isNew) {
                $detail = new StockDetail();
                $detail->ref = $detailRef;
            }

            $qty = (float) $it['qty'];
            $detail->stock_order_id = $order->id;
            $detail->item_code      = $code;
            $detail->qty            = $qty;
            $detail->remain_qty     = $qty; // FIFO: เริ่มจาก qty เต็ม (ระบบจะลดเมื่อ issue)
            $detail->unit_price     = (float) ($it['unit_price'] ?? 0);
            $detail->lot_number     = $it['lot_number'] ?: ('LOT-' . $it['id']);
            $detail->expiry_date    = $it['exp_date'] ?: null;
            $detail->data_json      = json_encode([
                'v1_id' => (int) $it['id'],
            ], JSON_UNESCAPED_UNICODE);

            if (!$detail->save()) {
                throw new \RuntimeException('save detail failed for ' . $code . ': '
                    . implode(', ', $detail->getFirstErrors()));
            }
            if ($isNew) $this->stats['details_inserted']++;
            else $this->stats['details_updated']++;

            $itemCodes[] = $code;
        }
        return $itemCodes;
    }

    /**
     * ตรวจว่า item_code มีอยู่ใน stock_item ไหม — ถ้าไม่มี จะสร้างให้อัตโนมัติจาก categorise
     */
    protected function ensureStockItem($itemCode)
    {
        if (StockItem::find()->where(['item_code' => $itemCode])->exists()) {
            return true;
        }
        // ลองสร้างจาก categorise
        $cat = (new Query())
            ->select(['code', 'title', 'category_id'])
            ->from('categorise')
            ->where(['code' => $itemCode, 'name' => 'asset_item'])
            ->one();
        if (!$cat) return false;

        $item = new StockItem();
        $item->item_code = $cat['code'];
        $item->item_name = $cat['title'] ?: $cat['code'];
        $item->category_id = $cat['category_id'];
        $item->is_active = true;
        if (!$item->save()) {
            $this->errors[] = "ensureStockItem failed for {$itemCode}: "
                . implode(', ', $item->getFirstErrors());
            return false;
        }
        $this->logs[] = "auto-created stock_item {$itemCode}";
        return true;
    }

    /**
     * คำนวณ stock_balance ใหม่จาก stock_detail (ของ confirmed orders เท่านั้น)
     * — รายการเดียว: (item_code × warehouse_id × lot_number)
     */
    public function recalcBalance($itemCode, $warehouseId)
    {
        $sql = "
            SELECT sd.lot_number,
                   SUM(CASE WHEN so.order_type = 'IN'  THEN sd.qty ELSE 0 END) -
                   SUM(CASE WHEN so.order_type = 'OUT' THEN sd.qty ELSE 0 END) AS balance
              FROM stock_detail sd
              JOIN stock_order so ON so.id = sd.stock_order_id
             WHERE so.status = :st
               AND sd.item_code = :code
               AND (so.main_warehouse_id = :wh OR so.sub_warehouse_id = :wh)
             GROUP BY sd.lot_number
        ";
        $rows = Yii::$app->db->createCommand($sql, [
            ':st'   => StockOrder::STATUS_CONFIRMED,
            ':code' => $itemCode,
            ':wh'   => (int) $warehouseId,
        ])->queryAll();

        $db = Yii::$app->db;
        // ลบของเก่าก่อน (warehouse + item) แล้วใส่ใหม่
        $db->createCommand()->delete('stock_balance', [
            'item_code'    => $itemCode,
            'warehouse_id' => (int) $warehouseId,
        ])->execute();

        foreach ($rows as $r) {
            if (((float) $r['balance']) <= 0) continue;
            $bal = new StockBalance();
            $bal->item_code    = $itemCode;
            $bal->warehouse_id = (int) $warehouseId;
            $bal->lot_number   = $r['lot_number'] ?: '-';
            $bal->balance_qty  = (float) $r['balance'];
            $bal->save(false);
        }
    }

    // ===================================================================
    // VERIFY — เทียบยอด V1 vs V2 แยกรายสินค้า
    // ===================================================================
    public function verify($dateFrom, $dateTo, $warehouseId = null)
    {
        // V1: รวม qty IN/OUT ในช่วง (จาก stock_events.name='order_item' + header status='success')
        $v1Sql = "
            SELECT
                i.asset_item AS item_code,
                SUM(CASE WHEN i.transaction_type='IN'  THEN i.qty ELSE 0 END) AS v1_in_qty,
                SUM(CASE WHEN i.transaction_type='OUT' THEN i.qty ELSE 0 END) AS v1_out_qty,
                SUM(CASE WHEN i.transaction_type='IN'  THEN i.qty * i.unit_price ELSE 0 END) AS v1_in_value,
                SUM(CASE WHEN i.transaction_type='OUT' THEN i.qty * i.unit_price ELSE 0 END) AS v1_out_value
            FROM stock_events i
            LEFT JOIN stock_events e ON e.id = i.category_id AND e.name='order'
            WHERE i.name='order_item'
              AND i.order_status='success'
              AND e.order_status='success'
              AND i.movement_date BETWEEN :from AND :to
              " . ($warehouseId ? "AND e.warehouse_id = :wh" : "") . "
            GROUP BY i.asset_item
        ";
        $v1Params = [':from' => $dateFrom.' 00:00:00', ':to' => $dateTo.' 23:59:59'];
        if ($warehouseId) $v1Params[':wh'] = (int) $warehouseId;
        $v1Rows = Yii::$app->db->createCommand($v1Sql, $v1Params)->queryAll();

        // V2: รวม qty IN/OUT ในช่วง (จาก stock_detail + stock_order CONFIRMED)
        $v2Sql = "
            SELECT
                sd.item_code,
                SUM(CASE WHEN so.order_type='IN'  THEN sd.qty ELSE 0 END) AS v2_in_qty,
                SUM(CASE WHEN so.order_type='OUT' THEN sd.qty ELSE 0 END) AS v2_out_qty,
                SUM(CASE WHEN so.order_type='IN'  THEN sd.qty * sd.unit_price ELSE 0 END) AS v2_in_value,
                SUM(CASE WHEN so.order_type='OUT' THEN sd.qty * sd.unit_price ELSE 0 END) AS v2_out_value
            FROM stock_detail sd
            JOIN stock_order so ON so.id = sd.stock_order_id
            WHERE so.status = 'CONFIRMED'
              AND so.order_date BETWEEN :from AND :to
              " . ($warehouseId ? "AND (so.main_warehouse_id = :wh OR so.sub_warehouse_id = :wh)" : "") . "
            GROUP BY sd.item_code
        ";
        $v2Rows = Yii::$app->db->createCommand($v2Sql, $v1Params)->queryAll();

        $map = [];
        foreach ($v1Rows as $r) {
            $map[$r['item_code']] = [
                'item_code' => $r['item_code'],
                'v1_in_qty'    => (float) $r['v1_in_qty'],
                'v1_out_qty'   => (float) $r['v1_out_qty'],
                'v1_in_value'  => (float) $r['v1_in_value'],
                'v1_out_value' => (float) $r['v1_out_value'],
                'v2_in_qty'    => 0,
                'v2_out_qty'   => 0,
                'v2_in_value'  => 0,
                'v2_out_value' => 0,
            ];
        }
        foreach ($v2Rows as $r) {
            $code = $r['item_code'];
            if (!isset($map[$code])) {
                $map[$code] = [
                    'item_code' => $code,
                    'v1_in_qty'    => 0,
                    'v1_out_qty'   => 0,
                    'v1_in_value'  => 0,
                    'v1_out_value' => 0,
                    'v2_in_qty'    => 0,
                    'v2_out_qty'   => 0,
                    'v2_in_value'  => 0,
                    'v2_out_value' => 0,
                ];
            }
            $map[$code]['v2_in_qty']    = (float) $r['v2_in_qty'];
            $map[$code]['v2_out_qty']   = (float) $r['v2_out_qty'];
            $map[$code]['v2_in_value']  = (float) $r['v2_in_value'];
            $map[$code]['v2_out_value'] = (float) $r['v2_out_value'];
        }

        // คำนวณ diff
        foreach ($map as &$row) {
            $row['diff_in_qty']    = $row['v1_in_qty']    - $row['v2_in_qty'];
            $row['diff_out_qty']   = $row['v1_out_qty']   - $row['v2_out_qty'];
            $row['diff_in_value']  = $row['v1_in_value']  - $row['v2_in_value'];
            $row['diff_out_value'] = $row['v1_out_value'] - $row['v2_out_value'];
            $row['has_diff'] = abs($row['diff_in_qty']) > 0.001
                            || abs($row['diff_out_qty']) > 0.001
                            || abs($row['diff_in_value']) > 0.001
                            || abs($row['diff_out_value']) > 0.001;
        }
        unset($row);

        ksort($map);
        return array_values($map);
    }
}
