<?php

namespace app\modules\inventoryV2\controllers;

use app\modules\inventoryV2\components\InventoryService;
use app\modules\inventoryV2\models\StockDetail;
use app\modules\inventoryV2\models\StockOrder;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RequisitionController extends Controller
{

public function behaviors()
{
    return [
        'verbs' => [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
                'cancel' => ['POST'],
                'approve' => ['POST'],
            ],
        ],
    ];
}

    /**
     * รายการใบขอเบิกทั้งหมด
     */
    public function actionIndex()
    {
        // เปลี่ยนจาก 'REQUISITION' เป็น 'OUT' ตามค่า ENUM ใน DB 
        // และใช้ source_type ช่วยกรองถ้าคุณระบุไว้ตอน Save
        $query = StockOrder::find()->where([
            'order_type' => 'OUT',
            'source_type' => 'REQUEST' // ถ้าคุณบันทึกค่านี้ไว้ใน Controller ตอน actionCreate
        ]);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]] // เปลี่ยนเป็น id ถ้ายังไม่ได้เก็บ created_at
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * สร้างใบขอเบิก (คลังย่อยเป็นคนทำ)
     */
    public function actionCreate()
    {
        $model = new StockOrder();
        $model->order_type = 'REQUISITION';
        $model->status = 'PENDING'; // เริ่มต้นเป็นรออนุมัติ
        $model->order_date = date('Y-m-d');
        $model->order_no = 'TEMP-' . time();

        if ($this->request->isPost) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($model->load($this->request->post())) {

                    // --- กำหนดค่าคงที่ตรงนี้ (หลังโหลด POST) ---
                    $model->order_type = 'OUT';      // สำหรับใบเบิกพัสดุออก
                    $model->status = 'DRAFT';        // เริ่มต้นเป็น DRAFT (หรือ CONFIRMED ถ้าต้องการให้มีผลทันที)
                    $model->source_type = 'REQUEST'; // ระบุว่าเป็นประเภทการเบิก (ใช้ฟิลด์ string ที่คุณเตรียมไว้)

                    if (empty($model->order_no) || $model->order_no === 'REQ-AUTO') {
                        $model->order_no = 'REQ-' . date('YmdHis');
                    }

                    // เจนเลขที่ใบเบิกถ้ายังไม่มี
                    if (empty($model->order_no) || $model->order_no === 'REQ-AUTO') {
                        $model->order_no = $this->generateOrderNo();
                    }

                    if ($model->save()) {
                        $details = $this->request->post('StockDetail', []);
                        foreach ($details as $data) {
                            $detail = new StockDetail();
                            if ($detail->load($data, '')) {
                                $detail->stock_order_id = $model->id; // ใช้ ID จาก $model ที่เพิ่ง save
                                $detail->lot_number = '-';
                                if (!$detail->save()) {
                                    throw new \Exception("ไม่สามารถบันทึกรายการวัสดุได้: " . implode(', ', $detail->getFirstErrors()));
                                }
                            }
                        }

                        $transaction->commit();

                        // แก้ไขการส่ง URL กลับไป: ให้ระบุเส้นทางให้ชัดเจน
                        return [
                            'success' => true,
                            'redirect' => \yii\helpers\Url::to(['view', 'id' => $model->id]) // ตรวจสอบว่า $model->id มีค่าแล้ว
                        ];
                    } else {
                        $error = implode('<br>', $model->getFirstErrors());
                        throw new \Exception($error);
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    protected function generateOrderNo()
    {
        return 'REQ-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999));
    }

    /**
     * การอนุมัติและจ่ายของ (คลังหลักเป็นคนทำ)
     */
    // app\modules\inventoryV2\controllers\RequisitionController.php

    public function actionApprove($id)
{
    $model = $this->findModel($id);
    $transaction = Yii::$app->db->beginTransaction();

    try {
        $model->status = 'CONFIRMED';
        $model->save(false);

        foreach ($model->stockDetails as $detail) {
            // ใช้ Logic FIFO ในการจ่ายของออก
            InventoryService::moveStock(
        $detail->item_code,
        $model->main_warehouse_id, // คลังหลักต้นทาง
        $detail->qty,
        'OUT',
        $model->id,
        $detail->id
    );
        }

        $transaction->commit();
        Yii::$app->session->setFlash('success', 'อนุมัติจ่ายพัสดุตามระบบ FIFO เรียบร้อยแล้ว');
    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::$app->session->setFlash('error', $e->getMessage());
    }
    return $this->redirect(['view', 'id' => $model->id]);
}


    /**
     * ฟังก์ชันช่วยอัปเดตยอดคงเหลือในตาราง stock_balance
     */
    protected function updateStockBalance($detail)
    {
        $order = $detail->stockOrder;
        $balance = \app\modules\inventoryV2\models\StockBalance::findOne([
            'item_code' => $detail->item_code,
            'warehouse_id' => $order->warehouse_id, // คลังต้นทาง
        ]);

        if (!$balance) {
            throw new \Exception("ไม่พบยอดคงเหลือของรายการ " . $detail->item->item_name . " ในคลังหลัก");
        }

        if ($balance->balance_qty < $detail->qty) {
            throw new \Exception("สินค้า " . $detail->item->item_name . " คงเหลือไม่พอจ่าย");
        }

        // ตัดยอดออก
        $balance->balance_qty -= $detail->qty;
        if (!$balance->save()) {
            throw new \Exception("ไม่สามารถปรับปรุงยอดคงเหลือได้");
        }
    }

    /**
     * ดูรายละเอียดใบขอเบิก
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    public function actionCancel($id)
{
    $model = $this->findModel($id);

    // เช็คสถานะ: ถ้าเป็น DRAFT (ยังไม่ตัดสต็อก) ให้ยกเลิกได้เลย 
    // แต่ถ้าเป็น CONFIRMED (ตัดสต็อกไปแล้ว) ต้องคืนสต็อกด้วย
    if ($model->status === 'CANCELLED') {
        \Yii::$app->session->setFlash('warning', 'เอกสารนี้ถูกยกเลิกไปแล้ว');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    $transaction = \Yii::$app->db->beginTransaction();
    try {
        // หากสถานะเดิมคือ CONFIRMED แปลว่าของถูกหักออกจากคลังไปแล้ว
        if ($model->status === 'CONFIRMED') {
            foreach ($model->stockDetails as $detail) {
                // คืนของเข้าคลังต้นทาง (warehouse_id)
                $success = InventoryService::moveStock(
                    $detail->item_code,
                    $model->warehouse_id, // คืนเข้าคลังหลักที่โดนหักไป
                    $detail->qty,
                    'IN', // เปลี่ยนเป็น IN เพื่อเพิ่มยอดกลับเข้าไป
                    $model->id,
                    $detail->id
                );

                if (!$success) {
                    throw new \Exception("ไม่สามารถคืนพัสดุรหัส: " . $detail->item_code . " เข้าคลังได้");
                }
            }
        }

        // เปลี่ยนสถานะเป็น CANCELLED
        $model->status = 'CANCELLED';
        if ($model->save(false)) {
            $transaction->commit();
            \Yii::$app->session->setFlash('success', 'ยกเลิกใบเบิกและคืนพัสดุเข้าคลังเรียบร้อยแล้ว');
        } else {
            throw new \Exception("ไม่สามารถบันทึกการยกเลิกได้");
        }

    } catch (\Exception $e) {
        $transaction->rollBack();
        \Yii::$app->session->setFlash('error', 'ข้อผิดพลาด: ' . $e->getMessage());
    }

    return $this->redirect(['view', 'id' => $model->id]);
}

    protected function findModel($id)
    {
        if (($model = StockOrder::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบข้อมูลที่ต้องการ');
    }
}
