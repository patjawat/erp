<?php

declare(strict_types=1);

namespace app\modules\housing\controllers;

use app\modules\housing\models\Building;
use app\modules\housing\models\Floor;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
            'query' => Building::find()->with(['units', 'floors'])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);
        return $this->render('index', ['dataProvider' => $provider]);
    }

    public function actionCreate()
    {
        return $this->save(new Building(), 'เพิ่มบ้านพัก/แฟลต');
    }

    public function actionUpdate(int $id)
    {
        return $this->save($this->findModel($id), 'แก้ไขบ้านพัก/แฟลต');
    }

    public function actionCreateFloor(int $building_id)
    {
        $building = $this->findModel($building_id);
        $model = new Floor(['building_id' => $building->id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'เพิ่มชั้นเรียบร้อย', 'container' => '#housing-building-container'];
            }
            return $this->redirect(['index']);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'เพิ่มชั้นใน ' . $building->name,
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
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อย');
        }
        return $this->redirect(['index']);
    }

    private function save(Building $model, string $title)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อย', 'container' => '#housing-building-container'];
            }
            return $this->redirect(['index']);
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['title' => Yii::$app->request->get('title', $title), 'content' => $this->renderAjax('_form', ['model' => $model])];
        }
        return $this->render('form-page', ['model' => $model, 'title' => $title]);
    }

    private function findModel(int $id): Building
    {
        if (($model = Building::findOne($id)) === null) {
            throw new NotFoundHttpException('ไม่พบข้อมูลบ้านพักหรือแฟลต');
        }
        return $model;
    }
}
