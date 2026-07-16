<?php

namespace app\modules\inventoryV2\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\db\Query;
use app\components\AppHelper;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockMonthlyReport;
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
        $orderDateInput = trim((string) Yii::$app->request->post('order_date', ''));
        // โหมด: 'qty_only' = ปรับจำนวนอย่างเดียว ไม่กระทบมูลค่า (unit_price=null)
        //        อื่นๆ (recount) = คิดมูลค่า: เพิ่มใช้ต้นทุนที่ระบุ/เฉลี่ย, ลดคิดตามต้นทุนเฉลี่ย
        $mode = trim((string) Yii::$app->request->post('mode', 'recount'));
        $isValueMode = ($mode !== 'qty_only');
        $unitPriceRaw = Yii::$app->request->post('unit_price');
        $unitPriceInput = is_numeric($unitPriceRaw) ? (float) $unitPriceRaw : null;
        $targetValueRaw = Yii::$app->request->post('target_value');
        $targetValue = is_numeric($targetValueRaw) ? (float) $targetValueRaw : null;
        $historyOnlyReverse = (int) Yii::$app->request->post('history_only_reverse', 0) === 1;

        if ($historyOnlyReverse) {
            return [
                'success' => false,
                'message' => 'ปุ่มยกเลิกผลรายการซ้ำถูกปิดแล้ว กรุณาใช้ปุ่มลบรายการใบเบิกซ้ำในประวัติ',
            ];
        }

        if ($warehouseId <= 0) {
            return ['success' => false, 'message' => 'กรุณาเลือกคลัง'];
        }
        if ($itemCode === '') {
            return ['success' => false, 'message' => 'กรุณาเลือกรหัสพัสดุ'];
        }
        if ($adjustmentQty == 0 && (!$isValueMode || $targetValue === null)) {
            return ['success' => false, 'message' => 'กรุณาระบุจำนวนที่ปรับ หรือมูลค่าเป้าหมายที่ต้องการแก้ไข'];
        }
        if ($targetValue !== null && $targetValue < 0) {
            return ['success' => false, 'message' => 'มูลค่าเป้าหมายต้องไม่ติดลบ'];
        }
        if ($historyOnlyReverse && $reverseDetailId <= 0) {
            return ['success' => false, 'message' => 'ไม่พบรายการต้นทางสำหรับยกเลิกผลซ้ำ'];
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
        $valueOnly = false;
        $valueDelta = 0.0;
        if ($adjustmentQty == 0) {
            $valueDelta = (float) $targetValue - $currentValue;
            if (abs($valueDelta) < 0.000001) {
                return ['success' => false, 'message' => 'จำนวนและมูลค่าไม่เปลี่ยนแปลง'];
            }
            $valueOnly = true;
        }

        // ราคาต่อหน่วยของเอกสาร ADJUST ตามโหมด/ทิศทาง
        if ($valueOnly) {
            $detailUnitPrice = null;
        } elseif ($historyOnlyReverse && $unitPriceInput !== null && $unitPriceInput >= 0) {
            $detailUnitPrice = $unitPriceInput;
        } elseif (!$isValueMode) {
            $detailUnitPrice = null;                       // qty_only = ไม่กระทบมูลค่า
        } elseif ($adjustmentQty > 0) {
            // เพิ่ม (เจอของ) — ใช้ราคาที่กรอก มิฉะนั้นใช้ต้นทุนเฉลี่ย
            $detailUnitPrice = ($unitPriceInput !== null && $unitPriceInput >= 0) ? $unitPriceInput : $avgCost;
        } else {
            // ลด (ของหาย/เสีย) — คิดต้นทุนตามราคาเฉลี่ยปัจจุบัน
            $detailUnitPrice = $avgCost;
        }
        if (!$valueOnly) {
            $valueDelta = $isValueMode ? ($adjustmentQty * (float) $detailUnitPrice) : 0.0;
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $orderNo = $this->generateAdjustOrderNo();
            $now = date('Y-m-d H:i:s');

            // วันที่เอกสาร (order_date) = วันที่ผู้ใช้ระบุ (ถ้ามี) มิฉะนั้นใช้วันนี้
            // order_date กำหนดว่ารายการนี้ถูกนับในงวด/เดือนไหน (computeMonthlyRows กรองด้วย order_date)
            // created_at ยังคงเป็นเวลาบันทึกจริงเสมอ
            $orderDate = $now;
            if ($orderDateInput !== '') {
                // ช่องวันที่เป็น Thai datepicker (วว/ดด/พ.ศ.) — แปลงเป็น ค.ศ. ก่อน; รองรับ ISO ด้วย
                $greg = strpos($orderDateInput, '/') !== false
                    ? AppHelper::convertToGregorian($orderDateInput)
                    : (preg_match('/^\d{4}-\d{2}-\d{2}$/', $orderDateInput) ? $orderDateInput : null);
                if ($greg !== null && strtotime($greg) !== false) {
                    $orderDate = $greg . ' ' . date('H:i:s');
                }
            }

            // เพิ่มแบบคิดมูลค่า → lot เฉพาะผูกกับเอกสาร (ต้นทุน lot นี้ = detailUnitPrice) เพื่อ FIFO อนาคต
            // นอกนั้นใช้ lot 'ADJUST'
            $lotNumber = ($isValueMode && !$valueOnly && !$historyOnlyReverse && $adjustmentQty > 0) ? $orderNo : 'ADJUST';

            $order = new StockOrder();
            $order->order_no = $orderNo;
            $order->order_type = StockOrder::ORDER_TYPE_ADJUST;
            $order->source_type = StockOrder::ORDER_TYPE_ADJUST;
            $order->order_date = $orderDate;
            $order->main_warehouse_id = $warehouseId;
            $order->status = StockOrder::STATUS_CONFIRMED;
            $order->ref = $note ?: 'ปรับยอด';
            $order->data_json = json_encode([
                'adjust_source' => $source ?: 'stock-adjust',
                'adjust_mode' => $historyOnlyReverse ? 'history_reverse' : ($valueOnly ? 'value_only' : ($isValueMode ? 'recount' : 'qty_only')),
                'history_only_reverse' => $historyOnlyReverse ? 1 : 0,
                'current_qty_before' => $currentQty,
                'target_qty' => $targetQty,
                'adjustment_qty' => $adjustmentQty,
                'current_value_before' => round($currentValue, 6),
                'target_value' => $targetValue,
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
            $detail->qty = $valueOnly ? 0 : $adjustmentQty;
            $detail->unit_price = $detailUnitPrice; // ledger คิดมูลค่าจากราคานี้ (null = ไม่กระทบมูลค่า)
            $detail->lot_number = $lotNumber;
            $detail->remain_qty = (!$valueOnly && !$historyOnlyReverse && $adjustmentQty > 0) ? $adjustmentQty : 0;
            if ($valueOnly) {
                $detail->data_json = json_encode([
                    'adjust_value_only' => 1,
                    'value_delta' => round($valueDelta, 6),
                    'current_value_before' => round($currentValue, 6),
                    'target_value' => round((float) $targetValue, 6),
                ], JSON_UNESCAPED_UNICODE);
            } elseif ($historyOnlyReverse) {
                $detail->data_json = json_encode([
                    'history_only_reverse' => 1,
                    'reverse_detail_id' => $reverseDetailId ?: null,
                    'reverse_order_no' => $reverseOrderNo ?: null,
                    'value_delta' => round($valueDelta, 6),
                ], JSON_UNESCAPED_UNICODE);
            }
            $detail->created_at = $now;
            $detail->updated_at = $now;
            $detail->created_by = Yii::$app->user->id;
            $detail->updated_by = Yii::$app->user->id;
            if (!$detail->save(false)) {
                throw new \Exception('บันทึกรายละเอียดไม่สำเร็จ');
            }

            if (!$valueOnly && !$historyOnlyReverse) {
                if ($adjustmentQty > 0) {
                    InventoryService::adjustBalance($itemCode, $warehouseId, $lotNumber, $adjustmentQty);
                } else {
                    InventoryService::processFIFO($itemCode, $warehouseId, abs($adjustmentQty), $order->id, $detail->id);
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'ปรับยอดสำเร็จ',
                'order_no' => $orderNo,
                'order_date' => date('Y-m-d', strtotime($orderDate)),
                'value_delta' => round($valueDelta, 2),
                'mode' => $historyOnlyReverse ? 'history_reverse' : ($valueOnly ? 'value_only' : ($isValueMode ? 'recount' : 'qty_only')),
                'closed_month_warning' => $this->closedMonthWarning($warehouseId, $orderDate),
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

    /**
     * ลบแถวจ่ายซ้ำออกจากประวัติ เฉพาะรายการใบเบิก (OUT) เท่านั้น
     * ไม่ปรับ stock_balance/FIFO ใช้สำหรับเคสประวัติซ้ำที่ยอดจริงในระบบถูกต้องอยู่แล้ว
     */
    public function actionDeleteRequisitionDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        $detailId = (int) Yii::$app->request->post('detail_id', 0);
        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->post('item_code', ''));
        $note = trim((string) Yii::$app->request->post('note', 'ลบรายการใบเบิกซ้ำจากประวัติการเคลื่อนไหววัสดุ'));

        if ($detailId <= 0 || $warehouseId <= 0 || $itemCode === '') {
            return ['success' => false, 'message' => 'ข้อมูลรายการที่ต้องการลบไม่ครบถ้วน'];
        }

        $detail = StockDetail::findOne($detailId);
        if (!$detail) {
            return ['success' => false, 'message' => 'ไม่พบรายการใบเบิกที่ต้องการลบ'];
        }

        $order = StockOrder::findOne($detail->stock_order_id);
        if (!$order || $order->order_type !== StockOrder::ORDER_TYPE_OUT) {
            return ['success' => false, 'message' => 'ลบได้เฉพาะรายการใบเบิกเท่านั้น'];
        }
        if ((string) $order->status !== StockOrder::STATUS_CONFIRMED) {
            return ['success' => false, 'message' => 'ลบได้เฉพาะเอกสารที่ยืนยันแล้วเท่านั้น'];
        }
        if ((string) $detail->item_code !== $itemCode || (int) $order->main_warehouse_id !== $warehouseId) {
            return ['success' => false, 'message' => 'รายการที่ส่งมาไม่ตรงกับคลังหรือรหัสพัสดุ'];
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $oldQty = (float) $detail->qty;
            $oldUnitPrice = (float) $detail->unit_price;
            $oldLotNumber = (string) $detail->lot_number;
            $oldRemainQty = (float) $detail->remain_qty;

            $orderData = is_string($order->data_json) && $order->data_json !== ''
                ? json_decode($order->data_json, true)
                : (is_array($order->data_json) ? $order->data_json : []);
            if (!is_array($orderData)) {
                $orderData = [];
            }
            if (!isset($orderData['history_deleted_details']) || !is_array($orderData['history_deleted_details'])) {
                $orderData['history_deleted_details'] = [];
            }
            $orderData['history_deleted_details'][] = [
                'detail_id' => $detailId,
                'item_code' => $itemCode,
                'old_qty' => $oldQty,
                'old_unit_price' => $oldUnitPrice,
                'old_lot_number' => $oldLotNumber,
                'old_remain_qty' => $oldRemainQty,
                'note' => $note,
                'by_user_id' => Yii::$app->user->id,
                'at' => date('Y-m-d H:i:s'),
            ];
            $order->data_json = json_encode($orderData, JSON_UNESCAPED_UNICODE);
            $order->updated_at = date('Y-m-d H:i:s');
            $order->updated_by = Yii::$app->user->id;
            $order->save(false);

            if ($detail->delete() === false) {
                throw new \Exception('ลบรายการใบเบิกซ้ำไม่สำเร็จ');
            }

            $currentQty = (float) StockBalance::find()
                ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
                ->sum('balance_qty');

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'ลบรายการใบเบิกซ้ำสำเร็จ',
                'order_no' => $order->order_no,
                'deleted_detail_id' => $detailId,
                'old_qty' => $oldQty,
                'old_unit_price' => $oldUnitPrice,
                'current_qty' => $currentQty,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ถอนผลของเอกสาร ADJUST ออกจาก stock/FIFO — ใช้ร่วมกันทั้งลบและแก้ไข
     * - qty > 0 (เพิ่มเข้า lot): ต้องยังไม่ถูกจ่ายออก (remain_qty ยังครบ) แล้วหักคืนจาก lot
     * - qty < 0 (ตัด FIFO): คืน allocation กลับเข้าต้นทาง + stock_balance
     * - value_only / history_reverse: ไม่มีผลต่อ stock — ไม่ต้องถอน
     * @throws \Exception เมื่อรายการเพิ่มถูกจ่ายออกไปบางส่วนแล้ว (ถอนไม่ได้แบบปลอดภัย)
     */
    protected function reverseAdjustEffect(StockOrder $order, StockDetail $detail, int $warehouseId): void
    {
        $qty = (float) $detail->qty;
        $lot = (string) ($detail->lot_number ?: 'ADJUST');

        $orderData = is_string($order->data_json) && $order->data_json !== ''
            ? (json_decode($order->data_json, true) ?: [])
            : (is_array($order->data_json) ? $order->data_json : []);
        $detailData = is_string($detail->data_json) && $detail->data_json !== ''
            ? (json_decode($detail->data_json, true) ?: [])
            : (is_array($detail->data_json) ? $detail->data_json : []);
        $mode = (string) ($orderData['adjust_mode'] ?? '');

        $noStockEffect = $mode === 'value_only' || $mode === 'history_reverse'
            || !empty($detailData['adjust_value_only']) || !empty($detailData['history_only_reverse']);

        if ($noStockEffect || abs($qty) < 0.000001) {
            return;
        }

        if ($qty > 0) {
            $remain = (float) $detail->remain_qty;
            if ($remain + 0.000001 < $qty) {
                throw new \Exception('รายการปรับยอดนี้ถูกจ่ายออกไปบางส่วนแล้ว จึงแก้ไข/ลบไม่ได้ กรุณาสร้างรายการปรับยอดใหม่เพื่อแก้ไขแทน');
            }
            // หักจำนวนที่เคยเพิ่มออกจาก lot (allowNegative กัน float ปัดเศษ)
            InventoryService::adjustBalance((string) $detail->item_code, $warehouseId, $lot, -$qty, true);
            $detail->remain_qty = 0;
        } else {
            // เคยตัด FIFO — คืน allocation กลับ (คืน remain_qty ต้นทาง + stock_balance)
            InventoryService::returnFifoAllocation($detail, $warehouseId, abs($qty));
        }
    }

    /**
     * ลบรายการปรับยอด (ADJUST) ออกจากประวัติ พร้อมถอนผลต่อ stock จริง
     */
    public function actionDeleteAdjustDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        $detailId = (int) Yii::$app->request->post('detail_id', 0);
        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->post('item_code', ''));

        if ($detailId <= 0 || $warehouseId <= 0 || $itemCode === '') {
            return ['success' => false, 'message' => 'ข้อมูลรายการที่ต้องการลบไม่ครบถ้วน'];
        }

        $detail = StockDetail::findOne($detailId);
        if (!$detail) {
            return ['success' => false, 'message' => 'ไม่พบรายการปรับยอดที่ต้องการลบ'];
        }

        $order = StockOrder::findOne($detail->stock_order_id);
        if (!$order || $order->order_type !== StockOrder::ORDER_TYPE_ADJUST) {
            return ['success' => false, 'message' => 'ลบได้เฉพาะรายการปรับยอด (ADJUST) เท่านั้น'];
        }
        if ((string) $order->status !== StockOrder::STATUS_CONFIRMED) {
            return ['success' => false, 'message' => 'ลบได้เฉพาะเอกสารที่ยืนยันแล้วเท่านั้น'];
        }
        if ((string) $detail->item_code !== $itemCode || (int) $order->main_warehouse_id !== $warehouseId) {
            return ['success' => false, 'message' => 'รายการที่ส่งมาไม่ตรงกับคลังหรือรหัสพัสดุ'];
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $orderNo = (string) $order->order_no;
            $closedWarning = $this->closedMonthWarning($warehouseId, (string) $order->order_date);
            $this->reverseAdjustEffect($order, $detail, $warehouseId);

            $otherDetails = (int) StockDetail::find()
                ->where(['stock_order_id' => $order->id])
                ->andWhere(['<>', 'id', $detailId])
                ->count();

            if ($detail->delete() === false) {
                throw new \Exception('ลบรายการปรับยอดไม่สำเร็จ');
            }
            // ADJUST มี 1 detail ต่อ 1 order — ลบหัวเอกสารทิ้งด้วยถ้าไม่เหลือรายการ
            if ($otherDetails === 0) {
                $order->delete();
            }

            $currentQty = (float) StockBalance::find()
                ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
                ->sum('balance_qty');

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'ลบรายการปรับยอดและถอนผลต่อยอดคงเหลือเรียบร้อย',
                'order_no' => $orderNo,
                'deleted_detail_id' => $detailId,
                'current_qty' => $currentQty,
                'closed_month_warning' => $closedWarning,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * แก้ไขรายการปรับยอด (ADJUST) — แก้ได้ทั้งจำนวน (+/-) และราคา/หน่วย
     * วิธีทำ: ถอนผลเดิมออก แล้ว apply ผลใหม่ในเอกสารเดิม
     */
    public function actionUpdateAdjustDetail()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        $detailId = (int) Yii::$app->request->post('detail_id', 0);
        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->post('item_code', ''));
        $newQty = (float) Yii::$app->request->post('adjustment_qty', 0); // signed
        $priceRaw = Yii::$app->request->post('unit_price');
        $newPrice = is_numeric($priceRaw) ? (float) $priceRaw : null;
        $note = trim((string) Yii::$app->request->post('note', ''));

        if ($detailId <= 0 || $warehouseId <= 0 || $itemCode === '') {
            return ['success' => false, 'message' => 'ข้อมูลรายการที่ต้องการแก้ไขไม่ครบถ้วน'];
        }
        if (abs($newQty) < 0.000001) {
            return ['success' => false, 'message' => 'จำนวนที่ปรับต้องไม่เป็น 0 (หากต้องการยกเลิกรายการให้ใช้ปุ่มลบ)'];
        }
        if ($newPrice !== null && $newPrice < 0) {
            return ['success' => false, 'message' => 'ราคา/หน่วยต้องไม่น้อยกว่า 0'];
        }

        $detail = StockDetail::findOne($detailId);
        if (!$detail) {
            return ['success' => false, 'message' => 'ไม่พบรายการปรับยอดที่ต้องการแก้ไข'];
        }

        $order = StockOrder::findOne($detail->stock_order_id);
        if (!$order || $order->order_type !== StockOrder::ORDER_TYPE_ADJUST) {
            return ['success' => false, 'message' => 'แก้ไขได้เฉพาะรายการปรับยอด (ADJUST) เท่านั้น'];
        }
        if ((string) $order->status !== StockOrder::STATUS_CONFIRMED) {
            return ['success' => false, 'message' => 'แก้ได้เฉพาะเอกสารที่ยืนยันแล้วเท่านั้น'];
        }
        if ((string) $detail->item_code !== $itemCode || (int) $order->main_warehouse_id !== $warehouseId) {
            return ['success' => false, 'message' => 'รายการที่ส่งมาไม่ตรงกับคลังหรือรหัสพัสดุ'];
        }

        $oldQty = (float) $detail->qty;
        $oldPrice = $detail->unit_price === null ? null : (float) $detail->unit_price;

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            // 1) ถอนผลเดิม (กัน edit รายการที่เพิ่มแล้วถูกจ่ายไปบางส่วน)
            $this->reverseAdjustEffect($order, $detail, $warehouseId);

            // 2) คำนวณราคา/โหมดใหม่ — กรอกราคา = คิดมูลค่า (recount), ไม่กรอก/0 = value-neutral (qty_only)
            $isValueMode = ($newPrice !== null && $newPrice > 0);
            $detailUnitPrice = $isValueMode ? $newPrice : null;
            // เพิ่มแบบคิดมูลค่า → lot ผูกกับเอกสาร (ต้นทุน lot สำหรับ FIFO อนาคต); นอกนั้น 'ADJUST'
            $lotNumber = ($isValueMode && $newQty > 0) ? (string) $order->order_no : 'ADJUST';

            $now = date('Y-m-d H:i:s');
            $editLog = [
                'at' => $now,
                'by_user_id' => Yii::$app->user->id,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'old_unit_price' => $oldPrice,
                'new_unit_price' => $detailUnitPrice,
                'note' => $note,
                'source' => 'item-history-adjust-edit',
            ];

            // 3) เขียน detail ด้วยค่าใหม่ก่อน (ลำดับเดียวกับ actionSave) เพื่อให้ processFIFO
            //    เก็บ fifo_allocations ลง detail นี้ได้ถูกต้อง; ล้างร่องรอยโหมด/allocation เดิม
            $detailData = is_string($detail->data_json) && $detail->data_json !== ''
                ? (json_decode($detail->data_json, true) ?: [])
                : (is_array($detail->data_json) ? $detail->data_json : []);
            if (!is_array($detailData)) {
                $detailData = [];
            }
            unset($detailData['adjust_value_only'], $detailData['history_only_reverse'], $detailData['fifo_allocations']);
            $detailData['history_adjust_edits'][] = $editLog;

            $detail->qty = $newQty;
            $detail->unit_price = $detailUnitPrice;
            $detail->lot_number = $lotNumber;
            $detail->remain_qty = $newQty > 0 ? $newQty : 0;
            $detail->data_json = json_encode($detailData, JSON_UNESCAPED_UNICODE);
            $detail->updated_at = $now;
            $detail->updated_by = Yii::$app->user->id;
            if (!$detail->save(false)) {
                throw new \Exception('บันทึกรายการปรับยอดไม่สำเร็จ');
            }

            // 4) apply ผลใหม่ต่อ stock/FIFO (processFIFO จะเติม remain_qty + fifo_allocations ให้ detail นี้)
            if ($newQty > 0) {
                InventoryService::adjustBalance($itemCode, $warehouseId, $lotNumber, $newQty);
            } else {
                InventoryService::processFIFO($itemCode, $warehouseId, abs($newQty), $order->id, $detail->id);
            }

            // 5) อัปเดตหัวเอกสาร
            $orderData = is_string($order->data_json) && $order->data_json !== ''
                ? (json_decode($order->data_json, true) ?: [])
                : (is_array($order->data_json) ? $order->data_json : []);
            if (!is_array($orderData)) {
                $orderData = [];
            }
            $orderData['adjust_mode'] = $isValueMode ? 'recount' : 'qty_only';
            $orderData['unit_price'] = $detailUnitPrice;
            $orderData['lot_number'] = $lotNumber;
            $orderData['adjustment_qty'] = $newQty;
            $orderData['history_adjust_edits'][] = $editLog;
            if ($note !== '') {
                $order->ref = $note;
            }
            $order->data_json = json_encode($orderData, JSON_UNESCAPED_UNICODE);
            $order->updated_at = $now;
            $order->updated_by = Yii::$app->user->id;
            $order->save(false);

            $currentQty = (float) StockBalance::find()
                ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
                ->sum('balance_qty');

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'แก้ไขรายการปรับยอดสำเร็จ',
                'order_no' => $order->order_no,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'old_unit_price' => $oldPrice,
                'new_unit_price' => $detailUnitPrice,
                'current_qty' => $currentQty,
                'closed_month_warning' => $this->closedMonthWarning($warehouseId, (string) $order->order_date),
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * แก้ยอดคงเหลือของ lot โดยตรง (stock_balance.balance_qty) พร้อม sync FIFO (stock_detail.remain_qty)
     * ใช้เป็นเครื่องมือ reconcile ข้อมูลจากประวัติการเคลื่อนไหววัสดุ (ไม่ผ่านเอกสาร ADJUST)
     */
    public function actionUpdateLotBalance()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return ['success' => false, 'message' => 'Invalid method'];
        }

        $warehouseId = (int) Yii::$app->request->post('warehouse_id', 0);
        $itemCode = trim((string) Yii::$app->request->post('item_code', ''));
        $lot = trim((string) Yii::$app->request->post('lot_number', ''));
        $newQtyRaw = Yii::$app->request->post('new_qty');
        $note = trim((string) Yii::$app->request->post('note', ''));

        if ($warehouseId <= 0 || $itemCode === '' || $lot === '') {
            return ['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน (คลัง/รหัสพัสดุ/lot)'];
        }
        if (!is_numeric($newQtyRaw)) {
            return ['success' => false, 'message' => 'จำนวนคงเหลือใหม่ไม่ถูกต้อง'];
        }
        $newQty = (float) $newQtyRaw;
        if ($newQty < 0) {
            return ['success' => false, 'message' => 'จำนวนคงเหลือต้องไม่ติดลบ'];
        }
        if (!StockItem::findOne(['item_code' => $itemCode])) {
            return ['success' => false, 'message' => 'ไม่พบรหัสพัสดุในระบบ'];
        }
        if (!Warehouse::findOne($warehouseId)) {
            return ['success' => false, 'message' => 'ไม่พบคลังในระบบ'];
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $userId = Yii::$app->user->id;

            // source details ของ lot นี้ในคลังนี้ (ใช้ทั้งเช็คเพดาน + sync remain)
            $sources = StockDetail::find()
                ->joinWith('stockOrder')
                ->where(['stock_detail.item_code' => $itemCode, 'stock_detail.lot_number' => $lot])
                ->andWhere(['stock_order.status' => StockOrder::STATUS_CONFIRMED])
                ->andWhere(['or',
                    ['and', ['stock_order.main_warehouse_id' => $warehouseId], ['or',
                        ['stock_order.order_type' => StockOrder::ORDER_TYPE_IN],
                        ['and', ['stock_order.order_type' => StockOrder::ORDER_TYPE_ADJUST], ['>', 'stock_detail.qty', 0]],
                    ]],
                    ['and', ['stock_order.order_type' => StockOrder::ORDER_TYPE_TRANSFER], ['stock_order.sub_warehouse_id' => $warehouseId], ['>', 'stock_detail.qty', 0]],
                ])
                ->orderBy(['stock_order.order_date' => SORT_ASC, 'stock_detail.id' => SORT_ASC])
                ->all();

            // guard: ยอดคงเหลือใหม่ต้องไม่เกินจำนวนที่เคยรับเข้า lot นี้ (กัน typo)
            // เว้น lot ที่ไม่มี source detail (หาเพดานไม่ได้ — orphan)
            $totalReceived = 0.0;
            foreach ($sources as $s) {
                $totalReceived += (float) $s->qty;
            }
            if (!empty($sources) && $newQty > $totalReceived + 0.000001) {
                $fmt = function ($n) { return rtrim(rtrim(number_format($n, 4, '.', ''), '0'), '.'); };
                throw new \Exception('ยอดคงเหลือใหม่ (' . $fmt($newQty) . ') มากกว่าจำนวนที่เคยรับเข้า lot นี้ (' . $fmt($totalReceived) . ') กรุณาตรวจสอบ');
            }

            // 1) stock_balance: รวม row ซ้ำ (item,wh,lot) ให้เหลือแถวเดียว = newQty
            $balances = StockBalance::find()
                ->where(['item_code' => $itemCode, 'warehouse_id' => $warehouseId, 'lot_number' => $lot])
                ->orderBy(['id' => SORT_ASC])
                ->all();
            $oldBalance = 0.0;
            foreach ($balances as $b) {
                $oldBalance += (float) $b->balance_qty;
            }
            if (empty($balances)) {
                if ($newQty > 0) {
                    $b = new StockBalance([
                        'item_code' => $itemCode,
                        'warehouse_id' => $warehouseId,
                        'lot_number' => $lot,
                        'balance_qty' => $newQty,
                    ]);
                    $b->created_at = $now;
                    $b->updated_at = $now;
                    $b->created_by = $userId;
                    $b->updated_by = $userId;
                    $b->save(false);
                }
            } else {
                $keep = array_shift($balances);
                $bd = is_string($keep->data_json) && $keep->data_json !== ''
                    ? (json_decode($keep->data_json, true) ?: [])
                    : (is_array($keep->data_json) ? $keep->data_json : []);
                if (!is_array($bd)) {
                    $bd = [];
                }
                $bd['lot_balance_edits'][] = [
                    'at' => $now, 'by_user_id' => $userId,
                    'old' => $oldBalance, 'new' => $newQty, 'note' => $note,
                    'merged_rows' => count($balances),
                ];
                $keep->balance_qty = $newQty;
                $keep->data_json = json_encode($bd, JSON_UNESCAPED_UNICODE);
                $keep->updated_at = $now;
                $keep->updated_by = $userId;
                $keep->save(false);
                foreach ($balances as $dup) {
                    $dup->delete(); // ลบ row ซ้ำที่เหลือ
                }
            }

            // 2) sync stock_detail.remain_qty ของ source lot นี้ (reuse $sources ที่ fetch ไว้ด้านบน) ให้ผลรวม = newQty
            $oldRemain = 0.0;
            foreach ($sources as $s) {
                $oldRemain += (float) $s->remain_qty;
            }
            $remainSynced = false;
            if (!empty($sources)) {
                $alloc = $newQty;
                $last = count($sources) - 1;
                foreach ($sources as $i => $s) {
                    // เติมจากเก่า→ใหม่ (cap ที่ qty ที่รับเข้า) ตัวสุดท้ายรับส่วนที่เหลือทั้งหมดให้ผลรวมตรงพอดี
                    $give = ($i === $last) ? max(0.0, $alloc) : max(0.0, min($alloc, (float) $s->qty));
                    $s->remain_qty = $give;
                    $s->updated_at = $now;
                    $s->updated_by = $userId;
                    $s->save(false);
                    $alloc -= $give;
                }
                $remainSynced = true;
            }

            $currentQty = (float) StockBalance::find()
                ->where(['warehouse_id' => $warehouseId, 'item_code' => $itemCode])
                ->sum('balance_qty');

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'แก้ยอดคงเหลือ lot สำเร็จ',
                'lot_number' => $lot,
                'old_balance' => round($oldBalance, 4),
                'new_balance' => round($newQty, 4),
                'old_remain' => round($oldRemain, 4),
                'new_remain' => $remainSynced ? round($newQty, 4) : round($oldRemain, 4),
                'remain_synced' => $remainSynced,
                'current_qty' => $currentQty,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ถ้า order_date ตกในเดือนที่ปิดงวดไปแล้ว (มี snapshot ใน stock_monthly_report)
     * คืนข้อความเตือนให้ผู้ใช้ปิดเดือนนั้นใหม่ — มิฉะนั้นคืน null
     */
    protected function closedMonthWarning(int $warehouseId, string $orderDate): ?string
    {
        $ts = strtotime($orderDate);
        if ($ts === false) {
            return null;
        }
        $y = (int) date('Y', $ts);
        $m = (int) date('n', $ts);
        $closed = StockMonthlyReport::find()
            ->where(['report_year' => $y, 'report_month' => $m, 'warehouse_id' => $warehouseId])
            ->exists();
        if (!$closed) {
            return null;
        }
        $monthNames = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
            'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        $label = ($monthNames[$m] ?? '') . ' ' . ($y + 543);
        return 'วันที่ที่เลือกอยู่ในเดือน ' . $label . ' ซึ่งปิดงวดไปแล้ว — กรุณาปิดเดือนนั้นใหม่เพื่อให้ยอดยกมา/ยกไปถูกต้อง';
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
