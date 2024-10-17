<?php

namespace app\modules\purchase\controllers;

use app\components\AppHelper;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use app\modules\sm\models\Vendor;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Html;

/**
 * PoOrderController implements the CRUD actions for Order model.
 */
class PoOrderController extends Controller
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

    // ตรวจสอบความถูกต้อง
    public function actionValidator()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Order();
        $requiredName = 'ต้องระบุ';
        if ($this->request->isPost && $model->load($this->request->post())) {
            if (isset($model->data_json['po_date'])) {
                preg_replace('/\D/', '', $model->data_json['po_date']) == '' ? $model->addError('data_json[po_date]', 'ลงวันที่ต้องระบุ') : null;
            }

            if (isset($model->data_json['po_expire_date'])) {
                preg_replace('/\D/', '', $model->data_json['po_expire_date']) == '' ? $model->addError('data_json[po_expire_date]', 'วันสิ้นสุดต้องระบุ') : null;
            }

            if (isset($model->data_json['credit_days'])) {
                $model->data_json['credit_days'] == '' ? $model->addError('data_json[credit_days]', 'เครดิต (วัน)ต้องระบุ') : null;
            }

            if (isset($model->data_json['order_receipt_date'])) {
                preg_replace('/\D/', '', $model->data_json['order_receipt_date']) == '' ? $model->addError('data_json[order_receipt_date]', 'วันที่รับใบสั่งต้องระบุ') : null;
            }

            if (isset($model->data_json['delivery_date'])) {
                preg_replace('/\D/', '', $model->data_json['delivery_date']) == '' ? $model->addError('data_json[delivery_date]', 'กำหนดวันส่งมอบต้องระบุ') : null;
            }

            if (isset($model->data_json['warranty_date'])) {
                preg_replace('/\D/', '', $model->data_json['warranty_date']) == '' ? $model->addError('data_json[warranty_date]', 'การรับประกันต้องระบุ') : null;
            }
            if (isset($model->data_json['signing_date'])) {
                preg_replace('/\D/', '', $model->data_json['signing_date']) == '' ? $model->addError('data_json[signing_date]', 'วันที่ลงนามต้องระบุ') : null;
            }

            if (isset($model->data_json['contact_name'])) {
                $model->data_json['contact_name'] == '' ? $model->addError('data_json[contact_name]', 'ผู้รับใบสั่งซื้อต้องระบุ') : null;
            }
            if (isset($model->data_json['qr_number'])) {
                $model->data_json['qr_number'] == '' ? $model->addError('data_json[qr_number]', 'ต้องระบุ') : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[Yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
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
        $dataProvider->query->andwhere(['is not', 'po_number', null]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);
        $dataProvider->query->andFilterWhere([
            'or',
            ['like', 'pr_number', $searchModel->q],
            ['like', 'po_number', $searchModel->q],
        ]);

        return $this->render('@app/modules/purchase/views/order/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Order model.
     *
     * @param int $id ID
     *
     * @return string
     *
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
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $id = $this->request->get('id');
        $model = $this->findModel($id);
        // $model = new Order([
        //     'name' => $this->request->get('name'),
        //     'status' => $this->request->get('status'),
        //     // 'name' => 'order',
        //     'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
        // ]);

        $thaiYear = substr(AppHelper::YearBudget(), 2);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                // $model->code = \mdm\autonumber\AutoNumber::generate('PR-' . $thaiYear . '????');
                $model->save(false);

                return $this->redirect(['/purchase/po-order']);
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
            ];
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id ID
     *
     * @return string|Response
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $vendor = Vendor::findOne(['name' => 'vendor', 'code' => $model->vendor_id]);
        $oldObj = $model->data_json;

        $thaiYear = substr(AppHelper::YearBudget(), 2);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                if ($model->po_number == '') {
                    $model->po_number = \mdm\autonumber\AutoNumber::generate('PO-'.$thaiYear.'????');
                }  // validate all models

                $convertDate = [
                    'po_date' => AppHelper::convertToGregorian($model->data_json['po_date']),
                    'po_expire_date' => AppHelper::convertToGregorian($model->data_json['po_expire_date']),
                    'delivery_date' => AppHelper::convertToGregorian($model->data_json['delivery_date']),
                    'order_receipt_date' => AppHelper::convertToGregorian($model->data_json['order_receipt_date']),
                    'warranty_date' => AppHelper::convertToGregorian($model->data_json['warranty_date']),
                    'signing_date' => AppHelper::convertToGregorian($model->data_json['signing_date']),
                ];

                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json, $convertDate);

                $model->status = 3;
                $model->save(false);
                $this->VendorUpdate($model);

                // //  update pr po pq on items
                // $sql = "UPDATE `orders` SET  pr_number = :pr_number,pq_number = :pq_number,po_number = :po_number WHERE name = 'order_item' AND category_id = :category_id";
                // $command = \Yii::$app
                //     ->db
                //     ->createCommand($sql)
                //     ->bindValues([':pr_number' => $model->pr_number])
                //     ->bindValues([':pq_number' => $model->pq_number])
                //     ->bindValues([':po_number' => $model->po_number])
                //     ->bindValues([':category_id' => $model->id])
                //     ->execute();

                return [
                    'status' => 'success',
                    'container' => '#purchase-container',
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
            try {
                $model->data_json = [
                    'po_date' => AppHelper::convertToThai($model->data_json['po_date']),
                    'po_expire_date' => AppHelper::convertToThai($model->data_json['po_expire_date']),
                    'delivery_date' => AppHelper::convertToThai($model->data_json['delivery_date']),
                    'order_receipt_date' => AppHelper::convertToThai($model->data_json['order_receipt_date']),
                    'warranty_date' => AppHelper::convertToThai($model->data_json['warranty_date']),
                    'signing_date' => AppHelper::convertToThai($model->data_json['signing_date']),
                    'contact_name' => $vendor->data_json['contact_name'],
                    'contact_position' => $vendor->data_json['contact_position'],
                ];
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json);
                // code...
            } catch (\Throwable $th) {
                // throw $th;
            }
        }

        if ($this->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;

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

    protected function VendorUpdate($order)
    {   
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $vendor = Vendor::findOne(['name' => 'vendor', 'code' => $order->vendor_id]);
        $oldObj = $vendor->data_json;
        try {
            if (($order->data_json['contact_name'] != $vendor->data_json['contact_name']) OR $order->data_json['contact_position'] != $vendor->data_json['contact_position']) {
                 $updateData = [
                    'contact_name' => $order->data_json['contact_name'],
                    'contact_position' => $order->data_json['contact_position']
                    ];
                 $vendor->data_json = ArrayHelper::merge($oldObj, $updateData);
                }
            } catch (\Throwable $th) {
                $updateData = [
                    'contact_name' => $order->data_json['contact_name'],
                    'contact_position' => $order->data_json['contact_position']
                    ];
                 $vendor->data_json = ArrayHelper::merge($oldObj, $updateData);
            }
            


        return $vendor->save(false);
    }

    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id ID
     *
     * @return Response
     *
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
     *
     * @param int $id ID
     *
     * @return Order the loaded model
     *
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
