<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\Building;
use app\modules\housing\models\Floor;
use app\modules\housing\models\Room;
use app\modules\housing\models\Unit;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class UnitController extends BaseController
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
            'query' => Unit::find()->with(['building', 'floor', 'rooms'])
                ->orderBy(['building_id' => SORT_ASC, 'floor_id' => SORT_ASC, 'sort_order' => SORT_ASC, 'code' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);
        return $this->render('index', ['dataProvider' => $provider]);
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

    private function findModel(int $id): Unit
    {
        if (($model = Unit::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบยูนิต');
        }
        return $model;
    }
}
