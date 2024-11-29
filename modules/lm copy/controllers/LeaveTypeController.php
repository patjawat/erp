<?php

namespace app\modules\lm\controllers;

use Yii;
use app\modules\lm\models\LeaveType;
use app\modules\lm\models\LeaveTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * LeaveTypeController implements the CRUD actions for LeaveType model.
 */
class LeaveTypeController extends Controller
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
     * Lists all LeaveType models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new LeaveTypeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single LeaveType model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new LeaveType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new LeaveType();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return 
                [
                    'status' => 'success',
                    'container' => '#leave'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing LeaveType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {

                Yii::$app->response->format = Response::FORMAT_JSON;
                return 
                [
                    'status' => 'success',
                    'container' => '#leave'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionSetActive()
    {

        $id = $this->request->post('id');

        $model = $this->findModel($id);
        if ($this->request->isPost && $this->request->post('id') ) {
            $model->active = ($model->active == 1 ? 0 : 1);
            if ($model->save(false)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return 
                [
                    'status' => 'success',
                    'container' => '#leave'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($this->request->isAJax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Deletes an existing LeaveType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the LeaveType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return LeaveType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = LeaveType::findOne(['code' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
