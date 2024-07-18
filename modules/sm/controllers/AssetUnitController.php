<?php

namespace app\modules\sm\controllers;

use app\modules\sm\models\AssetUnit;
use app\modules\sm\models\AssetUnitSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
use yii\web\Response;
use Yii;
/**
 * AssetUnitController implements the CRUD actions for AssetUnit model.
 */
class AssetUnitController extends Controller
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
     * Creates a new Assetitem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Categorise::findOne(['name' => 'unit']);
        $searchModel = new AssetUnitSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'unit']);
        return [
            'title' => 'หน่วยนับ',
            'content' => $this->renderAjax('index',[
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ])
        ] ;
    }


        /**
     * Creates a new Assetitem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateUnit()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetUnit([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => 'unit',
        ]);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                if  (empty(Categorise::findOne(['name' => 'unit',"title"=>$model->title]))){
                    $model->save();
                    return [
                        'status' => 'success',
                        'container' => '#sm-container',
                    ];
                }else{
                    return [
                        'status' => 'fail',
                        'container' => '#sm-container',
                    ];
                }
                

            }
        } else {
            $model->loadDefaultValues();
        }
        return [
            'title' => 'สร้างหน่วยใหม่' ,
            'content' => $this->renderAjax('_form_unit', [
                'model' => $model,
            ]),
        ];
       
    }

    /**
     * Displays a single AssetUnit model.
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
     * Creates a new AssetUnit model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        
        $model = new AssetUnit();

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
     * Updates an existing AssetUnit model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return [
                'status' => 'success',
                'container' => '#sm-container',
            ];
        }


        return [
            'title' => 'แก้ไขหน่วยนับ' ,
            'content' => $this->renderAjax('update', [
                'model' => $model,
            ]),
        ];
    }

    /**
     * Deletes an existing AssetUnit model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return [
            'status' => 'success',
            'container' => '#sm-container',
        ]; 
    }

    /**
     * Finds the AssetUnit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AssetUnit the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetUnit::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
