<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\hr\models\Employees;
use app\modules\housing\models\Building;
use app\modules\housing\models\Floor;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\MonthlyAccount;
use app\modules\housing\services\HousingAccessService;
use app\modules\housing\services\HousingUploadService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

final class BuildingController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST']]],
        ]);
    }

    public function actionIndex()
    {
        $provider = new ActiveDataProvider([
            'query' => Building::find()
                ->with(['units', 'floors', 'responsibleEmployee.statusName'])
                ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);
        $refs = array_values(array_filter(array_map(
            static fn(Building $building) => $building->ref,
            $provider->getModels()
        )));
        $buildingImages = $refs === [] ? [] : Uploads::find()
            ->where(['ref' => $refs, 'name' => HousingUploadService::SLOT_BUILDING_IMAGE])
            ->orderBy(['id' => SORT_ASC])
            ->indexBy('ref')
            ->all();
        $eligibleEmployeeIds = HousingAccessService::eligibleEmployeeIds();
        $responsibleAttentionCount = (int) Building::find()
            ->where($eligibleEmployeeIds === []
                ? []
                : ['or',
                    ['responsible_employee_id' => null],
                    ['not in', 'responsible_employee_id', $eligibleEmployeeIds],
                ])
            ->count();

        return $this->render('index', [
            'dataProvider' => $provider,
            'buildingImages' => $buildingImages,
            'responsibleAttentionCount' => $responsibleAttentionCount,
        ]);
    }

    public function actionCreate()
    {
        return $this->save(new Building(), 'เพิ่มบ้านพัก/แฟลต');
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        $model->populateRelation('floors', $model->getFloors()->with(['units.rooms'])->all());
        $units = $model->getUnits()->with(['floor', 'rooms', 'assets', 'photos.upload'])->orderBy(['floor_id' => SORT_ASC, 'sort_order' => SORT_ASC])->all();
        $occupancies = Occupancy::find()
            ->with(['employee', 'residents', 'unit', 'room'])
            ->joinWith('unit')
            ->where(['housing_unit.building_id' => $model->id])
            ->andWhere(['housing_occupancy.status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE]])
            ->orderBy(['housing_unit.code' => SORT_ASC, 'housing_occupancy.start_date' => SORT_ASC])
            ->all();
        $buildingImage = Uploads::find()
            ->where(['ref' => $model->ref, 'name' => HousingUploadService::SLOT_BUILDING_IMAGE])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $this->render('view', [
            'model' => $model,
            'units' => $units,
            'occupancies' => $occupancies,
            'maintenanceRequests' => $model->getMaintenanceRequests()->with('assignedEmployee')->limit(20)->all(),
            'buildingImage' => $buildingImage,
            'expenseHistory' => MonthlyAccount::find()->joinWith('period')->where([
                'housing_monthly_account.building_id' => $model->id,
                'housing_billing_period.status' => 'closed',
            ])->orderBy(['housing_billing_period.start_date' => SORT_DESC])->limit(20)->all(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        return $this->save($this->findModel($id), 'แก้ไขบ้านพัก/แฟลต');
    }

    public function actionCreateFloor(int $building_id)
    {
        $building = $this->findModel($building_id);
        $model = new Floor(['building_id' => $building->id]);
        return $this->saveFloor($model, $building, 'เพิ่มชั้นใน ' . $building->name);
    }

    public function actionUpdateFloor(int $id)
    {
        $model = $this->findFloorModel($id);
        return $this->saveFloor($model, $model->building, 'แก้ไขข้อมูลชั้น');
    }

    private function saveFloor(Floor $model, Building $building, string $title)
    {
        $isNewRecord = $model->isNewRecord;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'message' => $isNewRecord ? 'เพิ่มชั้นเรียบร้อย' : 'แก้ไขข้อมูลชั้นเรียบร้อย',
                    'container' => '#housing-building-container',
                ];
            }
            return $this->redirect(['index']);
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => ActiveForm::validate($model)];
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax('_floor_form', ['model' => $model, 'building' => $building]),
            ];
        }
        return $this->render('_floor_form', ['model' => $model, 'building' => $building]);
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);
        if ($model->getUnits()->exists() || $model->getFloors()->exists()) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบได้ เนื่องจากมีข้อมูลชั้นหรือยูนิตอยู่ในอาคารนี้');
        } else {
            $uploadService = new HousingUploadService();
            $uploadIds = $uploadService->findIdsByRefsAndSlots(
                [(string) $model->ref],
                [HousingUploadService::SLOT_BUILDING_IMAGE]
            );
            try {
                if ($model->delete() === false) {
                    throw new \RuntimeException('ไม่สามารถลบข้อมูลอาคารได้');
                }
                $failedIds = $uploadService->deleteUploads($uploadIds);
                if ($failedIds !== []) {
                    Yii::warning('ลบไฟล์รูปอาคารไม่สำเร็จ upload IDs: ' . implode(',', $failedIds), __METHOD__);
                    Yii::$app->session->setFlash('warning', 'ลบข้อมูลแล้ว แต่มีไฟล์รูปภาพบางรายการรอการตรวจสอบ');
                } else {
                    Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อย');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception, __METHOD__);
                Yii::$app->session->setFlash('error', 'ไม่สามารถลบข้อมูลได้ กรุณาตรวจสอบข้อมูลที่เกี่ยวข้องแล้วลองใหม่');
            }
        }
        return $this->redirect(['index']);
    }

    private function save(Building $model, string $title)
    {
        if ($model->load(Yii::$app->request->post())) {
            $model->building_image = UploadedFile::getInstance($model, 'building_image');
        }
        if (Yii::$app->request->isPost && $model->validate()) {
            $wasNewRecord = $model->isNewRecord;
            $transaction = Yii::$app->db->beginTransaction();
            $uploadService = new HousingUploadService();
            $newUpload = null;
            $oldUploadIds = [];
            try {
                if (!$model->save(false)) {
                    throw new \RuntimeException('ไม่สามารถบันทึกข้อมูลอาคารได้');
                }
                if ($model->building_image !== null) {
                    $oldUploadIds = $uploadService->findIdsByRefsAndSlots(
                        [(string) $model->ref],
                        [HousingUploadService::SLOT_BUILDING_IMAGE]
                    );
                    $newUpload = FileManagerHelper::saveUploadedFile(
                        $model->building_image,
                        (string) $model->ref,
                        HousingUploadService::SLOT_BUILDING_IMAGE,
                        false
                    );
                    if ($newUpload === null) {
                        $model->addError('building_image', 'ไม่สามารถจัดเก็บรูปภาพบ้านพักได้ กรุณาลองใหม่อีกครั้ง');
                        throw new \RuntimeException('จัดเก็บรูปภาพอาคารไม่สำเร็จ');
                    }
                }
                $transaction->commit();
            } catch (\Throwable $exception) {
                if ($newUpload !== null) {
                    $failedCleanupIds = $uploadService->deleteUploads([(int) $newUpload->id]);
                    if ($failedCleanupIds !== []) {
                        Yii::error('ย้อนกลับไฟล์รูปอาคารไม่สำเร็จ upload IDs: ' . implode(',', $failedCleanupIds), __METHOD__);
                    }
                }
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                if ($wasNewRecord) {
                    $model->setOldAttributes(null);
                    $model->id = null;
                }
                if (!$model->hasErrors('building_image')) {
                    $model->addError('building_image', 'ไม่สามารถบันทึกข้อมูลและรูปภาพได้ กรุณาลองใหม่อีกครั้ง');
                }
                Yii::error($exception, __METHOD__);
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['errors' => $this->activeFormErrors($model)];
                }
                return $this->renderForm($model, $title);
            }

            $failedCleanupIds = $newUpload === null
                ? []
                : $uploadService->deleteUploads($oldUploadIds);
            $hasCleanupWarning = $failedCleanupIds !== [];
            if ($hasCleanupWarning) {
                Yii::warning('ลบรูปอาคารเดิมไม่สำเร็จ upload IDs: ' . implode(',', $failedCleanupIds), __METHOD__);
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'level' => $hasCleanupWarning ? 'warning' : 'success',
                    'message' => $hasCleanupWarning
                        ? 'บันทึกข้อมูลแล้ว แต่มีรูปภาพเดิมรอการตรวจสอบ'
                        : 'บันทึกข้อมูลเรียบร้อย',
                    'container' => '#housing-building-container',
                ];
            }
            if ($hasCleanupWarning) {
                Yii::$app->session->setFlash('warning', 'บันทึกข้อมูลแล้ว แต่มีรูปภาพเดิมรอการตรวจสอบ');
            } else {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อย');
            }
            return $this->redirect(['index']);
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => ActiveForm::validate($model)];
        }
        return $this->renderForm($model, $title);
    }

    private function renderForm(Building $model, string $title)
    {
        $buildingImage = $model->isNewRecord ? null : Uploads::find()
            ->where(['ref' => $model->ref, 'name' => HousingUploadService::SLOT_BUILDING_IMAGE])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        $inactiveResponsible = null;
        if (!$model->isNewRecord && $model->responsibleEmployee !== null
            && !HousingAccessService::canBeResponsible($model->responsibleEmployee)) {
            $inactiveResponsible = $model->responsibleEmployee;
            $model->responsible_employee_id = null;
        }
        $eligibleEmployeeIds = HousingAccessService::eligibleEmployeeIds();
        $activeEmployees = Employees::find()
            ->where(['id' => $eligibleEmployeeIds])
            ->orderBy(['fname' => SORT_ASC, 'lname' => SORT_ASC])
            ->all();
        $employeeItems = ArrayHelper::map(
            $activeEmployees,
            'id',
            static fn(Employees $employee) => $employee->fullname()
        );

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => Yii::$app->request->get('title', $title),
                'content' => $this->renderAjax('_form', [
                    'model' => $model,
                    'buildingImage' => $buildingImage,
                    'employeeItems' => $employeeItems,
                    'inactiveResponsible' => $inactiveResponsible,
                ]),
            ];
        }
        return $this->render('form-page', [
            'model' => $model,
            'title' => $title,
            'buildingImage' => $buildingImage,
            'employeeItems' => $employeeItems,
            'inactiveResponsible' => $inactiveResponsible,
        ]);
    }

    private function findModel(int $id): Building
    {
        if (($model = Building::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบ้านพักหรือแฟลต');
        }
        return $model;
    }

    private function findFloorModel(int $id): Floor
    {
        if (($model = Floor::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบข้อมูลชั้น');
        }
        return $model;
    }
}
