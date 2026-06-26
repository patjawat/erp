<?php

namespace app\modules\approveV2\controllers;

use Yii;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\base\DynamicModel;
use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\approveV2\models\Approve;
use app\modules\approveV2\models\ApproveSearch;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;

class MainStockController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $searchModel = new ApproveSearch([
            'status' => 'Pending'
        ]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith(['stock'], true, 'INNER JOIN');
        $dataProvider->query->andFilterWhere(['approve.name' => 'main_stock', 'approve.emp_id' => $me->id]);
        $dataProvider->query->andWhere(['<>', 'approve.status', 'None']);
        $dataProvider->query->andFilterWhere(['stock_events.emp_id' => $searchModel->emp_id]);
        $startDate = $searchModel->date_start ? AppHelper::convertToGregorian($searchModel->date_start) . ' 00:00:00' : null;
$endDate = $searchModel->date_end ? AppHelper::convertToGregorian($searchModel->date_end) . ' 23:59:59' : null;

$dataProvider->query->andFilterWhere(['>=', 'stock_events.created_at', $startDate]);
$dataProvider->query->andFilterWhere(['<=', 'stock_events.created_at', $endDate]);

        $dataProvider->query->orderBy(['id' => SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * รายการขออนุมัติเบิกวัสดุ (inventoryV2) — แสดงทั้งรออนุมัติและย้อนหลัง (filter ตาม status ได้)
     * query: status = all | pending | APPROVED | CONFIRMED | CANCELLED
     * ค้นหา: order_no, main_warehouse_id, sub_warehouse_id, date_start, date_end
     */
    public function actionRequisitionV2()
    {
        $filterStatus = $this->request->get('status', 'all');

        $searchModel = new DynamicModel(['order_no', 'main_warehouse_id', 'sub_warehouse_id', 'date_start', 'date_end']);
        $searchModel->addRule(['order_no', 'main_warehouse_id', 'sub_warehouse_id', 'date_start', 'date_end'], 'safe');
        $searchModel->load($this->request->get(), '');

        $query = StockOrder::find()
            ->where([
                'order_type' => StockOrder::ORDER_TYPE_OUT,
                'source_type' => 'REQUEST',
            ])
            ->andWhere(['<>', 'status', StockOrder::STATUS_DRAFT])
            ->with(['mainWarehouse', 'subWarehouse'])
            ->orderBy(['id' => SORT_DESC]);

        $me = UserHelper::GetEmployee();
        if (!$me) {
            $query->andWhere('0=1');
        } else {
            $query->andWhere(new \yii\db\Expression(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(data_json, '$.issue_approver.emp_id')) AS UNSIGNED) = :empId",
                [':empId' => (int) $me->id]
            ));
        }

        if ($filterStatus === 'pending') {
            $query->andWhere(['status' => StockOrder::STATUS_PENDING]);
        } elseif (in_array($filterStatus, [StockOrder::STATUS_APPROVED, StockOrder::STATUS_CONFIRMED, StockOrder::STATUS_CANCELLED], true)) {
            $query->andWhere(['status' => $filterStatus]);
        }

        if (trim($searchModel->order_no ?? '') !== '') {
            $query->andWhere(['like', 'order_no', trim($searchModel->order_no)]);
        }
        if (isset($searchModel->main_warehouse_id) && $searchModel->main_warehouse_id !== '' && $searchModel->main_warehouse_id !== null) {
            $query->andWhere(['main_warehouse_id' => (int) $searchModel->main_warehouse_id]);
        }
        if (isset($searchModel->sub_warehouse_id) && $searchModel->sub_warehouse_id !== '' && $searchModel->sub_warehouse_id !== null) {
            $query->andWhere(['sub_warehouse_id' => (int) $searchModel->sub_warehouse_id]);
        }
        if (trim($searchModel->date_start ?? '') !== '') {
            try {
                $from = AppHelper::convertToGregorian($searchModel->date_start);
                if ($from) {
                    $query->andWhere(['>=', 'order_date', $from]);
                }
            } catch (\Throwable $e) {
            }
        }
        if (trim($searchModel->date_end ?? '') !== '') {
            try {
                $to = AppHelper::convertToGregorian($searchModel->date_end);
                if ($to) {
                    $query->andWhere(['<=', 'order_date', $to]);
                }
            } catch (\Throwable $e) {
            }
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'defaultPageSize' => 20,
            ],
        ]);

        $mainWarehouses = ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'MAIN'])->orderBy(['warehouse_name' => SORT_ASC])->all(), 'id', 'warehouse_name');
        $subWarehouses = ArrayHelper::map(Warehouse::find()->where(['warehouse_type' => 'SUB'])->orderBy(['warehouse_name' => SORT_ASC])->all(), 'id', 'warehouse_name');

        return $this->render('requisition-v2', [
            'dataProvider' => $dataProvider,
            'filterStatus' => $filterStatus,
            'searchModel' => $searchModel,
            'mainWarehouses' => $mainWarehouses,
            'subWarehouses' => $subWarehouses,
        ]);
    }

    public function actionUpdate($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(['id' => $id,  'name' => 'main_stock', 'emp_id' => $me->id]);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $status = $this->request->post('status');
            // ระบบอนุมัติเบิกคลัง
            $old = $model->data_json;
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($old, $model->data_json, $approveDate);
            $model->status = $status;
            //ถ้าบันทุกเรียบร้อย
            if ($model->save(false)) {
                // update ส่วน stock
                $oldStockObj = $model->stock->data_json;
                $checkData = $model->stock->empChecker;
                $checkerData = [
                    'checker_confirm_date' => date('Y-m-d H:i:s'),
                    'checker_name' => $checkData->fullname,
                    'checker_position' => $checkData->positionName(),
                    'checker_confirm' => ($model->status == 'Pass' ? 'Y' : 'N')
                ];

                if ($model->status == 'Pass') {
                    $model->stock->order_status = 'pending';
                }

                if ($model->status == 'Reject') {
                    $model->stock->order_status = 'cancel';
                }
                $model->stock->data_json = ArrayHelper::merge($oldStockObj, $model->stock->data_json, $checkerData);
                $model->stock->save(false);
            }

            return [
                'status' => 'success'
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => isset($model->stock) ? $model->stock->CreateBy('ขอเบิกวัสดุ')['avatar'] : '',
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

    public function actionUpdateFormStore($id)
    {
        $me = UserHelper::GetEmployee();
        $model = Approve::findOne(['id' => $id, 'name' => 'main_stock']);
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if ($this->request->isPost) {
            $status = $this->request->post('status');
            // ระบบอนุมัติเบิกคลัง
            $old = $model->data_json;
            $approveDate = ['approve_date' => date('Y-m-d H:i:s')];
            $model->data_json = ArrayHelper::merge($old, $model->data_json, $approveDate);
            $model->status = $status;
            //ถ้าบันทุกเรียบร้อย
            if ($model->save(false)) {
                // update ส่วน stock
                $oldStockObj = $model->stock->data_json;
                $checkData = $model->stock->empChecker;
                $checkerData = [
                    'checker_confirm_date' => date('Y-m-d H:i:s'),
                    'checker_name' => $checkData->fullname,
                    'checker_position' => $checkData->positionName(),
                    'checker_confirm' => ($model->status == 'Pass' ? 'Y' : 'N')
                ];

                if ($model->status == 'Pass') {
                    $model->stock->order_status = 'pending';
                }

                if ($model->status == 'Reject') {
                    $model->stock->order_status = 'cancel';
                }
                $model->stock->data_json = ArrayHelper::merge($oldStockObj, $model->stock->data_json, $checkerData);
                $model->stock->save(false);
            }

            return [
                'status' => 'success'
            ];
        }

        if ($this->request->isAJax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => isset($model->stock) ? $model->stock->CreateBy('ขอเบิกวัสดุ')['avatar'] : '',
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
