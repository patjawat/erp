<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\CommitteeDecision;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Room;
use app\modules\housing\models\Resident;
use app\modules\housing\models\Unit;
use app\modules\housing\services\RequestWorkflowService;
use app\modules\housing\services\UnitStatusService;
use app\modules\hr\models\Employees;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

final class RequestController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'transition' => ['POST'],
                    'decision' => ['POST'],
                    'allocate' => ['POST'],
                    'activate' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex(?string $status = null)
    {
        $query = HousingRequest::find()->with(['decision', 'occupancy'])->orderBy(['id' => SORT_DESC]);
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        $dataProvider = new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]);
        $models = $dataProvider->getModels();
        $employeeIds = array_values(array_unique(array_map(static fn(HousingRequest $item) => (int)$item->emp_id, $models)));
        $employeeNames = [];
        foreach (Employees::find()->where(['id' => $employeeIds])->all() as $employee) {
            $employeeNames[(int)$employee->id] = trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? ''));
        }
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'status' => $status,
            'employeeNames' => $employeeNames,
        ]);
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        $employee = Employees::findOne($model->emp_id);
        return $this->render('view', [
            'model' => $model,
            'employeeName' => $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : 'บุคลากร #' . $model->emp_id,
            'unitOptions' => Unit::find()
                ->where(['or',
                    ['status' => Unit::STATUS_VACANT],
                    ['and', ['occupancy_mode' => Unit::MODE_SHARED], ['status' => [Unit::STATUS_RESERVED, Unit::STATUS_OCCUPIED]]],
                ])
                ->select('name')->indexBy('id')->column(),
            'roomOptions' => Room::find()->where(['status' => Unit::STATUS_VACANT])->select('name')->indexBy('id')->column(),
        ]);
    }

    public function actionTransition(int $id, string $to)
    {
        $model = $this->findModel($id);
        (new RequestWorkflowService())->transition($model, $to, Yii::$app->request->post('comment'));
        Yii::$app->session->setFlash('success', 'ปรับสถานะคำขอเรียบร้อย');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionDecision(int $id)
    {
        $request = $this->findModel($id);
        if ($request->status !== HousingRequest::STATUS_COMMITTEE_REVIEW) {
            throw new \DomainException('คำขอยังไม่อยู่ในขั้นพิจารณาของคณะกรรมการ');
        }
        $decision = new CommitteeDecision([
            'request_id' => $request->id,
            'decision' => Yii::$app->request->post('decision'),
            'decision_date' => Yii::$app->request->post('decision_date'),
            'meeting_reference' => Yii::$app->request->post('meeting_reference'),
            'note' => Yii::$app->request->post('note'),
            'recorded_by' => Yii::$app->user->id,
        ]);
        if (!$decision->save()) {
            Yii::$app->session->setFlash('error', implode(' ', $decision->getFirstErrors()));
        } else {
            $to = $decision->decision === CommitteeDecision::APPROVED
                ? HousingRequest::STATUS_APPROVED
                : HousingRequest::STATUS_REJECTED;
            (new RequestWorkflowService())->transition($request, $to, $decision->note);
            Yii::$app->session->setFlash('success', 'บันทึกผลมติเรียบร้อย');
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionAllocate(int $id)
    {
        $request = $this->findModel($id);
        if ($request->status !== HousingRequest::STATUS_APPROVED) {
            throw new \DomainException('คำขอยังไม่พร้อมจัดสรร');
        }
        $unit = Unit::findOne((int)Yii::$app->request->post('unit_id'));
        $roomId = Yii::$app->request->post('room_id') ?: null;
        if (!$unit) {
            throw new NotFoundHttpException('ไม่พบยูนิตที่เลือก');
        }
        $occupancy = new Occupancy([
            'request_id' => $request->id,
            'emp_id' => $request->emp_id,
            'payer_emp_id' => $request->emp_id,
            'unit_id' => $unit->id,
            'room_id' => $roomId,
            'occupancy_type' => $unit->occupancy_mode,
            'allocated_at' => new Expression('NOW()'),
        ]);
        if (!$occupancy->save()) {
            Yii::$app->session->setFlash('error', implode(' ', $occupancy->getFirstErrors()));
            return $this->redirect(['view', 'id' => $id]);
        }
        (new UnitStatusService())->refresh((int)$unit->id);
        (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_ALLOCATED, 'จัดสรรที่พักแล้ว');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionActivate(int $id)
    {
        $request = $this->findModel($id);
        $occupancy = $request->occupancy;
        if (!$occupancy || $request->status !== HousingRequest::STATUS_ALLOCATED) {
            throw new \DomainException('ไม่พบการจัดสรรที่พร้อมยืนยันเข้าอยู่');
        }
        $occupancy->status = Occupancy::STATUS_ACTIVE;
        $occupancy->start_date = Yii::$app->request->post('start_date') ?: date('Y-m-d');
        $occupancy->save(false, ['status', 'start_date', 'updated_at', 'updated_by']);
        $employee = Employees::findOne($occupancy->emp_id);
        if ($employee && !Resident::find()->where(['occupancy_id' => $occupancy->id, 'resident_type' => 'employee'])->exists()) {
            $resident = new Resident([
                'occupancy_id' => $occupancy->id,
                'resident_type' => 'employee',
                'relationship' => 'self',
                'prefix' => $employee->prefix,
                'first_name' => $employee->fname,
                'last_name' => $employee->lname,
                'citizen_id' => $employee->cid,
                'phone' => $employee->phone,
                'start_date' => $occupancy->start_date,
                'count_for_charge' => true,
            ]);
            if (!$resident->save()) {
                throw new \RuntimeException(implode(' ', $resident->getFirstErrors()));
            }
        }
        (new UnitStatusService())->refresh((int)$occupancy->unit_id);
        (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_ACTIVE, 'ยืนยันเข้าอยู่');
        return $this->redirect(['view', 'id' => $id]);
    }

    private function findModel(int $id): HousingRequest
    {
        if (($model = HousingRequest::find()->with(['logs', 'decision', 'occupancy.unit', 'occupancy.room'])->where(['housing_request.id' => $id])->one()) === null) {
            throw new NotFoundHttpException('ไม่พบคำขอ');
        }
        return $model;
    }
}
