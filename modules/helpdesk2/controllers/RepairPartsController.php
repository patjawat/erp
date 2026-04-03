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
                        $now = time();
                        $orderSuffix = Yii::$app->security->generateRandomString(6);
                        $repairToken = $repairNumber !== '' ? $repairNumber : ('HDB' . (int) $helpdesk_id);
                        $repairToken = preg_replace('/[^A-Za-z0-9\-_]/', '', $repairToken) ?: ('HDB' . (int) $helpdesk_id);

                        $orderNo = 'ISS-HDB-' . $repairToken . '-' . date('YmdHis') . '-' . $orderSuffix;
                        $orderNo = mb_substr($orderNo, 0, 100);

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
                        $stockOrder->updated_at = $now; // ใช้ filter ในหน้า inventory-v2

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
        $subWarehouses = Warehouse::findSubWarehousesForUser();

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

    public function actionInventoryLookup($q = '', $warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim((string) $q);
        $warehouseId = (int) $warehouse_id;

        $allowedSubWarehouses = Warehouse::findSubWarehousesForUser();
        $allowedWarehouseIds = array_map(static fn($w) => (int) $w->id, $allowedSubWarehouses);
        if ($warehouseId <= 0 || !in_array($warehouseId, $allowedWarehouseIds, true)) {
            return ['results' => []];
        }

        $where = '';
        $params = [];
        if ($q !== '') {
            $where = "WHERE si.is_active = 1 AND sb.warehouse_id = :warehouse_id AND (si.item_code LIKE :q OR si.item_name LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        } else {
            $where = "WHERE si.is_active = 1 AND sb.warehouse_id = :warehouse_id";
        }
        $params[':warehouse_id'] = $warehouseId;

        $sql = "
            SELECT 
                si.item_code,
                si.item_name,
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT(si.data_json, '$.unit_name')), '') AS unit_name,
                COALESCE(SUM(sb.balance_qty), 0) AS balance_qty
            FROM stock_item si
            INNER JOIN stock_balance sb ON sb.item_code = si.item_code
            {$where}
            GROUP BY si.item_code, si.item_name, si.data_json
            ORDER BY si.item_name ASC
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
     * นับจำนวน IN-lots ที่เหลือสำหรับ FIFO (remain_qty > 0)
     */
    private function countFifoUsableInLots(string $itemCode, int $warehouseId): int
    {
        return (int) StockDetail::find()
            ->joinWith('stockOrder')
            ->where([
                'stock_detail.item_code' => $itemCode,
                'stock_order.order_type' => 'IN',
                'stock_order.main_warehouse_id' => $warehouseId,
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
