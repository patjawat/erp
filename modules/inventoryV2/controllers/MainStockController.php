<?php

namespace app\modules\inventoryV2\controllers;

use app\components\AppHelper;
use app\modules\inventoryV2\models\Warehouse;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\components\InventoryService;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class MainStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Dashboard คลังหลัก - แสดง KPIs และรายการจากข้อมูลจริง
     */
    public function actionDashboard()
    {
        $mainWarehouseIds = $this->getMainWarehouseIds();
        if (empty($mainWarehouseIds)) {
            $mainWarehouseIds = [-1];
        }

        $warehouseId = $this->getFilterWarehouseId();
        // ถ้าไม่ใช่ admin และเลือกคลังที่ไม่อยู่ในรายการที่รับผิดชอบ ให้ล้างการเลือก
        if ($warehouseId !== null && !in_array($warehouseId, $mainWarehouseIds, true)) {
            Yii::$app->session->remove('dashboard_warehouse_id');
            $warehouseId = null;
        }

        $stats = $this->getDashboardStats($warehouseId, $mainWarehouseIds);
        $warehouses = $this->getMainWarehousesList();
        $pendingRequisitions = $this->getPendingRequisitions($warehouseId, $mainWarehouseIds, 10);
        $chartData = $this->getChartData($warehouseId, $mainWarehouseIds);

        return $this->render('dashboard', [
            'stats' => $stats,
            'warehouses' => $warehouses,
            'pendingRequisitions' => $pendingRequisitions,
            'chartData' => $chartData,
            'currentWarehouseId' => $warehouseId,
        ]);
    }

    /** คลังหลักที่ยังไม่ถูกลบ (ถ้าไม่ใช่ admin เฉพาะคลังที่ผู้ใช้ถูกกำหนดเป็นผู้รับผิดชอบใน warehouse) */
    protected function getMainWarehouseIds()
    {
        $query = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->select('id');
        $this->applyOfficerFilter($query);
        return $query->column();
    }

    /** รายการคลังหลักสำหรับ dropdown (ถ้าไม่ใช่ admin เฉพาะคลังที่ผู้ใช้ถูกกำหนดเป็นผู้รับผิดชอบใน warehouse) */
    protected function getMainWarehousesList()
    {
        $query = Warehouse::find()
            ->where(['warehouse_type' => 'MAIN'])
            ->andWhere(['or', ['delete' => null], ['delete' => '']])
            ->orderBy('warehouse_name');
        $this->applyOfficerFilter($query);
        return $query->all();
    }

    /**
     * กรองคลังเฉพาะที่ current user ถูกกำหนดเป็นผู้รับผิดชอบ (data_json.officer)
     * ไม่ใช้กับ admin
     */
    protected function applyOfficerFilter($query)
    {
        if (Yii::$app->user->can('admin')) {
            return;
        }
        $userId = (string) Yii::$app->user->id;
        $query->andWhere(
            new Expression("JSON_CONTAINS(COALESCE(data_json,'{}'), '\"$userId\"', '$.officer')")
        );
    }

    /** warehouse id จาก session หรือ query (all = null) */
    protected function getFilterWarehouseId()
    {
        $session = Yii::$app->session;
        if ($this->request->get('warehouse_id') === 'all' || $this->request->get('warehouse_id') === '') {
            $session->remove('dashboard_warehouse_id');
            return null;
        }
        $id = $this->request->get('warehouse_id');
        if ($id !== null && $id !== '') {
            $id = (int) $id;
            $session->set('dashboard_warehouse_id', $id);
            return $id;
        }
        return $session->get('dashboard_warehouse_id');
    }

    /**
     * สถิติ Dashboard: รอจ่าย, รายการวิกฤต, มูลค่าคลัง, จำนวน Lot/รายการ
     */
    protected function getDashboardStats($warehouseId, array $mainWarehouseIds)
    {
        $baseRequisition = StockOrder::find()
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['main_warehouse_id' => $warehouseId ? [$warehouseId] : $mainWarehouseIds]);

        $pendingCount = (int) (clone $baseRequisition)->count();

        $criticalQuery = (new Query())
            ->from(['i' => StockItem::tableName()])
            ->leftJoin(
                ['b' => (new Query())
                    ->select(['item_code', 'SUM(balance_qty) as total_qty'])
                    ->from(StockBalance::tableName())
                    ->where($warehouseId ? ['warehouse_id' => $warehouseId] : ['warehouse_id' => $mainWarehouseIds])
                    ->groupBy('item_code')],
                'b.item_code = i.item_code'
            )
            ->where(['and',
                ['i.is_active' => 1],
                ['not', ['i.min_qty' => null]],
                ['>', 'i.min_qty', 0],
            ])
            ->andWhere('COALESCE(b.total_qty, 0) < i.min_qty');
        $criticalCount = (int) $criticalQuery->count();

        $valueQuery = (new Query())
            ->from(['sb' => StockBalance::tableName()])
            ->leftJoin(
                ['sd' => StockDetail::tableName()],
                'sd.item_code = sb.item_code AND sd.lot_number = sb.lot_number'
            )
            ->innerJoin(
                ['so' => StockOrder::tableName()],
                'so.id = sd.stock_order_id AND so.order_type = \'IN\''
            )
            ->innerJoin(
                ['latest' => (new Query())
                    ->select(['sd2.item_code', 'sd2.lot_number', 'MAX(sd2.id) as mid'])
                    ->from(['sd2' => StockDetail::tableName()])
                    ->innerJoin(['so2' => StockOrder::tableName()], 'so2.id = sd2.stock_order_id AND so2.order_type = \'IN\'')
                    ->groupBy('sd2.item_code', 'sd2.lot_number')],
                'latest.item_code = sd.item_code AND latest.lot_number = sd.lot_number AND latest.mid = sd.id'
            )
            ->where($warehouseId ? ['sb.warehouse_id' => $warehouseId] : ['sb.warehouse_id' => $mainWarehouseIds]);
        $valueQuery->select(['SUM(sb.balance_qty * COALESCE(sd.unit_price, 0)) as total']);
        $totalValue = (float) $valueQuery->scalar();

        $usageQuery = (new Query())
            ->from(['sb' => StockBalance::tableName()])
            ->innerJoin(['i' => StockItem::tableName()], 'i.item_code = sb.item_code')
            ->where($warehouseId ? ['sb.warehouse_id' => $warehouseId] : ['sb.warehouse_id' => $mainWarehouseIds])
            ->andWhere(['>', 'sb.balance_qty', 0]);
        $lotsCount = (int) (clone $usageQuery)->count();
        $itemsCount = (int) (clone $usageQuery)->select('sb.item_code')->groupBy('sb.item_code')->count();

        $insufficientCount = $this->getInsufficientToDisburseCount($warehouseId, $mainWarehouseIds);

        return [
            'pending_count' => $pendingCount,
            'critical_count' => $criticalCount,
            'total_value' => $totalValue,
            'lots_count' => $lotsCount,
            'items_with_stock' => $itemsCount,
            'insufficient_to_disburse_count' => $insufficientCount,
        ];
    }

    /**
     * นับจำนวนรายการวัสดุที่ขอเบิก (ใบ PENDING + APPROVED) แต่ยอดในสต็อกไม่พอจ่าย
     * นับจาก: ยอดที่ขอเบิก (รวมจาก stock_detail ของใบ OUT/REQUEST/APPROVED) เทียบกับยอดคงเหลือใน stock_balance
     */
    protected function getInsufficientToDisburseCount($warehouseId, array $mainWarehouseIds)
    {
        $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;
        $reqSub = (new Query())
            ->select(['sd.item_code', 'SUM(sd.qty) AS requested_qty'])
            ->from(['sd' => StockDetail::tableName()])
            ->innerJoin(['so' => StockOrder::tableName()], 'so.id = sd.stock_order_id')
            ->where([
                'so.order_type' => 'OUT',
                'so.source_type' => 'REQUEST',
                'so.status' => [StockOrder::STATUS_PENDING, StockOrder::STATUS_APPROVED],
            ])
            ->andWhere(['so.main_warehouse_id' => $warehouseIds])
            ->groupBy('sd.item_code');
        $balSub = (new Query())
            ->select(['item_code', 'SUM(balance_qty) AS balance_qty'])
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $warehouseIds])
            ->groupBy('item_code');
        $q = (new Query())
            ->from(['req' => $reqSub])
            ->leftJoin(['bal' => $balSub], 'bal.item_code = req.item_code')
            ->where('req.requested_qty > COALESCE(bal.balance_qty, 0)');
        return (int) $q->count();
    }

    /** ใบขอเบิกรอคลังจ่าย (อนุมัติแล้ว APPROVED) */
    protected function getPendingRequisitions($warehouseId, array $mainWarehouseIds, $limit = 10)
    {
        $query = StockOrder::find()
            ->with(['mainWarehouse', 'subWarehouse', 'stockDetails'])
            ->where([
                'order_type' => 'OUT',
                'source_type' => 'REQUEST',
                'status' => StockOrder::STATUS_APPROVED,
            ])
            ->andWhere(['main_warehouse_id' => $warehouseId ? [$warehouseId] : $mainWarehouseIds])
            ->orderBy(['order_date' => SORT_DESC])
            ->limit($limit);
        return $query->all();
    }

    /**
     * ข้อมูลกราฟ: การรับเข้าและจ่ายออกต่อเดือน ในปีงบประมาณไทย (ต.ค. - ก.ย.)
     */
    protected function getChartData($warehouseId, array $mainWarehouseIds)
    {
        $thaiYear = (int) AppHelper::YearBudget();
        $range = AppHelper::BudgetYearRange($thaiYear);
        $from = $range['start'] . ' 00:00:00';
        $to = $range['end'] . ' 23:59:59';

        $warehouseIds = $warehouseId ? [$warehouseId] : $mainWarehouseIds;

        $monthOrder = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $monthLabels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];

        $inByMonth = array_fill_keys($monthOrder, 0);
        $outByMonth = array_fill_keys($monthOrder, 0);

        $inRows = (new Query())
            ->select(['MONTH(so.order_date) as m', 'COUNT(*) as cnt'])
            ->from(['so' => StockOrder::tableName()])
            ->where([
                'so.order_type' => 'IN',
                'so.status' => 'CONFIRMED',
            ])
            ->andWhere(['so.main_warehouse_id' => $warehouseIds])
            ->andWhere(['>=', 'so.order_date', $from])
            ->andWhere(['<=', 'so.order_date', $to])
            ->groupBy('m')
            ->all();

        foreach ($inRows as $r) {
            $m = (int) $r['m'];
            if (isset($inByMonth[$m])) {
                $inByMonth[$m] = (int) $r['cnt'];
            }
        }

        $outRows = (new Query())
            ->select(['MONTH(so.order_date) as m', 'COUNT(*) as cnt'])
            ->from(['so' => StockOrder::tableName()])
            ->where([
                'so.order_type' => 'OUT',
                'so.source_type' => 'REQUEST',
                'so.status' => 'CONFIRMED',
            ])
            ->andWhere(['so.main_warehouse_id' => $warehouseIds])
            ->andWhere(['>=', 'so.order_date', $from])
            ->andWhere(['<=', 'so.order_date', $to])
            ->groupBy('m')
            ->all();

        foreach ($outRows as $r) {
            $m = (int) $r['m'];
            if (isset($outByMonth[$m])) {
                $outByMonth[$m] = (int) $r['cnt'];
            }
        }

        $inData = array_values($inByMonth);
        $outData = array_values($outByMonth);

        return [
            'categories' => $monthLabels,
            'series' => [
                ['name' => 'รับเข้า', 'data' => $inData],
                ['name' => 'จ่ายออก', 'data' => $outData],
            ],
            'fiscal_year' => $thaiYear,
        ];
    }
    //     public function actionReceive()
    // {
    //     $model = new StockOrder();
    //     $model->order_type = 'IN'; // ระบุเป็นประเภทรับเข้า
    //     $model->order_date = date('Y-m-d H:i:s');
    //     $model->order_no = 'RCV' . date('YmdHis'); // แนะนำให้ทำ Auto Gen ใน model
    //     $model->status = 'CONFIRMED'; // หรือ DRAFT ตาม workflow

    //     $items = [new StockDetail()];

    //     if ($this->request->isPost) {
    //         $model->load($this->request->post());

    //         // รับค่ารายการพัสดุจากหน้าฟอร์ม (Array of objects)
    //         $postDetails = $this->request->post('StockDetail', []);
    //         $items = [];

    //         // Database Transaction เพื่อความปลอดภัยของข้อมูล
    //         $dbTransaction = Yii::$app->db->beginTransaction();
    //         try {
    //             if ($model->save()) {
    //                 foreach ($postDetails as $i => $detailData) {
    //                     $detail = new StockDetail();
    //                     $detail->load($detailData, ''); // load ข้อมูลรายบรรทัด
    //                     $detail->stock_order_id = $model->id; // เชื่อม id กับหัวเอกสาร

    //                     if ($detail->save()) {
    //                         // 🚀 ส่งข้อมูลไป Update ยอดคงเหลือ Real-time ใน StockBalance
    //                         // และบันทึกลง StockDetail (Transaction Log) ผ่าน Service
    //                         $isUpdated = InventoryService::moveStock(
    //                             $detail->item_code, 
    //                             $model->warehouse_id, 
    //                             $detail->qty, 
    //                             'IN', 
    //                             $model->id
    //                         );

    //                         if (!$isUpdated) {
    //                             throw new \Exception("ไม่สามารถอัปเดตยอดสต็อกรายการที่ " . ($i + 1));
    //                         }
    //                     } else {
    //                         throw new \Exception("บันทึกรายการพัสดุไม่สำเร็จ: " . implode(', ', $detail->getFirstErrors()));
    //                     }
    //                 }

    //                 $dbTransaction->commit();
    //                 Yii::$app->session->setFlash('success', 'บันทึกรับเข้าวัสดุเรียบร้อยแล้ว');
    //                 return $this->redirect(['view', 'id' => $model->id]);
    //             }
    //         } catch (\Exception $e) {
    //             $dbTransaction->rollBack();
    //             Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    //         }
    //     }

    //     return $this->render('_form_receive', [
    //         'model' => $model,
    //         'items' => (empty($items)) ? [new StockDetail()] : $items,
    //         'listWarehouse' => ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->all(), 'id', 'warehouse_name'),
    //         // ดึงรายการพัสดุไปใช้ใน Tom-Select
    //         'listItems' => StockItem::find()->where(['is_active' => 1])->all(),
    //     ]);
    // }

    public function actionReceive()
    {
        $model = new StockOrder();
        $model->order_type = 'IN';
        $model->status = 'CONFIRMED'; // กำหนดสถานะรอไว้เลย

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                // 1. โหลดข้อมูลหัวเอกสาร
                if ($model->load($this->request->post()) && $model->save()) {

                    // 2. รับค่า StockDetail จากฟอร์ม
                    $details = $this->request->post('StockDetail', []);

                    if (empty($details)) {
                        throw new \Exception("กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ");
                    }

                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();

                        // สำคัญ: ต้องโหลดแบบเซตค่าตรงๆ หรือ load($data, '') 
                        // เพราะเราสร้าง Array name="StockDetail[i][field]" ใน JS
                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if (!$detail->save()) {
                                // ถ้า Detail บันทึกไม่สำเร็จ ให้ส่ง Error กลับไปดู
                                $errors = implode(', ', $detail->getFirstErrors());
                                throw new \Exception("รายการที่ " . ($i + 1) . " ติดปัญหา: " . $errors);
                            }

                            // 3. อัปเดตสต็อกจริง (Service ที่เราทำไว้ตอนแรก)
                            $success = InventoryService::moveStock(
                                $detail->item_code,
                                $model->main_warehouse_id,
                                $detail->qty,
                                'IN',
                                $model->id
                            );

                            if (!$success) {
                                throw new \Exception("ระบบไม่สามารถอัปเดตยอดคงเหลือในคลังได้");
                            }
                        }
                    }

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                } else {
                    // ถ้า Model หลัก (StockOrder) save ไม่ผ่าน
                    $errors = implode(', ', $model->getFirstErrors());
                    throw new \Exception("ข้อมูลหลักไม่ถูกต้อง: " . $errors);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        // กรณีโหลดหน้าเว็บปกติ (GET) — คลังสินค้าแสดงตามการกำหนดเจ้าหน้าที่รับผิดชอบคลัง (admin เห็นทั้งหมด)
        return $this->render('_form_receive', [
            'model' => $model,
            'listWarehouse' => ArrayHelper::map(Warehouse::findMainWarehousesForReceive(), 'id', 'warehouse_name'),
        ]);
    }

    public function actionUpdateReceive($id)
    {
        $model = StockOrder::find()
            ->where(['id' => $id])
            ->with('stockDetails.item') // โหลดรายละเอียดและข้อมูลพัสดุมาพร้อมกันเลย
            ->one();

        $oldItems = $model->stockDetails; // เก็บรายการเก่าไว้สำหรับเปรียบเทียบหรือคืนสต็อก

        if ($this->request->isPost) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $db = \Yii::$app->db;
            $transaction = $db->beginTransaction();

            try {
                // 1. ลบรายการเก่าใน DB ออกก่อน (ภายใต้ Transaction)
                StockDetail::deleteAll(['stock_order_id' => $model->id]);
                // 2. รับค่าจากฟอร์ม (ซึ่งตอนนี้ rowIndex จะไม่ซ้ำกันแล้ว)
                $details = $this->request->post('StockDetail', []);
                // 1. คืนสต็อกของรายการเก่าทั้งหมดก่อน (Reverse Stock)
                foreach ($oldItems as $oldItem) {
                    InventoryService::moveStock(
                        $oldItem->item_code,
                        $model->main_warehouse_id,
                        $oldItem->qty,
                        'OUT', // ใช้ OUT เพื่อหักลดยอดที่เคยรับเข้า (Reverse IN)
                        $model->id
                    );
                }

                // 2. โหลดและบันทึกข้อมูลหัวเอกสารใหม่
                if ($model->load($this->request->post()) && $model->save()) {

                    // ลบรายการ Detail เดิมทั้งหมดเพื่อเตรียมบันทึกใหม่ (แบบ Re-insert)
                    // หรือจะใช้วิธีเช็ค ID เพื่อ Update รายบรรทัดก็ได้ แต่ Re-insert จะจัดการง่ายกว่าในเคสนี้
                    StockDetail::deleteAll(['stock_order_id' => $model->id]);

                    $details = $this->request->post('StockDetail', []);
                    foreach ($details as $i => $data) {
                        $detail = new StockDetail();
                        if ($detail->load($data, '')) {
                            $detail->stock_order_id = $model->id;

                            if ($detail->save()) {
                                // 3. ปรับปรุงสต็อกตามยอดใหม่ (Update Balance)
                                InventoryService::moveStock(
                                    $detail->item_code,
                                    $model->main_warehouse_id,
                                    $detail->qty,
                                    'IN',
                                    $model->id
                                );
                            } else {
                                throw new \Exception("รายการที่ " . ($i + 1) . " บันทึกไม่สำเร็จ");
                            }
                        }
                    }

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id])
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
                ];
            }
        }

        // คลังสินค้าแสดงตามการกำหนดเจ้าหน้าที่รับผิดชอบคลัง (admin เห็นทั้งหมด)
        return $this->render('_form_receive', [
            'model' => $model,
            'items' => $model->stockDetails, // ส่งรายการเดิมไปแสดงในตาราง
            'listWarehouse' => ArrayHelper::map(Warehouse::findMainWarehousesForReceive(), 'id', 'warehouse_name'),
        ]);
    }


    protected function findModel($id)
    {
        if (($model = StockOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
