<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\SiteHelper;
use yii\web\NotFoundHttpException;
use app\modules\dms\models\DocumentTags;
use app\modules\dms\models\DocumentTagsSearch;

/**
 * DocumentTagsController implements the CRUD actions for DocumentTags model.
 */
class DocumentTagsController extends Controller
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
     * Lists all DocumentTags models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocumentTagsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single DocumentTags model.
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
     * Creates a new DocumentTags model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new DocumentTags();
        $model->document_id = $this->request->get('document_id');
        $model->ref = $this->request->get('ref');
        $model->name = $this->request->get('name');

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if($model->save()){
                    return [
                        'title' => $this->request->get('title'),
                        'status' => 'success',
                        'container' => '#document-tag',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        if($this->request->isAJax){
            Yii::$app->response->format = Response::FORMAT_JSON;

                return [
                    'title' => $this->request->get('tilte'),
                    'content' => $this->renderAjax('create', [
                        'model' => $model,
                    ])
                 ];
            }else{
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
    }

    public function actionReqApprove()
    {
       
        if ($this->request->isPost) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $info = SiteHelper::getInfo();
            $emp_id = $info['director_name'];
            $document_id =$this->request->post('document_id');
            $name =$this->request->post('name');
            $check = DocumentTags::findOne(['document_id' =>$document_id,'name' => $name,'emp_id' => $emp_id]);
            if($check){
                return [
                    'status' => 'error',
                    'container' => '#document-tag',
                ];
            }
            
                $model = new DocumentTags();
                $model->document_id = $document_id;
                $model->ref = $this->request->post('ref');
                $model->name = $name;
                $model->emp_id = $info['director_name'];
                // return $model;
                if($model->save()){
                    return [
                        'title' => $this->request->get('title'),
                        'status' => 'success',
                        'container' => '#document-tag',
                    ];
                }
        } 
    }


    /**
     * Updates an existing DocumentTags model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing DocumentTags model.
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
     * Finds the DocumentTags model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DocumentTags the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = DocumentTags::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}