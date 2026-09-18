<?php

namespace app\modules\laundry\controllers;

use app\components\AppHelper;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * นับ-รีด-QC — นับผ้าสะอาดหลังอบ (รายประเภท เป็นชิ้น) → บวกเข้าคลังหลัก (CLEAN inflow)
 * นี่คือจุดแปลง กก.→ชิ้น และเป็น "ขาเข้า" ของคลังหลัก
 */
class FinishController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['save'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['save' => ['POST']]],
        ];
    }

    public function actionIndex()
    {
        $dateInput = trim((string) Yii::$app->request->get('date'));
        $date = $dateInput !== '' ? (AppHelper::convertToGregorian($dateInput) ?: date('Y-m-d')) : date('Y-m-d');

        $items = (new Query())->select(['id', 'item_name'])->from('laundry_item')
            ->where(['is_active' => 1])->orderBy(['item_name' => SORT_ASC])->all();

        // รอบอบที่เสร็จแล้ว (อ้างอิงได้)
        $dryBatches = (new Query())->select(['b.id', 'b.batch_no', 'b.output_kg', 'b.ended_at', 'code' => 'a.code'])
            ->from(['b' => 'laundry_processing_batch'])
            ->leftJoin(['a' => 'asset'], 'a.id = b.asset_id')
            ->where(['b.stage' => 'DRY', 'b.status' => 'COMPLETED'])
            ->orderBy(['b.id' => SORT_DESC])->limit(30)->all();

        // รายการนับหลังอบของวันนั้น
        $finishes = (new Query())
            ->select(['f.id', 'f.finish_no', 'f.counted_at', 'f.created_by', 'f.dry_batch_id',
                'types' => new Expression('COUNT(l.id)'), 'total' => new Expression('COALESCE(SUM(l.qty),0)')])
            ->from(['f' => 'laundry_finish'])
            ->leftJoin(['l' => 'laundry_finish_line'], 'l.finish_id = f.id')
            ->where(['between', 'f.counted_at', $date . ' 00:00:00', $date . ' 23:59:59'])
            ->groupBy('f.id')->orderBy(['f.id' => SORT_DESC])->all();
        $staffNames = $this->staffNames(array_filter(array_column($finishes, 'created_by')));

        // ยอดคลังหลักปัจจุบัน (รวมชิ้น)
        $cleanTotal = (int) (new Query())->from('laundry_piece_event')
            ->where(['status' => 'CONFIRMED'])
            ->select(new Expression("COALESCE(SUM(CASE WHEN to_location='CLEAN' THEN qty ELSE 0 END) - SUM(CASE WHEN from_location='CLEAN' THEN qty ELSE 0 END),0)"))
            ->scalar();

        return $this->render('index', compact('date', 'items', 'dryBatches', 'finishes', 'staffNames', 'cleanTotal'));
    }

    public function actionSave()
    {
        $req = Yii::$app->request;
        $date = AppHelper::convertToGregorian(trim((string) $req->post('counted_date'))) ?: date('Y-m-d');
        $time = trim((string) $req->post('counted_time')) ?: date('H:i');
        $dryBatchId = (int) $req->post('dry_batch_id') ?: null;
        $qty = (array) $req->post('qty', []);

        $lines = [];
        foreach ($qty as $itemId => $q) {
            $q = trim((string) $q);
            if ($q !== '' && ctype_digit($q) && (int) $q > 0 && ctype_digit((string) $itemId)) {
                $lines[(int) $itemId] = (int) $q;
            }
        }
        if (!$lines) {
            Yii::$app->session->setFlash('error', 'กรุณาลงจำนวนอย่างน้อยหนึ่งประเภท');
            return $this->redirect(['index', 'date' => AppHelper::convertToThai($date)]);
        }

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $ts = strtotime($date . ' ' . $time) ?: time();
            $now = date('Y-m-d H:i:s');
            $uid = Yii::$app->user->id ? (int) Yii::$app->user->id : null;
            $db->createCommand()->insert('laundry_finish', [
                'dry_batch_id' => $dryBatchId, 'counted_at' => date('Y-m-d H:i:s', $ts),
                'created_by' => $uid, 'created_at' => $now,
            ])->execute();
            $finishId = (int) $db->getLastInsertID();
            $db->createCommand()->update('laundry_finish',
                ['finish_no' => 'FN' . (((int) date('Y', $ts) + 543) % 100) . '-' . str_pad((string) $finishId, 6, '0', STR_PAD_LEFT)],
                ['id' => $finishId])->execute();
            foreach ($lines as $itemId => $q) {
                $db->createCommand()->insert('laundry_finish_line', [
                    'finish_id' => $finishId, 'item_id' => $itemId, 'qty' => $q,
                ])->execute();
                // บวกเข้าคลังหลัก: piece_event PRODUCTION (EXTERNAL → CLEAN)
                $db->createCommand()->insert('laundry_piece_event', [
                    'event_no' => 'LP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                    'event_type' => 'PRODUCTION', 'item_id' => $itemId, 'qty' => $q,
                    'from_location' => 'EXTERNAL', 'to_location' => 'CLEAN',
                    'processing_batch_id' => $dryBatchId,
                    'status' => 'CONFIRMED', 'occurred_at' => date('Y-m-d H:i:s', $ts),
                    'created_by' => $uid, 'approved_at' => $now, 'approved_by' => $uid,
                ])->execute();
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกผ้าสะอาดเข้าคลังหลักแล้ว');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
        }
        return $this->redirect(['index', 'date' => AppHelper::convertToThai($date)]);
    }

    private function staffNames(array $userIds): array
    {
        if (!$userIds) {
            return [];
        }
        return \yii\helpers\ArrayHelper::map(
            \app\modules\hr\models\Employees::find()->select(['user_id', 'prefix', 'fname', 'lname'])->where(['user_id' => $userIds])->asArray()->all(),
            'user_id',
            static fn($e) => trim(($e['prefix'] ?? '') . ($e['fname'] ?? '') . ' ' . ($e['lname'] ?? ''))
        );
    }
}
