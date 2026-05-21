<?php

namespace app\modules\purchaseV2\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\db\Expression;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\hr\models\Employees;
use app\modules\purchaseV2\models\PurchaseRequest;
use app\modules\purchaseV2\models\PurchaseRequestItem;
use app\modules\purchaseV2\models\PurchaseRequestApproval;
use app\modules\purchaseV2\models\PurchaseRequestLog;
use app\modules\purchaseV2\models\PurchaseRequestSearch;
use app\modules\purchaseV2\services\PurchaseWorkflowService;

class RequestController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'submit' => ['POST'],
                    'cancel' => ['POST'],
                    'mark-ordered' => ['POST'],
                    'mark-received' => ['POST'],
                    'mark-stocked' => ['POST'],
                    'mark-completed' => ['POST'],
                    'approve' => ['POST', 'GET'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PurchaseRequestSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $me = UserHelper::GetEmployee();
        $canViewAll = Yii::$app->user->can('admin') || Yii::$app->user->can('purchase');
        $this->applyListScope($dataProvider->query, $me, $canViewAll);
        $statusCounts = $this->buildStatusCounts($searchModel, $me, $canViewAll);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function actionCreate()
    {
        $model = new PurchaseRequest();
        $me = UserHelper::GetEmployee();
        $model->ref = PurchaseRequest::generateRef();
        $model->request_no = PurchaseRequest::generateRequestNo();
        $model->request_date = AppHelper::convertToThai(date('Y-m-d'));
        $model->request_type = PurchaseRequest::TYPE_PLANNED;
        $model->vat_type = PurchaseRequest::VAT_NONE;
        $model->budget_year = AppHelper::YearBudget();
        if ($me) {
            $model->requester_emp_id = $me->id;
            $model->department_id = $me->department;
        }

        $items = [[]];

        if ($this->request->isPost) {
            return $this->saveRequest($model, $items, true);
        }

        return $this->renderForm($model, $items, 'สร้างคำขอจัดซื้อ');
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (!$this->canViewRequest($model) || (!$model->canEdit() && !Yii::$app->user->can('admin'))) {
            throw new NotFoundHttpException('รายการนี้ไม่สามารถแก้ไขได้');
        }

        $items = $model->items;
        if (empty($items)) {
            $items = [[]];
        }

        if ($this->request->isPost) {
            return $this->saveRequest($model, $items, false);
        }

        return $this->renderForm($model, $items, 'แก้ไขคำขอจัดซื้อ');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        if (!$this->canViewRequest($model)) {
            throw new NotFoundHttpException('ไม่พบข้อมูล');
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'รายละเอียดคำขอจัดซื้อ',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionSubmit($id)
    {
        $model = $this->findModel($id);
        if (!$model->canSubmit() && !Yii::$app->user->can('admin')) {
            throw new NotFoundHttpException('ไม่สามารถส่งอนุมัติได้');
        }
        if ((int) $model->created_by !== (int) Yii::$app->user->id && !Yii::$app->user->can('admin') && !Yii::$app->user->can('purchase')) {
            throw new NotFoundHttpException('คุณไม่มีสิทธิ์ส่งคำขอนี้');
        }

        if (!$model->items) {
            Yii::$app->session->setFlash('error', 'กรุณาเพิ่มรายการอย่างน้อย 1 รายการ');
            return $this->redirect(['view', 'id' => $id]);
        }

        PurchaseWorkflowService::submit($model, UserHelper::GetEmployee());
        Yii::$app->session->setFlash('success', 'ส่งคำขอเข้ากระบวนการอนุมัติแล้ว');

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        if (!$model->canCancel() && !Yii::$app->user->can('admin')) {
            throw new NotFoundHttpException('ไม่สามารถยกเลิกได้');
        }
        if ((int) $model->created_by !== (int) Yii::$app->user->id && !Yii::$app->user->can('admin') && !Yii::$app->user->can('purchase')) {
            throw new NotFoundHttpException('คุณไม่มีสิทธิ์ยกเลิกรายการนี้');
        }

        PurchaseWorkflowService::markStatus($model, PurchaseRequest::STATUS_CANCELLED, 'cancel', 'ยกเลิกรายการ', UserHelper::GetEmployee());
        Yii::$app->session->setFlash('success', 'ยกเลิกรายการแล้ว');

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionMarkOrdered($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('purchase')) {
            throw new NotFoundHttpException('คุณไม่มีสิทธิ์เปลี่ยนสถานะ');
        }
        PurchaseWorkflowService::markStatus($model, PurchaseRequest::STATUS_ORDERED, 'mark_ordered', 'ออกใบสั่งซื้อแล้ว', UserHelper::GetEmployee());
        Yii::$app->session->setFlash('success', 'อัปเดตเป็นสถานะออกใบสั่งซื้อแล้ว');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionMarkReceived($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('purchase')) {
            throw new NotFoundHttpException('คุณไม่มีสิทธิ์เปลี่ยนสถานะ');
        }
        PurchaseWorkflowService::markStatus($model, PurchaseRequest::STATUS_RECEIVED, 'mark_received', 'ตรวจรับแล้ว', UserHelper::GetEmployee());
        Yii::$app->session->setFlash('success', 'อัปเดตเป็นสถานะตรวจรับแล้ว');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionMarkStocked($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('purchase')) {
            throw new NotFoundHttpException('คุณไม่มีสิทธิ์เปลี่ยนสถานะ');
        }
        PurchaseWorkflowService::markStatus($model, PurchaseRequest::STATUS_STOCKED, 'mark_stocked', 'รับเข้าคลังแล้ว', UserHelper::GetEmployee());
        Yii::$app->session->setFlash('success', 'อัปเดตเป็นสถานะเข้าคลังแล้ว');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionMarkCompleted($id)
    {
        $model = $this->findModel($id);
        if (!Yii::$app->user->can('admin') && !Yii::$app->user->can('purchase')) {
            throw new NotFoundHttpException('คุณไม่มีสิทธิ์เปลี่ยนสถานะ');
        }
        PurchaseWorkflowService::markStatus($model, PurchaseRequest::STATUS_COMPLETED, 'mark_completed', 'ปิดงานแล้ว', UserHelper::GetEmployee());
        Yii::$app->session->setFlash('success', 'ปิดงานเรียบร้อยแล้ว');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionApprove($id)
    {
        $approval = PurchaseRequestApproval::findOne($id);
        if (!$approval) {
            throw new NotFoundHttpException('ไม่พบขั้นอนุมัติ');
        }
        if ($approval->status !== PurchaseRequestApproval::STATUS_PENDING) {
            throw new NotFoundHttpException('ขั้นตอนนี้ไม่อยู่ในสถานะรออนุมัติ');
        }

        $me = UserHelper::GetEmployee();
        $canAct = Yii::$app->user->can('admin') || ($me && (int) $approval->approver_emp_id === (int) $me->id);
        if (!$canAct) {
            throw new NotFoundHttpException('คุณไม่มีสิทธิ์ดำเนินการขั้นนี้');
        }

        if ($this->request->isPost) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $decision = $this->request->post('decision', 'approve');
            $comment = trim((string) $this->request->post('comment', ''));
            $request = PurchaseWorkflowService::approve($approval, $decision, $comment, $me);

            return [
                'status' => 'success',
                'message' => 'บันทึกผลอนุมัติแล้ว',
                'redirect' => Url::to(['view', 'id' => $request->id]),
            ];
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'บันทึกผลอนุมัติ',
                'content' => $this->renderAjax('_approve_form', [
                    'model' => $approval,
                ]),
            ];
        }

        return $this->render('_approve_form', [
            'model' => $approval,
        ]);
    }

    private function applyListScope($query, ?Employees $me, bool $canViewAll): void
    {
        if ($canViewAll) {
            return;
        }

        if ($me) {
            $assignedIds = PurchaseRequestApproval::find()
                ->select('request_id')
                ->where(['approver_emp_id' => $me->id, 'status' => PurchaseRequestApproval::pendingStatusValues()])
                ->column();

            $query->andWhere([
                'or',
                ['pr.created_by' => Yii::$app->user->id],
                ['pr.requester_emp_id' => $me->id],
                ['pr.id' => $assignedIds ?: [0]],
            ]);
        } else {
            $query->andWhere(['pr.created_by' => Yii::$app->user->id]);
        }
    }

    private function buildStatusCounts(PurchaseRequestSearch $searchModel, ?Employees $me, bool $canViewAll): array
    {
        $query = $searchModel->buildQuery(false);
        $this->applyListScope($query, $me, $canViewAll);

        $rawRows = (clone $query)
            ->select([
                'status' => 'pr.status',
                'count' => new Expression('COUNT(*)'),
            ])
            ->groupBy(['pr.status'])
            ->asArray()
            ->all();

        $counts = [
            'all' => (int) (clone $query)->count(),
            'draft' => 0,
            'pending' => 0,
            'approved' => 0,
            'ordered' => 0,
            'received' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];

        foreach ($rawRows as $row) {
            $status = (int) ($row['status'] ?? 0);
            $count = (int) ($row['count'] ?? 0);

            switch ($status) {
                case PurchaseRequest::STATUS_DRAFT:
                    $counts['draft'] += $count;
                    break;
                case PurchaseRequest::STATUS_PENDING_APPROVAL:
                    $counts['pending'] += $count;
                    break;
                case PurchaseRequest::STATUS_APPROVED:
                    $counts['approved'] += $count;
                    break;
                case PurchaseRequest::STATUS_ORDERED:
                    $counts['ordered'] += $count;
                    break;
                case PurchaseRequest::STATUS_RECEIVED:
                    $counts['received'] += $count;
                    break;
                case PurchaseRequest::STATUS_STOCKED:
                case PurchaseRequest::STATUS_COMPLETED:
                    $counts['completed'] += $count;
                    break;
                case PurchaseRequest::STATUS_CANCELLED:
                    $counts['cancelled'] += $count;
                    break;
            }
        }

        return $counts;
    }

    protected function saveRequest(PurchaseRequest $model, array $items, bool $isCreate)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = $this->request->post();
        $action = $request['save_action'] ?? 'draft';
        $items = $this->normalizeItems($request['PurchaseRequestItem'] ?? $items);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->load($request)) {
                throw new \RuntimeException('ไม่สามารถอ่านข้อมูลฟอร์มได้');
            }

            if (empty($model->requester_emp_id)) {
                $me = UserHelper::GetEmployee();
                if ($me) {
                    $model->requester_emp_id = $me->id;
                }
            }
            if (empty($model->department_id) && $model->requesterEmployee()) {
                $model->department_id = $model->requesterEmployee()->department;
            }

            $totals = PurchaseRequest::calculateTotals($items, (float) $model->discount_amount, $model->vat_type ?: PurchaseRequest::VAT_NONE);
            $model->subtotal_amount = $totals['subtotal_amount'];
            $model->discount_amount = $totals['discount_amount'];
            $model->vat_amount = $totals['vat_amount'];
            $model->grand_total = $totals['grand_total'];
            $model->budget_amount = $model->budget_amount ?: $model->grand_total;

            if ($action === 'submit') {
                $model->status = PurchaseRequest::STATUS_PENDING_APPROVAL;
            } else {
                $model->status = $model->status ?: PurchaseRequest::STATUS_DRAFT;
            }

            if (!$model->save()) {
                throw new \RuntimeException(implode('<br>', $model->getFirstErrors()));
            }

            PurchaseRequestItem::deleteAll(['request_id' => $model->id]);
            foreach ($items as $index => $itemData) {
                $item = new PurchaseRequestItem();
                $item->request_id = $model->id;
                $item->line_no = $index + 1;
                $item->item_type = $itemData['item_type'] ?? 'consumable';
                $item->item_code = $itemData['item_code'] ?? null;
                $item->item_name = $itemData['item_name'] ?? '';
                $item->detail = $itemData['detail'] ?? null;
                $item->unit_name = $itemData['unit_name'] ?? null;
                $item->qty = (float) ($itemData['qty'] ?? 0);
                $item->unit_price = (float) ($itemData['unit_price'] ?? 0);
                $item->amount = $item->qty * $item->unit_price;
                $item->budget_type_code = $model->budget_type_code;
                $item->data_json = $itemData['data_json'] ?? [];
                if (!$item->save()) {
                    throw new \RuntimeException('ไม่สามารถบันทึกรายการได้: ' . implode(', ', $item->getFirstErrors()));
                }
            }

            if ($action === 'submit') {
                if (empty($items)) {
                    throw new \RuntimeException('กรุณาเพิ่มรายการอย่างน้อย 1 รายการ');
                }
                PurchaseWorkflowService::submit($model, UserHelper::GetEmployee());
            } else {
                PurchaseRequestLog::deleteAll(['request_id' => $model->id, 'action' => 'save_draft']);
                $log = new PurchaseRequestLog();
                $log->request_id = $model->id;
                $log->action = 'save_draft';
                $log->message = $isCreate ? 'สร้างคำขอแบบร่าง' : 'ปรับปรุงคำขอแบบร่าง';
                $log->from_status = $model->status;
                $log->to_status = $model->status;
                $log->actor_emp_id = UserHelper::GetEmployee()?->id;
                $log->actor_user_id = Yii::$app->user->id;
                $log->data_json = ['action' => $action];
                $log->save(false);
            }

            $transaction->commit();
            return [
                'status' => 'success',
                'message' => $action === 'submit' ? 'ส่งอนุมัติเรียบร้อยแล้ว' : 'บันทึกร่างเรียบร้อยแล้ว',
                'redirect' => Url::to(['view', 'id' => $model->id]),
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'items' => $items,
            ];
        }
    }

    protected function normalizeItems(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['item_name'] ?? ''));
            $qty = (float) ($row['qty'] ?? 0);
            $unitPrice = (float) ($row['unit_price'] ?? 0);

            if ($name === '' && $qty <= 0 && $unitPrice <= 0) {
                continue;
            }

            $items[] = [
                'item_type' => $row['item_type'] ?? 'consumable',
                'item_code' => $row['item_code'] ?? null,
                'item_name' => $name ?: 'รายการ',
                'detail' => $row['detail'] ?? null,
                'unit_name' => $row['unit_name'] ?? null,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'data_json' => $row['data_json'] ?? [],
            ];
        }

        return $items;
    }

    protected function renderForm(PurchaseRequest $model, array $items, string $title)
    {
        if (!$this->request->isAjax) {
            $this->view->title = $title;
            $this->view->params['breadcrumbs'][] = ['label' => 'จัดซื้อจัดจ้าง V2', 'url' => ['/purchase-v2/default/index']];
            $this->view->params['breadcrumbs'][] = ['label' => 'รายการคำขอ', 'url' => ['/purchase-v2/request/index']];
            $this->view->params['breadcrumbs'][] = $title;
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                    'items' => $items,
                    'title' => $title,
                ]),
            ];
        }

        return $this->render('_form', [
            'model' => $model,
            'items' => $items,
            'title' => $title,
        ]);
    }

    protected function findModel($id): PurchaseRequest
    {
        if (($model = PurchaseRequest::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('ไม่พบข้อมูล');
    }

    protected function canViewRequest(PurchaseRequest $model): bool
    {
        if (Yii::$app->user->can('admin') || Yii::$app->user->can('purchase')) {
            return true;
        }

        $me = UserHelper::GetEmployee();
        if ((int) $model->created_by === (int) Yii::$app->user->id) {
            return true;
        }

        if ($me && (int) $model->requester_emp_id === (int) $me->id) {
            return true;
        }

        if ($me) {
            return PurchaseRequestApproval::find()
                ->where(['request_id' => $model->id, 'approver_emp_id' => $me->id])
                ->exists();
        }

        return false;
    }
}
