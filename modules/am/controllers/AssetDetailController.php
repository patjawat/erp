<?php

namespace app\modules\am\controllers;

use Yii;
use app\components\AppHelper;
use app\modules\am\models\AssetDetail;
use app\modules\am\models\AssetDetailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use app\modules\am\models\AssetItem;
use app\modules\am\models\Asset;
use app\components\CategoriseHelper;
use yii\helpers\ArrayHelper;
/**
 * AssetDetailController implements the CRUD actions for AssetDetail model.
 */
class AssetDetailController extends Controller
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


    
    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new AssetDetail();
        
        
        if ($this->request->isPost && $model->load($this->request->post()))
         { 
            // return $model;
        $requiredName = "ต้องระบุ"; 
            //ตรวจสอบการบำรุงรักษา MA
        if($model->name == "ma"){
            $model->data_json['status'] == "" ? $model->addError('data_json[status]', 'สถานะต้องไม่ว่าง') : null;
            $model->date_start == "" ? $model->addError('date_start',$requiredName) : null;
            if (\DateTime::createFromFormat('d/m/Y', $model->date_start)->format('Y') < 2500 ){
                $model->addError('date_start',"รูปแบบ พ.ศ.");
            }
            foreach ($model->ma as $index => $item){ 
                $model->ma[$index]["item"] == "" ? $model->addError('ma['.$index.'][item]',$requiredName) : null;
                $model->ma[$index]["ma_status"] == "" ? $model->addError('ma['.$index.'][ma_status]',$requiredName) : null;
            }
        }
    
             foreach ($model->getErrors() as $attribute => $errors) {
                 $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
                }
                if (!empty($result)) {
                    return $this->asJson($result);
                }
            }
        }

    /**
     * Lists all AssetDetail models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $id = $this->request->get('id');
        $code = $this->request->get('code');

        $searchModel = new AssetDetailSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->where(['name' => $name]);
        if($name == "tax_car"){
            $dataProvider->sort->defaultOrder = ['date_start' => SORT_DESC];
        }
        #Yii::$app->response->format = Response::FORMAT_JSON;
        #return AssetItem::find()->where(["code"=>Asset::find()->where(['id'=>421])->one()->asset_item])->one();
        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $title,
                'content' => $this->renderAjax($name.'/index', [
                    'id' => $id,
                    'code' => $code,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'model' => $id == '' ? '' : 
                        (Asset::find()->where(['id'=>$id])->one() ? AssetItem::find()->where(["code"=>Asset::find()->where(['id'=>$id])->one()->asset_item])->one() : ""
                        
                    ),
                    'model_asset' => $id == '' ? '' : Asset::find()->where(['id'=>$id])->one(),
                    'id_category' => $id == '' ? '' :  
                    (Asset::find()->where(['id'=>$id])->one() ?  AssetItem::find()->where(["code"=>Asset::find()->where(['id'=>$id])->one()->asset_item])->one()->id : ""),

                ]),
            ];
        } else {
            return $this->render('index', [
                'id' => $id,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,

            ]);
        }
    }

    /**
     * Displays a single AssetDetail model.
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
     * Creates a new AssetDetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    // public function actionCreate()
    // {
    //     $model = new AssetDetail();

    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post()) && $model->save()) {
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }

    //     return $this->render('create', [
    //         'model' => $model,
    //     ]);
    // }
    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $name = $this->request->get('name');
        $category_id = $this->request->get('category_id');
        $title = $this->request->get('title');
        $code = $this->request->get('code');
        $id = $this->request->get('id');

        $model = new AssetDetail([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'name' => $name,
            'code' => $code,
            // 'data_json' => ['title' => $title],
        ]);
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                $model->date_start = AppHelper::DateToDb($model->date_start);
                $model->date_end = AppHelper::DateToDb($model->date_end);
                // $model->data_json = ArrayHelper::merge($model_old_data_json,$model->data_json);
                if($model->save()){   
                    return [
                        'status' => 'success',
                        'container' => '#am-container',
                    ];
                }else{
                    return [
                        'status' => 'error',
                        'container' => '#am-container',
                    ];
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        if ($name == "tax_car"){
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax($name.'/create', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
            ];
        }
        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            ]),
        ];
        return $this->render('create', [
            'model' => $model,
            'id' => $id,
        ]);
    }

    /**
     * Updates an existing AssetDetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $name = $this->request->get('name');
        $model->date_start = AppHelper::DateFormDb($model->date_start);
        $model->date_end = AppHelper::DateFormDb($model->date_end);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->date_start = AppHelper::DateToDb($model->date_start);
            $model->date_end = AppHelper::DateToDb($model->date_end);
                if($model->save()){   
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                    'res' => $model
                ];
            }else{
                return [
                    'status' => 'error',
                    'container' => '#am-container',
                ];
            }
        }
        if ($name == "tax_car"){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax($name.'/update', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
            ];
        }
        if ($this->request->isAjax) {
            if ($this->request->get('name') == "ma"){
                $model->ma = $model->data_json["items"];
            }
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => '<i class="fa-regular fa-pen-to-square me-1"></i>'.$this->request->get('title'),
                'content' => $this->renderAjax('update', [
                    'model' => $model,
                    'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
                ]),
            ];
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing AssetDetail model.
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


/*     public function actionDeleteMaItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $id_row = $this->request->get('id_row');
        $model = AssetItem::findOne(['id' => $id]);
        $dataJson = $model->data_json;
        unset($dataJson["ma_items"][$id_row]);
        $model->data_json = $dataJson;
        if($model->save()){  
        return [
            'status' => 'success',
            'container' => '#am-container',
        ];
        }
    }

    public function actionUpdateMaItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $id_row = $this->request->get('id_row');
        $value = $this->request->get('value');
        $model = AssetItem::findOne(['id' => $id]);
        $dataJson = $model->data_json;
        $dataJson["ma_items"][$id_row] = $value;
        $model->data_json = $dataJson;
        if($model->save()){  
        return [
            'status' => 'success',
            'container' => '#am-container',
            'value' => $value
        ];
        }
    }


    public function actionPushMaItem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $id_row = $this->request->get('id_row');
        $value = $this->request->get('value');
        $model = AssetItem::findOne(['id' => $id]);
        $dataJson = $model->data_json;
        if ($dataJson == null){
            $dataJson = [
                "ma_items" => null
            ];
        }
        $maItems = $dataJson["ma_items"];
        if ($maItems == null){
            $dataJson["ma_items"] = [
                0 => $value
            ];
        }else{
            $maItems[] = $value; 
            $dataJson["ma_items"] = $maItems;
        }
        $model->data_json = $dataJson;
        if($model->save()){  
        return [
            'status' => 'success',
            'container' => '#am-container',
            'value' => $value,
            'index' => $maItems == null ? 0 : max(array_keys($maItems))
        ];
        }
    }


    public function actionTest()
    {
        $model = new AssetDetail();
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                    #$item["items"] = CategoriseHelper::CategoriseByCodeName($model->category_id,"asset_item")->data_json["ma_items"][$item["items"]];
                    #$model->data_json = $item;
                    #$model->data_json["items"] = CategoriseHelper::CategoriseByCodeName($model->category_id,"asset_item")->one()->data_json["ma_items"][$model->data_json["item"]];
                    $model->save();
            }
        }
        return [
            'status' => 'success',
            'container' => '#am-container',
            'data' => $model
        ];
    }



    public function actionUpdateHistoryMa($id)
    {
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->get('id');
        $id_category = $this->request->get('id_category');
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        if ($this->request->isPost && $model->load($this->request->post())) {
            if($model->save()){   
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'status' => 'success',
                    'container' => '#am-container',
                    'data' => $model
                ];
            }
        }
        #$model = AssetDetail::findOne(['id' => $id]);
        $model->ma = $model->data_json["items"];
        $model->date_start = date_format(date_create_from_format('Y-m-d', $model->date_start), 'd/m/Y');
        return [
            'title' => $title,
            'content' => $this->renderAjax($name.'/update', [
                'model_form' => $model,
                'id_category' => $id_category
            ]),
        ];
    } */

    public function actionTest()
    {
        $model = new AssetDetail();
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) ) {
                    #$item["items"] = CategoriseHelper::CategoriseByCodeName($model->category_id,"asset_item")->data_json["ma_items"][$item["items"]];
                    #$model->data_json = $item;
                    #$model->data_json["items"] = CategoriseHelper::CategoriseByCodeName($model->category_id,"asset_item")->one()->data_json["ma_items"][$model->data_json["item"]];
                return [
                    "res" => $model
            ];
            }
        }
        return [
            'status' => 'success',
            'container' => '#am-container',
            'data' => $model
        ];
    }


    public function actionViewHistoryMa()
    {
        
        $title = $this->request->get('title');
        $name = $this->request->get('name');
        $id_category = $this->request->get('id_category');
        $id = $this->request->get('id');
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = AssetDetail::findOne(['id' => $id]);
        return [
            'title' => $title,
            'content' => $this->renderAjax($name.'/view', [
                'model' => $model,
                'id_category' => $id_category
            ]),
        ];
    }
    /**
     * Finds the AssetDetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AssetDetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AssetDetail::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
