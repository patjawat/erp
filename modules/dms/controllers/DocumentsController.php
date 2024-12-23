<?php

namespace app\modules\dms\controllers;

use Yii;
use app\models\Uploads;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\modules\dms\models\Documents;
use app\modules\dms\models\DocumentSearch;
use app\modules\filemanager\components\FileManagerHelper;

/**
 * DocumentsController implements the CRUD actions for Documents model.
 */
class DocumentsController extends Controller
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
     * Lists all Documents models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $type = $this->request->get('doc_type');
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andWhere(['doc_type_id' => $type]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Documents model.
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
     * Creates a new Documents model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Documents();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Documents model.
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

    public function actionShow($id)
    {
        $model = $this->findModel($id);
        if(!Yii::$app->user->isGuest){

            $id = Yii::$app->request->get('id');
            $fileUpload = Uploads::findOne(['ref' => $model->ref]);
            $filename = $fileUpload->real_filename;
            $filepath = FileManagerHelper::getUploadPath().$fileUpload->ref.'/'. $filename;
            if (!file_exists($filepath)) {
                throw new \yii\web\NotFoundHttpException('The requested file does not exist.');
            }
            
            $this->setHttpHeaders($fileUpload->type);
            \Yii::$app->response->data = file_get_contents($filepath);
            return \Yii::$app->response;

        }else{
            return false;
        }

    }
    
    protected function setHttpHeaders($type)
    {
        
        \Yii::$app->response->format = yii\web\Response::FORMAT_RAW;
        if($type == 'png'){
            \Yii::$app->response->headers->add('content-type','image/png');
        }
        
        if($type == 'pdf'){
            \Yii::$app->response->headers->add('content-type','application/pdf');

        }
        }

    /**
     * Deletes an existing Documents model.
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
     * Finds the Documents model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Documents the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Documents::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
