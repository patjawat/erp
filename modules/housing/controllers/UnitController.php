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
use app\modules\housing\models\Room;
use app\modules\housing\models\Unit;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
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
        return $this->render('index', ['dataProvider' => $provider]);
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
                ->where(['ref' => $assetRefs, 'name' => 'housing_asset_photo'])
                ->indexBy('ref')
                ->all();
        }

        return $this->render('view', [
            'unit' => $unit,
            'room' => $room,
            'assets' => $assets,
            'photos' => $photos,
            'assetImages' => $assetImages,
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
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบยูนิตเรียบร้อย');
        }
        return $this->redirect(['index']);
    }

    public function actionCreateRoom(int $unit_id)
    {
        $unit = $this->findModel($unit_id);
        $model = new Room(['unit_id' => $unit->id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'เพิ่มห้องเรียบร้อย', 'container' => '#housing-unit-container'];
            }
            return $this->redirect(['index']);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => 'เพิ่มห้องใน ' . $unit->code, 'content' => $this->renderAjax('_room_form', ['model' => $model, 'unit' => $unit])];
        }
        return $this->render('_room_form', ['model' => $model, 'unit' => $unit]);
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
        foreach (Uploads::find()->where(['ref' => $model->ref, 'name' => 'housing_asset_photo'])->all() as $upload) {
            FileManagerHelper::Deletefile($upload->id);
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการอุปกรณ์แล้ว');
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
            $upload = FileManagerHelper::saveUploadedFile(
                $model->photo_file,
                (string) ($room?->ref ?? $unit->ref),
                'housing_location_photo',
                false
            );
            if ($upload === null) {
                $model->addError('photo_file', 'ไม่สามารถจัดเก็บรูปภาพได้ กรุณาลองใหม่อีกครั้ง');
            } else {
                $model->upload_id = $upload->id;
                if (!$this->locationHasPrimaryPhoto($unit, $room)) {
                    $model->is_primary = 1;
                }
                $transaction = Yii::$app->db->beginTransaction();
                if ($model->is_primary) {
                    LocationPhoto::updateAll(
                        ['is_primary' => 0],
                        ['unit_id' => $unit->id, 'room_id' => $room?->id]
                    );
                }
                if ($model->save(false)) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'เพิ่มรูปภาพแล้ว');
                    return $this->redirect($this->locationUrl($unit->id, $room?->id));
                }
                $transaction->rollBack();
                FileManagerHelper::Deletefile($upload->id);
            }
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => ActiveForm::validate($model)];
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
            LocationPhoto::updateAll(
                ['is_primary' => 0],
                ['unit_id' => $photo->unit_id, 'room_id' => $photo->room_id]
            );
            $photo->updateAttributes(['is_primary' => 1]);
            $transaction->commit();
            Yii::$app->session->setFlash('success', 'กำหนดภาพหลักแล้ว');
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        return $this->redirect($this->locationUrl((int) $photo->unit_id, $photo->room_id ? (int) $photo->room_id : null));
    }

    public function actionDeletePhoto(int $id)
    {
        $photo = $this->findPhotoModel($id);
        $redirect = $this->locationUrl((int) $photo->unit_id, $photo->room_id ? (int) $photo->room_id : null);
        $wasPrimary = (bool) $photo->is_primary;
        $uploadId = (int) $photo->upload_id;
        $unitId = (int) $photo->unit_id;
        $roomId = $photo->room_id ? (int) $photo->room_id : null;
        if ($photo->delete()) {
            FileManagerHelper::Deletefile($uploadId);
            if ($wasPrimary) {
                $replacement = LocationPhoto::find()
                    ->where(['unit_id' => $unitId, 'room_id' => $roomId])
                    ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                    ->one();
                $replacement?->updateAttributes(['is_primary' => 1]);
            }
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
        $params = [
            'model' => $model,
            'buildingOptions' => ArrayHelper::map(Building::find()->orderBy('name')->all(), 'id', 'name'),
            'floorOptions' => ArrayHelper::map(Floor::find()->with('building')->orderBy(['building_id' => SORT_ASC, 'floor_no' => SORT_ASC])->all(), 'id', fn(Floor $f) => ($f->building->name ?? '') . ' · ' . $f->name),
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
        if (Yii::$app->request->isPost && $model->validate() && $model->save(false)) {
            if ($model->image_file !== null) {
                $upload = FileManagerHelper::saveUploadedFile(
                    $model->image_file,
                    (string) $model->ref,
                    'housing_asset_photo',
                    true
                );
                if ($upload === null) {
                    $model->addError('image_file', 'ไม่สามารถจัดเก็บรูปอุปกรณ์ได้ กรุณาลองใหม่อีกครั้ง');
                }
            }
            if (!$model->hasErrors()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'message' => 'บันทึกรายการอุปกรณ์แล้ว',
                        'redirect' => $this->locationUrl($unit->id, $room?->id),
                    ];
                }
                return $this->redirect($this->locationUrl($unit->id, $room?->id));
            }
        }
        if (Yii::$app->request->isPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['errors' => ActiveForm::validate($model)];
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

    private function locationUrl(int $unitId, ?int $roomId): array
    {
        $url = ['view', 'id' => $unitId];
        if ($roomId !== null) {
            $url['room_id'] = $roomId;
        }
        return $url;
    }
}
