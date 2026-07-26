<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\Building;
use app\modules\housing\models\Checkout;
use app\modules\housing\models\Handover;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\MaintenanceRequest;
use app\modules\filemanager\models\Uploads;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\housing\services\HandoverWorkflowService;
use app\modules\housing\services\CheckoutWorkflowService;
use app\modules\housing\services\HousingContextService;
use app\modules\housing\services\RequestNumberService;
use app\modules\housing\services\RequestWorkflowService;
use app\modules\housing\services\UnitStatusService;
use app\modules\hr\models\Employees;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

final class MyController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['housing.user']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'submit' => ['POST'],
                    'sign-handover' => ['POST'],
                    'sign-checkout' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id, [
            'tab' => Yii::$app->request->get('housing_tab', 'overview'),
            'expenseYear' => Yii::$app->request->get('expense_year'),
            'maintenanceStatus' => Yii::$app->request->get('maintenance_status', 'all'),
            'maintenanceYear' => Yii::$app->request->get('maintenance_year'),
        ]);
        return $this->render('index', ['context' => $context]);
    }

    public function actionCreateRequest()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        if (!$context['employee']) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบุคลากรที่เชื่อมกับบัญชีผู้ใช้');
        }
        if ((string)$context['employee']->status !== '1') {
            Yii::$app->session->setFlash('error', 'ไม่สามารถยื่นคำขอได้ เนื่องจากสถานะบุคลากรไม่ได้อยู่ระหว่างปฏิบัติงาน');
            return $this->redirect(['/profile', 'name' => 'housing']);
        }
        if ($context['mode'] !== 'applicant') {
            Yii::$app->session->setFlash('error', 'มีคำขอหรือสิทธิ์เข้าพักที่กำลังใช้งานอยู่แล้ว');
            return $this->redirect(['/profile', 'name' => 'housing']);
        }
        $model = new HousingRequest([
            'request_no' => (new RequestNumberService())->next(),
            'request_type' => HousingRequest::TYPE_MOVE_IN,
            'emp_id' => $context['employee']->id,
        ]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->post('submit') === '1') {
                (new RequestWorkflowService())->transition($model, HousingRequest::STATUS_SUBMITTED, 'ผู้ใช้ส่งคำขอ');
            }
            Yii::$app->session->setFlash('success', 'บันทึกคำขอเรียบร้อย');
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/profile', 'name' => 'housing'])];
            }
            return $this->redirect(['/profile', 'name' => 'housing']);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เขียนคำร้องขอใช้บ้านพัก',
                'content' => $this->renderAjax('_request_modal', ['model' => $model]),
            ];
        }
        return $this->render('request-form', ['model' => $model]);
    }

    public function actionSubmit(int $id)
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        if (!$context['employee'] || (string)$context['employee']->status !== '1') {
            throw new \DomainException('ไม่สามารถส่งคำขอได้ เนื่องจากสถานะบุคลากรไม่ได้อยู่ระหว่างปฏิบัติงาน');
        }
        $model = HousingRequest::findOne(['id' => $id, 'emp_id' => $context['employee']->id ?? 0, 'status' => HousingRequest::STATUS_DRAFT]);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบคำขอที่ส่งได้');
        }
        (new RequestWorkflowService())->transition($model, HousingRequest::STATUS_SUBMITTED, 'ผู้ใช้ส่งคำขอ');
        return $this->redirect(['/profile', 'name' => 'housing']);
    }

    public function actionHandover(int $id)
    {
        $model = $this->findOwnHandover($id);
        return $this->render('handover', [
            'model' => $model,
            'photos' => Uploads::find()
                ->where(['ref' => $model->ref, 'name' => 'housing_handover_condition'])
                ->orderBy(['id' => SORT_ASC])
                ->all(),
        ]);
    }

    public function actionSignHandover(int $id)
    {
        $model = $this->findOwnHandover($id);
        if (!Yii::$app->request->post('received_ack')) {
            Yii::$app->session->setFlash('error', 'กรุณายืนยันว่าตรวจข้อมูลและรับมอบที่พักแล้ว');
            return $this->redirect(['handover', 'id' => $id]);
        }
        try {
            $employee = Employees::findOne(['user_id' => Yii::$app->user->id]);
            (new HandoverWorkflowService())->signReceiver($model, (int)($employee?->id ?? 0));
            Yii::$app->session->setFlash('success', 'ลงนามรับมอบและเปิดสถานะเข้าพักเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['handover', 'id' => $id]);
    }

    public function actionCreateMaintenance()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        $occupancy = $context['occupancy'];
        $employee = $context['employee'];
        if (!$occupancy || !$employee || $context['mode'] !== 'resident') {
            throw new \DomainException('แจ้งปัญหาได้เมื่อมีสถานะเข้าพักแล้ว');
        }
        $model = new MaintenanceRequest([
            'building_id' => $occupancy->unit?->building_id,
            'occupancy_id' => $occupancy->id,
            'reporter_emp_id' => $employee->id,
            'reporter_name' => $employee->fullname(),
            'reporter_type' => MaintenanceRequest::REPORTER_RESIDENT,
            'problem_scope' => $occupancy->room_id ? MaintenanceRequest::SCOPE_ROOM : MaintenanceRequest::SCOPE_HOUSE,
            'reported_at' => date('Y-m-d\TH:i'),
            'priority' => MaintenanceRequest::PRIORITY_NORMAL,
            'status' => MaintenanceRequest::STATUS_NEW,
            'acknowledgement_status' => MaintenanceRequest::ACK_PENDING,
        ]);
        if ($model->load(Yii::$app->request->post())) {
            $model->building_id = $occupancy->unit?->building_id;
            $model->occupancy_id = $occupancy->id;
            $model->reporter_emp_id = $employee->id;
            $model->reporter_name = $employee->fullname();
            $model->reporter_type = MaintenanceRequest::REPORTER_RESIDENT;
            $model->status = MaintenanceRequest::STATUS_NEW;
            $model->acknowledgement_status = MaintenanceRequest::ACK_PENDING;
            $model->assigned_employee_id = null;
            $model->expense_amount = 0;
            $model->resolution = null;
            $model->repaired_at = null;
            $model->reported_at = str_replace('T', ' ', (string)$model->reported_at);
            $model->before_photos = UploadedFile::getInstances($model, 'before_photos');
            if (!in_array($model->problem_scope, [MaintenanceRequest::SCOPE_HOUSE, MaintenanceRequest::SCOPE_UNIT, MaintenanceRequest::SCOPE_ROOM], true)) {
                $model->problem_scope = $occupancy->room_id ? MaintenanceRequest::SCOPE_ROOM : MaintenanceRequest::SCOPE_HOUSE;
            }
        }
        if (Yii::$app->request->isPost && $model->validate() && $model->save(false)) {
            $failed = false;
            foreach ($model->before_photos ?? [] as $file) {
                if (FileManagerHelper::saveUploadedFile($file, (string)$model->ref, 'housing_repair_before', false) === null) {
                    $failed = true;
                }
            }
            Yii::$app->session->setFlash(
                $failed ? 'warning' : 'success',
                $failed ? 'บันทึกการแจ้งปัญหาแล้ว แต่รูปภาพบางไฟล์จัดเก็บไม่สำเร็จ' : 'ส่งรายการแจ้งปัญหาเรียบร้อยแล้ว'
            );
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/profile', 'name' => 'housing'])];
            }
            return $this->redirect(['/profile', 'name' => 'housing']);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'แจ้งปัญหาบ้านพัก/ห้องพัก',
                'content' => $this->renderAjax('_maintenance_modal', ['model' => $model, 'occupancy' => $occupancy]),
            ];
        }
        return $this->render('maintenance-form', ['model' => $model, 'occupancy' => $occupancy]);
    }

    public function actionCreateCheckout()
    {
        $context = (new HousingContextService())->forUser((int)Yii::$app->user->id);
        $occupancy = $context['occupancy'];
        $employee = $context['employee'];
        if (!$occupancy || !$employee || $context['mode'] !== 'resident') {
            throw new \DomainException('ยื่นคำขอคืนได้เมื่อมีสถานะเข้าพักอยู่');
        }
        $existing = Checkout::findOne(['occupancy_id' => $occupancy->id]);
        if ($existing) {
            return $this->redirect(['checkout', 'id' => $existing->id]);
        }
        $model = new Checkout([
            'checkout_no' => $this->nextCheckoutNumber(),
            'occupancy_id' => $occupancy->id,
            'resident_emp_id' => $employee->id,
            'resident_name' => $employee->fullname(),
            'requested_date' => date('Y-m-d'),
        ]);
        if ($model->load(Yii::$app->request->post())) {
            $model->occupancy_id = $occupancy->id;
            $model->resident_emp_id = $employee->id;
            $model->resident_name = $employee->fullname();
        }
        if (Yii::$app->request->isPost && $model->save()) {
            (new UnitStatusService())->refresh((int)$occupancy->unit_id);
            Yii::$app->session->setFlash('success', 'ส่งคำขอคืนบ้านพักแล้ว ผู้ดูแลจะนัดหมายเพื่อตรวจรับคืน');
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'redirect' => \yii\helpers\Url::to(['/housing/my/checkout', 'id' => $model->id])];
            }
            return $this->redirect(['checkout', 'id' => $model->id]);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => 'แจ้งคืนบ้านพัก', 'content' => $this->renderAjax('_checkout_modal', ['model' => $model, 'occupancy' => $occupancy])];
        }
        return $this->render('checkout-request', ['model' => $model, 'occupancy' => $occupancy]);
    }

    public function actionCheckout(int $id)
    {
        $model = $this->findOwnCheckout($id);
        return $this->render('checkout', [
            'model' => $model,
            'photos' => Uploads::find()->where(['ref' => $model->ref, 'name' => 'housing_checkout_condition'])->orderBy('id')->all(),
        ]);
    }

    public function actionSignCheckout(int $id)
    {
        $model = $this->findOwnCheckout($id);
        if (!Yii::$app->request->post('resident_ack')) {
            Yii::$app->session->setFlash('error', 'กรุณายืนยันการส่งคืนบ้านพักและอุปกรณ์');
            return $this->redirect(['checkout', 'id' => $id]);
        }
        try {
            $employee = Employees::findOne(['user_id' => Yii::$app->user->id]);
            (new CheckoutWorkflowService())->signResident($model, (int)($employee?->id ?? 0));
            Yii::$app->session->setFlash('success', 'ลงนามส่งคืนแล้ว รอผู้ดูแลตรวจรับและปิดรายการ');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['checkout', 'id' => $id]);
    }

    private function findOwnHandover(int $id): Handover
    {
        $employee = Employees::findOne(['user_id' => Yii::$app->user->id]);
        $model = Handover::find()
            ->joinWith('occupancy')
            ->with(['occupancy.employee', 'occupancy.unit.building', 'occupancy.unit.floor', 'occupancy.room'])
            ->where(['housing_handover.id' => $id, 'housing_occupancy.emp_id' => $employee?->id ?? 0])
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเอกสารรับมอบที่มีสิทธิ์ดำเนินการ');
        }
        return $model;
    }

    private function findOwnCheckout(int $id): Checkout
    {
        $employee = Employees::findOne(['user_id' => Yii::$app->user->id]);
        $model = Checkout::find()->joinWith('occupancy')
            ->with(['occupancy.employee', 'occupancy.unit.building', 'occupancy.unit.floor', 'occupancy.room'])
            ->where(['housing_checkout.id' => $id, 'housing_occupancy.emp_id' => $employee?->id ?? 0])->one();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเอกสารส่งคืนบ้านพักที่มีสิทธิ์ดำเนินการ');
        }
        return $model;
    }

    private function nextCheckoutNumber(): string
    {
        $prefix = 'HCO-' . substr((string)((int)date('Y') + 543), -2) . '-';
        $last = Checkout::find()->where(['like', 'checkout_no', $prefix . '%', false])->orderBy(['checkout_no' => SORT_DESC])->select('checkout_no')->scalar();
        return $prefix . str_pad((string)($last ? (int)substr((string)$last, -4) + 1 : 1), 4, '0', STR_PAD_LEFT);
    }
}
