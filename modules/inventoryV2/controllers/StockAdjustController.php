<?php

namespace app\modules\inventoryV2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;

/**
 * ปรับยอด stock สินค้า (เพิ่ม/ลดยอดคงเหลือโดยตรง)
 */
class StockAdjustController extends Controller
{
    /**
     * ฟอร์มปรับยอด
     */
    public function actionIndex()
    {
        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- เลือกคลัง --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        // prefill จาก variance banner ในหน้า main-stock/balance
        $request = Yii::$app->request;
        $prefill = [
            'warehouse_id' => (int) $request->get('prefill_warehouse_id', 0) ?: null,
            'item_code' => trim((string) $request->get('prefill_item_code', '')) ?: null,
            'qty' => $request->get('prefill_qty'),
            'current_qty' => $request->get('prefill_current_qty'),
            'target_qty' => $request->get('prefill_target_qty'),
            'lot_number' => trim((string) $request->get('prefill_lot_number', '')),
            'note' => trim((string) $request->get('prefill_note', '')),
            'source' => trim((string) $request->get('prefill_source', '')),
        ];
        foreach (['qty', 'current_qty', 'target_qty'] as $key) {
            if ($prefill[$key] !== null && $prefill[$key] !== '') {
                $prefill[$key] = is_numeric($prefill[$key]) ? (string) (float) $prefill[$key] : null;
            } else {
                $prefill[$key] = null;
            }
        }
        if ($prefill['qty'] === null && $prefill['current_qty'] !== null && $prefill['target_qty'] !== null) {
            $prefill['qty'] = (string) ((float) $prefill['target_qty'] - (float) $prefill['current_qty']);
        }

        return $this->render('index', [
            'warehouses' => $warehouses,
            'prefill' => $prefill,
        ]);
    }

    /**
     * API: คืนยอดคงเหลือรวมของพัสดุในคลัง (สำหรับแสดงในฟอร์ม)
     */
    public function actionGetBalance()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouseId = (int) Yii::$app->request->get('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->get('item_code', ''));

        if ($warehouseId <= 0 || $itemCode === '') {
            return ['balance' => 0, 'error' => 'กรุณาเลือกคลังและรหัสพัสดุ'];
        }

        $sum = (float) (new Query())
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
            ->sum('balance_qty');

        // มูลค่าคงเหลือแบบ ledger (ตรงกับหน้าสรุปยอดคงเหลือ + ประวัติ) และต้นทุนเฉลี่ยต่อหน่วย
        $ledgerMap = \app\modules\inventoryV2\controllers\ReportController::loadLedgerValues([$warehouseId]);
        $value = (float) ($ledgerMap[$warehouseId . ':' . $itemCode] ?? 0.0);
        $avgCost = $sum > 0 ? $value / $sum : 0.0;

        return [
            'balance' => $sum,
            'value' => round($value, 2),
            'avg_cost' => round($avgCost, 6),
        ];
    }

    /**
     * ฟอร์มปรับยอดแบบ modal (เปิดจากหน้า balance ผ่าน .open-modal) — ผูกกับพัสดุ+คลังที่เลือกไว้แล้ว
     * คืน JSON {status, title, content, footer} ตาม convention ของ .open-modal (web/js/erp.js)
     */
    public function actionModal($warehouse_id, $item_code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $warehouseId = (int) $warehouse_id;
        $itemCode = trim((string) $item_code);

        $item = StockItem::findOne(['item_code' => $itemCode]);
        $warehouse = Warehouse::findOne($warehouseId);
        if (!$item || !$warehouse) {
            return ['status' => 'error', 'message' => 'ไม่พบพัสดุหรือคลังในระบบ'];
        }

        $balance = (float) (new Query())
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
            ->sum('balance_qty');
        $ledgerMap = \app\modules\inventoryV2\controllers\ReportController::loadLedgerValues([$warehouseId]);
        $value = (float) ($ledgerMap[$warehouseId . ':' . $itemCode] ?? 0.0);
        $avgCost = $balance > 0 ? $value / $balance : 0.0;

        return [
            'status' => 'success',
            'title' => '<i class="bi bi-wrench-adjustable me-1"></i> ปรับยอด stock สินค้า',
            'content' => $this->renderPartial('_adjust_modal', [
                'item' => $item,
                'warehouse' => $warehouse,
                'balance' => $balance,
                'value' => $value,
                'avgCost' => $avgCost,
                'saveUrl' => \yii\helpers\Url::to(['/inventory-v2/stock-adjust/save']),
            ]),
            'footer' => '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>',
        ];
    }

