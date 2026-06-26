<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\approveV2\models\Approve;
use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\components\InventoryTelegramNotify;
use app\modules\inventoryV2\models\StockBalance;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockItemWarehouseSetting;
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
                'approve-with-edits' => ['POST'],
                'save-draft' => ['POST'],
            ],
        ],
    ];
}

    /**
     * ตรวจสิทธิ์ผู้อนุมัติของใบนี้ (approver-emp ที่ระบุไว้ หรือผู้มีสิทธิ inventory)
     * @return bool
     */
    protected function isApproverOrInventoryStaff(StockOrder $model)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        if (Yii::$app->user->can('inventory')) {
            return true;
        }
        $approverEmpId = $model->getIssueSignatureEmpId('approver');
        if (!$approverEmpId) {
            return false;
        }
        $approverEmp = Employees::findOne($approverEmpId);
        return $approverEmp && (int) $approverEmp->user_id === (int) Yii::$app->user->id;
    }

    /**
     * ตรวจว่า user ปัจจุบันคือผู้อนุมัติที่ระบุไว้ในใบเบิกนี้จริง
     * ใช้กับ panel แก้จำนวนในหน้า view และปุ่มบันทึก/อนุมัติจาก panel
     * @return bool
     */
    protected function isAssignedRequisitionApprover(StockOrder $model)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        $approverEmpId = $model->getIssueSignatureEmpId('approver');
        if (!$approverEmpId) {
            return false;
        }
        $approverEmp = Employees::findOne($approverEmpId);
        return $approverEmp && (int) $approverEmp->user_id === (int) Yii::$app->user->id;
    }

    /**
     * snapshot รายการปัจจุบันสำหรับเก็บใน approver_revisions
     * @return array [{item_code, qty}, ...]
     */
    protected function snapshotDetails(StockOrder $model)
    {
        $rows = [];
        foreach ($model->stockDetails as $d) {
            $rows[] = [
                'item_code' => (string) $d->item_code,
                'qty' => (float) $d->qty,
            ];
        }
        return $rows;
    }

    protected function syncApproveRow(StockOrder $model): void
    {
        if ($model->order_type !== StockOrder::ORDER_TYPE_OUT || $model->source_type !== 'REQUEST') {
            return;
        }

        $fromId = (string) $model->id;
        $row = Approve::findOne(['name' => 'requisition_v2', 'from_id' => $fromId, 'level' => 1])
            ?: Approve::findOne(['name' => 'requisition_v2', 'from_id' => $fromId]);

        $approveStatus = $this->mapStockOrderStatusToApproveStatus($model->status);
        $approverEmpId = $model->getIssueSignatureEmpId('approver');

        if ($approveStatus === null || !$approverEmpId) {
            if ($row) {
                $row->delete();
            }
            return;
        }

        if ($row === null) {
            $row = new Approve([
                'name' => 'requisition_v2',
                'from_id' => $fromId,
                'level' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => Yii::$app->user->isGuest ? null : Yii::$app->user->id,
            ]);
        }

        $currentData = $this->normalizeApproveDataJson($row->data_json);
        $approverSig = $model->getIssueSignature('approver');
        $approveDate = null;
        if ($approveStatus === 'Pass') {
            $approveDate = $approverSig['date'] ?: ($currentData['approve_date'] ?? date('Y-m-d H:i:s'));
        } elseif ($approveStatus === 'Reject') {
            $approveDate = $currentData['approve_date'] ?? date('Y-m-d H:i:s');
        }

        $row->name = 'requisition_v2';
        $row->from_id = $fromId;
        $row->level = 1;
        $row->emp_id = (int) $approverEmpId;
        $row->title = $model->order_no;
        $row->status = $approveStatus;
        $row->data_json = [
            'label' => 'อนุมัติเบิกวัสดุ',
            'order_no' => $model->order_no,
            'main_warehouse_id' => $model->main_warehouse_id !== null ? (int) $model->main_warehouse_id : null,
            'sub_warehouse_id' => $model->sub_warehouse_id !== null ? (int) $model->sub_warehouse_id : null,
            'approve_date' => $approveDate,
        ];
        $row->updated_at = date('Y-m-d H:i:s');
        $row->updated_by = Yii::$app->user->isGuest ? null : Yii::$app->user->id;

        if (!$row->save(false)) {
            throw new \RuntimeException('ไม่สามารถ sync รายการอนุมัติได้');
        }
    }

    protected function mapStockOrderStatusToApproveStatus($status): ?string
    {
        switch ($status) {
            case StockOrder::STATUS_PENDING:
                return 'Pending';
            case StockOrder::STATUS_APPROVED:
            case StockOrder::STATUS_CONFIRMED:
                return 'Pass';
            case StockOrder::STATUS_CANCELLED:
                return 'Reject';
            default:
                return null;
        }
    }

    protected function normalizeApproveDataJson($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * รายการใบขอเบิกทั้งหมด — รองรับ quick search filter
     * @see \app\modules\inventoryV2\models\RequisitionSearch
     */
    public function actionIndex()
    {
        $searchModel = new \app\modules\inventoryV2\models\RequisitionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
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
                    $model->status = StockOrder::STATUS_PENDING;
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

                    $this->syncApproveRow($model);

                    $transaction->commit();
                    InventoryTelegramNotify::notifyRequisitionCreated($model);
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['index']),
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

        // Resolve context อัตโนมัติจาก user ที่ล็อกอิน:
        //   - ผู้เบิก (current employee)
        //   - คลังที่รับของ (eligible sub-warehouses; auto-pick แรก)
        //   - ผู้เห็นชอบ (หัวหน้าหน่วยงาน → org diagram → null)
        $ctx = $this->resolveCreateContext();

        if ($ctx['approver'] && !$model->getIssueSignatureEmpId('approver')) {
            $model->setIssueSignatures([
                'approver' => [
                    'name' => $ctx['approver']['name'],
                    'position' => $ctx['approver']['position'],
                    'date' => '',
                    'emp_id' => $ctx['approver']['emp_id'],
                ],
            ]);
        }

        if (empty($model->sub_warehouse_id) && !empty($ctx['sub_warehouses'])) {
            $model->sub_warehouse_id = $ctx['sub_warehouses'][0]->id;
        }

        return $this->render('create', [
            'model' => $model,
            'ctx' => $ctx,
        ]);
    }

    /**
     * Resolve context อัตโนมัติสำหรับฟอร์มสร้างใบขอเบิก
     *
     * @return array{
     *   requester: array{emp: \app\modules\hr\models\Employees|null, name: string, position: string, department_id: int|null, department_name: string, avatar: string},
     *   sub_warehouses: \app\modules\inventoryV2\models\Warehouse[],
     *   approver: array{name: string, position: string, emp_id: int|null, source: string}|null
     * }
     */
    protected function resolveCreateContext()
    {
        $userId = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        $emp = $userId ? Employees::findOne(['user_id' => $userId]) : null;

        $requester = [
            'emp' => $emp,
            'name' => '',
            'position' => '',
            'department_id' => null,
            'department_name' => '',
            'avatar' => '',
        ];
        if ($emp) {
            $info = StockOrder::getEmployeeNameAndPosition($emp->id);
            $requester['name'] = $info['name'];
            $requester['position'] = $info['position'];
            $requester['department_id'] = $emp->department !== null && $emp->department !== '' ? (int) $emp->department : null;
            $requester['avatar'] = method_exists($emp, 'getAvatar') ? (string) $emp->getAvatar(false) : '';
            if ($requester['department_id']) {
                $org = Organization::findOne(['id' => $requester['department_id']]);
                $requester['department_name'] = $org ? (string) $org->name : '';
            }
        }

        $subWarehouses = Warehouse::findSubWarehousesForUser();

        $approver = null;
        if ($requester['department_id']) {
            $org = Organization::findOne(['id' => $requester['department_id']]);
            $dj = $org ? $org->data_json : null;
            if (is_string($dj)) {
                $dj = json_decode($dj, true) ?: [];
            }
            $leader1 = is_array($dj) && !empty($dj['leader1']) ? (int) $dj['leader1'] : 0;
            if ($leader1 > 0) {
                $info = StockOrder::getEmployeeNameAndPosition($leader1);
                if ($info['name'] !== '' || $info['position'] !== '') {
                    $approver = [
                        'name' => $info['name'],
                        'position' => $info['position'],
                        'emp_id' => $leader1,
                        'source' => 'department',
                    ];
                }
            }
        }
        if (!$approver) {
            $fallback = static::getDefaultApproverFromOrgDiagram();
            if ($fallback && !empty($fallback['emp_id'])) {
                $approver = [
                    'name' => $fallback['name'],
                    'position' => $fallback['position'],
                    'emp_id' => (int) $fallback['emp_id'],
                    'source' => 'org_diagram',
                ];
            }
        }

        return [
            'requester' => $requester,
            'sub_warehouses' => $subWarehouses,
            'approver' => $approver,
        ];
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
     * รายการวัสดุที่คลังที่รับของ (คลังย่อย) เหลือต่ำกว่า Min — เติมให้ถึง Max
     * GET warehouse_id (คลังที่จ่ายของ), sub_warehouse_id (คลังที่รับของ)
     * - ดึง min/max จาก stock_item_warehouse_setting ตามคลังที่รับของ (sub_warehouse_id)
     *   เฉพาะรายการที่ admin ตั้งค่าและเปิดใช้งานไว้ (is_active = 1)
     * - คำนวณจากยอดคงเหลือที่คลังที่รับของ
     * - แสดงเฉพาะรายการที่ยอดที่หน่วยงานรับของ < min_qty (เหลือน้อยเกินกว่า min)
     * - เบิกให้พอดี = max_qty - ยอดที่หน่วยงานรับของ
     * - กรองเฉพาะประเภทที่คลังหลักรับได้
     */
    public function actionItemsBelowMax($warehouse_id, $sub_warehouse_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $warehouse_id = (int) $warehouse_id;
        $sub_warehouse_id = $sub_warehouse_id !== null && $sub_warehouse_id !== '' ? (int) $sub_warehouse_id : null;
        if ($warehouse_id <= 0 || !$sub_warehouse_id || $sub_warehouse_id <= 0) {
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

        $balanceSubQuery = (new Query())
            ->select(['item_code', 'SUM([[balance_qty]]) AS total_balance'])
            ->from(StockBalance::tableName())
            ->where(['warehouse_id' => $sub_warehouse_id])
            ->groupBy('item_code');

        $query = (new Query())
            ->select([
                'item_code' => 'i.code',
                'item_name' => 'i.title',
                'min_qty' => 's.min_qty',
                'max_qty' => 's.max_qty',
                'COALESCE(b.total_balance, 0) AS balance_qty',
            ])
            ->from(['i' => StockItem::tableName()])
            ->innerJoin(
                ['s' => StockItemWarehouseSetting::tableName()],
                's.item_code = i.code AND s.warehouse_id = :setting_wh AND s.is_active = 1',
                [':setting_wh' => $sub_warehouse_id]
            )
            ->leftJoin(['b' => $balanceSubQuery], 'b.item_code = i.code')
            ->andWhere(['i.name' => 'asset_item', 'i.group_id' => 'MATER'])
            ->andWhere(['i.active' => 1])
            ->andWhere(['>', 's.max_qty', 0])
            ->andWhere('(
                (s.min_qty > 0 AND COALESCE(b.total_balance, 0) < s.min_qty)
                OR
                (s.min_qty <= 0 AND COALESCE(b.total_balance, 0) < s.max_qty)
            )');

        if (!empty($allowedTypes)) {
            $query->andWhere(['i.category_id' => $allowedTypes]);
        }

        $rows = $query->orderBy(['i.code' => SORT_ASC])->all();

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
                'item_img' => $item && method_exists($item, 'ShowImg') ? (string) $item->ShowImg() : '',
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
     * Preflight check: ตรวจว่ายอดในคลังที่จ่ายของพอจ่ายตามรายการที่ขอเบิกหรือไม่
     * POST: main_warehouse_id, items[]: [{item_code, qty}]
     * คืน: { success, has_issue, items: [{item_code, item_name, qty_requested, balance, fifo_remain, status, message}] }
     *
     * เปรียบเทียบ 2 แหล่ง:
     * - stock_balance รวมทุก Lot ในคลังนั้น (ที่ผู้ใช้เห็นในหน้ายอดคงเหลือ)
     * - Σ stock_detail.remain_qty ของ order_type=IN ในคลังนั้น (ที่ FIFO ใช้จ่ายได้จริง)
     * ถ้าไม่ตรงกัน = มีใบรับเข้าฉบับร่างที่ยังไม่ confirm หรือมี data drift
     */
    public function actionCheckStockAvailability()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;
        if (!$req->isPost) {
            return ['success' => false, 'message' => 'POST only'];
        }

        $warehouseId = (int) $req->post('main_warehouse_id', 0);
        $items = $req->post('items', []);
        if ($warehouseId <= 0 || !is_array($items) || empty($items)) {
            return ['success' => false, 'message' => 'ข้อมูลไม่ครบ'];
        }

        $results = [];
        $hasIssue = false;

        foreach ($items as $item) {
            $code = trim((string) ($item['item_code'] ?? ''));
            $qty = (float) ($item['qty'] ?? 0);
            if ($code === '' || $qty <= 0) {
                continue;
            }

            $balance = (float) (new Query())
                ->from(StockBalance::tableName())
                ->where(['item_code' => $code, 'warehouse_id' => $warehouseId])
                ->sum('balance_qty');

            $fifoRemain = (float) (new Query())
                ->from(['d' => StockDetail::tableName()])
                ->innerJoin(['o' => StockOrder::tableName()], 'o.id = d.stock_order_id')
                ->where([
                    'd.item_code' => $code,
                    'o.main_warehouse_id' => $warehouseId,
                    'o.order_type' => 'IN',
                ])
                ->andWhere(['>', 'd.remain_qty', 0])
                ->sum('d.remain_qty');

            $stockItem = StockItem::findOne(['code' => $code]);
            $itemName = $stockItem ? $stockItem->title : $code;

            $status = 'ok';
            $message = 'พอจ่าย';
            if ($balance <= 0 && $fifoRemain <= 0) {
                $status = 'not_in_warehouse';
                $message = 'ไม่มีในคลังนี้';
                $hasIssue = true;
            } elseif ($fifoRemain < $qty) {
                if ($balance >= $qty) {
                    $status = 'balance_only';
                    $message = 'ยอดคลังพอ แต่ไม่มี Lot ใน FIFO (ใบรับเข้าอาจยังเป็นฉบับร่าง)';
                } else {
                    $status = 'insufficient';
                    $message = 'คงเหลือไม่พอ';
                }
                $hasIssue = true;
            }

            $results[] = [
                'item_code' => $code,
                'item_name' => $itemName,
                'qty_requested' => round($qty, 2),
                'balance' => round($balance, 2),
                'fifo_remain' => round($fifoRemain, 2),
                'status' => $status,
                'message' => $message,
            ];
        }

        return [
            'success' => true,
            'has_issue' => $hasIssue,
            'items' => $results,
        ];
    }

    /**
     * Diagnostic: ดู stock_balance + stock_detail (FIFO) ของวัสดุนี้ทั่วระบบ
     * เปิดผ่าน: /inventory-v2/requisition/debug-stock?item_code=XXX
     * ใช้วินิจฉัยเคส "preflight แจ้งจ่ายไม่ได้ ทั้งที่เพิ่งรับเข้า"
     */
    public function actionDebugStock($item_code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $code = trim((string) $item_code);
        if ($code === '') {
            return ['error' => 'item_code required'];
        }

        // 0. ค้น stock_item แบบ exact และ fuzzy (case/space/dash variations)
        $exactItem = StockItem::findOne(['code' => $code]);
        $fuzzyItems = (new Query())
            ->select(['code', 'title', 'active', 'name', 'group_id'])
            ->from(StockItem::tableName())
            ->where(['or',
                ['code' => $code],
                ['like', 'code', $code],
                ['like', 'code', str_replace('-', '', $code)],
                ['like', 'code', str_replace('-', '.', $code)],
                ['like', 'title', '%' . str_replace(['-', '.'], '', $code) . '%'],
            ])
            ->limit(20)
            ->all();

        // ค้น stock_balance ทั้งแบบ exact และ LIKE (เผื่อ trailing space)
        $balanceLikeRows = (new Query())
            ->select(['item_code', 'warehouse_id', 'lot_number', 'balance_qty'])
            ->from(StockBalance::tableName())
            ->where(['like', 'item_code', $code])
            ->limit(20)
            ->all();
        $detailLikeRows = (new Query())
            ->select([
                'd.item_code',
                'order_no' => 'o.order_no',
                'order_status' => 'o.status',
                'main_warehouse_id' => 'o.main_warehouse_id',
                'lot_number' => 'd.lot_number',
                'qty' => 'd.qty',
                'remain_qty' => 'd.remain_qty',
                'order_date' => 'o.order_date',
            ])
            ->from(['d' => StockDetail::tableName()])
            ->innerJoin(['o' => StockOrder::tableName()], 'o.id = d.stock_order_id')
            ->where(['like', 'd.item_code', $code])
            ->andWhere(['o.order_type' => 'IN'])
            ->orderBy(['o.order_date' => SORT_DESC])
            ->limit(20)
            ->all();

        $itemName = $exactItem ? $exactItem->title : ($fuzzyItems[0]['title'] ?? null);

        // 1. stock_balance ทุกคลัง ทุก Lot
        $balances = (new Query())
            ->select(['warehouse_id', 'lot_number', 'balance_qty'])
            ->from(StockBalance::tableName())
            ->where(['item_code' => $code])
            ->all();
        $balancesOut = [];
        foreach ($balances as $b) {
            $wh = Warehouse::findOne($b['warehouse_id']);
            $balancesOut[] = [
                'warehouse_id' => (int) $b['warehouse_id'],
                'warehouse_name' => $wh ? $wh->warehouse_name : '?',
                'warehouse_type' => $wh ? $wh->warehouse_type : '?',
                'lot_number' => $b['lot_number'],
                'balance_qty' => (float) $b['balance_qty'],
            ];
        }

        // 2. stock_detail IN ทุกใบ ทุก Lot
        $rows = (new Query())
            ->select([
                'order_no' => 'o.order_no',
                'order_status' => 'o.status',
                'main_warehouse_id' => 'o.main_warehouse_id',
                'lot_number' => 'd.lot_number',
                'qty' => 'd.qty',
                'remain_qty' => 'd.remain_qty',
                'order_date' => 'o.order_date',
            ])
            ->from(['d' => StockDetail::tableName()])
            ->innerJoin(['o' => StockOrder::tableName()], 'o.id = d.stock_order_id')
            ->where(['d.item_code' => $code, 'o.order_type' => 'IN'])
            ->orderBy(['o.order_date' => SORT_DESC])
            ->all();
        $detailsOut = [];
        foreach ($rows as $r) {
            $wh = Warehouse::findOne($r['main_warehouse_id']);
            $detailsOut[] = [
                'order_no' => $r['order_no'],
                'order_status' => $r['order_status'],
                'warehouse_id' => (int) $r['main_warehouse_id'],
                'warehouse_name' => $wh ? $wh->warehouse_name : '?',
                'lot_number' => $r['lot_number'],
                'qty_received' => (float) $r['qty'],
                'remain_qty' => (float) $r['remain_qty'],
                'order_date' => $r['order_date'],
            ];
        }

        return [
            'item_code_queried' => $code,
            'item_code_queried_bytes' => strlen($code),
            'item_name' => $itemName,
            'stock_item_master' => [
                'exact_match' => $exactItem ? [
                    'code' => $exactItem->code,
                    'code_bytes' => strlen($exactItem->code),
                    'title' => $exactItem->title,
                    'active' => (int) $exactItem->active,
                ] : null,
                'fuzzy_matches' => $fuzzyItems,
            ],
            'stock_balance_exact' => $balancesOut,
            'stock_balance_like' => $balanceLikeRows,
            'stock_detail_in_exact' => $detailsOut,
            'stock_detail_in_like' => $detailLikeRows,
            'hint' => [
                'no_exact_item' => $exactItem === null,
                'has_fuzzy_item' => count($fuzzyItems) > 0,
                'has_balance_like' => count($balanceLikeRows) > 0,
                'has_detail_like' => count($detailLikeRows) > 0,
            ],
            'note' => 'ดู stock_item_master.fuzzy_matches: ถ้ามีรหัสคล้ายกัน = master ใช้คนละรูปแบบกับใบเบิก. ดู stock_balance_like / stock_detail_in_like: ถ้ามีแต่ exact ไม่มี = trailing space หรือรหัสคล้ายกัน',
        ];
    }

    /**
     * Diagnostic: หา master items (stock_item) ที่ "ชื่อซ้ำกัน" — root cause ของเคส
     * "เลือก code ผิด" / "preflight บอกไม่มี ทั้งที่ชื่อตรง"
     *
     * เกณฑ์ซ้ำ:
     *   - exact:  title เท่ากันเป๊ะ
     *   - normalized: trim + ลด whitespace ซ้อน → ตัดสินใจเสริมว่าซ้ำเชิงสายตา
     *
     * สำหรับแต่ละ code แสดง:
     *   - active, total_balance (รวมทุกคลัง), has_in_history, has_out_history
     *   → ตัดสินได้ว่า code ไหนคือ "ตัวจริง" (มีกิจกรรม) vs "ตัวซ้ำ" (ปล่อยไว้/ปิด active)
     */
    public function actionDebugDuplicateItems($mode = 'exact')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $normalizeExpr = "TRIM(REPLACE(REPLACE(REPLACE(title, '  ', ' '), '  ', ' '), '  ', ' '))";
        $groupExpr = $mode === 'normalized' ? $normalizeExpr : 'title';

        $duplicateTitles = (new Query())
            ->select(['title_key' => $groupExpr, 'cnt' => 'COUNT(*)'])
            ->from(StockItem::tableName())
            ->where(['name' => 'asset_item', 'group_id' => 'MATER'])
            ->groupBy([$groupExpr])
            ->having(['>', 'COUNT(*)', 1])
            ->orderBy(['cnt' => SORT_DESC, 'title_key' => SORT_ASC])
            ->all();

        $totalGroups = count($duplicateTitles);
        $totalAffected = 0;
        $groups = [];

        foreach ($duplicateTitles as $g) {
            $titleKey = $g['title_key'];
            $itemsQuery = (new Query())
                ->select(['code', 'title', 'active'])
                ->from(StockItem::tableName())
                ->where(['name' => 'asset_item', 'group_id' => 'MATER']);
            if ($mode === 'normalized') {
                $itemsQuery->andWhere([$normalizeExpr => $titleKey]);
            } else {
                $itemsQuery->andWhere(['title' => $titleKey]);
            }
            $items = $itemsQuery->orderBy(['code' => SORT_ASC])->all();

            $codes = [];
            foreach ($items as $it) {
                $code = $it['code'];

                $balance = (float) (new Query())
                    ->from(StockBalance::tableName())
                    ->where(['item_code' => $code])
                    ->sum('balance_qty');

                $hasIn = (bool) (new Query())
                    ->from(['d' => StockDetail::tableName()])
                    ->innerJoin(['o' => StockOrder::tableName()], 'o.id = d.stock_order_id')
                    ->where(['d.item_code' => $code, 'o.order_type' => 'IN'])
                    ->exists();

                $hasOut = (bool) (new Query())
                    ->from(['d' => StockDetail::tableName()])
                    ->innerJoin(['o' => StockOrder::tableName()], 'o.id = d.stock_order_id')
                    ->where(['d.item_code' => $code, 'o.order_type' => 'OUT'])
                    ->exists();

                $codes[] = [
                    'code' => $code,
                    'title_exact' => $it['title'],
                    'active' => (int) $it['active'],
                    'total_balance' => round($balance, 2),
                    'has_in_history' => $hasIn,
                    'has_out_history' => $hasOut,
                    'suggested_action' => self::suggestDuplicateAction($it['active'], $balance, $hasIn, $hasOut),
                ];
            }
            $totalAffected += count($codes);
            $groups[] = [
                'title_key' => $titleKey,
                'count' => (int) $g['cnt'],
                'codes' => $codes,
            ];
        }

        return [
            'mode' => $mode,
            'mode_hint' => 'ใช้ ?mode=normalized ถ้าอยากเทียบแบบลด whitespace ซ้อน',
            'total_duplicate_groups' => $totalGroups,
            'total_affected_items' => $totalAffected,
            'duplicates' => $groups,
            'legend' => [
                'KEEP' => 'มี balance หรือกิจกรรม — ควรเก็บเป็นตัวจริง',
                'DEACTIVATE' => 'ไม่มี balance ไม่มีกิจกรรม — ปิด active ได้ปลอดภัย',
                'REVIEW' => 'มี IN/OUT history แต่ balance = 0 — ตรวจสอบก่อนตัดสินใจ',
            ],
        ];
    }

    private static function suggestDuplicateAction($active, $balance, $hasIn, $hasOut)
    {
        if ($balance > 0 || $hasOut) {
            return 'KEEP';
        }
        if (!$hasIn && !$hasOut && $balance <= 0) {
            return 'DEACTIVATE';
        }
        return 'REVIEW';
    }

    /**
     * Diagnostic: แสดงใบรับเข้า 10 ใบล่าสุด พร้อม item_code ใน detail แต่ละ row
     * ใช้วินิจฉัยเคส "รับเข้าแล้วแต่ระบบหาไม่เจอ" — ดูว่าใบรับเข้าจริง ๆ ใช้ item_code อะไร
     */
    public function actionDebugRecentReceives()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $orders = (new Query())
            ->select(['id', 'order_no', 'status', 'order_date', 'main_warehouse_id', 'created_at'])
            ->from(StockOrder::tableName())
            ->where(['order_type' => 'IN'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(10)
            ->all();

        $results = [];
        foreach ($orders as $o) {
            $details = (new Query())
                ->select(['item_code', 'lot_number', 'qty', 'remain_qty'])
                ->from(StockDetail::tableName())
                ->where(['stock_order_id' => $o['id']])
                ->all();
            foreach ($details as &$d) {
                $masterItem = StockItem::findOne(['code' => $d['item_code']]);
                $d['item_code_bytes'] = strlen($d['item_code']);
                $d['master_title'] = $masterItem ? $masterItem->title : '*** ไม่มีใน master ***';
            }
            unset($d);
            $wh = Warehouse::findOne($o['main_warehouse_id']);
            $results[] = [
                'order_id' => (int) $o['id'],
                'order_no' => $o['order_no'],
                'status' => $o['status'],
                'order_date' => $o['order_date'],
                'created_at' => $o['created_at'],
                'warehouse_id' => (int) $o['main_warehouse_id'],
                'warehouse_name' => $wh ? $wh->warehouse_name : '?',
                'warehouse_type' => $wh ? $wh->warehouse_type : '?',
                'detail_count' => count($details),
                'details' => $details,
            ];
        }

        return ['recent_receives' => $results];
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
        $transaction = Yii::$app->db->beginTransaction();
        try {
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
            if (!$model->save(false)) {
                throw new \Exception('ไม่สามารถบันทึกการอนุมัติได้');
            }
            $this->syncApproveRow($model);
            $transaction->commit();
            InventoryTelegramNotify::notifyRequisitionApproved($model);
            Yii::$app->session->setFlash('success', 'อนุมัติใบขอเบิกแล้ว — คลังสามารถดำเนินการจ่ายที่เมนู "รายการจ่ายพัสดุ"');
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
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
     *
     * สิทธิ์การแก้:
     * - DRAFT: ผู้สร้างใบเอง (created_by) หรือผู้มีสิทธิ inventory
     * - PENDING: ผู้อนุมัติของใบ (approver-emp) หรือผู้มีสิทธิ inventory
     *   (ผู้ขอที่ส่งใบไปแล้ว ต้องการแก้ ต้องให้หัวหน้าตีกลับเป็น DRAFT — ยังไม่อยู่ในขอบเขตงานนี้)
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('warning', 'แก้ไขได้เฉพาะใบที่ยังไม่ได้รับการอนุมัติ (ฉบับร่าง หรือ รอหัวหน้าอนุมัติ)');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $isInventoryStaff = !Yii::$app->user->isGuest && Yii::$app->user->can('inventory');
        $isCreator = !Yii::$app->user->isGuest && (int) $model->created_by === (int) Yii::$app->user->id;
        $isApprover = $this->isApproverOrInventoryStaff($model);
        $allowed = false;
        if ($model->status === StockOrder::STATUS_DRAFT) {
            $allowed = $isCreator || $isInventoryStaff;
        } elseif ($model->status === StockOrder::STATUS_PENDING) {
            $allowed = $isApprover;
        }
        if (!$allowed) {
            Yii::$app->session->setFlash('warning', 'ไม่มีสิทธิ์แก้ไขใบนี้');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $isApproverEditing = $model->status === StockOrder::STATUS_PENDING && $isApprover;
        $beforeSnapshot = $isApproverEditing ? $this->snapshotDetails($model) : null;

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

                    if ($isApproverEditing && $beforeSnapshot !== null) {
                        $afterSnapshot = array_map(function ($d) {
                            return [
                                'item_code' => (string) ($d['item_code'] ?? ''),
                                'qty' => (float) ($d['qty'] ?? 0),
                            ];
                        }, $details);
                        $byEmpId = null;
                        $byName = '';
                        $editorEmp = Employees::findOne(['user_id' => Yii::$app->user->id]);
                        if ($editorEmp) {
                            $byEmpId = (int) $editorEmp->id;
                            $info = StockOrder::getEmployeeNameAndPosition($editorEmp->id);
                            $byName = $info['name'];
                        }
                        $model->recordApproverRevision(
                            $beforeSnapshot,
                            $afterSnapshot,
                            Yii::$app->user->id,
                            $byEmpId,
                            $byName
                        );
                        if (!$model->save(false)) {
                            throw new \Exception('ไม่สามารถบันทึกประวัติการปรับรายการได้');
                        }
                    }

                    $this->syncApproveRow($model);

                    $transaction->commit();
                    return [
                        'success' => true,
                        'redirect' => \yii\helpers\Url::to(['index']),
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
     * บันทึก & อนุมัติในจังหวะเดียว (สำหรับ inline edit ที่หน้า view)
     * POST: items[] = [{item_code, qty}, ...]
     * - status ปัจจุบันต้องเป็น DRAFT หรือ PENDING
     * - เฉพาะผู้อนุมัติที่ระบุไว้ในใบเบิกนี้
     * - transaction: snapshot → ลบ stock_detail → สร้างใหม่ → record revision → set APPROVED + approver.date
     */
    public function actionApproveWithEdits($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if (!in_array($model->status, [StockOrder::STATUS_DRAFT, StockOrder::STATUS_PENDING])) {
            return ['success' => false, 'message' => 'เอกสารนี้ไม่อยู่ในสถานะที่อนุมัติได้'];
        }
        if (!$this->isAssignedRequisitionApprover($model)) {
            return ['success' => false, 'message' => 'เฉพาะผู้อนุมัติใบเบิกนี้เท่านั้นที่บันทึกและอนุมัติได้'];
        }

        $itemsPost = $this->request->post('items', []);
        if (!is_array($itemsPost)) {
            $itemsPost = [];
        }
        $items = [];
        foreach ($itemsPost as $row) {
            $code = trim((string) ($row['item_code'] ?? ''));
            $qty = (float) ($row['qty'] ?? 0);
            if ($code === '' || $qty <= 0) {
                continue;
            }
            $items[] = [
                'item_code' => $code,
                'qty' => $qty,
                'lot_number' => trim((string) ($row['lot_number'] ?? '-')) ?: '-',
            ];
        }
        if (empty($items)) {
            return ['success' => false, 'message' => 'ต้องมีรายการอย่างน้อย 1 รายการ'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $beforeSnapshot = $this->snapshotDetails($model);

            StockDetail::deleteAll(['stock_order_id' => $model->id]);
            foreach ($items as $data) {
                $detail = new StockDetail();
                $detail->stock_order_id = $model->id;
                $detail->item_code = $data['item_code'];
                $detail->qty = $data['qty'];
                $detail->lot_number = $data['lot_number'];
                if (!$detail->save()) {
                    throw new \Exception('ไม่สามารถบันทึกรายการวัสดุได้: ' . implode(', ', $detail->getFirstErrors()));
                }
            }

            $afterSnapshot = array_map(function ($d) {
                return ['item_code' => $d['item_code'], 'qty' => (float) $d['qty']];
            }, $items);
            $byEmpId = null;
            $byName = '';
            $editorEmp = Employees::findOne(['user_id' => Yii::$app->user->id]);
            if ($editorEmp) {
                $byEmpId = (int) $editorEmp->id;
                $info = StockOrder::getEmployeeNameAndPosition($editorEmp->id);
                $byName = $info['name'];
            }
            $model->recordApproverRevision(
                $beforeSnapshot,
                $afterSnapshot,
                Yii::$app->user->id,
                $byEmpId,
                $byName
            );

            $approverSig = $model->getIssueSignature('approver');
            $model->setIssueSignatures([
                'approver' => [
                    'name' => $approverSig['name'],
                    'position' => $approverSig['position'],
                    'date' => date('Y-m-d H:i:s'),
                    'emp_id' => $model->getIssueSignatureEmpId('approver'),
                ],
            ]);
            $model->status = StockOrder::STATUS_APPROVED;
            if (!$model->save(false)) {
                throw new \Exception('ไม่สามารถบันทึกสถานะอนุมัติได้');
            }
            $this->syncApproveRow($model);

            $transaction->commit();
            InventoryTelegramNotify::notifyRequisitionApproved($model);
            return [
                'success' => true,
                'message' => 'บันทึกการปรับรายการและอนุมัติแล้ว — คลังสามารถดำเนินการจ่ายได้',
                'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id]),
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * บันทึกรายการที่แก้แล้ว แต่ยังไม่อนุมัติ (เก็บไว้ให้หัวหน้าทบทวนต่อ)
     * POST: items[] เหมือน actionApproveWithEdits
     */
    public function actionSaveDraft($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if (!in_array($model->status, [StockOrder::STATUS_DRAFT, StockOrder::STATUS_PENDING])) {
            return ['success' => false, 'message' => 'เอกสารนี้ไม่อยู่ในสถานะที่แก้ไขได้'];
        }
        if (!$this->isAssignedRequisitionApprover($model)) {
            return ['success' => false, 'message' => 'เฉพาะผู้อนุมัติใบเบิกนี้เท่านั้นที่แก้ไขจำนวนที่ขอเบิกได้'];
        }

        $itemsPost = $this->request->post('items', []);
        $items = [];
        foreach ((array) $itemsPost as $row) {
            $code = trim((string) ($row['item_code'] ?? ''));
            $qty = (float) ($row['qty'] ?? 0);
            if ($code === '' || $qty <= 0) {
                continue;
            }
            $items[] = [
                'item_code' => $code,
                'qty' => $qty,
                'lot_number' => trim((string) ($row['lot_number'] ?? '-')) ?: '-',
            ];
        }
        if (empty($items)) {
            return ['success' => false, 'message' => 'ต้องมีรายการอย่างน้อย 1 รายการ'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $beforeSnapshot = $this->snapshotDetails($model);

            StockDetail::deleteAll(['stock_order_id' => $model->id]);
            foreach ($items as $data) {
                $detail = new StockDetail();
                $detail->stock_order_id = $model->id;
                $detail->item_code = $data['item_code'];
                $detail->qty = $data['qty'];
                $detail->lot_number = $data['lot_number'];
                if (!$detail->save()) {
                    throw new \Exception('ไม่สามารถบันทึกรายการวัสดุได้: ' . implode(', ', $detail->getFirstErrors()));
                }
            }

            $afterSnapshot = array_map(function ($d) {
                return ['item_code' => $d['item_code'], 'qty' => (float) $d['qty']];
            }, $items);
            $byEmpId = null;
            $byName = '';
            $editorEmp = Employees::findOne(['user_id' => Yii::$app->user->id]);
            if ($editorEmp) {
                $byEmpId = (int) $editorEmp->id;
                $info = StockOrder::getEmployeeNameAndPosition($editorEmp->id);
                $byName = $info['name'];
            }
            $model->recordApproverRevision(
                $beforeSnapshot,
                $afterSnapshot,
                Yii::$app->user->id,
                $byEmpId,
                $byName
            );
            if (!$model->save(false)) {
                throw new \Exception('ไม่สามารถบันทึกประวัติการปรับรายการได้');
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'บันทึกแล้ว — ยังไม่ได้อนุมัติ',
                'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id]),
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
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
            $this->syncApproveRow($model);
            $transaction->commit();
            Yii::$app->session->setFlash('success', $wasConfirmed
                ? 'ยกเลิกใบเบิกและคืนพัสดุเข้าคลังเรียบร้อยแล้ว'
                : 'ยกเลิกใบขอเบิกเรียบร้อยแล้ว');
            return $this->redirect(['index']);
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
