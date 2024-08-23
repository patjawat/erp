<?php

namespace app\modules\purchase\controllers;

use app\components\AppHelper;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use Yii;

/**
 * PqOrderController implements the CRUD actions for Order model.
 */
class PqOrderController extends Controller
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
     * Lists all Order models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['is not', 'pq_number', null]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);

        if ($this->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'content' => $this->renderAjax('@app/modules/purchase/views/order/index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'isAjax' => true
                ]),
            ];
        } else {
          
        return $this->render('@app/modules/purchase/views/order/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'isAjax' => false
        ]);
        }
    }



    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['order_date'])) {
                preg_replace('/\D/', '', $model->data_json['order_date']) == "" ? $model->addError('data_json[order_date]', 'ลงวันที่ต้องระบุ') : null;
            }
            if (isset($model->data_json['order'])) {
                $model->data_json['order'] == "" ? $model->addError('data_json[order]', 'ต้องระบุคำสั่ง') : null;
            }

            if (isset($model->data_json['order_number'])) {
                $model->data_json['order_number'] == "" ? $model->addError('data_json[order_number]', 'ต้องระบุเลขที่คำสั่ง') : null;
            }
            
            if (isset($model->data_json['pq_purchase_type'])) {
                $model->data_json['pq_purchase_type'] == "" ? $model->addError('data_json[pq_purchase_type]', 'ต้องระบุวิธีซื้อหรือจ้าง') : null;
            }

            if (isset($model->data_json['pq_method_get'])) {
                $model->data_json['pq_method_get'] == "" ? $model->addError('data_json[pq_method_get]', 'ต้องระบุวิธีจัดหา') : null;
            }

            if (isset($model->data_json['pq_budget_group'])) {
                $model->data_json['pq_budget_group'] == "" ? $model->addError('data_json[pq_budget_group]', 'ต้องระบุหมวดเงินที่ใช้') : null;
            }

            if (isset($model->data_json['pq_budget_type'])) {
                $model->data_json['pq_budget_type'] == "" ? $model->addError('data_json[pq_budget_type]', 'ต้องระบุประเภทเงิน') : null;
            }

            if (isset($model->data_json['pq_reason'])) {
                $model->data_json['pq_reason'] == "" ? $model->addError('data_json[pq_reason]', 'ต้องระบุเหตุผลความจำเป็น') : null;
            }
            if (isset($model->data_json['pq_condition'])) {
                $model->data_json['pq_condition'] == "" ? $model->addError('data_json[pq_condition]', 'ต้องระบุเงื่อนไข') : null;
            }
            if (isset($model->data_json['pq_consideration'])) {
                $model->data_json['pq_consideration'] == "" ? $model->addError('data_json[pq_consideration]', 'ต้องระบุการพิจารณา') : null;
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
     * Displays a single Order model.
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
     * Creates a new Order model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    // public function actionCreate()
    // {
    //     $model = new Order([
    //         'name' => 'pq',
    //         'category_id' => $this->request->get('category_id'),
    //         'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
    //     ]);

    //     $thaiYear = substr((date('Y') + 543), 2);
    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post())) {
    //             $model->pq_number = \mdm\autonumber\AutoNumber::generate('PQ-' . $thaiYear . '????');
    //             $model->save(false);
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         } else {
    //             return false;
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }

    //     if ($this->request->isAjax) {
    //         Yii::$app->response->format = Response::FORMAT_JSON;
    //         return [
    //             'title' => $this->request->get('title'),
    //             'content' => $this->renderAjax('create', [
    //                 'model' => $model,
    //             ]),
    //         ];
    //     } else {
    //         return $this->render('create', [
    //             'model' => $model,
    //         ]);
    //     }
    // }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $thaiYear = substr((date('Y') + 543), 2);
        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($model->pq_number == '') {
                    $model->pq_number = \mdm\autonumber\AutoNumber::generate('PQ-' . $thaiYear . '????');
                }  // validate all models

                $convertDate =[
                    'order_date' =>  AppHelper::convertToGregorian($model->data_json['order_date']),
                ];

                $model->data_json =  ArrayHelper::merge($oldObj,$model->data_json,$convertDate);

                if($model->status == 1){
                    $model->status = 2;
                }
                $model->save(false);
                return [
                    'status' => 'success',
                    'container' => '#purchase-container',
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
            $oldObj = $model->data_json;

            try {
                $model->data_json = [
                    'order_date' =>  AppHelper::convertToThai($model->data_json['order_date']),
                ];
            } catch (\Throwable $th) {
                //throw $th;
            }
     
            $model->data_json = ArrayHelper::merge($oldObj,$model->data_json);
            if ($this->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'title' => $this->request->get('title'),
                    'content' => $this->renderAjax('update', [
                        'model' => $model,
                    ]),
                ];
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Deletes an existing Order model.
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
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
