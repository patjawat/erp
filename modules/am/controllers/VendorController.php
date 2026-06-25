<?php

namespace app\modules\am\controllers;

use Yii;
use app\modules\am\models\ListVendor;
use app\modules\am\models\ListVendorSearch;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CRUD for ListVendor (ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค) — backed by categorise.name='vendor'.
 */
class VendorController extends Controller
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
        $model = $id ? ListVendor::findOne(['id' => $id]) ?? new ListVendor() : new ListVendor();

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->validate();
            $result = [];
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[Html::getInputId($model, $attribute)] = $errors;
            }
            return $result;
        }

        return [];
    }

    /**
     * Returns the active vendor list as JSON [{id, text}, ...] for live Select2 refresh.
     * Used by the inline "+ เพิ่มผู้ขายใหม่" flow on asset forms — refetches the canonical
     * list from server after save so the dropdown stays in sync and remains searchable
     * without a full page reload.
     */
    public function actionOptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $rows = ListVendor::find()
            ->andWhere(['active' => 1])
            ->orderBy(['title' => SORT_ASC])
            ->all();
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'id'   => (string) $row->code,
                'text' => (string) $row->title,
            ];
        }
        return $items;
    }

    public function actionIndex()
    {
        $searchModel = new ListVendorSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->orderBy([
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
                'title' => $this->request->get('title', 'รายละเอียดผู้ขาย/ผู้จำหน่าย'),
                'content' => $this->renderAjax('view', ['model' => $model]),
                'footer' => '',
            ];
        }

        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $model = new ListVendor();
        $quick = (int) $this->request->get('quick', 0) === 1;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                if ($this->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'success',
                        'container' => '#pjax-vendor',
                        'vendor' => [
                            'id' => (string) $model->code,
                            'text' => (string) $model->title,
                        ],
                    ];
                }
                return $this->redirect(['index']);
            }
        }

        $view = $quick ? '_form_quick' : '_form';

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'เพิ่มผู้ขาย/ผู้จำหน่าย'),
                'content' => $this->renderAjax($view, ['model' => $model]),
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
                return [
                    'status' => 'success',
                    'container' => '#pjax-vendor',
                    'vendor' => [
                        'id' => (string) $model->code,
                        'text' => (string) $model->title,
                    ],
                ];
            }
            return $this->redirect(['index']);
        }

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title', 'แก้ไขผู้ขาย/ผู้จำหน่าย'),
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
            return ['status' => 'success', 'container' => '#pjax-vendor'];
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = ListVendor::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบข้อมูลที่ระบุ');
    }
}
