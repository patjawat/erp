<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\Warehouse;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RequisitionController extends Controller
{

public function behaviors()
{
    return [
        'verbs' => [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
                'cancel' => ['POST'],
                'approve' => ['POST'],
            ],
        ],
    ];
}

    /**
     * รายการใบขอเบิกทั้งหมด
     */
    public function actionIndex()
    {
        // เปลี่ยนจาก 'REQUISITION' เป็น 'OUT' ตามค่า ENUM ใน DB 
        // และใช้ source_type ช่วยกรองถ้าคุณระบุไว้ตอน Save
        $query = StockOrder::find()->where([
            'order_type' => 'OUT',
            'source_type' => 'REQUEST' // ถ้าคุณบันทึกค่านี้ไว้ใน Controller ตอน actionCreate
        ]);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]] // เปลี่ยนเป็น id ถ้ายังไม่ได้เก็บ created_at
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * สร้างใบขอเบิก (หน่วยงานผู้เบิกสร้างคำขอไปยังคลังหลัก เพื่อรอการอนุมัติจ่าย)
     */
    public function actionCreate()
    {
        $model = new StockOrder();
        $model->order_type = StockOrder::ORDER_TYPE_OUT;
        $model->status = StockOrder::STATUS_DRAFT;
        $model->source_type = 'REQUEST';
        $model->order_date = date('Y-m-d');
        $model->order_no = 'REQ-AUTO';

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {
                    $model->order_type = StockOrder::ORDER_TYPE_OUT;
                    $model->status = StockOrder::STATUS_DRAFT;
                    $model->source_type = 'REQUEST';
                    if (!empty($model->order_date) && preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', trim($model->order_date), $m)) {
                        $y = (int) $m[3];
                        $model->order_date = ($y > 2400 ? $y - 543 : $y) . '-' . sprintf('%02d', (int) $m[2]) . '-' . sprintf('%02d', (int) $m[1]);
                    }

                    if (empty($model->order_no) || $model->order_no === 'REQ-AUTO') {
                        $model->order_no = $this->generateOrderNo();
                    }

                    $model->setIssueReason($this->request->post('issue_reason', ''));

                    $approverEmpId = $this->request->post('approver_emp_id');
                    $model->setIssueSignatures([
                        'approver' => [
                            'name' => $this->request->post('approver_name', ''),
                            'position' => $this->request->post('approver_position', ''),
                            'date' => '',
                            'emp_id' => $approverEmpId ? (int) $approverEmpId : null,
                        ],
                    ]);

                    // ผู้เบิก = พนักงานจาก user ที่ล็อกอิน (ดึงตำแหน่งจากระบบพนักงาน)
                    $userId = Yii::$app->user->id;
                    $emp = $userId ? Employees::findOne(['user_id' => $userId]) : null;
                    if ($emp) {
                        $reqData = StockOrder::getEmployeeNameAndPosition($emp->id);
                        $model->setIssueSignatures([
                            'requester' => [
                                'name' => $reqData['name'],
                                'position' => $reqData['position'],
                                'date' => date('Y-m-d'),
                                'emp_id' => $emp->id,
                            ],
                        ]);
                    }

                    $details = $this->request->post('StockDetail', []);
                    $details = array_values(array_filter($details, function ($d) {
                        return !empty($d['item_code']) && isset($d['qty']) && (float) $d['qty'] > 0;
                    }));
                    if (empty($details)) {
                        throw new \Exception('กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ');
                    }

                    if (!$model->save()) {
                        throw new \Exception(implode('<br>', $model->getFirstErrors()));
                    }

                    foreach ($details as $data) {
                        $detail = new StockDetail();
                        $detail->load($data, '');
                        $detail->stock_order_id = $model->id;
                        $detail->lot_number = $detail->lot_number ?: '-';
                        if (!$detail->save()) {
                            throw new \Exception('ไม่สามารถบันทึกรายการวัสดุได้: ' . implode(', ', $detail->getFirstErrors()));
                        }
                    }

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id]),
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // ดึงผู้เห็นชอบ (หัวหน้า) จากการตั้งค่าผังโครงสร้างองค์กร ถ้ายังไม่ได้ตั้ง
        if (!$model->getIssueSignatureEmpId('approver')) {
            $defaultApprover = static::getDefaultApproverFromOrgDiagram();
            if ($defaultApprover && !empty($defaultApprover['emp_id'])) {
                $model->setIssueSignatures([
                    'approver' => [
                        'name' => $defaultApprover['name'],
                        'position' => $defaultApprover['position'],
                        'date' => '',
                        'emp_id' => $defaultApprover['emp_id'],
                    ],
                ]);
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * ดึงหัวหน้า/ผู้ควบคุม/ประสานงาน จากผังโครงสร้างองค์กร (hr/organization/diagram)
     * ใช้โหนดแรกที่ตั้งค่า leader1 (หัวหน้า/ผู้ควบคุม/ประสานงาน) แล้ว
     * @return array{name: string, position: string, emp_id: int|null}|null
     */
    protected static function getDefaultApproverFromOrgDiagram()
    {
        if (!class_exists(Organization::class)) {
            return null;
        }
        $nodes = Organization::find()
            ->where(['tb_name' => 'diagram'])
            ->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC])
            ->all();
        foreach ($nodes as $node) {
            $dj = $node->data_json;
            if (is_string($dj)) {
                $dj = json_decode($dj, true) ?: [];
            }
            if (!is_array($dj)) {
                $dj = [];
            }
            $leader1 = isset($dj['leader1']) ? (int) $dj['leader1'] : 0;
            if ($leader1 > 0) {
                $info = StockOrder::getEmployeeNameAndPosition($leader1);
                $info['emp_id'] = $leader1;
                return $info;
            }
        }
        return null;
    }

    protected function generateOrderNo()
    {
        return 'REQ-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
    }

    /**
     * รายการวัสดุที่หน่วยงานที่รับของ (คลังย่อย) เหลือต่ำกว่า Min — เติมให้ถึง Max
     * GET warehouse_id (คลังที่จ่ายของ), sub_warehouse_id (หน่วยงานที่รับของ)
     * - คำนวณจากยอดคงเหลือที่หน่วยงานที่รับของ (sub) ถ้า sub_warehouse_id ส่งมา
     * - แสดงเฉพาะรายการที่ยอดที่หน่วยงานรับของ < min_qty (เหลือน้อยเกินกว่า min)
     * - เบิกให้พอดี = max_qty - ยอดที่หน่วยงานรับของ
     * - ถ้า max_qty เป็น 0 ไม่โหลด โหลดเฉพาะประเภทที่คลังหลักรับได้
     */
    public function actionItemsBelowMax($warehouse_id, $sub_warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse_id = (int) $warehouse_id;
        $sub_warehouse_id = $sub_warehouse_id !== null && $sub_warehouse_id !== '' ? (int) $sub_warehouse_id : null;
        if ($warehouse_id <= 0) {
            return [];
        }

        $warehouse = Warehouse::findOne($warehouse_id);
        if (!$warehouse) {
            return [];
        }

        $allowedTypes = $warehouse->getAllowedItemTypeCodes();
        if (is_array($allowedTypes)) {
            $allowedTypes = array_map('strval', array_filter($allowedTypes, function ($v) {
                return $v !== null && $v !== '';
            }));
        } else {
            $allowedTypes = [];
        }

        $balanceWarehouseId = $sub_warehouse_id > 0 ? $sub_warehouse_id : $warehouse_id;
        $balanceSubQuery = (new Query())
            ->select(['item_code', 'SUM([[balance_qty]]) AS total_balance'])
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $balanceWarehouseId])
            ->groupBy('item_code');

        $query = (new Query())
            ->select([
                'i.item_code',
                'i.item_name',
                'i.min_qty',
                'i.max_qty',
                'COALESCE(b.total_balance, 0) AS balance_qty',
            ])
            ->from(['i' => StockItem::tableName()])
            ->leftJoin(['b' => $balanceSubQuery], 'b.item_code = i.item_code')
            ->where(['i.is_active' => 1])
            ->andWhere(['>', 'i.max_qty', 0])
            ->andWhere('COALESCE(b.total_balance, 0) < i.max_qty');

        if ($sub_warehouse_id > 0) {
            $query->andWhere('(
                (i.min_qty IS NOT NULL AND i.min_qty > 0 AND COALESCE(b.total_balance, 0) < i.min_qty)
                OR
                ((i.min_qty IS NULL OR i.min_qty <= 0) AND COALESCE(b.total_balance, 0) < i.max_qty)
            )');
        } else {
            $query->andWhere('COALESCE(b.total_balance, 0) < i.max_qty');
        }

        if (!empty($allowedTypes)) {
            $query->andWhere(['i.category_id' => $allowedTypes]);
        }

        $rows = $query->orderBy(['i.item_code' => SORT_ASC])->all();

        $results = [];
        foreach ($rows as $r) {
            $item = StockItem::findOne($r['item_code']);
            $unitName = $item && method_exists($item, 'getUnitName') ? $item->getUnitName() : null;
            $balance = (float) $r['balance_qty'];
            $maxQty = (float) $r['max_qty'];
            $minQty = $r['min_qty'] !== null ? (float) $r['min_qty'] : null;
            $qtyToReachMax = $maxQty - $balance;
            if ($qtyToReachMax <= 0) {
                continue;
            }
            $results[] = [
                'item_code' => (string) $r['item_code'],
                'item_name' => (string) $r['item_name'],
                'unit_name' => $unitName ? (string) $unitName : '-',
                'balance_qty' => round($balance, 2),
                'min_qty' => $minQty !== null ? round($minQty, 2) : null,
                'max_qty' => round($maxQty, 2),
                'qty_to_reach_max' => round($qtyToReachMax, 2),
            ];
        }
        return $results;
    }

    /**
     * หัวหน้ากดอนุมัติใบขอเบิก (ยังไม่ตัดสต็อก — คลังจะจ่ายที่เมนู "ดำเนินการจ่าย")
     * เฉพาะผู้เห็นชอบ (หัวหน้า) หรือผู้มีสิทธิ inventory เท่านั้น
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== StockOrder::STATUS_DRAFT && $model->status !== StockOrder::STATUS_PENDING) {
            Yii::$app->session->setFlash('warning', 'เอกสารนี้ไม่อยู่ในสถานะที่อนุมัติได้');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $approverEmpId = $model->getIssueSignatureEmpId('approver');
        $isCurrentUserApprover = false;
        if ($approverEmpId && !Yii::$app->user->isGuest) {
            $approverEmp = Employees::findOne($approverEmpId);
            $isCurrentUserApprover = $approverEmp && (int) $approverEmp->user_id === (int) Yii::$app->user->id;
        }
        $hasInventoryPermission = Yii::$app->user->can('inventory');
        if (!$isCurrentUserApprover && !$hasInventoryPermission) {
            Yii::$app->session->setFlash('warning', 'เฉพาะผู้เห็นชอบ (หัวหน้า) หรือผู้มีสิทธิคลังสินค้าเท่านั้นที่อนุมัติได้');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->status = StockOrder::STATUS_APPROVED;
        // บันทึกวันที่อนุมัติใน issue_approver.date
        $approverSig = $model->getIssueSignature('approver');
        $model->setIssueSignatures([
            'approver' => [
                'name' => $approverSig['name'],
                'position' => $approverSig['position'],
                'date' => date('Y-m-d H:i:s'),
                'emp_id' => $model->getIssueSignatureEmpId('approver'),
            ],
        ]);
        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'อนุมัติใบขอเบิกแล้ว — คลังสามารถดำเนินการจ่ายที่เมนู "รายการจ่ายพัสดุ"');
        } else {
            Yii::$app->session->setFlash('error', 'ไม่สามารถบันทึกการอนุมัติได้');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }


    /**
     * ฟังก์ชันช่วยอัปเดตยอดคงเหลือในตาราง stock_balance
     */
    protected function updateStockBalance($detail)
    {
        $order = $detail->stockOrder;
        $balance = \app\modules\inventoryV2\models\StockBalance::findOne([
            'item_code' => $detail->item_code,
            'warehouse_id' => $order->main_warehouse_id, // คลังต้นทาง
        ]);

        if (!$balance) {
            throw new \Exception("ไม่พบยอดคงเหลือของรายการ " . $detail->item->item_name . " ในคลังหลัก");
        }

        if ($balance->balance_qty < $detail->qty) {
            throw new \Exception("สินค้า " . $detail->item->item_name . " คงเหลือไม่พอจ่าย");
        }

        // ตัดยอดออก
        $balance->balance_qty -= $detail->qty;
        if (!$balance->save()) {
            throw new \Exception("ไม่สามารถปรับปรุงยอดคงเหลือได้");
        }
    }

    /**
     * แก้ไขใบขอเบิก (ได้เฉพาะสถานะ DRAFT หรือ PENDING)
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('warning', 'แก้ไขได้เฉพาะใบที่ยังไม่ได้รับการอนุมัติ (ฉบับร่าง หรือ รอหัวหน้าอนุมัติ)');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {
                    $model->order_type = StockOrder::ORDER_TYPE_OUT;
                    $model->source_type = 'REQUEST';
                    // ไม่เปลี่ยน status จาก form
                    if (!empty($model->order_date) && preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', trim($model->order_date), $m)) {
                        $y = (int) $m[3];
                        $model->order_date = ($y > 2400 ? $y - 543 : $y) . '-' . sprintf('%02d', (int) $m[2]) . '-' . sprintf('%02d', (int) $m[1]);
                    }

                    $model->setIssueReason($this->request->post('issue_reason', ''));

                    $approverEmpId = $this->request->post('approver_emp_id');
                    $model->setIssueSignatures([
                        'approver' => [
                            'name' => $this->request->post('approver_name', ''),
                            'position' => $this->request->post('approver_position', ''),
                            'date' => $model->getIssueSignature('approver')['date'],
                            'emp_id' => $approverEmpId ? (int) $approverEmpId : null,
                        ],
                    ]);

                    $details = $this->request->post('StockDetail', []);
                    $details = array_values(array_filter($details, function ($d) {
                        return !empty($d['item_code']) && isset($d['qty']) && (float) $d['qty'] > 0;
                    }));
                    if (empty($details)) {
                        throw new \Exception('กรุณาเพิ่มรายการวัสดุอย่างน้อย 1 รายการ');
                    }

                    if (!$model->save()) {
                        throw new \Exception(implode('<br>', $model->getFirstErrors()));
                    }

                    StockDetail::deleteAll(['stock_order_id' => $model->id]);
                    foreach ($details as $data) {
                        $detail = new StockDetail();
                        $detail->load($data, '');
                        $detail->stock_order_id = $model->id;
                        $detail->lot_number = $detail->lot_number ?: '-';
                        if (!$detail->save()) {
                            throw new \Exception('ไม่สามารถบันทึกรายการวัสดุได้: ' . implode(', ', $detail->getFirstErrors()));
                        }
                    }

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id]),
                    ];
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * ดูรายละเอียดใบขอเบิก
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'รายละเอียดใบขอเบิก: ' . $model->order_no,
                'content' => $this->renderAjax('view', ['model' => $model]),
                'footer' => '',
            ];
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }


    public function actionCancel($id)
    {
        $model = $this->findModel($id);

        if ($model->status === StockOrder::STATUS_CANCELLED) {
            Yii::$app->session->setFlash('warning', 'เอกสารนี้ถูกยกเลิกไปแล้ว');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // คืนสต็อกเฉพาะเมื่อสถานะเป็น CONFIRMED (คลังจ่ายของไปแล้ว)
            if ($model->status === StockOrder::STATUS_CONFIRMED) {
                foreach ($model->stockDetails as $detail) {
                    $success = InventoryService::moveStock(
                        $detail->item_code,
                        $model->main_warehouse_id,
                        $detail->qty,
                        'IN',
                        $model->id,
                        $detail->id
                    );
                    if (!$success) {
                        throw new \Exception("ไม่สามารถคืนพัสดุรหัส: " . $detail->item_code . " เข้าคลังได้");
                    }
                }
            }

            $wasConfirmed = $model->status === StockOrder::STATUS_CONFIRMED;
            $model->status = StockOrder::STATUS_CANCELLED;
            if (!$model->save(false)) {
                throw new \Exception("ไม่สามารถบันทึกการยกเลิกได้");
            }
            $transaction->commit();
            Yii::$app->session->setFlash('success', $wasConfirmed
                ? 'ยกเลิกใบเบิกและคืนพัสดุเข้าคลังเรียบร้อยแล้ว'
                : 'ยกเลิกใบขอเบิกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'ข้อผิดพลาด: ' . $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    protected function findModel($id)
    {
        if (($model = StockOrder::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบข้อมูลที่ต้องการ');
    }
}
