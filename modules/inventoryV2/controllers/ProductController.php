<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\models\StockItem;
use app\modules\inventoryV2\models\StockItemSearch;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * StockItemController implements the CRUD actions for StockItem model.
 */
class ProductController extends Controller
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
     * Lists all StockItem models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StockItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'asset_item']);
        $dataProvider->query->andFilterWhere(['group_id' => 'MATER']);
        $dataProvider->query->andFilterWhere(['category_id' => $searchModel->category_id]);
        if($searchModel->innovation_account == 1){
            $dataProvider->query->andFilterWhere(['like', new Expression("JSON_EXTRACT(data_json, '$.innovation_account')"), "1"]);
        }
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
        $searchModel = new StockItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'StockItem_item']);
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
     * Displays a single StockItem model.
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
     * Creates a new StockItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new StockItem([
            'name' => 'asset_item',
            'group_id' => 'MATER',
            'ref' => substr(\Yii::$app->getSecurity()->generateRandomString(), 10),
        ]);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $categoryId = $model->category_id;
                $code = $model->code;
                $checkCodeDuplicate = StockItemHelper::checkCodeDuplicate($categoryId,$code);

                if($checkCodeDuplicate['status'] == false){
                    return [
                        'status' => 'error',
                        'msg' => 'รหัสซ้ำ =='. $checkCodeDuplicate['data']['code'].' ชื่อรายการ == '.$checkCodeDuplicate['data']['title'],
                        'data' => $checkCodeDuplicate['data']
                    ];
                }
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
     * Updates an existing StockItem model.
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
            $newObj =  $_POST['StockItem']['data_json'];
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
     * Deletes an existing StockItem model.
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



   public function actionCreatevalidator()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    $model = new StockItem();
    $requiredName = "ต้องระบุ";
    $result = []; // ประกาศตัวแปรไว้ก่อนเพื่อป้องกัน error

    if ($this->request->isPost && $model->load($this->request->post())) {
        
        // --- การตรวจสอบเดิมของคุณ ---
        if (isset($model->title)) {
            $model->title == "" ? $model->addError('title', $requiredName) : null;
        }
        
        if (isset($model->code) && $model->auto == 0) {
            $model->code == "" ? $model->addError('code', $requiredName) : null;
            $checkCode = StockItem::findOne(['name' => 'asset_item', 'code' => $model->code]);
            if ($checkCode) {
                $model->addError('code', 'รหัสซ้ำ');
            }
        }

        if (isset($model->category_id)) {
            $model->category_id == "" ? $model->addError('category_id', $requiredName) : null;
        }

        // --- เพิ่มการตรวจสอบ qty_min และ qty_max ---
        $qtyMin = (float)$model->qty_min;
        $qtyMax = (float)$model->qty_max;

        if ($qtyMin > $qtyMax) {
            $model->addError('qty_min', 'ค่าขั้นต่ำต้องไม่มากกว่าค่าสูงสุด');
            $model->addError('qty_max', 'ค่าสูงสุดต้องไม่น้อยกว่าค่าขั้นต่ำ');
        }

        // กรณีตรวจสอบ data_json
        if (isset($model->data_json['unit'])) {
            $model->data_json['unit'] == "" ? $model->addError('data_json[unit]', $requiredName) : null;
        }
    }

    // รวบรวม Error
    foreach ($model->getErrors() as $attribute => $errors) {
        // สำหรับฟิลด์ปกติ
        $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
    }

    if (!empty($result)) {
        return $result; // Yii2 จะแปลงเป็น JSON ให้อัตโนมัติเพราะ set format ไว้แล้ว
    }
    
    return []; // ส่งค่าว่างกลับถ้าไม่มี error
}


    /**
     * Finds the StockItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id ID
     *
     * @return StockItem the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StockItem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
