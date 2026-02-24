<?php

namespace app\modules\attendance\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\attendance\models\CheckinLocation;

class LocationController extends Controller
{
    public function actionIndex()
    {
        $query = CheckinLocation::find()->orderBy(['name' => SORT_ASC]);
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $model = new CheckinLocation();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (empty($model->qr_token)) {
                $model->qr_token = 'checkin_' . $model->id . '_' . substr(md5(uniqid((string)$model->id, true)), 0, 12);
                $model->save(false);
            }
            Yii::$app->session->setFlash('success', 'เพิ่มจุดลงเวลาสำเร็จ');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('_form', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'แก้ไขสำเร็จ');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('_form', ['model' => $model]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'ลบจุดลงเวลาสำเร็จ');
        }
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $model = CheckinLocation::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบจุดลงเวลา');
        }
        return $model;
    }
}
