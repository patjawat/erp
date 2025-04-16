<?php

namespace app\modules\hr\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\hr\models\TeamGroupDetail;
use app\modules\hr\models\TeamGroupDetailSearch;

/**
 * TeamGroupDetailController implements the CRUD actions for TeamGroupDetail model.
 */
class TeamGroupDetailController extends Controller
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
     * Lists all TeamGroupDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TeamGroupDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
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

    public function actionListAppointment()
    {
        $searchModel = new TeamGroupDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_appointment', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list_appointment', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
      
    }

    public function actionListCommittee()
    {
        $searchModel = new TeamGroupDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('list_committee', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list_committee', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Displays a single TeamGroupDetail model.
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
     * Creates a new TeamGroupDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TeamGroupDetail([
            'name' => $this->request->get('name'),
            'category_id' => $this->request->get('category_id'),
            'document_id' => $this->request->get('document_id'),
            'thai_year' => $this->request->get('thai_year'),
        ]);        

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if ($this->request->isAjax) {
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


    public function actionCreateCommittee()
    {
        $model = new TeamGroupDetail([
            'name' => 'committee',
            'category_id' => $this->request->get('category_id'),
            'category_id' => $this->request->get('category_id'),
            'document_id' => $this->request->get('document_id'),
            'thai_year' => $this->request->get('thai_year'),
        ]);        

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_committee', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_committee', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdateCommittee($id)
    {
        $model = TeamGroupDetail::findOne($id);        

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save(false)) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('_form_committee', [
                    'model' => $model,
                ])
            ];
        } else {
            return $this->render('_form_committee', [
                'model' => $model,
            ]);
        }
    }
    
    

    /**
     * Updates an existing TeamGroupDetail model.
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
                return [
                    'status' => 'success',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model
                ])
            ];
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TeamGroupDetail model.
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
     * Finds the TeamGroupDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return TeamGroupDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TeamGroupDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
