<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\models\Categorise;
use app\models\CategoriseSearch;

/**
 * ประเภทการลา — ย้ายจาก hr
 */
class LeaveTypeController extends Controller
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
        $searchModel = new CategoriseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'leave_type']);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ประเภทการลา',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]),
            ];
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdateColor($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $oldJson = $model->data_json;
        if ($this->request->isPost) {
            $color = $this->request->post();
            $model->data_json = ArrayHelper::merge($oldJson ?? [], $color);
            $model->save(false);
            return ['status' => 'success', 'data' => $model];
        }
    }

    protected function findModel($id)
    {
        if (($model = Categorise::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
