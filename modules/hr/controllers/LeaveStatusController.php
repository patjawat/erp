<?php

namespace app\modules\hr\controllers;

use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\CategoriseSearch;
use yii\web\NotFoundHttpException;

/**
 * LeaveTypeController implements the CRUD actions for Categorise model.
 */
class LeaveStatusController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Categorise models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CategoriseSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'leave_status']);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'สถานะการลา',
                'content' => $this->renderAjax('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    public function actionUpdateColor($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $oldJson = $model->data_json;
        if ($this->request->isPost) {
            $color = $this->request->post();
            $model->data_json = ArrayHelper::merge($oldJson, $color);
            $model->save();
            return [
                'status' => 'success',
                'data' => $model
            ];
        }
    }

    /**
     * Finds the Categorise model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Categorise the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Categorise::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
