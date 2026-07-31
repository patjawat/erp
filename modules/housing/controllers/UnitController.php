<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\Building;
use app\modules\housing\models\Floor;
use app\modules\housing\models\LocationPhoto;
use app\modules\housing\models\MonthlyAccount;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Room;
use app\modules\housing\models\Unit;
use app\modules\housing\services\HousingUploadService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;

final class UnitController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-asset' => ['POST'],
                    'delete-photo' => ['POST'],
                    'set-primary-photo' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $provider = new ActiveDataProvider([
            'query' => Unit::find()->with(['building', 'floor', 'rooms'])
                ->orderBy(['building_id' => SORT_ASC, 'floor_id' => SORT_ASC, 'sort_order' => SORT_ASC, 'code' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);
        $unitIds = array_map(static fn(Unit $unit): int => (int) $unit->id, $provider->getModels());
        $occupants = [];
        if ($unitIds !== []) {
            $occupancies = Occupancy::find()
                ->with('employee')
                ->where(['unit_id' => $unitIds, 'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE]])
                ->orderBy(['start_date' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
            foreach ($occupancies as $occupancy) {
                $occupants[(int) $occupancy->unit_id][(int) ($occupancy->room_id ?? 0)][] = $occupancy;
            }
        }
        return $this->render('index', ['dataProvider' => $provider, 'occupants' => $occupants]);
    }

    public function actionView(int $id, ?int $room_id = null, ?int $return_building_id = null)
    {
        [$unit, $room] = $this->findLocation($id, $room_id);
        if ($return_building_id !== null && (int) $unit->building_id !== $return_building_id) {
            $return_building_id = null;
        }
        $assetQuery = AssetAssignment::find()
            ->where(['unit_id' => $unit->id, 'is_active' => 1])
            ->andWhere($room ? ['room_id' => $room->id] : ['room_id' => null])
            ->orderBy(['item_name' => SORT_ASC]);
        $assets = $assetQuery->all();
        $photos = LocationPhoto::find()
            ->with('upload')
            ->where(['unit_id' => $unit->id])
            ->andWhere($room ? ['room_id' => $room->id] : ['room_id' => null])
            ->orderBy(['is_primary' => SORT_DESC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
        $assetImages = [];
        $assetRefs = array_values(array_filter(array_map(
            static fn(AssetAssignment $asset) => $asset->ref,
            $assets
        )));
        if ($assetRefs !== []) {
            $assetImages = Uploads::find()
                ->where(['ref' => $assetRefs, 'name' => HousingUploadService::SLOT_ASSET_PHOTO])
                ->orderBy(['id' => SORT_ASC])
                ->indexBy('ref')
                ->all();
        }
        $occupancyQuery = Occupancy::find()
            ->with(['employee', 'residents'])
            ->where([
                'unit_id' => $unit->id,
                'status' => [Occupancy::STATUS_ALLOCATED, Occupancy::STATUS_ACTIVE],
            ])
            ->orderBy(['start_date' => SORT_ASC, 'id' => SORT_ASC]);
        if ($room !== null) {
            $occupancyQuery->andWhere(['room_id' => $room->id]);
        }

        return $this->render('view', [
            'unit' => $unit,
            'room' => $room,
            'assets' => $assets,
            'photos' => $photos,
            'assetImages' => $assetImages,
            'occupancies' => $occupancyQuery->all(),
            'returnBuildingId' => $return_building_id,
            'expenseHistory' => MonthlyAccount::find()->joinWith('period')->where([
                'housing_monthly_account.unit_id' => $unit->id,
                'housing_billing_period.status' => 'closed',
            ])->andFilterWhere($room ? ['housing_monthly_account.room_id' => $room->id] : [])
                ->orderBy(['housing_billing_period.start_date' => SORT_DESC])->limit(24)->all(),
        ]);
    }

    public function actionCreate()
    {
        return $this->save(new Unit(), 'เพิ่มยูนิต');
    }

    public function actionUpdate(int $id)
    {
        return $this->save($this->findModel($id), 'แก้ไขยูนิต');
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);
        if ($model->getRooms()->exists()) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบยูนิตที่มีห้องอยู่ได้');
        } else {
            $uploadService = new HousingUploadService();
            $assetRefs = AssetAssignment::find()
                ->select('ref')
                ->where(['unit_id' => $model->id])
                ->column();
            $locationUploadIds = array_map(
                'intval',
                LocationPhoto::find()
                    ->select('upload_id')
                    ->where(['unit_id' => $model->id])
                    ->column()
            );
            $uploadIds = array_merge(
                $locationUploadIds,
                $uploadService->findIdsByRefsAndSlots(
                    [(string) $model->ref],
                    [HousingUploadService::SLOT_LOCATION_PHOTO]
                ),
                $uploadService->findIdsByRefsAndSlots(
                    $assetRefs,
                    [HousingUploadService::SLOT_ASSET_PHOTO]
                )
            );
            try {
                if ($model->delete() === false) {
                    throw new \RuntimeException('ไม่สามารถลบยูนิตได้');
                }
                $failedIds = $uploadService->deleteUploads($uploadIds);
                if ($failedIds !== []) {
                    Yii::warning('ลบไฟล์ของยูนิตไม่สำเร็จ upload IDs: ' . implode(',', $failedIds), __METHOD__);
                    Yii::$app->session->setFlash('warning', 'ลบยูนิตแล้ว แต่มีไฟล์รูปภาพบางรายการรอการตรวจสอบ');
                } else {
                    Yii::$app->session->setFlash('success', 'ลบยูนิตเรียบร้อย');
                }
            } catch (\Throwable $exception) {
                Yii::error($exception, __METHOD__);
                Yii::$app->session->setFlash('error', 'ไม่สามารถลบยูนิตได้ กรุณาตรวจสอบข้อมูลที่เกี่ยวข้องแล้วลองใหม่');
            }
        }
        return $this->redirect(['index']);
    }

    public function actionCreateRoom(int $unit_id)
    {
        $unit = $this->findModel($unit_id);
        return $this->saveRoom(new Room(['unit_id' => $unit->id]), $unit);
    }

    public function actionUpdateRoom(int $id)
    {
        $room = $this->findRoomModel($id);
        return $this->saveRoom($room, $this->findModel((int) $room->unit_id));
    }

    private function saveRoom(Room $model, Unit $unit)
    {
        $isNew = $model->isNewRecord;
        if ($model->load(Yii::$app->request->post())) {
            $model->unit_id = $unit->id;
            if ($model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'message' => $isNew ? 'เพิ่มห้องเรียบร้อย' : 'บันทึกห้องเรียบร้อย', 'container' => '#housing-unit-container'];
                }
                return $this->redirect(['index']);
            }
        }
        $params = ['model' => $model, 'unit' => $unit];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => ($model->isNewRecord ? 'เพิ่มห้องใน ' : 'แก้ไขห้องใน ') . $unit->code, 'content' => $this->renderAjax('_room_form', $params)];
        }
        return $this->render('_room_form', $params);
    }

    public function actionCreateAsset(int $unit_id, ?int $room_id = null)
    {
        [$unit, $room] = $this->findLocation($unit_id, $room_id);
        return $this->saveAsset(new AssetAssignment([
            'unit_id' => $unit->id,
            'room_id' => $room?->id,
            'assigned_at' => date('Y-m-d'),
        ]), $unit, $room);
    }

    public function actionUpdateAsset(int $id)
    {
        $model = $this->findAssetModel($id);
        [$unit, $room] = $this->findLocation((int) $model->unit_id, $model->room_id ? (int) $model->room_id : null);
        return $this->saveAsset($model, $unit, $room);
    }

    public function actionDeleteAsset(int $id)
    {
        $model = $this->findAssetModel($id);
        $redirect = $this->locationUrl((int) $model->unit_id, $model->room_id ? (int) $model->room_id : null);
        $uploadService = new HousingUploadService();
        $uploadIds = $uploadService->findIdsByRefsAndSlots(
            [(string) $model->ref],
            [HousingUploadService::SLOT_ASSET_PHOTO]
        );
        if ($model->delete() === false) {
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบรายการอุปกรณ์ได้ กรุณาลองใหม่อีกครั้ง');
            return $this->redirect($redirect);
        }
        $failedIds = $uploadService->deleteUploads($uploadIds);
        if ($failedIds !== []) {
            Yii::warning('ลบรูปอุปกรณ์ไม่สำเร็จ upload IDs: ' . implode(',', $failedIds), __METHOD__);
            Yii::$app->session->setFlash('warning', 'ลบรายการอุปกรณ์แล้ว แต่มีไฟล์รูปภาพรอการตรวจสอบ');
        } else {
            Yii::$app->session->setFlash('success', 'ลบรายการอุปกรณ์แล้ว');
        }
        return $this->redirect($redirect);
    }

    public function actionUploadPhoto(int $unit_id, ?int $room_id = null)
    {
        [$unit, $room] = $this->findLocation($unit_id, $room_id);
        $model = new LocationPhoto(['unit_id' => $unit->id, 'room_id' => $room?->id]);
        if ($model->load(Yii::$app->request->post())) {
            $model->photo_file = UploadedFile::getInstance($model, 'photo_file');
        }
        if (Yii::$app->request->isPost && $model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            $uploadService = new HousingUploadService();
            $upload = null;
            try {
                $this->lockLocation($unit->id, $room?->id);
                $upload = FileManagerHelper::saveUploadedFile(
                    $model->photo_file,
                    (string) ($room?->ref ?? $unit->ref),
                    HousingUploadService::SLOT_LOCATION_PHOTO,
                    false
                );
                if ($upload === null) {
                    $model->addError('photo_file', 'ไม่สามารถจัดเก็บรูปภาพได้ กรุณาลองใหม่อีกครั้ง');
                    throw new \RuntimeException('จัดเก็บรูปภาพสถานที่ไม่สำเร็จ');
                }
                $model->upload_id = $upload->id;
                if (!$this->locationHasPrimaryPhoto($unit, $room)) {
                    $model->is_primary = 1;
                }
                if ($model->is_primary) {
                    LocationPhoto::updateAll(
                        ['is_primary' => 0],
                        ['unit_id' => $unit->id, 'room_id' => $room?->id]
                    );
                }
                if (!$model->save(false)) {
                    throw new \RuntimeException('บันทึกข้อมูลรูปภาพสถานที่ไม่สำเร็จ');
                }
                $transaction->commit();
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'เพิ่มรูปภาพแล้ว',
                        'redirect' => Url::to($this->locationUrl($unit->id, $room?->id)),
                    ];
                }
                Yii::$app->session->setFlash('success', 'เพิ่มรูปภาพแล้ว');
                return $this->redirect($this->locationUrl($unit->id, $room?->id));
            } catch (\Throwable $exception) {
                if ($upload !== null) {
                    $failedIds = $uploadService->deleteUploads([(int) $upload->id]);
                    if ($failedIds !== []) {
                        Yii::error('ย้อนกลับรูปสถานที่ไม่สำเร็จ upload IDs: ' . implode(',', $failedIds), __METHOD__);
                    }
                }
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                $model->setOldAttributes(null);
                $model->id = null;
                if (!$model->hasErrors('photo_file')) {
                    $model->addError('photo_file', 'ไม่สามารถบันทึกรูปภาพได้ กรุณาลองใหม่อีกครั้ง');
                }
                Yii::error($exception, __METHOD__);
            }
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => $model->hasErrors()
                ? $this->activeFormErrors($model)
                : ActiveForm::validate($model)];
        }
        $params = ['model' => $model, 'unit' => $unit, 'room' => $room];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => 'เพิ่มรูปภาพ', 'content' => $this->renderAjax('_photo_form', $params)];
        }
        return $this->render('_photo_form', $params);
    }

    public function actionSetPrimaryPhoto(int $id)
    {
        $photo = $this->findPhotoModel($id);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->lockLocation((int) $photo->unit_id, $photo->room_id ? (int) $photo->room_id : null);
            if (!$photo->refresh()) {
                throw new NotFoundHttpException('ไม่พบรูปภาพ');
            }
            LocationPhoto::updateAll(
                ['is_primary' => 0],
                ['unit_id' => $photo->unit_id, 'room_id' => $photo->room_id]
            );
            if (LocationPhoto::updateAll(['is_primary' => 1], ['id' => $photo->id]) < 1) {
                throw new \RuntimeException('ไม่สามารถกำหนดภาพหลักได้');
            }
            $photo->is_primary = 1;
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'กำหนดภาพหลักแล้ว');
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception, __METHOD__);
            Yii::$app->session->setFlash('error', 'ไม่สามารถกำหนดภาพหลักได้ กรุณาลองใหม่อีกครั้ง');
        }
        return $this->redirect($this->locationUrl((int) $photo->unit_id, $photo->room_id ? (int) $photo->room_id : null));
    }

    public function actionDeletePhoto(int $id)
    {
        $photo = $this->findPhotoModel($id);
        $redirect = $this->locationUrl((int) $photo->unit_id, $photo->room_id ? (int) $photo->room_id : null);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->lockLocation((int) $photo->unit_id, $photo->room_id ? (int) $photo->room_id : null);
            if (!$photo->refresh()) {
                throw new NotFoundHttpException('ไม่พบรูปภาพ');
            }
            $wasPrimary = (bool) $photo->is_primary;
            $uploadId = (int) $photo->upload_id;
            $unitId = (int) $photo->unit_id;
            $roomId = $photo->room_id ? (int) $photo->room_id : null;
            if ($photo->delete() === false) {
                throw new \RuntimeException('ไม่สามารถลบข้อมูลรูปภาพได้');
            }
            if ($wasPrimary) {
                $replacement = LocationPhoto::find()
                    ->where(['unit_id' => $unitId, 'room_id' => $roomId])
                    ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                    ->one();
                $replacement?->updateAttributes(['is_primary' => 1]);
            }
            $transaction->commit();
        } catch (\Throwable $exception) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            Yii::error($exception, __METHOD__);
            Yii::$app->session->setFlash('error', 'ไม่สามารถลบรูปภาพได้ กรุณาลองใหม่อีกครั้ง');
            return $this->redirect($redirect);
        }

        $failedIds = (new HousingUploadService())->deleteUploads([$uploadId]);
        if ($failedIds !== []) {
            Yii::warning('ลบไฟล์รูปสถานที่ไม่สำเร็จ upload IDs: ' . implode(',', $failedIds), __METHOD__);
            Yii::$app->session->setFlash('warning', 'ลบข้อมูลรูปภาพแล้ว แต่ไฟล์ยังรอการตรวจสอบ');
        } else {
            Yii::$app->session->setFlash('success', 'ลบรูปภาพแล้ว');
        }
        return $this->redirect($redirect);
    }

    private function save(Unit $model, string $title)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'บันทึกยูนิตเรียบร้อย', 'container' => '#housing-unit-container'];
            }
            return $this->redirect(['index']);
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => $model->hasErrors()
                ? $this->activeFormErrors($model)
                : ActiveForm::validate($model)];
        }
        $floors = Floor::find()->with('building')->orderBy(['building_id' => SORT_ASC, 'floor_no' => SORT_ASC])->all();
        $params = [
            'model' => $model,
            'buildingOptions' => ArrayHelper::map(Building::find()->orderBy('name')->all(), 'id', 'name'),
            'floorOptions' => ArrayHelper::map($floors, 'id', fn(Floor $f) => ($f->building->name ?? '') . ' · ' . $f->name),
            'floorBuildingMap' => ArrayHelper::map($floors, 'id', 'building_id'),
        ];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => Yii::$app->request->get('title', $title), 'content' => $this->renderAjax('_form', $params)];
        }
        return $this->render('form-page', $params + ['title' => $title]);
    }

    private function saveAsset(AssetAssignment $model, Unit $unit, ?Room $room)
    {
        if ($model->load(Yii::$app->request->post())) {
            $model->unit_id = $unit->id;
            $model->room_id = $room?->id;
            $model->image_file = UploadedFile::getInstance($model, 'image_file');
        }
        if (Yii::$app->request->isPost && $model->validate()) {
            $wasNewRecord = $model->isNewRecord;
            $transaction = Yii::$app->db->beginTransaction();
            $uploadService = new HousingUploadService();
            $newUpload = null;
            $oldUploadIds = [];
            try {
                if (!$model->save(false)) {
                    throw new \RuntimeException('ไม่สามารถบันทึกรายการอุปกรณ์ได้');
                }
                if ($model->image_file !== null) {
                    $oldUploadIds = $uploadService->findIdsByRefsAndSlots(
                        [(string) $model->ref],
                        [HousingUploadService::SLOT_ASSET_PHOTO]
                    );
                    $newUpload = FileManagerHelper::saveUploadedFile(
                        $model->image_file,
                        (string) $model->ref,
                        HousingUploadService::SLOT_ASSET_PHOTO,
                        false
                    );
                    if ($newUpload === null) {
                        $model->addError('image_file', 'ไม่สามารถจัดเก็บรูปอุปกรณ์ได้ กรุณาลองใหม่อีกครั้ง');
                        throw new \RuntimeException('จัดเก็บรูปอุปกรณ์ไม่สำเร็จ');
                    }
                }
                $transaction->commit();
            } catch (\Throwable $exception) {
                if ($newUpload !== null) {
                    $failedIds = $uploadService->deleteUploads([(int) $newUpload->id]);
                    if ($failedIds !== []) {
                        Yii::error('ย้อนกลับรูปอุปกรณ์ไม่สำเร็จ upload IDs: ' . implode(',', $failedIds), __METHOD__);
                    }
                }
                if ($transaction->isActive) {
                    $transaction->rollBack();
                }
                if ($wasNewRecord) {
                    $model->setOldAttributes(null);
                    $model->id = null;
                }
                if (!$model->hasErrors('image_file')) {
                    $model->addError('image_file', 'ไม่สามารถบันทึกรายการและรูปภาพได้ กรุณาลองใหม่อีกครั้ง');
                }
                Yii::error($exception, __METHOD__);
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['errors' => $this->activeFormErrors($model)];
                }
            }

            if (!$model->hasErrors()) {
                $failedCleanupIds = $newUpload === null
                    ? []
                    : $uploadService->deleteUploads($oldUploadIds);
                $hasCleanupWarning = $failedCleanupIds !== [];
                if ($hasCleanupWarning) {
                    Yii::warning('ลบรูปอุปกรณ์เดิมไม่สำเร็จ upload IDs: ' . implode(',', $failedCleanupIds), __METHOD__);
                }
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'level' => $hasCleanupWarning ? 'warning' : 'success',
                        'message' => $hasCleanupWarning
                            ? 'บันทึกรายการแล้ว แต่มีรูปภาพเดิมรอการตรวจสอบ'
                            : 'บันทึกรายการอุปกรณ์แล้ว',
                        'redirect' => $this->locationUrl($unit->id, $room?->id),
                    ];
                }
                if ($hasCleanupWarning) {
                    Yii::$app->session->setFlash('warning', 'บันทึกรายการแล้ว แต่มีรูปภาพเดิมรอการตรวจสอบ');
                } else {
                    Yii::$app->session->setFlash('success', 'บันทึกรายการอุปกรณ์แล้ว');
                }
                return $this->redirect($this->locationUrl($unit->id, $room?->id));
            }
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => $model->hasErrors()
                ? $this->activeFormErrors($model)
                : ActiveForm::validate($model)];
        }
        $params = ['model' => $model, 'unit' => $unit, 'room' => $room];
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $model->isNewRecord ? 'เพิ่มอุปกรณ์หรือของใช้' : 'แก้ไขอุปกรณ์หรือของใช้',
                'content' => $this->renderAjax('_asset_form', $params),
            ];
        }
        return $this->render('_asset_form', $params);
    }

    private function findModel(int $id): Unit
    {
        if (($model = Unit::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบยูนิต');
        }
        return $model;
    }

    private function findLocation(int $unitId, ?int $roomId): array
    {
        $unit = $this->findModel($unitId);
        $room = null;
        if ($roomId !== null) {
            $room = Room::findOne(['id' => $roomId, 'unit_id' => $unit->id]);
            if ($room === null) {
                throw new NotFoundHttpException('ไม่พบห้องพักในยูนิตนี้');
            }
        }
        return [$unit, $room];
    }

    private function findRoomModel(int $id): Room
    {
        if (($model = Room::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบห้องพัก');
        }
        return $model;
    }

    private function findAssetModel(int $id): AssetAssignment
    {
        if (($model = AssetAssignment::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบรายการอุปกรณ์');
        }
        return $model;
    }

    private function findPhotoModel(int $id): LocationPhoto
    {
        if (($model = LocationPhoto::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบรูปภาพ');
        }
        return $model;
    }

    private function locationHasPrimaryPhoto(Unit $unit, ?Room $room): bool
    {
        return LocationPhoto::find()
            ->where(['unit_id' => $unit->id, 'room_id' => $room?->id, 'is_primary' => 1])
            ->exists();
    }

    private function lockLocation(int $unitId, ?int $roomId): void
    {
        $db = Yii::$app->db;
        $tableName = $roomId === null ? Unit::tableName() : Room::tableName();
        $locationId = $roomId ?? $unitId;
        $quotedTable = $db->quoteTableName($tableName);
        $quotedId = $db->quoteColumnName('id');
        $lockedId = $db->createCommand(
            "SELECT {$quotedId} FROM {$quotedTable} WHERE {$quotedId} = :id FOR UPDATE",
            [':id' => $locationId]
        )->queryScalar();
        if ($lockedId === false) {
            throw new NotFoundHttpException('ไม่พบสถานที่สำหรับจัดการรูปภาพ');
        }
    }

    private function locationUrl(int $unitId, ?int $roomId): array
    {
        $url = ['view', 'id' => $unitId];
        if ($roomId !== null) {
            $url['room_id'] = $roomId;
        }
        return $url;
    }
}
