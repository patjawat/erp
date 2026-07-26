<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\Employees;
use app\modules\housing\models\Building;
use app\modules\housing\models\MaintenanceRequest;
use app\modules\housing\models\Occupancy;
use app\modules\housing\services\HousingAccessService;
use app\modules\housing\services\HousingUploadService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

final class MaintenanceController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete-photo' => ['POST']],
            ],
        ]);
    }

    public function actionIndex(?int $building_id = null)
    {
        $query = MaintenanceRequest::find()
            ->with(['building', 'assignedEmployee'])
            ->orderBy(['reported_at' => SORT_DESC, 'id' => SORT_DESC]);
        if ($building_id !== null) {
            $query->andWhere(['building_id' => $building_id]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
        ]);
        $summaryQuery = clone $query;
        $openCount = (int) (clone $summaryQuery)
            ->andWhere(['status' => [MaintenanceRequest::STATUS_NEW, MaintenanceRequest::STATUS_IN_PROGRESS]])
            ->count();
        $totalExpense = (float) (clone $summaryQuery)->sum('expense_amount');

        return $this->render('index', [
            'dataProvider' => $provider,
            'buildingId' => $building_id,
            'buildingOptions' => $this->buildingOptions(),
            'openCount' => $openCount,
            'totalExpense' => $totalExpense,
        ]);
    }

    public function actionCreate(?int $building_id = null)
    {
        $identity = Yii::$app->user->identity;
        $reporter = (string) ($identity->username ?? '');
        return $this->save(new MaintenanceRequest([
            'building_id' => $building_id,
            'reported_at' => date('Y-m-d\TH:i'),
            'reporter_name' => $reporter,
        ]));
    }

    public function actionUpdate(int $id)
    {
        return $this->save($this->findModel($id));
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'beforePhotos' => $this->photos($model, HousingUploadService::SLOT_REPAIR_BEFORE),
            'afterPhotos' => $this->photos($model, HousingUploadService::SLOT_REPAIR_AFTER),
        ]);
    }

    public function actionDeletePhoto(int $id, int $maintenance_id)
    {
        $model = $this->findModel($maintenance_id);
        $upload = Uploads::findOne(['id' => $id, 'ref' => $model->ref]);
        if ($upload === null || !in_array($upload->name, [
            HousingUploadService::SLOT_REPAIR_BEFORE,
            HousingUploadService::SLOT_REPAIR_AFTER,
        ], true)) {
            throw new NotFoundHttpException('ไม่พบรูปภาพ');
        }
        $failedIds = (new HousingUploadService())->deleteUploads([(int) $upload->id]);
        if ($failedIds !== []) {
            Yii::warning('ลบรูปแจ้งซ่อมไม่สำเร็จ upload IDs: ' . implode(',', $failedIds), __METHOD__);
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบไฟล์รูปภาพได้ กรุณาลองใหม่อีกครั้ง');
        } else {
            Yii::$app->session->setFlash('success', 'ลบรูปภาพแล้ว');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    private function save(MaintenanceRequest $model)
    {
        if (!$model->isNewRecord) {
            $model->reported_at = $this->toInputDateTime($model->reported_at);
            $model->repaired_at = $this->toInputDateTime($model->repaired_at);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->reporter_type === MaintenanceRequest::REPORTER_RESIDENT && $model->occupancy_id) {
                $occupancy = Occupancy::find()->with('employee')->where([
                    'id' => $model->occupancy_id,
                    'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE],
                ])->one();
                if ($occupancy !== null) {
                    $model->reporter_emp_id = $occupancy->emp_id;
                    $model->reporter_name = $occupancy->employee?->fullname() ?: $model->reporter_name;
                    $model->acknowledgement_status = $model->acknowledgement_status === MaintenanceRequest::ACK_NOT_REQUIRED
                        ? MaintenanceRequest::ACK_PENDING
                        : $model->acknowledgement_status;
                }
            } else {
                $model->occupancy_id = null;
                $model->reporter_emp_id = null;
                $model->acknowledgement_status = MaintenanceRequest::ACK_NOT_REQUIRED;
            }
            $model->before_photos = UploadedFile::getInstances($model, 'before_photos');
            $model->after_photos = UploadedFile::getInstances($model, 'after_photos');
            $model->reported_at = $this->toDatabaseDateTime($model->reported_at);
            $model->repaired_at = $this->toDatabaseDateTime($model->repaired_at);
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            if ($model->validate()) {
                $this->validatePhotoLimits($model);
            }
            if (!$model->hasErrors() && $this->saveWithPhotos($model)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'message' => 'บันทึกข้อมูลแจ้งซ่อมแล้ว',
                    'redirect' => Url::to(['view', 'id' => $model->id]),
                ];
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => $this->activeFormErrors($model)];
        }
        if (Yii::$app->request->isPost) {
            if ($model->validate()) {
                $this->validatePhotoLimits($model);
            }
            if (!$model->hasErrors() && $this->saveWithPhotos($model)) {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลแจ้งซ่อมแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        $model->reported_at = $this->toInputDateTime($model->reported_at);
        $model->repaired_at = $this->toInputDateTime($model->repaired_at);
        $params = [
            'model' => $model,
            'buildingOptions' => $this->buildingOptions(),
            'employeeOptions' => $this->employeeOptions(),
            'occupancyOptions' => $this->occupancyOptions($model->building_id ? (int) $model->building_id : null),
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->isNewRecord ? 'แจ้งปัญหาบ้านพัก' : 'ปรับปรุงรายการแจ้งซ่อม',
                'content' => $this->renderAjax('_form', $params),
            ];
        }
        return $this->render('form-page', $params);
    }

    private function buildingOptions(): array
    {
        return ArrayHelper::map(
            Building::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all(),
            'id',
            static fn(Building $building) => $building->code . ' · ' . $building->name
        );
    }

    private function employeeOptions(): array
    {
        return ArrayHelper::map(
            Employees::find()->where(['id' => HousingAccessService::eligibleEmployeeIds()])
                ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])->all(),
            'id',
            static fn(Employees $employee) => $employee->fullname()
        );
    }

    private function occupancyOptions(?int $buildingId = null): array
    {
        $items = [];
        $query = Occupancy::find()
            ->with(['employee', 'unit.building', 'room'])
            ->where(['housing_occupancy.status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE]])
            ->orderBy(['unit_id' => SORT_ASC, 'room_id' => SORT_ASC]);
        if ($buildingId !== null) {
            $query->joinWith('unit')->andWhere(['housing_unit.building_id' => $buildingId]);
        }
        $occupancies = $query->all();
        foreach ($occupancies as $occupancy) {
            $residentName = $occupancy->employee?->fullname() ?: ('รหัสบุคลากร ' . $occupancy->emp_id);
            $location = $occupancy->unit?->building?->name . ' / ' . $occupancy->unit?->name;
            if ($occupancy->room !== null) {
                $location .= ' / ' . $occupancy->room->name;
            }
            $items[$occupancy->id] = $residentName . ' (' . $location . ')';
        }
        return $items;
    }

    private function photos(MaintenanceRequest $model, string $slot): array
    {
        return Uploads::find()
            ->where(['ref' => $model->ref, 'name' => $slot])
            ->orderBy(['id' => SORT_ASC])
            ->all();
    }

    private function validatePhotoLimits(MaintenanceRequest $model): void
    {
        $uploadService = new HousingUploadService();
        foreach ([
            'before_photos' => HousingUploadService::SLOT_REPAIR_BEFORE,
            'after_photos' => HousingUploadService::SLOT_REPAIR_AFTER,
        ] as $attribute => $slot) {
            $incomingCount = count($model->$attribute ?? []);
            if ($incomingCount === 0) {
                continue;
            }
            $existingCount = $model->isNewRecord
                ? 0
                : $uploadService->countByRefAndSlot((string) $model->ref, $slot);
            if (HousingUploadService::exceedsLimit(
                $existingCount,
                $incomingCount,
                HousingUploadService::MAX_REPAIR_PHOTOS_PER_SLOT
            )) {
                $model->addError(
                    $attribute,
                    sprintf(
                        'เพิ่มได้รวมไม่เกิน %d ภาพ ปัจจุบันมี %d ภาพ และเลือกเพิ่ม %d ภาพ',
                        HousingUploadService::MAX_REPAIR_PHOTOS_PER_SLOT,
                        $existingCount,
                        $incomingCount
                    )
                );
            }
        }
    }

    private function saveWithPhotos(MaintenanceRequest $model): bool
    {
        $wasNewRecord = $model->isNewRecord;
        $transaction = Yii::$app->db->beginTransaction();
        $uploadService = new HousingUploadService();
        $uploadedIds = [];
        try {
            if (!$model->save(false)) {
                throw new \RuntimeException('ไม่สามารถบันทึกข้อมูลแจ้งซ่อมได้');
            }
            if (!$wasNewRecord) {
                $this->lockMaintenanceRequest((int) $model->id);
            }
            $this->validatePhotoLimits($model);
            if ($model->hasErrors()) {
                throw new \RuntimeException('จำนวนรูปแจ้งซ่อมเกินขีดจำกัด');
            }
            foreach ([
                'before_photos' => HousingUploadService::SLOT_REPAIR_BEFORE,
                'after_photos' => HousingUploadService::SLOT_REPAIR_AFTER,
            ] as $attribute => $slot) {
                foreach ($model->$attribute ?? [] as $file) {
                    $upload = FileManagerHelper::saveUploadedFile(
                        $file,
                        (string) $model->ref,
                        $slot,
                        false
                    );
                    if ($upload === null) {
                        $model->addError(
                            $attribute,
                            'จัดเก็บไฟล์ ' . $file->name . ' ไม่สำเร็จ จึงยังไม่ได้บันทึกการเปลี่ยนแปลง'
                        );
                        throw new \RuntimeException('จัดเก็บรูปแจ้งซ่อมไม่สำเร็จ');
                    }
                    $uploadedIds[] = (int) $upload->id;
                }
            }
            $transaction->commit();
            return true;
        } catch (\Throwable $exception) {
            $failedCleanupIds = $uploadService->deleteUploads($uploadedIds);
            if ($failedCleanupIds !== []) {
                Yii::error('ย้อนกลับรูปแจ้งซ่อมไม่สำเร็จ upload IDs: ' . implode(',', $failedCleanupIds), __METHOD__);
            }
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            if ($wasNewRecord) {
                $model->setOldAttributes(null);
                $model->id = null;
            }
            if (!$model->hasErrors('before_photos') && !$model->hasErrors('after_photos')) {
                $model->addError('before_photos', 'ไม่สามารถบันทึกข้อมูลและรูปภาพได้ กรุณาลองใหม่อีกครั้ง');
            }
            Yii::error($exception, __METHOD__);
            return false;
        }
    }

    private function lockMaintenanceRequest(int $id): void
    {
        $db = Yii::$app->db;
        $quotedTable = $db->quoteTableName(MaintenanceRequest::tableName());
        $quotedId = $db->quoteColumnName('id');
        $lockedId = $db->createCommand(
            "SELECT {$quotedId} FROM {$quotedTable} WHERE {$quotedId} = :id FOR UPDATE",
            [':id' => $id]
        )->queryScalar();
        if ($lockedId === false) {
            throw new NotFoundHttpException('ไม่พบรายการแจ้งซ่อม');
        }
    }

    private function findModel(int $id): MaintenanceRequest
    {
        if (($model = MaintenanceRequest::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบรายการแจ้งซ่อม');
        }
        return $model;
    }

    private function toDatabaseDateTime($value): ?string
    {
        if (!$value) {
            return null;
        }
        return date('Y-m-d H:i:s', strtotime((string) $value));
    }

    private function toInputDateTime($value): ?string
    {
        if (!$value) {
            return null;
        }
        return date('Y-m-d\TH:i', strtotime((string) $value));
    }
}
