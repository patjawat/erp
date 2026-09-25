<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\services\FinanceInboxService;
use app\modules\finance\services\PurchaseFinanceSnapshotBuilder;
use app\modules\finance\services\FinanceInboxReviewService;
use app\modules\purchase\models\Order;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\ContractReceipt;
use app\modules\finance\services\ContractReceiptFinanceSnapshotBuilder;

/**
 * กล่องรอรับงานเจ้าหนี้ (การเงิน) — พัสดุตรวจรับ+เอกสารครบ ส่งเข้ามารอ,
 * การเงินตรวจกับเอกสารแล้วลงรับเข้าทะเบียนคุมเจ้าหนี้
 */
class InboxController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                // ปุ่ม "ส่งการเงิน" ฝั่งพัสดุ ถือ permission accountingInboxReceive (role purchase)
                ['allow' => true, 'actions' => ['receive-purchase', 'receive-contract-receipt'], 'roles' => ['accountingInboxReceive']],
                ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['review'], 'roles' => ['financeOperate']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['receive-purchase' => ['POST'], 'receive-contract-receipt' => ['POST'], 'review' => ['POST']]],
        ]);
    }

    public function actionReceivePurchase($id)
    {
        $order = Order::find()->where(['id' => $id, 'name' => 'order'])->one();
        if (!$order) {
            throw new NotFoundHttpException('ไม่พบเอกสารจัดซื้อจัดจ้างต้นทาง');
        }
        $sourceRedirect = ['/purchase/order/view', 'id' => $order->id];
        // ใบที่มีสัญญาตรวจรับรายงวด ส่งการเงินทีละงวดจากหน้าสัญญา — ห้ามส่งทั้งใบ (จะตั้งหนี้ซ้ำเต็มวงเงิน)
        $installment = Contract::find()->where(['order_id' => $order->id, 'deleted_at' => null])->one();
        if ($installment && $installment->isInstallment()) {
            Yii::$app->session->setFlash('error', 'ใบนี้ผูกสัญญาตรวจรับรายงวด — ส่งการเงินทีละงวดที่หน้าสัญญา "' . $installment->title . '" ไม่ส่งทั้งใบ');
            return $this->redirect($sourceRedirect);
        }
        // ส่งการเงินได้เฉพาะใบที่รับเข้าคลังแล้ว (สถานะ 6 วัสดุเข้าคลัง) เท่านั้น
        if ((int) $order->status !== 6) {
            Yii::$app->session->setFlash('error', 'ส่งการเงินได้เฉพาะใบที่รับเข้าคลังแล้ว (สถานะวัสดุเข้าคลัง) เท่านั้น');
            return $this->redirect($sourceRedirect);
        }
        try {
            $snapshot = (new PurchaseFinanceSnapshotBuilder())->build($order);
            if ($snapshot['blocking_errors']) {
                Yii::$app->session->setFlash('error', 'ยังส่งการเงินไม่ได้: ' . implode(' · ', $snapshot['blocking_errors']));
                return $this->redirect($sourceRedirect);
            }
            (new FinanceInboxService())->receive($snapshot['source'], $snapshot['payload']);
            // ส่งสำเร็จ → เดินสถานะพัสดุจาก 6 (วัสดุเข้าคลัง) ไป 7 (ส่งการเงิน)
            $order->status = 7;
            if (!$order->save(false, ['status'])) {
                throw new \RuntimeException('อัปเดตสถานะใบสั่งซื้อเป็น "ส่งการเงิน" ไม่สำเร็จ');
            }
            Yii::$app->session->setFlash('success', 'ส่งเอกสารเข้ากล่องรอรับของการเงินแล้ว และเปลี่ยนสถานะพัสดุเป็น "ส่งการเงิน" เรียบร้อย');
            return $this->redirect($sourceRedirect);
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('info', $e->getMessage());
            return $this->redirect($sourceRedirect);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'ส่งเข้ากล่องรอรับของการเงินไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
            return $this->redirect($sourceRedirect);
        }
    }

    /**
     * ส่งงวดตรวจรับของสัญญา (ตรวจรับรายงวด) เข้ากล่องรอรับ — 1 งวด = 1 รายการตั้งหนี้
     * สำเร็จแล้วงวดเปลี่ยนเป็น "ส่งการเงินแล้ว" (แก้ไขไม่ได้จนกว่าการเงินจะตีกลับ)
     */
    public function actionReceiveContractReceipt($id)
    {
        $receipt = ContractReceipt::findOne(['id' => $id, 'deleted_at' => null]);
        if (!$receipt || !$receipt->contract) {
            throw new NotFoundHttpException('ไม่พบงวดตรวจรับต้นทาง');
        }
        $back = ['/purchase/contract/view', 'id' => $receipt->contract_id, '#' => 'receipts'];

        $latest = ContractReceiptFinanceSnapshotBuilder::latestInbox((int) $receipt->id);
        if ($latest && !ContractReceiptFinanceSnapshotBuilder::isReturned($latest)) {
            Yii::$app->session->setFlash('info', 'งวดที่ ' . $receipt->seq . ' อยู่ในกล่องรอรับของการเงินแล้ว');
            return $this->redirect($back);
        }

        $snapshot = (new ContractReceiptFinanceSnapshotBuilder())->build($receipt);
        if ($snapshot['blocking_errors']) {
            Yii::$app->session->setFlash('error', 'งวดที่ ' . $receipt->seq . ' ยังส่งการเงินไม่ได้: ' . implode(' · ', $snapshot['blocking_errors']));
            return $this->redirect($back);
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            (new FinanceInboxService())->receive($snapshot['source'], $snapshot['payload']);
            $receipt->status = ContractReceipt::STATUS_SENT_FINANCE;
            $receipt->sent_finance_at = date('Y-m-d H:i:s');
            if (!$receipt->save(false, ['status', 'sent_finance_at', 'updated_at', 'updated_by'])) {
                throw new \RuntimeException('อัปเดตสถานะงวดไม่สำเร็จ');
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'ส่งงวดที่ ' . $receipt->seq . ' ยอด ' . number_format((float) $receipt->amount, 2)
                . ' บาท เข้ากล่องรอรับของการเงินแล้ว');
        } catch (\DomainException $e) {
            $tx->rollBack();
            Yii::$app->session->setFlash('info', $e->getMessage());
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'ส่งเข้ากล่องรอรับของการเงินไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect($back);
    }

    public function actionIndex()
    {
        $query = FinanceInbox::find()->orderBy(['received_at' => SORT_DESC, 'id' => SORT_DESC]);
        $status = Yii::$app->request->get('status');
        $sourceSystem = Yii::$app->request->get('source_system');
        if ($status && isset(FinanceInbox::statusOptions()[$status])) {
            $query->andWhere(['status' => $status]);
        }
        if ($sourceSystem) {
            $query->andWhere(['source_system' => $sourceSystem]);
        }
        $counts = FinanceInbox::find()->select(['status', 'count' => 'COUNT(*)'])->groupBy('status')->indexBy('status')->asArray()->all();
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30]]),
            'counts' => $counts,
            'status' => $status,
        ]);
    }

    public function actionReview($id)
    {
        $model = $this->findInbox($id);
        try {
            (new FinanceInboxReviewService())->review($model, (string) Yii::$app->request->post('decision', ''), Yii::$app->request->post('note'));
            Yii::$app->session->setFlash('success', 'บันทึกผลการตรวจสอบเรียบร้อยแล้ว');
        } catch (\DomainException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกผลการตรวจสอบไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionView($id)
    {
        $model = $this->findInbox($id);
        return $this->render('view', ['model' => $model, 'reviews' => $model->reviews]);
    }

    private function findInbox($id): FinanceInbox
    {
        $model = FinanceInbox::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการในกล่องรอรับของการเงิน');
        }
        return $model;
    }
}
