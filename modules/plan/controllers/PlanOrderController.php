<?php

namespace app\modules\plan\controllers;

use app\modules\plan\services\PlanRevisionService;
use app\modules\plan\components\PlanHelper;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\modules\plan\models\Plan;
use yii\web\NotFoundHttpException;
use app\modules\plan\models\PlanItem;
use app\modules\plan\models\PlanOrder;
use app\modules\plan\models\PlanSearch;

/**
 * PlanController implements the CRUD actions for Plan model.
 */
class PlanOrderController extends Controller
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
     * Lists all Plan models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PlanSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Plan model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $items = $model->getPlanItems()->all(); // ดึงรายการ plan_item

        return $this->render('view', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    /**
     * Creates a new Plan model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Plan();
        $items = []; // ไม่มีรายการเดิม

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $postItems = Yii::$app->request->post('items', []);
            foreach ($postItems as $item) {
                if (!empty($item['item_name'])) {
                    $pi = new PlanItem();
                    $pi->plan_order_id = $model->id;
                    $pi->item_name = $item['item_name'];
                    $pi->quantity = (int)$item['quantity'];
                    $pi->unit_price = (float)$item['unit_price'];
                    $pi->save();
                }
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model, 'items' => $items]);
    }


    /**
     * Updates an existing Plan model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id); // โหลดแผนหลัก
        $items = $model->getPlanItems()->all(); // โหลดรายการเดิม

        if ($model->load(Yii::$app->request->post())) {

            $postItems = Yii::$app->request->post('items', []);

            // $model->updated_at = time();
            if ($model->save()) {

                // ลบรายการเก่าของแผน
                \app\modules\plan\models\PlanItem::deleteAll(['plan_order_id' => $model->id]);

                // บันทึกรายการใหม่
                foreach ($postItems as $item) {
                    if (!empty($item['item_name'])) {
                        $pi = new \app\modules\plan\models\PlanItem();
                        $pi->plan_order_id = $model->id;
                        $pi->item_name = $item['item_name'];
                        $pi->qty = (int)$item['qty'];
                        $pi->unit_price = (float)$item['unit_price'];
                        $pi->save();
                    }
                }

                Yii::$app->session->setFlash('success', 'แก้ไขแผนสำเร็จ');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    public function actionApprove($id)
    {
           Yii::$app->response->format = Response::FORMAT_JSON;
           if (!Yii::$app->user->can('planApprove')) {
               return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์อนุมัติแผน (เฉพาะผู้อนุมัติ)'];
           }
            $model = $this->findModel($id); // โหลดแผนหลัก

        if ($model->load(Yii::$app->request->post())) {
            if ($model->status !== 'submit') {
                return ['status' => 'error', 'message' => 'อนุมัติได้เฉพาะแผนที่รออนุมัติ'];
            }
            $workflow = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
            // $model->updated_at = time();
            $model->status = 'approve';
            $json = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
            $model->data_json = array_merge($json, PlanOrder::decisionStamp());
            if ($model->save(false)) {
                $isAdjust = ($workflow['workflow_cycle'] ?? '') === 'adjust';
                $cycle = $isAdjust ? (int) ($workflow['adjustment_cycle'] ?? 1) : 0;
                PlanRevisionService::capture($model, $cycle, $isAdjust ? PlanRevisionService::ADJUSTED : PlanRevisionService::INITIAL);
                return [
                    'status' => 'success',
                    'url' => Url::to(['/plan/'.$model->plan_group_id]),
                ];
                Yii::$app->session->setFlash('success', 'แก้ไขแผนสำเร็จ');
            }
        }

        return [
            'title' => 'จัดการแผน',
            'content' => $this->renderAjax('_form_approve', [
            'model' => $model,
        ])];
    }


        public function actionRenew()
    {
             Yii::$app->response->format = Response::FORMAT_JSON;
             
             if ($this->request->post()) {
                 $id = $this->request->post('id');
                 $model = $this->findModel($id); // โหลดแผนหลัก
                 if ($model->status !== 'approve' || !PlanHelper::canAdjust($model->thai_year)) {
                     return ['status' => 'error', 'message' => 'ขณะนี้ไม่อยู่ในรอบปรับแผน หรือแผนยังไม่ได้อนุมัติ'];
                 }
                 $cycle = PlanRevisionService::nextCycle($model);
                 PlanRevisionService::capture($model, $cycle, PlanRevisionService::BEFORE_ADJUST);
                 $json = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
                 $json['adjustment_cycle'] = $cycle;
                 $json['workflow_cycle'] = 'adjust';
                 $model->data_json = $json;
                 $model->status = 'renew';
            if ($model->save(false)) {
                return [
                    'status' => 'success',
                    'url' => Url::to(['/plan/'.$model->plan_group_id]),
                ];
                Yii::$app->session->setFlash('success', 'แก้ไขแผนสำเร็จ');
            }
        }
    }



    //update สถานะ
    public function actionUpdateStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = $this->request->post('id');
        $status = $this->request->post('status');
        // การตัดสินใจอนุมัติ/ไม่อนุมัติ ต้องมีสิทธิ์ planApprove (ผู้ส่งคำขอ 'submit' ทำได้ตามปกติ)
        if (in_array($status, ['approve', 'reject'], true) && !Yii::$app->user->can('planApprove')) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์ไม่อนุมัติแผน (เฉพาะผู้อนุมัติ)'];
        }
        $model = $this->findModel($id);
        if ($model) {
            $currentStatus = (string) $model->status;
            $workflow = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
            if (in_array($status, ['approve', 'reject'], true) && $currentStatus !== 'submit') {
                return ['status' => 'error', 'message' => 'ดำเนินการได้เฉพาะแผนที่รออนุมัติ'];
            }
            if ($status === 'submit') {
                $normalSubmit = in_array($currentStatus, ['draft', 'reject'], true) && PlanHelper::canAdd($model->thai_year);
                $adjustSubmit = in_array($currentStatus, ['renew', 'reject'], true)
                    && ($workflow['workflow_cycle'] ?? '') === 'adjust'
                    && PlanHelper::canAdjust($model->thai_year);
                if (!$normalSubmit && !$adjustSubmit) {
                    return ['status' => 'error', 'message' => 'ขณะนี้ไม่สามารถส่งแผนในสถานะนี้ได้'];
                }
            }
            $model->status = $status;
            if (in_array($status, ['approve', 'reject'], true)) {
                $reason = trim((string) $this->request->post('reason', ''));
                $json = is_array($model->data_json) ? $model->data_json : (json_decode((string) $model->data_json, true) ?: []);
                $model->data_json = array_merge($json, PlanOrder::decisionStamp($reason !== '' ? $reason : null));
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (!$model->save(false)) {
                    throw new \RuntimeException('บันทึกสถานะแผนไม่สำเร็จ');
                }
                if ($status === 'approve') {
                    $isAdjust = ($workflow['workflow_cycle'] ?? '') === 'adjust';
                    $cycle = $isAdjust ? (int) ($workflow['adjustment_cycle'] ?? 1) : 0;
                    PlanRevisionService::capture($model, $cycle, $isAdjust ? PlanRevisionService::ADJUSTED : PlanRevisionService::INITIAL);
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::error($e, __METHOD__);
                return ['status' => 'error', 'message' => 'บันทึกสถานะแผนไม่สำเร็จ กรุณาลองใหม่'];
            }
              return $this->redirect(['/plan/'.$model->plan_group_id.'/index']);
            return [
                'status' => 'success',
                
            ];
        } else {
            return [
                'status' => 'error'
            ];
        }
    }
    /**
     * Deletes an existing Plan model.
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
     * Finds the Plan model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Plan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PlanOrder::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