    /**
     * บันทึกการปรับยอด (สร้างเอกสาร ADJUST และอัปเดต stock_balance)
     */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->post('item_code', ''));
        $adjustmentQty = (float) Yii::$app->request->post('adjustment_qty', 0);
        $currentQtyRaw = Yii::$app->request->post('current_qty');
        $targetQtyRaw = Yii::$app->request->post('target_qty');
        $currentQty = is_numeric($currentQtyRaw) ? (float) $currentQtyRaw : null;
        $targetQty = is_numeric($targetQtyRaw) ? (float) $targetQtyRaw : null;
        if ($adjustmentQty == 0 && $currentQty !== null && $targetQty !== null) {
            $adjustmentQty = $targetQty - $currentQty;
        }
        $source = trim((string) Yii::$app->request->post('source', ''));
        $reverseDetailId = (int) Yii::$app->request->post('reverse_detail_id', 0);
        $reverseOrderNo = trim((string) Yii::$app->request->post('reverse_order_no', ''));
        $note = trim((string) Yii::$app->request->post('note', ''));
        // โหมด: 'qty_only' = ปรับจำนวนอย่างเดียว ไม่กระทบมูลค่า (unit_price=null)
        //        อื่นๆ (recount) = คิดมูลค่า: เพิ่มใช้ต้นทุนที่ระบุ/เฉลี่ย, ลดคิดตามต้นทุนเฉลี่ย
        $mode = trim((string) Yii::$app->request->post('mode', 'recount'));
        $isValueMode = ($mode !== 'qty_only');
        $unitPriceRaw = Yii::$app->request->post('unit_price');
        $unitPriceInput = is_numeric($unitPriceRaw) ? (float) $unitPriceRaw : null;

        if ($warehouseId <= 0) {
            return ['success' => false, 'message' => 'กรุณาเลือกคลัง'];
        }
        if ($itemCode === '') {
            return ['success' => false, 'message' => 'กรุณาเลือกรหัสพัสดุ'];
        }
        if ($adjustmentQty == 0) {
            return ['success' => false, 'message' => 'กรุณาระบุจำนวนที่ปรับ (บวก = เพิ่ม, ลบ = ลด)'];
        }

        $item = StockItem::findOne(['item_code' => $itemCode]);
        if (!$item) {
            return ['success' => false, 'message' => 'ไม่พบรหัสพัสดุในระบบ'];
        }

        $warehouse = Warehouse::findOne($warehouseId);
        if (!$warehouse) {
            return ['success' => false, 'message' => 'ไม่พบคลังในระบบ'];
        }

        // ต้นทุนเฉลี่ยปัจจุบัน (moving-average) = มูลค่า ledger ÷ จำนวนคงเหลือ
        $ledgerMap = \app\modules\inventoryV2\controllers\ReportController::loadLedgerValues([$warehouseId]);
        $currentValue = (float) ($ledgerMap[$warehouseId . ':' . $itemCode] ?? 0.0);
        $currentQtyBalance = (float) StockBalance::find()
            ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
            ->sum('balance_qty');
        $avgCost = $currentQtyBalance > 0 ? $currentValue / $currentQtyBalance : 0.0;

        // ราคาต่อหน่วยของเอกสาร ADJUST ตามโหมด/ทิศทาง
        if (!$isValueMode) {
            $detailUnitPrice = null;                       // qty_only = ไม่กระทบมูลค่า
        } elseif ($adjustmentQty > 0) {
            // เพิ่ม (เจอของ) — ใช้ราคาที่กรอก มิฉะนั้นใช้ต้นทุนเฉลี่ย
            $detailUnitPrice = ($unitPriceInput !== null && $unitPriceInput >= 0) ? $unitPriceInput : $avgCost;
        } else {
            // ลด (ของหาย/เสีย) — คิดต้นทุนตามราคาเฉลี่ยปัจจุบัน
            $detailUnitPrice = $avgCost;
        }
        $valueDelta = $isValueMode ? ($adjustmentQty * (float) $detailUnitPrice) : 0.0;

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $orderNo = $this->generateAdjustOrderNo();
            $now = date('Y-m-d H:i:s');

            // เพิ่มแบบคิดมูลค่า → lot เฉพาะผูกกับเอกสาร (ต้นทุน lot นี้ = detailUnitPrice) เพื่อ FIFO อนาคต
            // นอกนั้นใช้ lot 'ADJUST'
            $lotNumber = ($isValueMode && $adjustmentQty > 0) ? $orderNo : 'ADJUST';

            $order = new StockOrder();
            $order->order_no = $orderNo;
            $order->order_type = StockOrder::ORDER_TYPE_ADJUST;
            $order->source_type = StockOrder::ORDER_TYPE_ADJUST;
            $order->order_date = $now;
            $order->main_warehouse_id = $warehouseId;
            $order->status = StockOrder::STATUS_CONFIRMED;
            $order->ref = $note ?: 'ปรับยอด';
            $order->data_json = json_encode([
                'adjust_source' => $source ?: 'stock-adjust',
                'adjust_mode' => $isValueMode ? 'recount' : 'qty_only',
                'current_qty_before' => $currentQty,
                'target_qty' => $targetQty,
                'adjustment_qty' => $adjustmentQty,
                'lot_number' => $lotNumber,
                'unit_price' => $detailUnitPrice,
                'avg_cost_before' => round($avgCost, 6),
                'value_delta' => round($valueDelta, 2),
                'note' => $note,
                'reverse_detail_id' => $reverseDetailId ?: null,
                'reverse_order_no' => $reverseOrderNo ?: null,
                'created_from' => Yii::$app->request->referrer,
            ], JSON_UNESCAPED_UNICODE);
            $order->created_at = $now;
            $order->updated_at = $now;
            $order->created_by = Yii::$app->user->id;
            $order->updated_by = Yii::$app->user->id;
            if (!$order->save(false)) {
                throw new \Exception('บันทึกหัวเอกสารไม่สำเร็จ');
            }

            $detail = new StockDetail();
            $detail->stock_order_id = $order->id;
            $detail->item_code = $itemCode;
            $detail->qty = $adjustmentQty;
            $detail->unit_price = $detailUnitPrice; // ledger คิดมูลค่าจากราคานี้ (null = ไม่กระทบมูลค่า)
            $detail->lot_number = $lotNumber;
            $detail->remain_qty = $adjustmentQty > 0 ? $adjustmentQty : 0;
            $detail->created_at = $now;
            $detail->updated_at = $now;
            $detail->created_by = Yii::$app->user->id;
            $detail->updated_by = Yii::$app->user->id;
            if (!$detail->save(false)) {
                throw new \Exception('บันทึกรายละเอียดไม่สำเร็จ');
            }

            if ($adjustmentQty > 0) {
                InventoryService::adjustBalance($itemCode, $warehouseId, $lotNumber, $adjustmentQty);
            } else {
                InventoryService::processFIFO($itemCode, $warehouseId, abs($adjustmentQty), $order->id, $detail->id);
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'ปรับยอดสำเร็จ',
                'order_no' => $orderNo,
                'value_delta' => round($valueDelta, 2),
                'mode' => $isValueMode ? 'recount' : 'qty_only',
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * แก้จำนวนรายการในใบเบิกที่ยืนยันแล้วจาก modal ประวัติการเคลื่อนไหววัสดุ
     * ระบบแก้ stock_detail.qty ของใบเบิก และปรับผลต่างเข้า/ออก stock_balance + FIFO
     */
    public function actionUpdateRequisitionDetailQty()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        $detailId = (int) Yii::$app->request->post('detail_id', 0);
        $newQty = (float) Yii::$app->request->post('qty', 0);
        $newUnitPriceRaw = Yii::$app->request->post('unit_price');
        $newUnitPrice = is_numeric($newUnitPriceRaw) ? (float) $newUnitPriceRaw : null;
        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->post('item_code', ''));
        $note = trim((string) Yii::$app->request->post('note', ''));

        if ($detailId <= 0) {
            return ['success' => false, 'message' => 'ไม่พบรายการใบเบิกที่ต้องการแก้ไข'];
        }
        if ($newQty <= 0) {
            return ['success' => false, 'message' => 'จำนวนใหม่ต้องมากกว่า 0'];
        }
        if ($newUnitPrice !== null && $newUnitPrice < 0) {
            return ['success' => false, 'message' => 'ราคา/หน่วยต้องไม่น้อยกว่า 0'];
        }

        $detail = StockDetail::findOne($detailId);
        if (!$detail) {
            return ['success' => false, 'message' => 'ไม่พบรายการใบเบิกในระบบ'];
        }

        $order = $detail->stockOrder;
        if (!$order || $order->order_type !== StockOrder::ORDER_TYPE_OUT) {
            return ['success' => false, 'message' => 'แก้จำนวนได้เฉพาะรายการในใบเบิก'];
        }
        if ($order->status !== StockOrder::STATUS_CONFIRMED) {
            return ['success' => false, 'message' => 'แก้ได้เฉพาะใบเบิกที่ยืนยันแล้ว'];
        }

        $warehouseId = $warehouseId > 0 ? $warehouseId : (int) $order->main_warehouse_id;
        $itemCode = $itemCode !== '' ? $itemCode : (string) $detail->item_code;
        if ((string) $detail->item_code !== $itemCode || (int) $order->main_warehouse_id !== $warehouseId) {
            return ['success' => false, 'message' => 'รายการที่ส่งมาไม่ตรงกับคลังหรือรหัสพัสดุ'];
        }

        $oldRawQty = (float) $detail->qty;
        $oldQty = abs($oldRawQty);
        $oldUnitPrice = (float) $detail->unit_price;
        if ($newUnitPrice === null) {
            $newUnitPrice = $oldUnitPrice;
        }
        $delta = $newQty - $oldQty;
        $priceDelta = $newUnitPrice - $oldUnitPrice;
        if (abs($delta) < 0.000001 && abs($priceDelta) < 0.000001) {
            return [
                'success' => true,
                'message' => 'จำนวนเดิมตรงกับจำนวนใหม่แล้ว',
                'order_no' => $order->order_no,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'old_unit_price' => $oldUnitPrice,
                'new_unit_price' => $newUnitPrice,
                'delta' => 0,
            ];
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            if ($delta > 0) {
                InventoryService::processFIFO($itemCode, $warehouseId, $delta, $order->id, $detail->id);
                $detail = StockDetail::findOne($detailId);
            } else {
                InventoryService::returnFifoAllocation($detail, $warehouseId, abs($delta));
            }

            $now = date('Y-m-d H:i:s');
            $detailData = is_array($detail->data_json)
                ? $detail->data_json
                : (is_string($detail->data_json) ? (json_decode($detail->data_json, true) ?: []) : []);
            if (!is_array($detailData)) {
                $detailData = [];
            }
            if (!isset($detailData['history_inline_qty_edits']) || !is_array($detailData['history_inline_qty_edits'])) {
                $detailData['history_inline_qty_edits'] = [];
            }
            $detailData['history_inline_qty_edits'][] = [
                'at' => $now,
                'by_user_id' => Yii::$app->user->id,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'old_unit_price' => $oldUnitPrice,
                'new_unit_price' => $newUnitPrice,
                'stock_delta' => -$delta,
                'value_delta' => ($oldQty * $oldUnitPrice) - ($newQty * $newUnitPrice),
                'note' => $note,
                'source' => 'item-history-inline-edit',
            ];

            $detail->qty = $oldRawQty < 0 ? -$newQty : $newQty;
            $detail->unit_price = $newUnitPrice;
            $detail->data_json = json_encode($detailData, JSON_UNESCAPED_UNICODE);
            $detail->updated_at = $now;
            $detail->updated_by = Yii::$app->user->id;
            if (!$detail->save(false)) {
                throw new \Exception('บันทึกรายการใบเบิกไม่สำเร็จ');
            }

            $orderData = is_array($order->data_json)
                ? $order->data_json
                : (is_string($order->data_json) ? (json_decode($order->data_json, true) ?: []) : []);
            if (!is_array($orderData)) {
                $orderData = [];
            }
            if (!isset($orderData['history_inline_qty_edits']) || !is_array($orderData['history_inline_qty_edits'])) {
                $orderData['history_inline_qty_edits'] = [];
            }
            $orderData['history_inline_qty_edits'][] = [
                'at' => $now,
                'by_user_id' => Yii::$app->user->id,
                'detail_id' => (int) $detail->id,
                'item_code' => (string) $detail->item_code,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'old_unit_price' => $oldUnitPrice,
                'new_unit_price' => $newUnitPrice,
                'stock_delta' => -$delta,
                'value_delta' => ($oldQty * $oldUnitPrice) - ($newQty * $newUnitPrice),
                'note' => $note,
                'source' => 'item-history-inline-edit',
            ];
            $order->data_json = json_encode($orderData, JSON_UNESCAPED_UNICODE);
            $order->updated_at = $now;
            $order->updated_by = Yii::$app->user->id;
            $order->save(false);

            $currentBalance = (float) (new Query())
                ->from(StockBalance::tableName())
                ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
                ->sum('balance_qty');

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'แก้จำนวนใบเบิกสำเร็จ',
                'order_no' => $order->order_no,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'old_unit_price' => $oldUnitPrice,
                'new_unit_price' => $newUnitPrice,
                'delta' => $delta,
                'stock_delta' => -$delta,
                'value_delta' => ($oldQty * $oldUnitPrice) - ($newQty * $newUnitPrice),
                'current_qty' => $currentBalance,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function generateAdjustOrderNo()
    {
        $prefix = 'ADJ-' . date('Ymd-His') . '-';
        do {
            $no = $prefix . mt_rand(100, 999);
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
    }

    /**
     * Dashboard ยอดคงเหลือติดลบ — รวบรวมรายการที่ balance_qty < 0
     * (ส่วนใหญ่เกิดจากการย้ายข้อมูลใบจ่ายจาก V1 ผ่าน TransferToV2 เมื่อ snapshot ไม่ครบ)
     * แสดงพร้อมปุ่มลัดไปสร้างใบ ADJUST เพื่อเคลียร์ยอด
     */
    public function actionNegativeBalance()
    {
        $rows = (new Query())
            ->select([
                'sb.id',
                'sb.item_code',
                'sb.warehouse_id',
                'sb.lot_number',
                'sb.balance_qty',
                'sb.updated_at',
                'item_name' => 'si.title',
                'warehouse_name' => 'w.warehouse_name',
            ])
            ->from(['sb' => StockBalance::tableName()])
            ->leftJoin(
                ['si' => StockItem::tableName()],
                'si.code = sb.item_code AND si.name = :asset_item',
                [':asset_item' => 'asset_item']
            )
            ->leftJoin(['w' => Warehouse::tableName()], 'w.id = sb.warehouse_id')
            ->where(['<', 'sb.balance_qty', 0])
            ->orderBy(['sb.warehouse_id' => SORT_ASC, 'sb.item_code' => SORT_ASC])
            ->all();

        // จัดกลุ่มตามคลังเพื่อให้อ่านง่าย
        $grouped = [];
        $totalNegative = 0.0;
        foreach ($rows as $r) {
            $wid = (int) $r['warehouse_id'];
            $grouped[$wid]['warehouse_name'] = $r['warehouse_name'] ?: ('#' . $wid);
            $grouped[$wid]['rows'][] = $r;
            $totalNegative += (float) $r['balance_qty'];
        }

        return $this->render('negative-balance', [
            'grouped' => $grouped,
            'totalCount' => count($rows),
            'totalNegative' => $totalNegative,
        ]);
    }

    /**
     * ล้างยอดคงเหลือในคลังเป็น 0 ทั้งหมด (สำหรับทดสอบระบบเท่านั้น)
     * ไม่ลบเอกสารรับ/จ่าย — แค่ set stock_balance.balance_qty = 0 ในคลังที่เลือก
     */
    public function actionResetWarehouse()
    {
        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $confirm = trim((string) Yii::$app->request->post('confirm_text', ''));

        $listWarehouse = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        $warehouses = ['' => '-- เลือกคลัง --'] + \yii\helpers\ArrayHelper::map($listWarehouse, 'id', 'warehouse_name');

        if (Yii::$app->request->isPost && $warehouseId > 0) {
            $warehouse = Warehouse::findOne($warehouseId);
            if (!$warehouse) {
                Yii::$app->session->setFlash('error', 'ไม่พบคลังที่เลือก');
                return $this->redirect(['reset-warehouse']);
            }
            if ($confirm !== 'ล้าง') {
                Yii::$app->session->setFlash('error', 'กรุณาพิมพ์คำว่า "ล้าง" เพื่อยืนยัน');
                return $this->render('reset-warehouse', [
                    'warehouses' => $warehouses,
                    'selectedWarehouseId' => $warehouseId,
                ]);
            }

            $count = StockBalance::updateAll(
                ['balance_qty' => 0, 'updated_at' => time(), 'updated_by' => Yii::$app->user->id],
                ['warehouse_id' => $warehouseId]
            );
            Yii::$app->session->setFlash('success', "ล้างยอดคลัง \"{$warehouse->warehouse_name}\" เรียบร้อย — ตั้งยอดคงเหลือเป็น 0 แล้ว {$count} รายการ (สำหรับทดสอบ)");
            return $this->redirect(['index']);
        }

        return $this->render('reset-warehouse', [
            'warehouses' => $warehouses,
            'selectedWarehouseId' => null,
        ]);
    }
}
