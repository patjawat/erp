<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Response;
use app\modules\helpdesk2\models\HelpdeskDetail;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\components\InventoryService;

class RepairPartsController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate($helpdesk_id)
    {
        $model = new HelpdeskDetail([
            'helpdesk_id' => $helpdesk_id
        ]);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $rowsJson = (string) $this->request->post('part_rows_json', '');
            if ($rowsJson === '') {
                return ['status' => 'error', 'message' => 'ไม่พบรายการอะไหล่'];
            }
            $rows = json_decode($rowsJson, true);
            if (!is_array($rows)) {
                return ['status' => 'error', 'message' => 'รูปแบบข้อมูลไม่ถูกต้อง'];
            }

            $tx = Yii::$app->db->beginTransaction();
            try {
                HelpdeskDetail::deleteAll([
                    'helpdesk_id' => (int) $helpdesk_id,
                    'name' => 'part_record',
                ]);

                $helpdesk = Helpdesk::findOne((int) $helpdesk_id);
                $repairNumber = trim((string) ($helpdesk->repair_number ?? ''));
                $stockOrdersByWarehouse = []; // [warehouse_id => StockOrder]

                foreach ($rows as $row) {
                    $itemCode = trim((string) ($row['item_code'] ?? ''));
                    $itemName = trim((string) ($row['item_name'] ?? ''));
                    $qty = (float) ($row['qty'] ?? 0);
                    $warehouseId = (int) ($row['warehouse_id'] ?? 0);
                    if ($itemCode === '' || $itemName === '' || $qty <= 0) {
                        continue;
                    }

                    $unit = trim((string) ($row['unit'] ?? ''));
                    $balance = (float) ($row['balance_qty'] ?? 0);

                    $part = new HelpdeskDetail();
                    $part->helpdesk_id = (int) $helpdesk_id;
                    $part->name = 'part_record';
                    $part->status = 'เบิกอะไหล่';
                    $part->code = $itemCode;
                    $part->title = $itemName;
                    $part->data_json = [
                        'item_code' => $itemCode,
                        'item_name' => $itemName,
                        'qty' => $qty,
                        'unit' => $unit,
                        'balance_qty' => $balance,
                        'warehouse_id' => $warehouseId,
                    ];
                    if (!$part->save()) {
                        throw new \RuntimeException('บันทึกรายการอะไหล่ไม่สำเร็จ');
                    }

                    // สร้างเอกสารจ่ายออกใน inventoryV2 เพื่อให้มี "ประวัติการตัดจ่าย" ในระบบการย่อย (Option B)
                    if (!isset($stockOrdersByWarehouse[$warehouseId])) {
                        // stock_order เก็บเวลาสองรูปแบบ (ตามที่ StockAdjustController ใช้อยู่):
                        //   disbursement_date = int (unix timestamp)
                        //   created_at/updated_at = datetime 'Y-m-d H:i:s'
                        // ห้ามสลับกัน ไม่งั้น MySQL โยน SQLSTATE[22007] Incorrect datetime value
                        $now = time();
                        $nowDateTime = date('Y-m-d H:i:s', $now);
                        // เลขที่เอกสารรูปแบบสั้น ISS-HDB-YYYYMMDD-NNNN (รันรายวัน)
                        // ทรงเดียวกับ SUB-OUT-/REQ- ของ inventoryV2 ซึ่งเป็นเอกสารชนิดเดียวกัน
                        // เลขที่ใบแจ้งซ่อมไม่ต้องใส่ในเลขเอกสาร เพราะเก็บใน ref + data_json.repair_number
                        // (หน้าประวัติการใช้ของหน่วยงานอ่านจาก data_json อยู่แล้ว)
                        $orderNo = $this->generateRepairIssueOrderNo();

                        $stockOrder = new StockOrder();
                        $stockOrder->order_type = StockOrder::ORDER_TYPE_OUT;
                        $stockOrder->order_date = date('Y-m-d');
                        $stockOrder->order_no = $orderNo;
                        $stockOrder->main_warehouse_id = $warehouseId;
                        // เบิกอะไหล่จากงานซ่อม = "การใช้งาน/ตัดจ่าย" ฝั่งคลังย่อย
                        $stockOrder->source_type = 'USAGE';
                        $stockOrder->status = StockOrder::STATUS_CONFIRMED;

                        $stockOrder->ref = 'HELPDESK_ID:' . (int) $helpdesk_id;
                        $stockOrder->data_json = [
                            'helpdesk_id' => (int) $helpdesk_id,
                            'repair_number' => $repairNumber,
                            'source' => 'helpdesk2.repair-parts',
                        ];
                        $stockOrder->setDisbursementDate($now);
                        $stockOrder->created_at = $nowDateTime;
                        $stockOrder->updated_at = $nowDateTime; // ใช้ filter ในหน้า inventory-v2
                        $stockOrder->created_by = Yii::$app->user->id;
                        $stockOrder->updated_by = Yii::$app->user->id;

                        if (!$stockOrder->save(false)) {
                            throw new \RuntimeException('สร้างเอกสารจ่ายออก (inventoryV2) ไม่สำเร็จ');
                        }

                        $stockOrdersByWarehouse[$warehouseId] = $stockOrder;
                    }

                    $stockOrderForWarehouse = $stockOrdersByWarehouse[$warehouseId];
                    $outDetail = new StockDetail();
                    $outDetail->stock_order_id = $stockOrderForWarehouse->id;
                    $outDetail->item_code = $itemCode;
                    $outDetail->qty = $qty;
                    $outDetail->unit_price = 0; // ไม่มีราคาทุนจากระบบซ่อมใน flow นี้
                    $outDetail->lot_number = '-'; // จำเป็นต่อ validate (FIFO จะใช้ lot ของฝั่ง IN อยู่แล้ว)
                    $outDetail->ref = 'HELPDESK_DETAIL_ID:' . (int) $part->id;
                    if (!$outDetail->save(false)) {
                        throw new \RuntimeException('สร้างรายการจ่ายออก (inventoryV2) ไม่สำเร็จ');
                    }

                    // ตัดสต๊อกคลังย่อยทันทีตามจำนวนที่เบิก
                    // พยายามใช้ FIFO ปกติก่อน และ fallback เป็นตัดตามยอดคงเหลือจริงใน stock_balance
                    // เฉพาะกรณีที่ยอดรวมพอแต่ lot/remain_qty ใน stock_detail ไม่ครบ ทำให้ FIFO โยน error
                    try {
                        InventoryService::moveStock(
                            $itemCode,
                            $warehouseId,
                            $qty,
                            'OUT',
                            $stockOrderForWarehouse->id,
                            $outDetail->id
                        );
                    } catch (\Throwable $stockError) {
                        // หมายเหตุ: FIFO โยน error จาก `stock_detail.remain_qty` ไม่ตรง
                        // fallback นี้จะตัดจาก `stock_balance` เฉพาะล็อตที่ balance_qty > 0
                        // ดังนั้นเงื่อนไขเช็คยอดต้องใช้ยอดเฉพาะฝั่งที่ "ตัดได้จริง" ด้วย
                        $availableQtyNet = $this->getAvailableBalanceQty($itemCode, $warehouseId);
                        $availableQtyPositive = $this->getAvailablePositiveBalanceQty($itemCode, $warehouseId);
                        if ($availableQtyPositive + 0.00001 < $qty) {
                            $topLots = $this->getTopPositiveLots($itemCode, $warehouseId, 5);
                            $topLotsText = '';
                            if (!empty($topLots)) {
                                $topLotsText = ' topLots(>0): ' . implode(
                                    ', ',
                                    array_map(static fn($l) => ($l['lot_number'] ?? '-') . ':' . (string) $l['balance_qty'], $topLots)
                                );
                            }

                            $fifoAvailableCount = $this->countFifoUsableInLots($itemCode, $warehouseId);

                            throw new \Exception(sprintf(
                                'พัสดุรหัส %s ในคลังย่อย id=%d มีไม่พอจ่าย (ขาดอีก %s) [ยอดคงเหลือสุทธิ=%.4f, ยอดคงเหลือที่ตัดได้(>0)=%.4f, FIFO_IN_lots_remain>0=%d]%s',
                                $itemCode,
                                (int) $warehouseId,
                                (string) max(0, $qty - $availableQtyPositive),
                                (float) $availableQtyNet,
                                (float) $availableQtyPositive,
                                (int) $fifoAvailableCount,
                                $topLotsText
                            ));
                        }
                        $this->deductFromBalanceLots($itemCode, $warehouseId, $qty);

                        // ถ้า FIFO ทำงานไม่ครบ แต่เราตัดสต๊อกจากยอดคงเหลือแล้ว
                        // ให้ mark remain_qty ของฝั่ง OUT เพื่อให้ประวัติไม่ว่าง
                        $outDetail->remain_qty = $qty;
                        $outDetail->save(false);
                    }
                }

                try {
                    $sumQty = 0.0;
                    foreach ($rows as $r) {
                        $sumQty += (float) ($r['qty'] ?? 0);
                    }
                    $log = new HelpdeskDetail();
                    $log->helpdesk_id = (int) $helpdesk_id;
                    $log->name = 'service_record';
                    $log->status = 'บันทึกการเบิกอะไหล่';
                    $log->title = 'เบิกอะไหล่ ' . count($rows) . ' รายการ';
                    $log->data_json = [
                        'part_count' => count($rows),
                        'part_total_qty' => $sumQty,
                    ];
                    $log->save(false);
                } catch (\Throwable $e) {
                    // ไม่ให้กระทบการบันทึกหลัก
                }

                $tx->commit();
                return ['status' => 'success'];
            } catch (\Throwable $e) {
                $tx->rollBack();
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        $partRows = HelpdeskDetail::find()
            ->where(['helpdesk_id' => (int) $helpdesk_id, 'name' => 'part_record'])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        // opt-in warehouse scope: admin/warehouse เบิกแทนได้ทุกคลังย่อย (idiom เดียวกับ inventoryV2 requisition)
        // ต้องใช้ argument เดียวกับ actionInventoryLookup ไม่งั้น dropdown กับตัวกรองสิทธิ์จะไม่ตรงกัน
        $subWarehouses = Warehouse::findSubWarehousesForUser(true);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'เบิกอะไหล่งานซ่อม'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                    'partRows' => $partRows,
                    'subWarehouses' => $subWarehouses,
                ]),
            ];
        }

        return $this->render('create', [
            'model' => $model,
            'partRows' => $partRows,
            'subWarehouses' => $subWarehouses,
        ]);
    }

    /**
     * เบิกอะไหล่ โดยใช้คลังจากโมดูล inventory (เวอร์ชันเดิม)
     * — UX แบบเดียวกับ actionCreate แต่ดึงข้อมูลคลัง/รายการจาก `warehouses` + `stock` + `categorise`
     * — ห้ามแตะ logic ของ inventoryV2; การหักสต๊อกฝั่ง inventory v1 ไม่ทำในขั้นนี้ (เก็บเฉพาะรายการเบิกใน helpdesk_detail)
     */
    public function actionCreateLegacy($helpdesk_id)
    {
        $model = new HelpdeskDetail([
            'helpdesk_id' => $helpdesk_id
        ]);

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $rowsJson = (string) $this->request->post('part_rows_json', '');
            if ($rowsJson === '') {
                return ['status' => 'error', 'message' => 'ไม่พบรายการอะไหล่'];
            }
            $rows = json_decode($rowsJson, true);
            if (!is_array($rows)) {
                return ['status' => 'error', 'message' => 'รูปแบบข้อมูลไม่ถูกต้อง'];
            }

            $tx = Yii::$app->db->beginTransaction();
            try {
                $helpdesk = \app\modules\helpdesk2\models\Helpdesk::findOne((int) $helpdesk_id);
                $repairNumber = trim((string) ($helpdesk->repair_number ?? ''));

                // 1) คืนสต๊อกจากการเบิกเดิม (ถ้ามี) เพื่อให้บันทึกซ้ำได้แบบ idempotent
                $prevRows = HelpdeskDetail::find()
                    ->where(['helpdesk_id' => (int) $helpdesk_id, 'name' => 'part_record_legacy'])
                    ->all();
                foreach ($prevRows as $prev) {
                    $dj = is_array($prev->data_json ?? null) ? $prev->data_json : [];
                    $alloc = isset($dj['lot_allocation']) && is_array($dj['lot_allocation']) ? $dj['lot_allocation'] : [];
                    foreach ($alloc as $a) {
                        $stockId = (int) ($a['stock_id'] ?? 0);
                        $allocQty = (float) ($a['qty'] ?? 0);
                        if ($stockId <= 0 || $allocQty <= 0) {
                            continue;
                        }
                        $stockRow = \app\modules\inventory\models\Stock::findOne($stockId);
                        if ($stockRow) {
                            $stockRow->qty = (float) $stockRow->qty + $allocQty;
                            $stockRow->save(false);
                        }
                    }
                }
                HelpdeskDetail::deleteAll([
                    'helpdesk_id' => (int) $helpdesk_id,
                    'name' => 'part_record_legacy',
                ]);

                // 2) ออกใบเบิก (parent StockEvent) แยกตามคลัง
                $parentByWarehouse = [];

                foreach ($rows as $row) {
                    $itemCode = trim((string) ($row['item_code'] ?? ''));
                    $itemName = trim((string) ($row['item_name'] ?? ''));
                    $qty = (float) ($row['qty'] ?? 0);
                    $warehouseId = (int) ($row['warehouse_id'] ?? 0);
                    if ($itemCode === '' || $itemName === '' || $qty <= 0 || $warehouseId <= 0) {
                        continue;
                    }
                    $unit = trim((string) ($row['unit'] ?? ''));
                    $balance = (float) ($row['balance_qty'] ?? 0);

                    // FIFO เลือก lot ที่มี qty > 0
                    $lots = \app\modules\inventory\models\Stock::find()
                        ->where(['asset_item' => $itemCode, 'warehouse_id' => $warehouseId])
                        ->andWhere(['>', 'qty', 0])
                        ->orderBy(['id' => SORT_ASC])
                        ->all();
                    $available = 0.0;
                    foreach ($lots as $l) {
                        $available += (float) $l->qty;
                    }
                    if ($available + 0.00001 < $qty) {
                        throw new \RuntimeException(sprintf(
                            'พัสดุ %s ในคลัง id=%d มีไม่พอจ่าย (ต้องการ %.4f, คงเหลือ %.4f)',
                            $itemCode,
                            $warehouseId,
                            $qty,
                            $available
                        ));
                    }

                    // สร้าง parent (StockEvent: name='order', transaction_type='OUT') 1 ใบ/คลัง
                    if (!isset($parentByWarehouse[$warehouseId])) {
                        $parent = new \app\modules\inventory\models\StockEvent();
                        $parent->name = 'order';
                        $parent->order_status = 'success';
                        try {
                            $parent->code = \mdm\autonumber\AutoNumber::generate(
                                'OUT-' . substr(\app\components\AppHelper::YearBudget(), 2) . '????'
                            );
                        } catch (\Throwable $e) {
                            $parent->code = 'OUT-HDB-' . date('YmdHis') . '-' . Yii::$app->security->generateRandomString(4);
                        }
                        $parent->from_warehouse_id = $warehouseId;
                        $parent->warehouse_id = $warehouseId;
                        $parent->transaction_type = 'OUT';
                        $parent->ref = 'HELPDESK_ID:' . (int) $helpdesk_id;
                        $parent->data_json = [
                            'helpdesk_id' => (int) $helpdesk_id,
                            'repair_number' => $repairNumber,
                            'source' => 'helpdesk2.repair-parts-legacy',
                        ];
                        $parent->save(false);
                        $parentByWarehouse[$warehouseId] = $parent;
                    }
                    $parent = $parentByWarehouse[$warehouseId];

                    // ตัดสต๊อกแบบ FIFO และบันทึก StockEvent (order_item) ต่อ lot
                    $remaining = $qty;
                    $allocation = [];
                    foreach ($lots as $lot) {
                        if ($remaining <= 0) break;
                        $lotQty = (float) $lot->qty;
                        if ($lotQty <= 0) continue;
                        $take = min($remaining, $lotQty);

                        $lot->qty = $lotQty - $take;
                        if (!$lot->save(false)) {
                            throw new \RuntimeException('ตัดสต๊อกไม่สำเร็จ (asset_item=' . $itemCode . ')');
                        }

                        $event = new \app\modules\inventory\models\StockEvent();
                        $event->name = 'order_item';
                        $event->order_status = 'success';
                        $event->code = $parent->code;
                        $event->lot_number = (string) ($lot->lot_number ?? '');
                        $event->asset_item = $itemCode;
                        $event->qty = $take;
                        $event->unit_price = (float) ($lot->unit_price ?? 0);
                        $event->transaction_type = 'OUT';
                        $event->warehouse_id = $warehouseId;
                        $event->from_warehouse_id = $warehouseId;
                        $event->category_id = $parent->id;
                        $event->ref = 'HELPDESK_ID:' . (int) $helpdesk_id;
                        $event->save(false);

                        $allocation[] = [
                            'stock_id' => (int) $lot->id,
                            'lot_number' => (string) ($lot->lot_number ?? ''),
                            'qty' => (float) $take,
                            'unit_price' => (float) ($lot->unit_price ?? 0),
                            'stock_event_id' => (int) $event->id,
                        ];
                        $remaining -= $take;
                    }
                    if ($remaining > 0.00001) {
                        throw new \RuntimeException(sprintf(
                            'ตัดสต๊อกไม่ครบ %s (ขาดอีก %.4f)',
                            $itemCode,
                            $remaining
                        ));
                    }

                    // บันทึกประวัติฝั่ง helpdesk
                    $part = new HelpdeskDetail();
                    $part->helpdesk_id = (int) $helpdesk_id;
                    $part->name = 'part_record_legacy';
                    $part->status = 'เบิกอะไหล่ (คลังเดิม)';
                    $part->code = $itemCode;
                    $part->title = $itemName;
                    $part->data_json = [
                        'item_code' => $itemCode,
                        'item_name' => $itemName,
                        'qty' => $qty,
                        'unit' => $unit,
                        'balance_qty' => $balance,
                        'warehouse_id' => $warehouseId,
                        'source' => 'inventory_legacy',
                        'stock_event_parent_id' => (int) $parent->id,
                        'stock_event_code' => (string) $parent->code,
                        'lot_allocation' => $allocation,
                    ];
                    if (!$part->save()) {
                        throw new \RuntimeException('บันทึกรายการอะไหล่ไม่สำเร็จ');
                    }
                }

                try {
                    $sumQty = 0.0;
                    foreach ($rows as $r) {
                        $sumQty += (float) ($r['qty'] ?? 0);
                    }
                    $log = new HelpdeskDetail();
                    $log->helpdesk_id = (int) $helpdesk_id;
                    $log->name = 'service_record';
                    $log->status = 'บันทึกการเบิกอะไหล่ (คลังเดิม)';
                    $log->title = 'เบิกอะไหล่จากคลัง (เดิม) ' . count($rows) . ' รายการ';
                    $log->data_json = [
                        'part_count' => count($rows),
                        'part_total_qty' => $sumQty,
                        'source' => 'inventory_legacy',
                    ];
                    $log->save(false);
                } catch (\Throwable $e) {
                    // ไม่ให้กระทบการบันทึกหลัก
                }

                $tx->commit();
                return ['status' => 'success'];
            } catch (\Throwable $e) {
                $tx->rollBack();
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        $partRows = HelpdeskDetail::find()
            ->where(['helpdesk_id' => (int) $helpdesk_id, 'name' => 'part_record_legacy'])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        // ใช้รายการคลังย่อยแบบเดียวกับ flow inventoryV2 (กรองตาม user/แผนกที่ล็อกอิน)
        $subWarehouses = Warehouse::findSubWarehousesForUser();

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'เบิกอะไหล่จากคลัง (เดิม)'),
                'content' => $this->renderAjax('create_legacy', [
                    'model' => $model,
                    'partRows' => $partRows,
                    'subWarehouses' => $subWarehouses,
                ]),
            ];
        }

        return $this->render('create_legacy', [
            'model' => $model,
            'partRows' => $partRows,
            'subWarehouses' => $subWarehouses,
        ]);
    }

    /**
     * ค้นหาอะไหล่จากคลัง inventory (เวอร์ชันเดิม)
     */
    public function actionInventoryLookupLegacy($q = '', $warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim((string) $q);
        $warehouseId = (int) $warehouse_id;
        if ($warehouseId <= 0) {
            return ['results' => []];
        }

        $where = "WHERE s.warehouse_id = :warehouse_id AND c.name = 'asset_item'";
        $params = [':warehouse_id' => $warehouseId];
        if ($q !== '') {
            $where .= " AND (s.asset_item LIKE :q OR c.title LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $sql = "
            SELECT
                s.asset_item AS item_code,
                COALESCE(c.title, s.asset_item) AS item_name,
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT(c.data_json, '$.unit_name')), '') AS unit_name,
                COALESCE(SUM(s.qty), 0) AS balance_qty
            FROM stock s
            LEFT JOIN categorise c ON c.code = s.asset_item AND c.name = 'asset_item'
            {$where}
            GROUP BY s.asset_item, c.title, c.data_json
            HAVING balance_qty > 0
            ORDER BY item_name ASC
            LIMIT 30
        ";
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'item_code' => (string) ($r['item_code'] ?? ''),
                'item_name' => (string) ($r['item_name'] ?? ''),
                'unit_name' => (string) ($r['unit_name'] ?? ''),
                'balance_qty' => (float) ($r['balance_qty'] ?? 0),
            ];
        }
        return ['results' => $results];
    }

    public function actionInventoryLookup($q = '', $warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim((string) $q);
        $warehouseId = (int) $warehouse_id;

        $allowedSubWarehouses = Warehouse::findSubWarehousesForUser(true);
        $allowedWarehouseIds = array_map(static fn($w) => (int) $w->id, $allowedSubWarehouses);
        if ($warehouseId <= 0 || !in_array($warehouseId, $allowedWarehouseIds, true)) {
            return ['results' => []];
        }

        // ทะเบียนพัสดุ inventoryV2 ถูกยุบไปรวมกับ `categorise` (name='asset_item', group_id='MATER')
        // ตาม StockItem/StockItemQuery แล้ว — ตาราง `stock_item` เดิมว่างเปล่า ห้ามใช้
        // column mapping: code -> item_code, title -> item_name, active -> is_active
        $where = "WHERE si.name = 'asset_item' AND si.group_id = 'MATER' AND si.active = 1 AND sb.warehouse_id = :warehouse_id";
        $params = [];
        if ($q !== '') {
            $where .= " AND (si.code LIKE :q OR si.title LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }
        $params[':warehouse_id'] = $warehouseId;

        $sql = "
            SELECT
                si.code AS item_code,
                si.title AS item_name,
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT(si.data_json, '$.unit_name')), '') AS unit_name,
                COALESCE(SUM(sb.balance_qty), 0) AS balance_qty
            FROM categorise si
            INNER JOIN stock_balance sb ON sb.item_code = si.code
            {$where}
            GROUP BY si.code, si.title, si.data_json
            HAVING balance_qty > 0
            ORDER BY item_name ASC
            LIMIT 30
        ";
        $rows = Yii::$app->db->createCommand($sql, $params)->queryAll();

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'item_code' => (string) ($r['item_code'] ?? ''),
                'item_name' => (string) ($r['item_name'] ?? ''),
                'unit_name' => (string) ($r['unit_name'] ?? ''),
                'balance_qty' => (float) ($r['balance_qty'] ?? 0),
            ];
        }

        return ['results' => $results];
    }

    /**
     * เลขที่ใบตัดจ่ายของงานซ่อม: ISS-HDB-YYYYMMDD-NNNN (รันรายวัน)
     * ล้อรูปแบบ SubStockController::generateSubIssueOrderNo() เพราะเป็นเอกสารชนิดเดียวกัน
     * (order_type=OUT, source_type=USAGE, จ่ายออกจากคลังย่อย)
     */
    private function generateRepairIssueOrderNo(): string
    {
        $prefix = 'ISS-HDB-' . date('Ymd') . '-';
        for ($n = 1; $n <= 9999; $n++) {
            $no = $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
            if (StockOrder::findOne(['order_no' => $no]) === null) {
                return $no;
            }
        }
        // กันเหนียว: เลขรันวันนี้เต็ม (แทบเป็นไปไม่ได้) — ต่อท้ายด้วยเวลาแทน
        // order_no เป็น UNIQUE ถ้าชนจริงจะ throw แล้ว transaction rollback ทั้งชุด ไม่มีข้อมูลค้าง
        return $prefix . date('His');
    }

    private function getAvailableBalanceQty(string $itemCode, int $warehouseId): float
    {
        return (float) StockBalance::find()
            ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
            ->sum('balance_qty');
    }

    /**
     * ยอดคงเหลือที่ "ตัดได้จริง" ตาม logic ของ deductFromBalanceLots
     */
    private function getAvailablePositiveBalanceQty(string $itemCode, int $warehouseId): float
    {
        return (float) StockBalance::find()
            ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
            ->andWhere(['>', 'balance_qty', 0])
            ->sum('balance_qty');
    }

    /**
     * ดึง lot ที่มี balance_qty > 0 สำหรับช่วย debug ตอนตัดไม่ผ่าน
     * @return array<int, array{lot_number:string, balance_qty:float}>
     */
    private function getTopPositiveLots(string $itemCode, int $warehouseId, int $limit = 5): array
    {
        $rows = StockBalance::find()
            ->select(['lot_number', 'balance_qty'])
            ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
            ->andWhere(['>', 'balance_qty', 0])
            ->orderBy(['balance_qty' => SORT_DESC, 'lot_number' => SORT_ASC])
            ->limit($limit)
            ->asArray()
            ->all();

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'lot_number' => (string) ($r['lot_number'] ?? '-'),
                'balance_qty' => (float) ($r['balance_qty'] ?? 0),
            ];
        }
        return $out;
    }

    /**
     * นับจำนวน source lots ที่เหลือสำหรับ FIFO (remain_qty > 0)
     */
    private function countFifoUsableInLots(string $itemCode, int $warehouseId): int
    {
        return (int) StockDetail::find()
            ->joinWith('stockOrder')
            ->where(['stock_detail.item_code' => $itemCode])
            ->andWhere(['stock_order.status' => StockOrder::STATUS_CONFIRMED])
            ->andWhere(['or',
                ['and',
                    ['stock_order.main_warehouse_id' => $warehouseId],
                    ['or',
                        ['stock_order.order_type' => StockOrder::ORDER_TYPE_IN],
                        ['and',
                            ['stock_order.order_type' => StockOrder::ORDER_TYPE_ADJUST],
                            ['>', 'stock_detail.qty', 0],
                        ],
                    ],
                ],
                ['and',
                    ['stock_order.order_type' => StockOrder::ORDER_TYPE_TRANSFER],
                    ['stock_order.sub_warehouse_id' => $warehouseId],
                    ['>', 'stock_detail.qty', 0],
                ],
            ])
            ->andWhere(['>', 'stock_detail.remain_qty', 0])
            ->count();
    }

    private function deductFromBalanceLots(string $itemCode, int $warehouseId, float $qty): void
    {
        $remaining = (float) $qty;
        $lots = StockBalance::find()
            ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId])
            ->andWhere(['>', 'balance_qty', 0])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        foreach ($lots as $lot) {
            if ($remaining <= 0) {
                break;
            }
            $lotQty = (float) $lot->balance_qty;
            if ($lotQty <= 0) {
                continue;
            }
            $take = min($remaining, $lotQty);
            $lot->balance_qty = $lotQty - $take;
            if (!$lot->save(false)) {
                throw new \RuntimeException('ตัดสต๊อกจากยอดคงเหลือไม่สำเร็จ');
            }
            $remaining -= $take;
        }

        if ($remaining > 0.00001) {
            throw new \RuntimeException("พัสดุรหัส {$itemCode} ในคลังมีไม่พอจ่าย (ขาดอีก {$remaining})");
        }
    }
}
