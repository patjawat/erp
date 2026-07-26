<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\CommitteeDecision;
use app\modules\housing\models\Building;
use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Room;
use app\modules\housing\models\Unit;
use app\modules\housing\services\RequestWorkflowService;
use app\modules\housing\services\RequestNumberService;
use app\modules\housing\services\UnitStatusService;
use app\modules\hr\models\Employees;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

final class RequestController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
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
        $employeeProfiles = [];
        foreach (Employees::find()->where(['id' => $employeeIds])->all() as $employee) {
            $employeeProfiles[(int)$employee->id] = [
                'name' => $employee->fullname(),
                'gender' => $employee->gender ?: 'ไม่ระบุ',
                'position' => $employee->positionName(),
                'department' => $employee->departmentName(),
            ];
        }
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'status' => $status,
            'employeeProfiles' => $employeeProfiles,
        ]);
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        $employee = Employees::findOne($model->emp_id);
        return $this->render('view', [
            'model' => $model,
            'employeeName' => $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : 'บุคลากร #' . $model->emp_id,
            'employee' => $employee,
            'allocationOptions' => $this->allocationOptions(),
            'unitOptions' => Unit::find()
                ->where(['or',
                    ['status' => Unit::STATUS_VACANT],
                    ['and', ['occupancy_mode' => Unit::MODE_SHARED], ['status' => [Unit::STATUS_RESERVED, Unit::STATUS_OCCUPIED]]],
                ])
                ->select('name')->indexBy('id')->column(),
            'roomOptions' => Room::find()->where(['status' => Unit::STATUS_VACANT])->select('name')->indexBy('id')->column(),
        ]);
    }

    public function actionCreate()
    {
        $model = new HousingRequest([
            'request_no' => (new RequestNumberService())->next(),
            'request_type' => HousingRequest::TYPE_MOVE_IN,
            'status' => HousingRequest::STATUS_DRAFT,
            'requested_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->saveRequest($model);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->status !== HousingRequest::STATUS_DRAFT) {
            throw new \DomainException('แก้ไขได้เฉพาะคำขอสถานะร่าง');
        }
        return $this->saveRequest($model);
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);
        if ($model->status !== HousingRequest::STATUS_DRAFT) {
            throw new \DomainException('ลบได้เฉพาะคำขอสถานะร่าง');
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบคำขอเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    public function actionTransition(int $id, string $to)
    {
        $model = $this->findModel($id);
        if (!in_array($to, [HousingRequest::STATUS_CANCELLED, HousingRequest::STATUS_REJECTED], true)) {
            if ($issue = $this->eligibilityIssue($model)) {
                throw new \DomainException($issue);
            }
        }
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
        $decisionValue = (string)Yii::$app->request->post('decision');
        $allocationTarget = (string)Yii::$app->request->post('allocation_target');
        $unit = null;
        $roomId = null;
        if ($decisionValue === CommitteeDecision::APPROVED) {
            [$unit, $roomId] = $this->resolveAllocationTarget($allocationTarget);
        }
        $decision = new CommitteeDecision([
            'request_id' => $request->id,
            'decision' => $decisionValue,
            'decision_date' => Yii::$app->request->post('decision_date'),
            'meeting_reference' => Yii::$app->request->post('meeting_reference'),
            'note' => Yii::$app->request->post('note'),
            'recorded_by' => Yii::$app->user->id,
        ]);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$decision->save()) {
                throw new \RuntimeException(implode(' ', $decision->getFirstErrors()));
            }
            if ($decision->decision === CommitteeDecision::APPROVED) {
                (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_APPROVED, $decision->note);
                $this->allocateRequest($request, $unit, $roomId);
                Yii::$app->session->setFlash('success', 'อนุมัติและจัดสรรที่พักเรียบร้อยแล้ว');
            } else {
                (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_REJECTED, $decision->note);
                Yii::$app->session->setFlash('success', 'บันทึกผลไม่อนุมัติเรียบร้อยแล้ว');
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionAllocate(int $id)
    {
        $request = $this->findModel($id);
        if ($request->status !== HousingRequest::STATUS_APPROVED) {
            throw new \DomainException('คำขอยังไม่พร้อมจัดสรร');
        }
        [$unit, $roomId] = $this->resolveAllocationTarget((string)Yii::$app->request->post('allocation_target'));
        $this->allocateRequest($request, $unit, $roomId);
        return $this->redirect(['view', 'id' => $id]);
    }

    private function allocateRequest(HousingRequest $request, Unit $unit, ?int $roomId): void
    {
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
            throw new \RuntimeException(implode(' ', $occupancy->getFirstErrors()));
        }
        (new UnitStatusService())->refresh((int)$unit->id);
        (new RequestWorkflowService())->transition($request, HousingRequest::STATUS_ALLOCATED, 'จัดสรรที่พักแล้ว');
    }

    public function actionActivate(int $id)
    {
        $request = $this->findModel($id);
        $occupancy = $request->occupancy;
        if (!$occupancy || $request->status !== HousingRequest::STATUS_ALLOCATED) {
            throw new \DomainException('ไม่พบการจัดสรรที่พร้อมยืนยันเข้าอยู่');
        }
        return $this->redirect(['/housing/handover/prepare', 'request_id' => $id]);
    }

    private function findModel(int $id): HousingRequest
    {
        if (($model = HousingRequest::find()->with(['logs', 'decision', 'occupancy.unit', 'occupancy.room', 'occupancy.handover'])->where(['housing_request.id' => $id])->one()) === null) {
            throw new NotFoundHttpException('ไม่พบคำขอ');
        }
        return $model;
    }

    private function saveRequest(HousingRequest $model)
    {
        if ($model->load(Yii::$app->request->post())) {
            if ($issue = $this->eligibilityIssue($model, false)) {
                $model->addError('emp_id', $issue);
            } elseif ($this->hasConflictingRequest($model)) {
                $model->addError('emp_id', 'บุคลากรรายนี้มีคำขอที่กำลังดำเนินการอยู่แล้ว');
            }
            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกร่างคำขอเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $employees = Employees::find()
            ->where(['status' => 1])
            ->andWhere(['not', ['id' => 1]])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->all();
        return $this->render('form', [
            'model' => $model,
            'employeeOptions' => ArrayHelper::map($employees, 'id', static fn(Employees $employee) => $employee->fullname()),
        ]);
    }

    private function hasConflictingRequest(HousingRequest $model): bool
    {
        $finished = [
            HousingRequest::STATUS_REJECTED,
            HousingRequest::STATUS_COMPLETED,
            HousingRequest::STATUS_CANCELLED,
        ];
        $query = HousingRequest::find()
            ->where(['emp_id' => $model->emp_id])
            ->andWhere(['not in', 'status', $finished]);
        if (!$model->isNewRecord) {
            $query->andWhere(['<>', 'id', $model->id]);
        }
        return $query->exists();
    }

    private function allocationOptions(): array
    {
        $options = [];
        $emptyHouses = Building::find()
            ->where(['status' => Building::STATUS_ACTIVE, 'building_type' => Building::TYPE_HOUSE])
            ->andWhere(['not exists', Unit::find()->select('id')->where('housing_unit.building_id = housing_building.id')])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
        foreach ($emptyHouses as $building) {
            $options['building:' . $building->id] = $building->name . ' / ว่างทั้งหลัง';
        }
        $units = Unit::find()
            ->with(['building', 'floor', 'rooms'])
            ->orderBy(['building_id' => SORT_ASC, 'sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();
        foreach ($units as $unit) {
            if (!$unit->building || $unit->building->status !== Building::STATUS_ACTIVE) {
                continue;
            }
            $prefix = implode(' / ', array_filter([
                $unit->building->name,
                $unit->floor?->name,
                $unit->name,
            ]));
            if ($unit->rooms) {
                foreach ($unit->rooms as $room) {
                    if ($room->status === Unit::STATUS_VACANT) {
                        $options['room:' . $room->id] = $prefix . ' / ' . $room->name;
                    }
                }
            } elseif ($unit->status === Unit::STATUS_VACANT) {
                $options['unit:' . $unit->id] = $prefix;
            }
        }
        return $options;
    }

    private function resolveAllocationTarget(string $target): array
    {
        if (!preg_match('/^(building|unit|room):(\d+)$/', $target, $matches)) {
            throw new \DomainException('กรุณาเลือกบ้านพักหรือห้องที่จะจัดสรร');
        }
        if ($matches[1] === 'room') {
            $room = Room::find()->with('unit')->where(['id' => (int)$matches[2], 'status' => Unit::STATUS_VACANT])->one();
            if (!$room || !$room->unit) {
                throw new \DomainException('ห้องที่เลือกไม่ว่างหรือไม่มีอยู่แล้ว');
            }
            return [$room->unit, (int)$room->id];
        }
        if ($matches[1] === 'building') {
            $building = Building::find()
                ->where(['id' => (int)$matches[2], 'status' => Building::STATUS_ACTIVE, 'building_type' => Building::TYPE_HOUSE])
                ->one();
            if (!$building || $building->getUnits()->exists()) {
                throw new \DomainException('บ้านพักที่เลือกไม่ว่างหรือมีการแบ่งยูนิตแล้ว');
            }
            $unit = new Unit([
                'building_id' => $building->id,
                'code' => $building->code . '-WHOLE',
                'name' => 'ทั้งหลัง',
                'occupancy_mode' => Unit::MODE_FAMILY,
                'capacity' => 1,
                'status' => Unit::STATUS_VACANT,
            ]);
            if (!$unit->save()) {
                throw new \RuntimeException(implode(' ', $unit->getFirstErrors()));
            }
            return [$unit, null];
        }
        $unit = Unit::find()->where(['id' => (int)$matches[2], 'status' => Unit::STATUS_VACANT])->one();
        if (!$unit || $unit->getRooms()->exists()) {
            throw new \DomainException('บ้านพักที่เลือกไม่ว่างหรือไม่สามารถจัดสรรทั้งยูนิตได้');
        }
        return [$unit, null];
    }

    private function eligibilityIssue(HousingRequest $model, bool $checkConflict = true): ?string
    {
        if (!Employees::find()->where(['id' => $model->emp_id, 'status' => 1])->exists()) {
            return 'บุคลากรรายนี้ไม่ได้อยู่ในสถานะปฏิบัติงาน';
        }
        if (
            $model->request_type === HousingRequest::TYPE_MOVE_IN
            && Occupancy::find()->where(['emp_id' => $model->emp_id, 'status' => Occupancy::STATUS_ACTIVE])->exists()
        ) {
            return 'บุคลากรรายนี้มีที่พักอยู่แล้ว หากต้องการเปลี่ยนห้องให้ใช้คำขอย้ายห้อง';
        }
        if ($checkConflict && $this->hasConflictingRequest($model)) {
            return 'บุคลากรรายนี้มีคำขอที่กำลังดำเนินการอยู่แล้ว';
        }
        return null;
    }

}
