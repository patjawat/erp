<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use app\components\SiteHelper;
use yii\web\NotFoundHttpException;
use app\modules\am\models\AssetCategory;
use app\modules\am\models\AssetCategorySearch;
use nickdenry\grid\toggle\actions\ToggleAction;

/**
 * FsnController implements the CRUD actions for Fsn model.
 */
class AssetCategoryController extends Controller
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


    public function beforeAction($action)
    {

        if ($this->request->get('view')) {
            SiteHelper::setDisplay($this->request->get('view'));
        }

        return parent::beforeAction($action);
    }



    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetCategory();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {

            $model->title == '' ? $model->addError('title', $requiredName) : null;
            $model->code == '' ? $model->addError('code', $requiredName) : null;
            $model->category_id == '' ? $model->addError('category_id', $requiredName) : null;
            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

    /**
     * Lists all Fsn models.
     *
     * @return string
     */
    public function actionIndex()
    {
       
        $searchModel = new AssetCategorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_category']);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
    }

        public function actionListFsn()
    {
        $searchModel = new FsnSearch();
         $dataProvider = $searchModel->search($this->request->queryParams);


 if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('list_fsn', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]),
            ];
        } else {
            return $this->render('list_fsn', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);
        }
       
    }

    /**
     * Displays a single Fsn model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
     public function actionView($id)
    {
       $model = $this->findModel($id);
       if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,

            ]);
        }
    }


    /**
     * Creates a new Fsn model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
     $model = new AssetCategory([
        'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        'name' => 'asset_category'
     ]);

        if ($this->request->isPost && $model->load($this->request->post()) ) 
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
        } else {
            $model->loadDefaultValues();
          
        }

     if($this->request->isAjax)
     {
        Yii::$app->response->format = Response::FORMAT_JSON;

         return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create', [
                'model' => $model,
            ]),
        ];
     }else{

         return $this->render('create', [
             'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Fsn model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        // $model->data_json =  ['asset_type' => '2','asset_type_text' => 'ครุภัณฑ์'];

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            // return $model->save();
            return [
                'status' => 'success',
                'container' => '#am-container',
            ];
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('update', [
                'model' => $model,
            ]),
        ];
    }

    /**
     * Deletes an existing Fsn model.
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
     * Finds the Fsn model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Fsn the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetCategory::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


}
