<?php

namespace app\modules\inventoryV2\controllers;

use app\models\Categorise;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * จัดการประเภทวัสดุ (asset_type ใน categorise, category_id = 4)
 */
class StockItemTypeController extends Controller
{
    const ASSET_TYPE_NAME = 'asset_type';
    const ASSET_TYPE_CATEGORY_ID = 4;

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    /**
     * รายการประเภทวัสดุ
     */
    public function actionIndex()
    {
        $query = Categorise::find()
            ->where(['name' => self::ASSET_TYPE_NAME, 'category_id' => self::ASSET_TYPE_CATEGORY_ID])
            ->orderBy(['code' => SORT_ASC]);
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * เพิ่มประเภทวัสดุ
     */
    public function actionCreate()
    {
        $model = new Categorise();
        $model->name = self::ASSET_TYPE_NAME;
        $model->category_id = self::ASSET_TYPE_CATEGORY_ID;
        $model->active = 1;

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->code)) {
                $model->addError('code', 'กรุณาระบุรหัสประเภท');
            }
            if (empty($model->title)) {
                $model->addError('title', 'กรุณาระบุชื่อประเภทวัสดุ');
            }
            if (!$model->hasErrors() && $model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return ['success' => true, 'message' => 'เพิ่มประเภทวัสดุเรียบร้อยแล้ว'];
                }
                Yii::$app->session->setFlash('success', 'เพิ่มประเภทวัสดุเรียบร้อยแล้ว');
                return $this->redirect(['index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form', ['model' => $model]);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * แก้ไขประเภทวัสดุ
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->code)) {
                $model->addError('code', 'กรุณาระบุรหัสประเภท');
            }
            if (empty($model->title)) {
                $model->addError('title', 'กรุณาระบุชื่อประเภทวัสดุ');
            }
            if (!$model->hasErrors() && $model->save()) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return ['success' => true, 'message' => 'บันทึกเรียบร้อยแล้ว'];
                }
                Yii::$app->session->setFlash('success', 'บันทึกเรียบร้อยแล้ว');
                return $this->redirect(['index']);
            }
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form', ['model' => $model]);
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * ลบประเภทวัสดุ (soft: ตั้ง active = 0 ถ้ามีฟิลด์ ไม่ก็ลบจริง)
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (isset($model->active)) {
            $model->active = 0;
            $model->save(false);
        } else {
            $model->delete();
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['success' => true];
        }
        Yii::$app->session->setFlash('success', 'ลบประเภทวัสดุเรียบร้อยแล้ว');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $model = Categorise::findOne([
            'id' => $id,
            'name' => self::ASSET_TYPE_NAME,
            'category_id' => self::ASSET_TYPE_CATEGORY_ID,
        ]);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบประเภทวัสดุที่ต้องการ');
    }
}
