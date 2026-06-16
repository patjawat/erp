<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\am\models\ListPurchase;
use app\modules\am\models\ListPurchaseSearch;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CRUD for ListPurchase (วิธีการได้มา) — backed by categorise.name='purchase'.
 */
class PurchaseController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    public function actionValidator($id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $id ? ListPurchase::findOne(['id' => $id]) ?? new ListPurchase() : new ListPurchase();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->validate();
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            return $result;
        }

        return [];
    }

    public function actionIndex()
    {
        $searchModel = new ListPurchaseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->orderBy([
            new \yii\db\Expression('CAST(sort AS UNSIGNED) ASC'),
            'title' => SORT_ASC,
            'id' => SORT_ASC,
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'รายละเอียดวิธีการได้มา'),
                'content' => $this->renderAjax('view', ['model' => $model]),
                'footer' => '',
            ];
        }

        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $model = new ListPurchase();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ['status' => 'success', 'container' => '#pjax-purchase'];
                }
                return $this->redirect(['index']);
            }
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'เพิ่มวิธีการได้มา'),
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => '',
            ];
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['status' => 'success', 'container' => '#pjax-purchase'];
            }
            return $this->redirect(['index']);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'แก้ไขวิธีการได้มา'),
                'content' => $this->renderAjax('_form', ['model' => $model]),
                'footer' => '',
            ];
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'success', 'container' => '#pjax-purchase'];
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = ListPurchase::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบข้อมูลที่ระบุ');
    }
}
