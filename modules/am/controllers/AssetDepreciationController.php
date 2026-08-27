<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\am\models\AccountingPeriod;
use app\modules\am\models\AssetDepreciation;
use app\modules\am\models\DepreciationProfile;
use app\modules\am\services\DepreciationRunService;
use app\modules\am\services\DepreciationPostingService;
use app\modules\am\services\DepreciationSnapshotService;

/**
 * ประมวลผล/ตรวจสอบ/ปรับปรุงค่าเสื่อมรายทรัพย์สิน (screens 4, 5, 8)
 */
class AssetDepreciationController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['overview', 'preview-asset', 'asset-search', 'run'], 'roles' => ['depreciationView']],
                    // ตรึงเกณฑ์ให้ทะเบียน = งานตั้งค่า · คำนวณ/ปรับปรุง/กลับรายการ = งานบัญชี
                    ['allow' => true, 'actions' => ['backfill', 'backfill-apply'], 'roles' => ['depreciationSetup']],
                    ['allow' => true, 'actions' => ['save', 'adjustment', 'reverse'], 'roles' => ['depreciationRun']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['POST'],
                    'adjustment' => ['POST'],
                    'reverse' => ['POST'],
                    'backfill-apply' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * ภาพรวมค่าเสื่อม — จุดเริ่มต้น: บอกลำดับงาน 3 ขั้น + สถานะความพร้อมของแต่ละขั้น
     */
    public function actionOverview()
    {
        $profileActive = (int) DepreciationProfile::find()
            ->where(['status' => DepreciationProfile::STATUS_ACTIVE])->count();

        // ระดับที่ผูกเกณฑ์ไว้ (ประเภท/หมวด/รายการ ใน data_json) + รายชิ้น (asset.depreciation_profile_id)
        $boundLevels = (int) Categorise::find()
            ->where(['name' => ['asset_type', 'asset_category', 'asset_item']])
            ->andWhere(['like', 'data_json', 'depreciation_profile_id'])
            ->count();

        // นับจากชุดเดียวกับที่ตัวคำนวณใช้จริง เพื่อให้ตัวเลขตรงกับหน้า "ตรึงเกณฑ์ให้ทะเบียนเดิม"
        $run = new DepreciationRunService();
        $assetTotal = (int) $run->eligibleQuery()->count();
        $assetSnapped = (int) $run->eligibleQuery()
            ->andWhere(['not', ['depreciation_profile_id' => null]])->count();

        // งวดบัญชีปีงบล่าสุด + สรุปสถานะงวดรายเดือน
        $latestFy = (int) AccountingPeriod::find()->max('fiscal_year');
        $monthCounts = [];
        if ($latestFy) {
            foreach (AccountingPeriod::find()
                ->where(['fiscal_year' => $latestFy, 'period_type' => AccountingPeriod::TYPE_MONTH])
                ->all() as $p) {
                $monthCounts[$p->status] = ($monthCounts[$p->status] ?? 0) + 1;
            }
        }

        return $this->render('overview', [
            'profileActive' => $profileActive,
            'boundLevels' => $boundLevels + $assetSnapped,
            'assetTotal' => $assetTotal,
            'assetSnapped' => $assetSnapped,
            'latestFy' => $latestFy,
            'monthCounts' => $monthCounts,
        ]);
    }

    /**
     * ตรึงเกณฑ์ค่าเสื่อมย้อนหลังให้ทะเบียนเดิม — หน้าทดลอง (dry-run) แสดงผลก่อนบันทึกจริง
     */
    public function actionBackfill($force = 0)
    {
        $force = (bool) $force;
        $result = (new DepreciationSnapshotService())->backfill(false, $force);

        return $this->render('backfill', [
            'result' => $result,
            'force' => $force,
            'profileNames' => $this->profileNameMap($result['by_profile']),
        ]);
    }

    /**
     * บันทึก snapshot จริง (ทำงานใน transaction เดียว)
     */
    public function actionBackfillApply()
    {
        $force = (bool) Yii::$app->request->post('force');
        $result = (new DepreciationSnapshotService())->backfill(true, $force);

        if ($result['error'] !== null) {
            Yii::$app->session->setFlash('error', 'ตรึงเกณฑ์ไม่สำเร็จ: ' . $result['error']);
        } else {
            Yii::$app->session->setFlash('success', sprintf(
                'ตรึงเกณฑ์ค่าเสื่อมให้ทรัพย์สิน %s รายการเรียบร้อย (ตรวจแล้ว %s รายการ)',
                number_format($result['applied']),
                number_format($result['total'])
            ));
        }

        return $this->redirect(['backfill']);
    }

    /**
     * map profile_id => "รหัส — ชื่อ" สำหรับแสดงผลสรุป
     *
     * @param array<string,int> $byProfile
     * @return array<int,string>
     */
    private function profileNameMap(array $byProfile): array
    {
        $ids = array_map('intval', array_keys($byProfile));
        if (!$ids) {
            return [];
        }
        $names = [];
        foreach (DepreciationProfile::find()->where(['id' => $ids])->all() as $p) {
            $names[$p->id] = $p->code . ' — ' . $p->name;
        }
        return $names;
    }

    /**
     * ตรวจสอบผลก่อนบันทึก (dry-run) ของงวดเดือน (screen 5)
     */
    public function actionRun($period_id)
    {
        $period = $this->findPeriod($period_id);
        $result = (new DepreciationRunService())->runForPeriod($period, false, Yii::$app->user->id);

        return $this->render('run', [
            'period' => $period,
            'result' => $result,
            'canRun' => Yii::$app->user->can('depreciationRun'),
        ]);
    }

    /**
     * บันทึกผลคำนวณลง DB (status=calculated)
     */
    public function actionSave($period_id)
    {
        $period = $this->findPeriod($period_id);
        $result = (new DepreciationRunService())->runForPeriod($period, true, Yii::$app->user->id);
        Yii::$app->session->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        return $this->redirect(['run', 'period_id' => $period->id]);
    }

    /**
     * ค้นครุภัณฑ์ด้วยรหัส/ชื่อ (JSON สำหรับ Select2) — ใช้แทนการกรอก asset.id
     */
    public function actionAssetSearch($q = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = trim((string) $q);
        $query = Asset::find()
            ->select(['id', 'code', 'fsn_number', 'asset_name'])
            ->where(['asset_group_id' => 4, 'deleted_at' => null]);
        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'code', $q],
                ['like', 'fsn_number', $q],
                ['like', 'asset_name', $q],
            ]);
        }
        $results = [];
        foreach ($query->orderBy(['id' => SORT_DESC])->limit(20)->asArray()->all() as $a) {
            $label = trim(($a['code'] ?: $a['fsn_number']) . ' — ' . $a['asset_name']);
            $results[] = ['id' => $a['id'], 'text' => $label !== '—' ? $label : ('#' . $a['id'])];
        }
        return ['results' => $results];
    }

    /**
     * ทดลองคำนวณค่าเสื่อมของทรัพย์สิน 1 ชิ้น (schedule เต็ม) (screen 4)
     */
    public function actionPreviewAsset($asset_id = null)
    {
        $asset = null;
        $schedule = null;
        if ($asset_id) {
            $asset = Asset::findOne($asset_id);
            if ($asset) {
                $schedule = (new DepreciationRunService())->previewForAsset($asset);
            }
        }
        return $this->render('preview-asset', [
            'asset' => $asset,
            'schedule' => $schedule,
            'assetId' => $asset_id,
        ]);
    }

    /**
     * สร้างรายการปรับปรุง (screen 8)
     */
    public function actionAdjustment($period_id)
    {
        $period = $this->findPeriod($period_id);
        $assetId = (int) Yii::$app->request->post('asset_id');
        $amount = (float) Yii::$app->request->post('adjustment_amount');
        $note = Yii::$app->request->post('note');

        $res = (new DepreciationPostingService())->createAdjustment($assetId, $period->id, $amount, $note, Yii::$app->user->id);
        Yii::$app->session->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        return $this->redirect(['run', 'period_id' => $period->id]);
    }

    /**
     * กลับรายการค่าเสื่อมที่ post แล้ว
     */
    public function actionReverse($id)
    {
        $row = AssetDepreciation::findOne($id);
        if (!$row) {
            throw new NotFoundHttpException('ไม่พบรายการค่าเสื่อม');
        }
        $note = Yii::$app->request->post('note');
        $res = (new DepreciationPostingService())->reverse($row, Yii::$app->user->id, $note);
        Yii::$app->session->setFlash($res['success'] ? 'success' : 'error', $res['message']);
        return $this->redirect(['run', 'period_id' => $row->accounting_period_id]);
    }

    protected function findPeriod($id)
    {
        if (($model = AccountingPeriod::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('ไม่พบงวดบัญชี');
    }
}
