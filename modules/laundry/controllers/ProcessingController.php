<?php

namespace app\modules\laundry\controllers;

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\am\models\Asset;
use app\modules\laundry\services\ProcessingService;
use Yii;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class ProcessingController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'new', 'asset-search', 'history'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['index', 'asset-search', 'history'], 'roles' => ['laundry.approve']],
                    ['allow' => true, 'actions' => ['register-machine', 'start', 'finish', 'abort', 'request-recovery'], 'roles' => ['laundry.manage']],
                    ['allow' => true, 'actions' => ['approve-recovery'], 'roles' => ['laundry.approve']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'register-machine' => ['POST'], 'start' => ['POST'],
                    'finish' => ['POST'], 'abort' => ['POST'],
                    'request-recovery' => ['POST'], 'approve-recovery' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex(string $stage = 'WASH')
    {
        if (!in_array($stage, ['WASH', 'DRY'], true)) {
            $stage = 'WASH';
        }
        $machines = (new Query())->select(['m.*', 'asset_name' => 'a.asset_name', 'asset_code' => 'a.code', 'lifecycle_status' => 'a.lifecycle_status'])
            ->from(['m' => 'laundry_machine'])->innerJoin(['a' => Asset::tableName()], 'a.id = m.asset_id')
            ->where(['m.machine_type' => $stage])
            ->orderBy(['a.code' => SORT_ASC])->all();
        $assetIds = array_column($machines, 'asset_id');
        $today = date('Y-m-d');

        // สรุป "วันนี้" ต่อเครื่อง (กะทัดรัด ไม่ยัดลิสต์เต็มในการ์ด) + รอบที่กำลังทำงาน
        $summaryByAsset = [];
        $runByAsset = [];
        if ($assetIds) {
            $summaryByAsset = (new Query())
                ->select([
                    'asset_id',
                    'times' => new Expression('COUNT(*)'),
                    'in_kg' => new Expression('COALESCE(SUM(input_kg),0)'),
                    'out_kg' => new Expression('COALESCE(SUM(output_kg),0)'),
                ])
                ->from('laundry_processing_batch')
                ->where(['stage' => $stage, 'asset_id' => $assetIds])
                ->andWhere(['between', 'started_at', $today . ' 00:00:00', $today . ' 23:59:59'])
                ->groupBy('asset_id')->indexBy('asset_id')->all();
            $runRows = (new Query())->from('laundry_processing_batch')
                ->where(['stage' => $stage, 'asset_id' => $assetIds, 'status' => 'RUNNING'])->all();
            foreach ($runRows as $b) {
                $runByAsset[$b['asset_id']] = $b;
            }
        }

        // ผ้ากู้คืนที่รออนุมัติ (bounded to-do list)
        $pendingRecoveries = (new Query())->select(['r.*', 'batch_no' => 'b.batch_no', 'linen_class' => 'b.linen_class', 'input_kg' => 'b.input_kg', 'asset_code' => 'a.code'])
            ->from(['r' => 'laundry_batch_recovery'])
            ->innerJoin(['b' => 'laundry_processing_batch'], 'b.id = r.aborted_batch_id')
            ->innerJoin(['a' => Asset::tableName()], 'a.id = b.asset_id')
            ->where(['b.stage' => $stage, 'r.status' => 'PENDING'])
            ->orderBy(['r.id' => SORT_DESC])->limit(50)->all();
        return $this->render('index', compact('machines', 'summaryByAsset', 'runByAsset', 'pendingRecoveries', 'stage'));
    }

    public function actionNew(string $stage = 'WASH', string $linenClass = 'SOILED', string $mode = 'NORMAL')
    {
        if (!in_array($stage, ['WASH', 'DRY'], true) || !in_array($linenClass, ['SOILED', 'INFECTIOUS'], true)
            || !in_array($mode, ['NORMAL', 'RECOVERY'], true)) {
            throw new InvalidArgumentException('ตัวกรองไม่ถูกต้อง');
        }
        $machines = (new Query())->select(['m.asset_id', 'm.capacity_kg', 'a.code', 'a.asset_name'])
            ->from(['m' => 'laundry_machine'])->innerJoin(['a' => Asset::tableName()], 'a.id = m.asset_id')
            ->where(['m.machine_type' => $stage, 'm.is_active' => 1])
            ->andWhere(['or', ['a.lifecycle_status' => null], ['a.lifecycle_status' => ['active', 'received']]])
            ->orderBy(['a.code' => SORT_ASC])->all();

        $sourceType = $mode === 'RECOVERY' ? 'RECOVERY' : ($stage === 'WASH' ? 'COLLECTION_WEIGHT' : 'WASH_BATCH');
        $usedQuery = (new Query())->select(['i.source_id', 'used_kg' => new \yii\db\Expression('SUM(i.allocated_kg)')])
            ->from(['i' => 'laundry_batch_input'])
            ->where(['i.source_type' => $sourceType])
            ->groupBy('i.source_id');
        if ($mode === 'RECOVERY') {
            $sources = (new Query())->select([
                'id' => 'r.id', 'kg' => 'r.measured_kg', 'used_kg' => new \yii\db\Expression('COALESCE(u.used_kg, 0)'),
                'label' => new \yii\db\Expression("CONCAT(b.batch_no, ' / กู้ผ้า ', r.id)"),
            ])->from(['r' => 'laundry_batch_recovery'])
                ->innerJoin(['b' => 'laundry_processing_batch'], 'b.id = r.aborted_batch_id')
                ->leftJoin(['u' => $usedQuery], 'u.source_id = r.id')
                ->where(['r.status' => 'APPROVED', 'r.outcome' => 'REPROCESS', 'b.stage' => $stage, 'b.linen_class' => $linenClass])
                ->andWhere('r.measured_kg > COALESCE(u.used_kg, 0)')
                ->orderBy(['r.id' => SORT_DESC])->limit(200)->all();
        } elseif ($stage === 'WASH') {
            $sources = (new Query())->select([
                'id' => 'w.id', 'kg' => 'w.net_kg', 'used_kg' => new \yii\db\Expression('COALESCE(u.used_kg, 0)'),
                'label' => new \yii\db\Expression("CONCAT(r.round_no, ' / ', d.name, ' / ', w.id)"),
            ])->from(['w' => 'laundry_collection_weight'])
                ->innerJoin(['s' => 'laundry_collection_stop'], 's.id = w.stop_id')
                ->innerJoin(['r' => 'laundry_collection_round'], 'r.id = s.round_id')
                ->leftJoin(['d' => 'tree'], 'd.id = s.department_id')
                ->leftJoin(['u' => $usedQuery], 'u.source_id = w.id')
                ->where(['r.status' => 'CONFIRMED', 'w.linen_class' => $linenClass])
                ->andWhere('w.net_kg > COALESCE(u.used_kg, 0)')
                ->orderBy(['w.id' => SORT_DESC])->limit(200)->all();
        } else {
            $sources = (new Query())->select([
                'id' => 'b.id', 'kg' => 'b.output_kg', 'used_kg' => new \yii\db\Expression('COALESCE(u.used_kg, 0)'),
                'label' => 'b.batch_no',
            ])->from(['b' => 'laundry_processing_batch'])
                ->leftJoin(['u' => $usedQuery], 'u.source_id = b.id')
                ->where(['b.stage' => 'WASH', 'b.status' => 'COMPLETED', 'b.linen_class' => $linenClass])
                ->andWhere('b.output_kg > COALESCE(u.used_kg, 0)')
                ->orderBy(['b.id' => SORT_DESC])->limit(200)->all();
        }
        return $this->render('new', compact('stage', 'linenClass', 'mode', 'machines', 'sources'));
    }

    /** ประวัติรอบเครื่อง — ช่วงวันที่ + เครื่อง + ค้นหา + แบ่งหน้า (รองรับข้อมูลเยอะ) */
    public function actionHistory(string $stage = 'WASH')
    {
        if (!in_array($stage, ['WASH', 'DRY'], true)) {
            $stage = 'WASH';
        }
        $req = Yii::$app->request;
        $from = AppHelper::convertToGregorian(trim((string) $req->get('from'))) ?: date('Y-m-01');
        $to = AppHelper::convertToGregorian(trim((string) $req->get('to'))) ?: date('Y-m-d');
        $q = trim((string) $req->get('q'));
        $assetId = (int) $req->get('asset_id');

        $query = (new Query())->select(['b.*', 'asset_name' => 'a.asset_name', 'asset_code' => 'a.code'])
            ->from(['b' => 'laundry_processing_batch'])->innerJoin(['a' => Asset::tableName()], 'a.id = b.asset_id')
            ->where(['b.stage' => $stage])
            ->andWhere(['between', 'b.started_at', $from . ' 00:00:00', $to . ' 23:59:59']);
        if ($assetId) {
            $query->andWhere(['b.asset_id' => $assetId]);
        }
        if ($q !== '') {
            $query->andWhere(['like', 'b.batch_no', $q]);
        }
        $query->orderBy(['b.id' => SORT_DESC]);
        $provider = new ActiveDataProvider(['query' => $query, 'pagination' => ['pageSize' => 30], 'sort' => false]);

        $machineOptions = [];
        foreach ((new Query())->select(['m.asset_id', 'code' => 'a.code', 'idx' => 'm.id'])
            ->from(['m' => 'laundry_machine'])->innerJoin(['a' => Asset::tableName()], 'a.id = m.asset_id')
            ->where(['m.machine_type' => $stage])->orderBy(['a.code' => SORT_ASC])->all() as $i => $row) {
            $label = ($stage === 'WASH' ? 'เครื่องซัก' : 'เครื่องอบ') . ' · ' . ($row['code'] ?: '#' . $row['asset_id']);
            $machineOptions[$row['asset_id']] = $label;
        }
        return $this->render('history', compact('provider', 'stage', 'from', 'to', 'q', 'assetId', 'machineOptions'));
    }

    /** ค้นหาครุภัณฑ์จากระบบทรัพย์สิน (สำหรับ Select2 ajax) */
    public function actionAssetSearch(string $q = '')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim($q);
        $rows = (new Query())->select(['id', 'code', 'asset_name'])
            ->from(Asset::tableName())
            ->andFilterWhere(['or', ['like', 'code', $q], ['like', 'asset_name', $q]])
            ->orderBy(['code' => SORT_ASC])->limit(20)->all();
        $results = [];
        foreach ($rows as $r) {
            $label = trim(($r['code'] ? $r['code'] . ' · ' : '') . ($r['asset_name'] ?? ''));
            $results[] = ['id' => (int) $r['id'], 'text' => $label ?: ('#' . $r['id'])];
        }
        return ['results' => $results];
    }

    public function actionRegisterMachine()
    {
        try {
            $assetId = (int) Yii::$app->request->post('asset_id');
            $asset = $assetId ? Asset::findOne($assetId) : Asset::findOne(['code' => trim((string) Yii::$app->request->post('asset_code'))]);
            if (!$asset) {
                throw new InvalidArgumentException('ไม่พบครุภัณฑ์นี้ในระบบทรัพย์สิน');
            }
            (new ProcessingService())->registerMachine((int) $asset->id,
                (string) Yii::$app->request->post('machine_type'),
                Yii::$app->request->post('capacity_kg'),
                Yii::$app->user->id ? (int) Yii::$app->user->id : null);
            Yii::$app->session->setFlash('success', 'ลงทะเบียนเครื่องเรียบร้อย');
        } catch (\Throwable $e) {
            $this->flashError($e);
        }
        return $this->redirect(['/laundry/setting/machine']);
    }

    public function actionStart()
    {
        $req = Yii::$app->request;
        $stage = (string) $req->post('stage');
        $class = (string) $req->post('linen_class');
        $uid = Yii::$app->user->id ? (int) Yii::$app->user->id : null;
        try {
            if ($req->post('input_kg') !== null) {
                // flow ง่าย: เครื่อง + กลุ่มผ้า + กก. (ไม่ผูกแหล่งผ้า)
                (new ProcessingService())->startSimple($stage, (int) $req->post('asset_id'), $class,
                    $req->post('input_kg'), trim((string) $req->post('program')), $uid);
            } else {
                // flow เดิม (recovery จากหน้า /new)
                $mode = (string) $req->post('mode', 'NORMAL');
                (new ProcessingService())->start($stage, (int) $req->post('asset_id'), $class,
                    [(int) $req->post('source_id') => $req->post('allocated_kg')],
                    trim((string) $req->post('program')), $uid, $mode);
            }
            Yii::$app->session->setFlash('success', 'เริ่มรอบเครื่องแล้ว');
        } catch (\Throwable $e) {
            $this->flashError($e);
        }
        return $this->redirect(['index']);
    }

    public function actionFinish(int $id)
    {
        try {
            (new ProcessingService())->finish($id, Yii::$app->request->post('output_kg'));
            Yii::$app->session->setFlash('success', 'ปิดรอบเครื่องแล้ว');
        } catch (\Throwable $e) {
            $this->flashError($e);
        }
        return $this->redirect(['index']);
    }

    public function actionAbort(int $id)
    {
        try {
            (new ProcessingService())->abort($id, (string) Yii::$app->request->post('reason'));
            Yii::$app->session->setFlash('success', 'หยุดรอบเครื่องแล้ว น้ำหนักต้นทางยังถูกกันไว้จนกว่าจะตรวจสอบผ้า');
        } catch (\Throwable $e) {
            $this->flashError($e);
        }
        return $this->redirect(['index']);
    }

    public function actionRequestRecovery(int $id)
    {
        try {
            (new ProcessingService())->requestRecovery($id,
                (string) Yii::$app->request->post('outcome'),
                Yii::$app->request->post('measured_kg'),
                (string) Yii::$app->request->post('evidence'), (int) Yii::$app->user->id);
            Yii::$app->session->setFlash('success', 'ส่งผลตรวจผ้าจากรอบหยุดเพื่ออนุมัติแล้ว');
        } catch (\Throwable $e) {
            $this->flashError($e);
        }
        return $this->redirect(['index']);
    }

    public function actionApproveRecovery(int $id)
    {
        try {
            (new ProcessingService())->approveRecovery($id, (int) Yii::$app->user->id);
            Yii::$app->session->setFlash('success', 'อนุมัติผลตรวจผ้าจากรอบหยุดแล้ว');
        } catch (\Throwable $e) {
            $this->flashError($e);
        }
        return $this->redirect(['index']);
    }

    private function flashError(\Throwable $e): void
    {
        Yii::$app->session->setFlash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'บันทึกไม่สำเร็จ กรุณาตรวจสอบข้อมูล');
        Yii::error($e, __METHOD__);
    }
}
