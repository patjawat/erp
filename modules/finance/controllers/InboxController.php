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

class InboxController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'receive-purchase' => ['POST'],
                    'review' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionReceivePurchase($id)
    {
        $order = Order::find()->where(['id' => $id, 'name' => 'order'])->one();
        if (!$order) {
            throw new NotFoundHttpException('ไม่พบเอกสารจัดซื้อจัดจ้างต้นทาง');
        }

        $redirect = ['/purchase/order/view', 'id' => $order->id];
        try {
            $snapshot = (new PurchaseFinanceSnapshotBuilder())->build($order);
            if ($snapshot['blocking_errors']) {
                Yii::$app->session->setFlash(
                    'error',
                    'ยังส่งบัญชีไม่ได้: ' . implode(' · ', $snapshot['blocking_errors'])
                );
                return $this->redirect($redirect);
            }

            $inbox = (new FinanceInboxService())->receive($snapshot['source'], $snapshot['payload']);
            Yii::$app->session->setFlash(
                'success',
                'ส่งสำเนาเอกสารเข้ากล่องรับงานบัญชีแล้ว โดยยังไม่เปลี่ยนสถานะพัสดุ'
            );
            return $this->redirect(['/finance/inbox/view', 'id' => $inbox->id]);
        } catch (\DomainException $e) {
            $existing = FinanceInbox::find()->where([
                'source_system' => PurchaseFinanceSnapshotBuilder::SOURCE_SYSTEM,
                'source_id' => (string) $order->id,
                'source_version' => 1,
            ])->orderBy(['id' => SORT_DESC])->one();
            Yii::$app->session->setFlash('info', $e->getMessage());
            return $existing
                ? $this->redirect(['/finance/inbox/view', 'id' => $existing->id])
                : $this->redirect($redirect);
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'ส่งเข้ากล่องรับงานบัญชีไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบ');
            return $this->redirect($redirect);
        }
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

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
        ]);

        $counts = FinanceInbox::find()
            ->select(['status', 'count' => 'COUNT(*)'])
            ->groupBy('status')
            ->indexBy('status')
            ->asArray()
            ->all();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'counts' => $counts,
            'status' => $status,
        ]);
    }

    public function actionReview($id)
    {
        $model = FinanceInbox::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการในกล่องรับบัญชี');
        }

        $decision = (string) Yii::$app->request->post('decision', '');
        $note = Yii::$app->request->post('note');
        try {
            (new FinanceInboxReviewService())->review($model, $decision, $note);
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
        $model = FinanceInbox::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบรายการในกล่องรับบัญชี');
        }
        return $this->render('view', [
            'model' => $model,
            'reviews' => $model->reviews,
        ]);
    }
}
