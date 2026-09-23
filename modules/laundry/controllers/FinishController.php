<?php

namespace app\modules\laundry\controllers;

use app\components\AppHelper;
use app\modules\laundry\models\LaundryExternalSource;
use app\modules\laundry\models\LaundryItemCategory;
use app\modules\laundry\services\LaundryBalance;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * นับ-รีด-QC — นับผ้าสะอาดหลังอบ (รายประเภท เป็นชิ้น) → บวกเข้าคลังหลัก (CLEAN inflow)
 * นี่คือจุดแปลง กก.→ชิ้น และเป็น "ขาเข้า" ของคลังหลัก
 * + รับผ้าจากหน่วยงานภายนอก (เช่น รพ.เลย ส่งผ้ากลับหลัง Refer) เข้าคลังหลักทางเดียวกัน
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

        $items = (new Query())->select(['id', 'item_name', 'category_id'])->from('laundry_item')
            ->where(['is_active' => 1])->orderBy(['item_name' => SORT_ASC])->all();
        $itemGroups = LaundryItemCategory::group($items);
        // หน่วยงานภายนอกที่ส่งผ้ามาให้ (เช่น รพ.เลย)
        $externalOptions = LaundryExternalSource::options();

        // รอบอบที่เสร็จแล้ว (อ้างอิงได้)
        $dryBatches = (new Query())->select(['b.id', 'b.batch_no', 'b.input_kg', 'b.output_kg', 'b.ended_at', 'code' => 'a.code'])
            ->from(['b' => 'laundry_processing_batch'])
            ->leftJoin(['a' => 'asset'], 'a.id = b.asset_id')
            ->where(['b.stage' => 'DRY', 'b.status' => 'COMPLETED'])
            ->orderBy(['b.id' => SORT_DESC])->limit(30)->all();

        // รายการนับเข้าคลังของวันนั้น (หลังอบ + รับจากภายนอก)
        $finishes = (new Query())
            ->select(['f.id', 'f.finish_no', 'f.counted_at', 'f.created_by', 'f.dry_batch_id', 'f.source_type',
                'external_name' => 'x.name', 'batch_no' => 'b.batch_no',
                'types' => new Expression('COUNT(l.id)'), 'total' => new Expression('COALESCE(SUM(l.qty),0)')])
            ->from(['f' => 'laundry_finish'])
            ->leftJoin(['l' => 'laundry_finish_line'], 'l.finish_id = f.id')
            ->leftJoin(['x' => 'laundry_external_source'], 'x.id = f.external_source_id')
            ->leftJoin(['b' => 'laundry_processing_batch'], 'b.id = f.dry_batch_id')
            ->where(['between', 'f.counted_at', $date . ' 00:00:00', $date . ' 23:59:59'])
            ->groupBy('f.id')->orderBy(['f.id' => SORT_DESC])->all();
        $staffNames = $this->staffNames(array_filter(array_column($finishes, 'created_by')));

        // ยอดคลังหลักปัจจุบัน (อ่านจากตารางยอดคงเหลือ — เร็วคงที่)
        $cleanTotal = LaundryBalance::cleanTotal();

        return $this->render('index', compact('date', 'items', 'itemGroups', 'externalOptions', 'dryBatches', 'finishes', 'staffNames', 'cleanTotal'));
    }

    public function actionSave()
    {
        $req = Yii::$app->request;
        $date = AppHelper::convertToGregorian(trim((string) $req->post('counted_date'))) ?: date('Y-m-d');
        $time = trim((string) $req->post('counted_time')) ?: date('H:i');
        $sourceType = $req->post('source_type') === 'EXTERNAL' ? 'EXTERNAL' : 'DRY';
        $dryBatchId = $sourceType === 'DRY' ? ((int) $req->post('dry_batch_id') ?: null) : null;
        $qty = (array) $req->post('qty', []);
        $uid = Yii::$app->user->id ? (int) Yii::$app->user->id : null;

        $back = ['index', 'date' => AppHelper::convertToThai($date), 'source' => $sourceType];

        $lines = [];
        foreach ($qty as $itemId => $q) {
            $q = trim((string) $q);
            if ($q !== '' && ctype_digit($q) && (int) $q > 0 && ctype_digit((string) $itemId)) {
                $lines[(int) $itemId] = (int) $q;
            }
        }
        if (!$lines) {
            Yii::$app->session->setFlash('error', 'กรุณาลงจำนวนอย่างน้อยหนึ่งประเภท');
            return $this->redirect($back);
        }

        $externalId = null;
        $externalName = '';
        if ($sourceType === 'EXTERNAL') {
            // เลือกจากทะเบียน หรือพิมพ์ชื่อใหม่ (สร้างให้อัตโนมัติ)
            $externalId = LaundryExternalSource::resolve($req->post('external_source_id'), $uid);
            if (!$externalId) {
                Yii::$app->session->setFlash('error', 'กรุณาเลือกหน่วยงานภายนอกที่ส่งผ้ามา');
                return $this->redirect($back);
            }
            $externalName = (string) LaundryExternalSource::find()->select('name')->where(['id' => $externalId])->scalar();
        }

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $ts = strtotime($date . ' ' . $time) ?: time();
            $now = date('Y-m-d H:i:s');
            $db->createCommand()->insert('laundry_finish', [
                'source_type' => $sourceType, 'dry_batch_id' => $dryBatchId, 'external_source_id' => $externalId,
                'counted_at' => date('Y-m-d H:i:s', $ts),
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
                // บวกเข้าคลังหลัก (EXTERNAL → CLEAN) + อัปเดตยอดคงเหลือ
                // หลังอบ = PRODUCTION / รับจากหน่วยงานภายนอก = RECEIVE_EXTERNAL (reason = ชื่อหน่วยงาน)
                LaundryBalance::applyEvent($db, [
                    'event_no' => 'LP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
                    'event_type' => $sourceType === 'EXTERNAL' ? 'RECEIVE_EXTERNAL' : 'PRODUCTION', 'item_id' => $itemId, 'qty' => $q,
                    'reason' => $externalId ? mb_substr('รับจาก ' . $externalName, 0, 255) : null,
                    'from_location' => 'EXTERNAL', 'to_location' => 'CLEAN',
                    'processing_batch_id' => $dryBatchId,
                    'status' => 'CONFIRMED', 'occurred_at' => date('Y-m-d H:i:s', $ts),
                    'created_by' => $uid, 'approved_at' => $now, 'approved_by' => $uid,
                ]);
            }
            $tx->commit();
            Yii::$app->session->setFlash('success', $externalId ? 'บันทึกรับผ้าจาก ' . $externalName . ' เข้าคลังหลักแล้ว' : 'บันทึกผ้าสะอาดเข้าคลังหลักแล้ว');
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error($e, __METHOD__);
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
        }
        return $this->redirect($back);
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
