<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\models\DepreciationProfileRate;

/**
 * เกณฑ์ค่าเสื่อม + อัตราหลายช่วง (screens 1-2)
 */
class DepreciationProfileController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'rate-delete' => ['POST'],
                    'rate-create' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => DepreciationProfile::find()->orderBy(['status' => SORT_ASC, 'code' => SORT_ASC]),
            'pagination' => ['pageSize' => 30],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $model = new DepreciationProfile();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'สร้างเกณฑ์ค่าเสื่อมเรียบร้อย');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // ป้องกันแก้เกณฑ์ที่ถูกใช้แล้ว (กระทบ snapshot ทรัพย์สินเก่า) — แนะนำสร้างเกณฑ์ใหม่
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขเรียบร้อย (ไม่กระทบ snapshot ทรัพย์สินเดิม)');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        // ลบไม่ได้ถ้ามีอัตราหรือถูกใช้งาน (FK restrict บน asset_depreciations/profile_rates)
        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบเกณฑ์เรียบร้อย');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้: เกณฑ์นี้ถูกใช้งานหรือมีอัตราค่าเสื่อมผูกอยู่');
        }
        return $this->redirect(['index']);
    }

    /**
     * เพิ่มช่วงอัตรา (validate ไม่ซ้อนกันใน model)
     */
    public function actionRateCreate($id)
    {
        $profile = $this->findModel($id);
        $rate = new DepreciationProfileRate();
        $rate->depreciation_profile_id = $profile->id;
        if ($rate->load(Yii::$app->request->post()) && $rate->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มช่วงอัตราเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'เพิ่มช่วงอัตราไม่สำเร็จ: ' . implode(' ', $rate->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $profile->id]);
    }

    public function actionRateDelete($id)
    {
        $rate = DepreciationProfileRate::findOne($id);
        if ($rate) {
            $pid = $rate->depreciation_profile_id;
            $rate->delete();
            Yii::$app->session->setFlash('success', 'ลบช่วงอัตราเรียบร้อย');
            return $this->redirect(['view', 'id' => $pid]);
        }
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = DepreciationProfile::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบเกณฑ์ค่าเสื่อม');
    }
}
