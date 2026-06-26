<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\components\InventoryService;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Response;

class SubStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Dashboard คลังย่อย — ใช้ข้อมูลจริงจากใบจ่ายที่ส่งมาที่คลังย่อย (มีตัวเลือกชื่อคลังย่อย)
     */
    public function actionDashboard()
    {
        $allowedSubIds = $this->getSubWarehouseIds();
        $allSubIds = $allowedSubIds;
        if (empty($allSubIds)) {
            $allSubIds = [-1];
        }

        $warehouseId = $this->getFilterWarehouseId($allowedSubIds);
        if ($warehouseId !== null && !in_array($warehouseId, $allSubIds, true)) {
            $warehouseId = null;
        }

        $subWarehouseIds = $warehouseId !== null ? [$warehouseId] : $allSubIds;

        $stats = $this->getDashboardStats($subWarehouseIds);
        $pendingIssueList = $this->getPendingDisbursementList($subWarehouseIds, 10);
        $chartData = $this->getChartDataLast7Days($subWarehouseIds);
        $warehouses = $this->getSubWarehousesList();


        $usageHistory = [];
        $helpdeskIdFilter = (int) $this->request->get('helpdesk_id', 0);
        if ($helpdeskIdFilter > 0) {
            // DEBUG/ค้นหาแบบกว้าง: แสดงว่ามี stock_order ที่เกี่ยวข้องถูกสร้างและมีเลข helpdesk_id นี้อยู่ใน ref/data_json หรือไม่
            // (ไม่จำกัด status เผื่อกรณีบันทึกแต่ยังไม่ CONFIRMED)
            $needle = (string) $helpdeskIdFilter;
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere([
                    'or',
                    ['like', 'ref', $needle],
                    ['like', 'data_json', $needle],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else if ($warehouseId > 0) {
            // แสดงเฉพาะคลังที่เลือก (ถ้าส่ง warehouse_id มา)
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->andWhere(['or', ['main_warehouse_id' => $warehouseId], ['sub_warehouse_id' => $warehouseId]])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else {
            // รวมทุกคลังย่อยที่ผู้ใช้มีสิทธิ
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        }


        return $this->render('dashboard', [
            'pendingDisbursementCount' => $stats['pending_disbursement'],
            'criticalCount' => $stats['critical_count'],
            'monthlyValue' => $stats['monthly_value'],
            'expiringSoonCount' => $stats['expiring_soon'],
            'pendingIssueList' => $pendingIssueList,
            'chartData' => $chartData,
            'subWarehouseIds' => $subWarehouseIds,
            'warehouses' => $warehouses,
            'currentWarehouseId' => $warehouseId,
            'usageHistory' => $usageHistory,
        ]);
    }

    /** รหัสคลังย่อยที่ผู้ใช้มีสิทธิ (ตามกำหนดแผนก/ฝ่ายที่มีสิทธิเบิก ของคลังย่อย + แผนกของ user ที่ล็อกอิน) */
    protected function getSubWarehouseIds()
    {
        $list = Warehouse::findSubWarehousesForUser();
        return array_values(array_map('intval', array_column($list, 'id')));
    }

    /** รายการคลังย่อยสำหรับ dropdown (ตาม user ที่ล็อกอินและกำหนดแผนก/ฝ่ายที่มีสิทธิเบิก) */
    protected function getSubWarehousesList()
    {
        return Warehouse::findSubWarehousesForUser();
    }

    /** warehouse_id จาก query หรือ session (all = null) */
    protected function getFilterWarehouseId(array $allowedWarehouseIds = [])
    {
        $get = Yii::$app->request->get('warehouse_id');
        if ($get === 'all') {
            Yii::$app->session->remove('sub_dashboard_warehouse_id');
            return null;
        }

        if ($get === null || $get === '') {
            $sessionId = Yii::$app->session->get('sub_dashboard_warehouse_id');
            if ($sessionId !== null && $sessionId !== '' && in_array((int) $sessionId, $allowedWarehouseIds, true)) {
                return (int) $sessionId;
            }

            return !empty($allowedWarehouseIds) ? (int) reset($allowedWarehouseIds) : null;
        }

        $id = (int) $get;
        Yii::$app->session->set('sub_dashboard_warehouse_id', $id);
        return $id;
    }

    /**
     * สถิติ: รอคลังหลักจ่าย, ต่ำกว่าจุดวิกฤต, มูลค่าเบิกใช้เดือนนี้, หมดอายุภายใน 30 วัน
     */
    protected function getDashboardStats(array $subWarehouseIds)
    {
        $pendingDisbursement = (int) StockOrder::find()
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['sub_warehouse_id' => $subWarehouseIds])
            ->count();

        $criticalQuery = (new Query())
            ->from(['i' => StockItem::tableName()])
            ->innerJoin(
                ['b' => (new Query())
                    ->select(['item_code', 'SUM(balance_qty) as total_qty'])
                    ->from(StockBalance::tableName())
                    ->where(['warehouse_id' => $subWarehouseIds])
                    ->groupBy('item_code')],
                'b.item_code = i.code'
            )
            ->where(['and',
                ['i.active' => 1],
                ['not', ['i.qty_min' => null]],
                ['>', 'i.qty_min', 0],
            ])
            ->andWhere('b.total_qty < i.qty_min');
        $criticalCount = (int) $criticalQuery->count();

        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');
        $monthlyValue = (float) (new Query())
            ->select(new Expression('SUM(sd.qty * COALESCE(sd.unit_price, 0))'))
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'so.order_type' => 'OUT',
                'so.source_type' => 'REQUEST',
                'so.status' => StockOrder::STATUS_CONFIRMED,
            ])
            ->andWhere(['so.sub_warehouse_id' => $subWarehouseIds])
            ->andWhere(['>=', 'so.order_date', $monthStart])
            ->andWhere(['<=', 'so.order_date', $monthEnd])
            ->scalar();

        return [
            'pending_disbursement' => $pendingDisbursement,
            'critical_count' => $criticalCount,
            'monthly_value' => $monthlyValue,
            'expiring_soon' => 0,
        ];
    }

    /** รายการใบขอเบิกที่หัวหน้าอนุมัติแล้ว รอคลังหลักจ่าย */
    protected function getPendingDisbursementList(array $subWarehouseIds, $limit = 10)
    {
        $orders = StockOrder::find()
            ->with('stockDetails')
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['sub_warehouse_id' => $subWarehouseIds])
            ->orderBy(['order_date' => SORT_DESC])
            ->limit($limit)
            ->all();

        $list = [];
        foreach ($orders as $o) {
            $list[] = [
                'id' => $o->id,
                'doc_no' => $o->order_no,
                'detail_count' => is_array($o->stockDetails) ? count($o->stockDetails) : 0,
                'order_date' => $o->order_date,
                'main_warehouse_name' => $o->mainWarehouse ? $o->mainWarehouse->warehouse_name : 'คลังหลัก',
            ];
        }
        return $list;
    }

    /** ข้อมูลกราฟ: มูลค่าที่จ่ายมาคลังย่อย 7 วันล่าสุด */
    protected function getChartDataLast7Days(array $subWarehouseIds)
    {
        $days = [];
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $days[] = $d;
            $start = $d . ' 00:00:00';
            $end = $d . ' 23:59:59';
            $v = (float) (new Query())
                ->select(new Expression('SUM(sd.qty * COALESCE(sd.unit_price, 0))'))
                ->from(['sd' => StockDetail::tableName()])
                ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
                ->where([
                    'so.order_type' => 'OUT',
                    'so.source_type' => 'REQUEST',
                    'so.status' => StockOrder::STATUS_CONFIRMED,
                ])
                ->andWhere(['so.sub_warehouse_id' => $subWarehouseIds])
                ->andWhere(['>=', 'so.order_date', $start])
                ->andWhere(['<=', 'so.order_date', $end])
                ->scalar();
            $values[] = $v;
        }
        $labels = array_map(function ($d) {
            return date('j/n', strtotime($d));
        }, $days);
        return ['categories' => $labels, 'series' => $values];
    }

    /**
     * ระบบตัดจ่ายพัสดุที่คลังย่อย (บันทึกการจ่าย/การใช้งาน)
     * คลังย่อยต้องมีสต็อกก่อน (รับจากคลังหลักเมื่อคลังหลักจ่ายของแล้ว)
     */
    public function actionIssue()
    {
        $subIds = $this->getSubWarehouseIds();
        $subWarehouses = Warehouse::find()
            ->where(['id' => $subIds])
            ->orderBy(['warehouse_name' => SORT_ASC])
            ->all();
        if (empty($subWarehouses)) {
            $subIds = [];
        }

        // ถ้าไม่ส่ง warehouse_id มา ให้เลือกคลังย่อยแรกที่ผู้ใช้มีสิทธิ์เป็นค่าเริ่มต้น
        $requestedWarehouseId = (int) $this->request->get('warehouse_id', 0);
        $currentWarehouseId = ($requestedWarehouseId > 0 && in_array($requestedWarehouseId, $subIds, true))
            ? $requestedWarehouseId
            : (!empty($subWarehouses) ? (int) $subWarehouses[0]->id : null);

        $usageHistory = [];
        $helpdeskIdFilter = (int) $this->request->get('helpdesk_id', 0);
        if ($helpdeskIdFilter > 0) {
            // DEBUG/ค้นหาแบบกว้าง: แสดงว่ามี stock_order ที่เกี่ยวข้องถูกสร้างและมีเลข helpdesk_id นี้อยู่ใน ref/data_json หรือไม่
            // (ไม่จำกัด status เผื่อกรณีบันทึกแต่ยังไม่ CONFIRMED)
            $needle = (string) $helpdeskIdFilter;
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere([
                    'or',
                    ['like', 'ref', $needle],
                    ['like', 'data_json', $needle],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else if ($currentWarehouseId > 0) {
            // แสดงเฉพาะคลังที่เลือก (ถ้าส่ง warehouse_id มา)
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->andWhere(['or', ['main_warehouse_id' => $currentWarehouseId], ['sub_warehouse_id' => $currentWarehouseId]])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        } else {
            // รวมทุกคลังย่อยที่ผู้ใช้มีสิทธิ
            $usageHistory = StockOrder::find()
                ->with('stockDetails')
                ->where([
                    'order_type' => StockOrder::ORDER_TYPE_OUT,
                ])
                ->andWhere(['!=', 'status', StockOrder::STATUS_CANCELLED])
                ->andWhere([
                    'or',
                    ['source_type' => 'USAGE'],
                    ['like', 'ref', 'HELPDESK_ID:%'],
                    ['like', 'data_json', '"helpdesk_id"'],
                    ['like', 'data_json', 'helpdesk_id'],
                ])
                ->orderBy(['id' => SORT_DESC])
                ->limit(20)
                ->all();
        }

        return $this->render('issue', [
            'subWarehouses' => $subWarehouses,
            'currentWarehouseId' => $currentWarehouseId,
            'usageHistory' => $usageHistory,
        ]);
    }

    /**
     * API: รายการ Lot ที่มีในคลังย่อย (จาก stock_balance)
     */
    public function actionGetAvailableLots($warehouse_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $subIds = $this->getSubWarehouseIds();
        $wid = (int) $warehouse_id;
        if ($wid <= 0 || !in_array($wid, $subIds, true)) {
            return [];
        }
        $rows = (new Query())
            ->select([
                'sb.item_code',
                'i.title AS item_name',
                'sb.lot_number',
                'sb.balance_qty',
            ])
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.code = sb.item_code')
            ->where(['sb.warehouse_id' => $wid])
            ->andWhere(['>', 'sb.balance_qty', 0])
            ->orderBy(['i.title' => SORT_ASC, 'sb.lot_number' => SORT_ASC])
            ->all();
        $out = [];
        $unitCache = [];
        foreach ($rows as $r) {
            $code = $r['item_code'];
            if (!array_key_exists($code, $unitCache)) {
                $item = StockItem::findOne($code);
                $unitCache[$code] = ($item && method_exists($item, 'getUnitName'))
                    ? ((string) ($item->getUnitName() ?: ''))
                    : '';
            }
            $out[] = [
                'item_code' => $code,
                'item_name' => $r['item_name'],
                'lot_number' => $r['lot_number'],
                'balance_qty' => (float) $r['balance_qty'],
                'unit' => $unitCache[$code],
            ];
        }
        return $out;
    }

    /**
     * บันทึกการตัดจ่ายพัสดุที่คลังย่อย (POST)
     */
    public function actionSaveUsage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (!$this->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }
        $warehouse_id = (int) $this->request->post('warehouse_id');
        $job_type = trim((string) $this->request->post('job_type', ''));
        $reference = trim((string) $this->request->post('reference', ''));
        $items = (array) $this->request->post('items', []);

        $subIds = $this->getSubWarehouseIds();
        if ($warehouse_id <= 0 || !in_array($warehouse_id, $subIds, true)) {
            return ['success' => false, 'message' => 'กรุณาเลือกคลังย่อยที่ถูกต้อง'];
        }
        $items = array_filter($items, function ($r) {
            return !empty($r['item_code']) && !empty($r['lot_number']) && isset($r['qty']) && (float)$r['qty'] > 0;
        });
        if (empty($items)) {
            return ['success' => false, 'message' => 'กรุณาเพิ่มรายการพัสดุที่ตัดจ่าย'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $orderNo = $this->generateSubIssueOrderNo();
            $order = new StockOrder();
            $order->order_no = $orderNo;
            $order->order_type = StockOrder::ORDER_TYPE_OUT;
            $order->source_type = 'USAGE';
            $order->order_date = date('Y-m-d H:i:s');
            $order->main_warehouse_id = $warehouse_id;
            $order->status = StockOrder::STATUS_CONFIRMED;
            $order->data_json = ['job_type' => $job_type, 'reference' => $reference];
            if (!$order->save(false)) {
                throw new \Exception('บันทึกหัวเอกสารไม่สำเร็จ');
            }

            foreach ($items as $row) {
                $itemCode = $row['item_code'];
                $lotNumber = $row['lot_number'];
                $qty = (float) $row['qty'];
                $balance = StockBalance::findOne([
                    'item_code' => $itemCode,
                    'warehouse_id' => $warehouse_id,
                    'lot_number' => $lotNumber,
                ]);
                if (!$balance || (float)$balance->balance_qty < $qty) {
                    throw new \Exception("พัสดุ {$itemCode} Lot {$lotNumber} มีไม่พอจ่าย (ยอดคงเหลือไม่เพียงพอ)");
                }
                InventoryService::updateBalance($itemCode, $warehouse_id, $qty, 'OUT', $lotNumber);
                $detail = new StockDetail();
                $detail->stock_order_id = $order->id;
                $detail->item_code = $itemCode;
                $detail->qty = $qty;
                $detail->remain_qty = 0;
                $detail->lot_number = $lotNumber;
                $detail->unit_price = 0;
                $detail->save(false);
            }

            $transaction->commit();
            return ['success' => true, 'message' => 'บันทึกการตัดจ่ายเรียบร้อย', 'order_no' => $orderNo];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function generateSubIssueOrderNo()
    {
        $prefix = 'SUB-OUT-' . date('Ymd') . '-';
        $n = 1;
        do {
            $no = $prefix . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
            $n++;
        } while (StockOrder::findOne(['order_no' => $no]) !== null);
        return $no;
    }

    public function actionRequisition()
    {
            $model = new StockOrder();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('_form_requisition', [
            'model' => $model,
        ]);
    }


}
