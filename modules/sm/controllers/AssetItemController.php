<?php

namespace app\modules\sm\controllers;

use app\modules\sm\models\AssetItem;
use app\modules\sm\models\AssetItemSearch;
use app\components\SiteHelper;
use nickdenry\grid\toggle\actions\ToggleAction;
use Yii;
use yii\helpers\Url;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use app\models\Categorise;


/**
 * AssetitemController implements the CRUD actions for Assetitem model.
 */
class AssetItemController extends Controller
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
    public function actionSetting()
    {
        $code = $this->request->get('code');
        $title = $this->request->get('title');
        $name = $this->request->get('name');

        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_group','active' => true]);
        // $dataProvider->query->andFilterWhere(['category_id' => $code]);


        return $this->render('setting', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => $title,
            'code' => $code,
            'name' => $name
        ]);
    }


    /**
     * Lists all Assetitem models.
     *
     * @return string
     */
    public function actionIndex()
    {

        $group = $this->request->get('group');
        $category_id = $this->request->get('category_id');
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $model = Categorise::findAll(['name' => 'asset_type',]);
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item','active' => true]);
        if($model){
            $dataProvider->query->andFilterWhere(['name' => 'asset_item', 'active' => true]);
            
        }
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        // $dataProvider->query->andFilterWhere(['category_id' => $code]);
        $dataProviderGroup->query->andFilterWhere(['name' => 'asset_item ','active' => true]);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderGroup' => $dataProviderGroup,
            'title' => $title,
            'name' => $name,
            'model' => $model
        ]);
    }


    /**
     * Lists all Assetitem models.
     *
     * @return string
     */
    public function actionNext()
    {
        $group = $this->request->get('group');
        $category_id = $this->request->get('category_id');
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $model = Categorise::findOne(['name' => 'asset_type','category_id' => $group]);
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item','active' => true]);
        if($model){
            $dataProvider->query->andFilterWhere(['name' => 'asset_item','category_id' => $model->code, 'active' => true]);
            
        }
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->andFilterWhere(['name' => 'asset_type','category_id' => $group, 'active' => true]);
        // $dataProvider->query->andFilterWhere(['category_id' => $code]);


        return $this->render('index_copy', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dataProviderGroup' => $dataProviderGroup,
            'title' => $title,
            'name' => $name,
            'model' => $model
        ]);
    }

    /**
     * Displays a single Assetitem model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewType($id)
    {

        $title = $this->request->get('title');
        $model = $this->findModel($id);
        $searchModel = new AssetItemSearch();

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view_type', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view_type', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view_type', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'title' => $title ,

        ]);
    }

    public function actionView($id)
    {

        $model = $this->findModel($id);
        $searchModel = new AssetItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => 'asset_item', 'active' => true, 'category_id' => $model->code]);
        $dataProviderGroup = $searchModel->search($this->request->queryParams);
        $dataProviderGroup->query->where(['name' => 'asset_type','category_id' => $model->category_id, 'active' => true]);


        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
                'content' => $this->renderAjax('view', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'dataProvider' => $dataProvider,
                    'dataProviderGroup' => $dataProviderGroup,
                ]),
            ];
        } else {
            return $this->render('view', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dataProvider' => $dataProvider,
                'dataProviderGroup' => $dataProviderGroup,

            ]);
        }
        // $small_model = Fsn::find()->where(['name' => 'asset_name','category_id'=>$model->code])->all();
        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'btn' => true,

        ]);
    }

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new Assetitem();
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {/* 
                $data = Categorise::findOne(["id" => $model->data_json["id"]]);
                $model->category_id = $data->category_id; */
                // $model->data_json = [
                //     "unit" => $model->data_json["unit"],
                //     "asset_type" => [
                //         'code' => $data->code,
                //         'name' => 'asset_type',
                //         'title' => $data->title,
                //         'category_id' => $data->category_id
                //     ],
                //     "asset_group" => null,
                // ];
                $model->save();
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => "สร้างครุภัณฑ์" ,
            'content' => $this->renderAjax('_form_item_', [
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]),
        ];
       
    }

    public function actionGetCategory()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Categorise::findOne(["id" => $this->request->get('id'),"name"=>"asset_type"]);
    }



    public function actionCreateItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = $this->request->get('id');
        $itemType = $this->findModel($id);
        $name = $this->request->get('name');
        $type_code = $this->request->get('type_code');
        $title = $this->request->get('title');
        // return $itemType->code;
        
    
        $model = new Assetitem([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => $name,
            'category_id' => $itemType->code,
            'data_json' => ['title' => $title],
            'code' => ($name == 'asset_item' && $itemType->code == 2) ? $itemType->code : ''
        ]);
        if ($this->request->isPost) {
            
            if ($model->load($this->request->post()) && $model->save()) {
                return [
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
        return [
            'title' => $title ,
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'itemType' => $itemType,
                'title' => $title ,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]),
        ];
       
    }

    /**
     * Updates an existing Assetitem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->save();
            return [
                'status' => 'success',
                'container' => '#sm-container',
            ];
        } else {
            $model->loadDefaultValues();
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('update', [
                'model' => $model,
                'ref' => $model->ref
            ]),
        ];
    }

    /**
     * Deletes an existing Assetitem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->delete())
        {
                 return $this->redirect(['/sm/asset-item','group' => $model->category_id]);
        }

    }

    /**
     * Finds the Assetitem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Assetitem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Assetitem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Assetitem();
        if ($this->request->isPost && $model->load($this->request->post())) {
     $fsnAuto = $this->request->post('AssetItem');
    //  return isset($fsnAuto['fsn_auto']);
            //  return  $model->ref;
            // ตรวจระหัสซ้ำ
                $checkCode = Assetitem::find()->where(['code' => $model->code])
                ->andWhere(['<>','ref',$model->ref])
                ->andWhere(['not', ['code' => null]])
                ->andWhere(['not', ['code' => ""]])
                ->one();
                // return $checkCode;
            if ($checkCode) {
                    $codeStatus = true;
            } else {
                $codeStatus = false;
            }
            // จบตรวจสอลรหัสซ้ำ
            $requiredName = "ต้องระบุ";
            //ตรวจสอบตำแหน่ง
            if ($model->name == "asset_group") {
                $model->data_json['depreciation'] == "" ? $model->addError('data_json[depreciation]', $requiredName) : null;
                $model->data_json['service_life'] == "" ? $model->addError('data_json[service_life]', $requiredName) : null; 
                    // $checkCode ? $model->addError('code', 'รหัสน้ำถูกใช้แล้ว') : null;
            }

            if ($model->name == "asset_item") {

                // ถ้าสร้างรหัสอัตโนมัติ
                if(!isset($fsnAuto['fsn_auto']) || $fsnAuto['fsn_auto'] == "1"){
                    $model->title == "" ? $model->addError('title', $requiredName) : null;
                }
                // ถ้ากำหนดรหัวเอง
                if(isset($fsnAuto['fsn_auto']) && $fsnAuto['fsn_auto'] == "0"){
                    $model->title == "" ? $model->addError('title', $requiredName) : null;
                    $model->code == "" ? $model->addError('code', $requiredName) : null;
                }
  
            }

            $codeStatus ? $model->addError('code', 'รหัสน้ำถูกใช้แล้ว') : null;

            foreach ($model->getErrors() as $attribute => $errors) {
                $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
            }
            if (!empty($result)) {
                return $this->asJson($result);
            }
        }
    }

}
