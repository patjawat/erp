<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\LeavePolicies;
use app\modules\hr\models\LeavePoliciesSearch;

/**
 * นโยบายการลา — ย้ายจาก hr
 */
class LeavePoliciesController extends Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('leave')) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าหน้าตั้งค่า');
        }
        return true;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new LeavePoliciesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('@app/modules/hr/views/leave-policies/view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $model = new LeavePolicies();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/leave-policies/create', ['model' => $model]),
            ];
        }
        return $this->render('@app/modules/hr/views/leave-policies/create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/hr/views/leave-policies/update', ['model' => $model]),
            ];
        }
        return $this->render('@app/modules/hr/views/leave-policies/update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = LeavePolicies::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
