<?php

namespace app\modules\employees\controllers;

use Yii;
use app\models\Categorise;
use app\models\CategoriseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * FamilyController implements the CRUD actions for Categorise model.
 */
class CategoriseController extends Controller
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
        $dataProvider->query->where(['name' => 'family']);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Categorise model.
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
     * Creates a new Categorise model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = $this->request->get();
        $model = new Categorise([
            'emp_id' => $req['emp_id'],
            'name' => $req['name'],

        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if($model->save()){
                    return [
                        'status' => 'success',
                        'data' => $model
                     ];
                 }else{
                    return ActiveForm::validate($model);
                 }
            }
        } else {
            $model->loadDefaultValues();
        }
               return [
             'title' => '<h4>'.$req['title'].'</h4>',
             'content' => $this->renderAjax('form/'.$req['name'], [
                'model' => $model,
            ])
         ];

        
    }

    /**
     * Updates an existing Categorise model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        $req = $this->request->get();
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if($model->save()){
                    return [
                        'status' => 'success',
                        'data' => $model
                     ];
                 }else{
                    return ActiveForm::validate($model);
                 }
            }
        } else {
            $model->loadDefaultValues();
        }
                return [
                    'title' => '<i class="fa-regular fa-pen-to-square"></i>'.$req['title'].' / (<code>แก้ไข</code>)'.$req['title'],
                    'content' => $this->renderAjax('form/'.$req['name'], [
                        'model' => $model,
                    ])
                ];
            
    }

    /**
     * Deletes an existing Categorise model.
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
