<?php

namespace app\modules\me\controllers;

use app\components\UserHelper;
use Yii;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;
use app\modules\inventory\models\StockEventSearch;
use app\modules\inventory\models\StockOut;
use app\modules\inventory\models\StockOutSearch;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\OrderSearch;
use yii\db\Expression;
use yii\web\Response;
use yii\helpers\ArrayHelper;

class ApproveController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    //รายการขอเบิกวัสดุที่ต้องอนุมัติ
    public function actionStockOut()
    {

        $emp = UserHelper::GetEmployee();
        $searchModel = new StockEventSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andFilterWhere(['name' => 'order', 'checker' => $emp->id]);
        $dataProvider->query->andWhere(new \yii\db\Expression("JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.checker_confirm')) = ''"));
        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => $this->request->get('title'),
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('list_stock_out', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ])
            ];
        } else {
            return $this->render('list_stock_out', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    // แสดงรายละเอียดและรายการขอเบิกและบันทึก
    public function actionViewStockOut($id)
    {
        
        $model = StockEvent::findOne($id);

        $oldObj = $model->data_json;
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $checkData = $model->getAvatar($model->checker);
                $checkerData = [
                    'checker_confirm_date' => date('Y-m-d H:i:s'),
                    'checker_name' => $checkData['fullname'],
                    'checker_position' => $checkData['position_name']
                ];
                $model->data_json = ArrayHelper::merge($oldObj, $model->data_json,$checkerData);
                if ($model->data_json['checker_confirm'] == 'Y') {
                    
                    if ($model->warehouse_id == $model->from_warehouse_id) {
                        $this->UpdateStock($model);
                        $model->order_status = 'success';
                    }
                }else{
                    $model->order_status = 'cancel';
                }
                $model->save(false);
                
                StockEvent::updateAll(['order_status' => $model->order_status], ['code' => $model->code]);
                return [
                    'status' => 'success',
                    'container' => '#me'
                ];
            } else {
                return false;
            }
        } else {
            $model->loadDefaultValues();
        }

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' =>  'เรื่องขอเบิกวัสดุ',
                'content' => $this->renderAjax('view_stock_out', [
                    'model' => $model
                ])
            ];
        } else {
            return $this->render('view_stock_out', [
                'model' => $model
            ]);
        }
    }

    private function UpdateStock($model)
    {
        // update Stock
        foreach ($model->getItems() as $item) {

            $item->order_status = 'success';
            $item->save(false);
            Yii::$app->response->format = Response::FORMAT_JSON;

            // ตัด stock และทำการ update
            $checkStock = Stock::findOne(['asset_item' => $item->asset_item, 'lot_number' => $item->lot_number, 'warehouse_id' => $item->warehouse_id]);
            $checkStock->qty = $checkStock->qty - $item->qty;
            $checkStock->save(false);
        }
        // End update Stock{

    }



    // ตรวจสอบความถูกต้อง
    public function actionStockConfirmValidator()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new StockEvent();
        $requiredName = "ต้องระบุ";
        if ($this->request->isPost && $model->load($this->request->post())) {

            if (isset($model->data_json['checker_confirm'])) {
                $model->data_json['checker_confirm'] == "" ? $model->addError('data_json[checker_confirm]', $requiredName) : null;
            }
        }
        foreach ($model->getErrors() as $attribute => $errors) {
            $result[\yii\helpers\Html::getInputId($model, $attribute)] = $errors;
        }
        if (!empty($result)) {
            return $this->asJson($result);
        }
    }



    public function actionPurchase()
    {

        // 
        

        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->andwhere(['is not', 'pr_number', null]);
        $dataProvider->query->andwhere(['status' => 1]);
        $dataProvider->query->andFilterwhere(['name' => 'order']);
        //ถ้าเป็นผู้อำนวยการ
        if(Yii::$app->user->can('director')){
            $dataProvider->query->andwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_director_confirm')"), ""]);
            $dataProvider->query->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_officer_checker')"),"Y"]);
            $dataProvider->query->andFilterwhere(['=', new Expression("JSON_EXTRACT(data_json, '$.pr_leader_confirm')"), "Y"]);

        }else{
            // $dataProvider->query->where(['=', new Expression("JSON_EXTRACT(data_json, '$.leader1')"),  (string) Yii::$app->user->id]);
        }

        $dataProvider->pagination->pageSize = 8;

        if ($this->request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'รายการขอซื้อ/ขอจ้าง',
                'count' => $dataProvider->getTotalCount(),
                'content' => $this->renderAjax('@app/modules/sm/views/default/list_order', [
                // 'content' => $this->renderAjax('purchase', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'container' => 'pr-order',
                    'title' => 'รายการขอซื้อ/ขอจ้าง',
                ])
            ];
        } else {
            return $this->render('@app/modules/sm/views/default/list_order', [
            // return $this->render('purchase', [
                'title' => 'รายการขอซื้อ/ขอจ้าง',
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'container' => 'pr-order'
            ]);
        }
    }
}
