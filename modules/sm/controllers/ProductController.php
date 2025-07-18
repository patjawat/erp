<?php

namespace app\modules\sm\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use app\models\Categorise;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\modules\sm\models\Product;
use yii\web\NotFoundHttpException;
use app\modules\sm\models\ProductSearch;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends Controller
{
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
     * Lists all Product models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_item','group_id' => 4]);
        $dataProvider->query->andFilterWhere(['category_id' => $searchModel->category_id]);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'code', $searchModel->q],
            ['like', 'title', $searchModel->q],
        ]);
        $dataProvider->query->orderBy(['id' => SORT_DESC]);
        $dataProvider->pagination->pageSize = 10;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndex2()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'product_item']);
        $dataProvider->query->andFilterWhere([
            'in',
            'category_id',
            $searchModel->q_category,
        ]);

        return $this->render('index2', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Product model.
     *
     * @param int $id ID
     *
     * @return string
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => '<i class="fa-solid fa-eye"></i> แสดง',
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
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Product([
            'name' => 'asset_item',
            'group_id' => 4,
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                if($model->auto == "1"){
                    $model->code  = \mdm\autonumber\AutoNumber::generate($model->category_id.'-?');
                }

                $model->save(false);
                
                $this->UpdateUnit($model);
                return [
                    'title' => $this->request->get('title'),
                    'status' => 'success',
                    'container' => '#sm-container',
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('create', [
                    'model' => $model,
                ]),
                'status' => 'success',
                'container' => '#sm-container',
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

 protected function UpdateUnit($model)
 {
    $unit  = Categorise::findOne(['name' => 'unit','title' => $model->data_json['unit']]);
    if(!$unit){
        $newUnit = new Categorise(['name' => 'unit','title' => $model->data_json['unit']]);
        $newUnit->save(false);
    }
 }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|\yii\web\Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if(!$model->ref){
            $model->ref  = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $this->UpdateUnit($model);
            return [
                'title' => $this->request->get('title'),
                // 'content' => $this->renderAjax('view', [
                //     'model' => $model,
                // ]),
                'container' => '#sm-container',
                'status' => 'success',
            ];
        }

        return [
            'title' => $this->request->get('title'),
            'content' => $this->renderAjax('create', [
                'model' => $model,
                'ref' => $model->ref == '' ? substr(\Yii::$app->getSecurity()->generateRandomString(), 10) : $model->ref,
            ]),
        ];
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
                    'container' => '#sm'
                ];
            }
        } else {
            $model->loadDefaultValues();
        }
       
    }


    public function actionUnitUpdate()
    {
        $id = $this->request->get('id');
        $model = $this->findModel($id);
        $old = $model->data_json;
        if (isset($_POST['hasEditable'])) {
            // use Yii's response format to encode output as JSON
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $newObj =  $_POST['Product']['data_json'];
            $model->data_json = ArrayHelper::merge($old, $newObj);
            if ($model->save()) {
                // return JSON encoded output in the below format on success with an empty `message`
                return ['output' => $model->data_json['unit'], 'message' => ''];
            } else {
                // alternatively you can return a validation error (by entering an error message in `message` key)
                return ['output' => $newObj, 'message' => 'Incorrect Value! Please reenter.'];
            }
        } else {
            return ['output'=>'', 'message'=>''];
        }
    }
        // \Yii::$app->response->format = Response::FORMAT_JSON;
        // $model = $this->findModel($id);
        // if(!$model->ref){
        //     $model->ref  = substr(\Yii::$app->getSecurity()->generateRandomString(), 10);
        // }

        // if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
        //     return [
        //         'title' => $this->request->get('title'),
        //         'content' => $this->renderAjax('view', [
        //             'model' => $model,
        //         ]),
        //         'status' => 'success',
        //     ];
        // }

        // return [
        //     'title' => $this->request->get('title'),
        //     'content' => $this->renderAjax('create', [
        //         'model' => $model,
        //         'ref' => $model->ref == '' ? substr(\Yii::$app->getSecurity()->generateRandomString(), 10) : $model->ref,
        //     ]),
        // ];
    // }

    /**
     * Deletes an existing Product model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }



    // ตรวจสอบความถูกต้อง
    public function actionCreatevalidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Product();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {
            
            if (isset($model->title)) {
                $model->title  == "" ? $model->addError('title', $requiredName) : null;
                // $checkTitle = Product::findOne(['name' => 'asset_item','title' => $model->title]);
                // if($checkTitle){
                //     $model->addError('title', 'ชื่อซ้ำ');
                // }
            }
            
            if (isset($model->code) && $model->auto == 0) {
                $model->code  == "" ? $model->addError('code', $requiredName) : null;
                $checkCode = Product::findOne(['name' => 'asset_item','code' => $model->code]);
                if($checkCode){
                    $model->addError('code', 'รหัสซ้ำ');
                }

            }


            if (isset($model->category_id)) {
                $model->category_id  == "" ? $model->addError('category_id', $requiredName) : null;
            }

            if (isset($model->data_json['unit'])) {
                $model->data_json['unit'] == "" ? $model->addError('data_json[unit]', $requiredName) : null;
            }



        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }


    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     *
     * @return Product the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
