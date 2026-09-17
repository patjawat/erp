<?php

namespace app\modules\laundry\controllers;

use app\components\AppHelper;
use app\modules\hr\models\Organization;
use app\modules\laundry\services\CollectionReport;
use app\modules\laundry\services\CollectionService;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CollectionController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view', 'report', 'inspect'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['create', 'add-stop', 'save', 'weigh', 'confirm'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['POST'], 'add-stop' => ['POST'], 'save' => ['POST'], 'weigh' => ['POST'], 'confirm' => ['POST']],
            ],
        ];
    }

    /** รับผ้า (mobile) — การ์ดหน่วยงาน + ยอดวันนั้น */
    public function actionIndex()
    {
        $dateInput = trim((string) Yii::$app->request->get('date'));
        $date = $dateInput !== '' ? (AppHelper::convertToGregorian($dateInput) ?: date('Y-m-d')) : date('Y-m-d');

        $units = \app\modules\laundry\models\LaundryUnit::find()
            ->where(['is_active' => 1])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        $treeIds = array_map(static fn($u) => $u->tree_id, $units);
        $names = $treeIds
            ? Organization::find()->select(['name', 'id'])->where(['id' => $treeIds])->indexBy('id')->column()
            : [];

        // ยอดต่อหน่วยงานของวันนั้น: จำนวนครั้ง + ผ้าเปื้อน/ผ้าติดเชื้อ (กก.)
        $totals = [];
        if ($treeIds) {
            $rows = (new Query())
                ->select([
                    'department_id' => 's.department_id',
                    'times' => new \yii\db\Expression('COUNT(DISTINCT s.id)'),
                    'soiled' => new \yii\db\Expression("COALESCE(SUM(CASE WHEN w.linen_class='SOILED' THEN w.net_kg ELSE 0 END),0)"),
                    'infectious' => new \yii\db\Expression("COALESCE(SUM(CASE WHEN w.linen_class='INFECTIOUS' THEN w.net_kg ELSE 0 END),0)"),
                ])
                ->from(['s' => 'laundry_collection_stop'])
                ->innerJoin(['r' => 'laundry_collection_round'], 'r.id = s.round_id')
                ->leftJoin(['w' => 'laundry_collection_weight'], 'w.stop_id = s.id')
                ->where(['r.collection_date' => $date, 's.department_id' => $treeIds])
                ->groupBy('s.department_id')->indexBy('department_id')->all();
            $totals = $rows;
        }
        $staffName = $this->currentStaffName();
        return $this->render('index', compact('date', 'units', 'names', 'totals', 'staffName'));
    }

    private function currentStaffName(): string
    {
        $emp = \app\modules\hr\models\Employees::find()->where(['user_id' => Yii::$app->user->id])->one();
        return $emp ? (string) $emp->fullname : (string) (Yii::$app->user->identity->username ?? '');
    }

    /** บันทึกรับผ้า 1 รายการ (mobile) — หา/สร้างรอบรายวันให้เอง */
    public function actionSave()
    {
        $req = Yii::$app->request;
        $date = AppHelper::convertToGregorian(trim((string) $req->post('collected_date'))) ?: date('Y-m-d');
        try {
            $svc = new CollectionService();
            $roundId = $svc->findOrCreateDailyRound($date, Yii::$app->user->id ? (int) Yii::$app->user->id : null);
            $time = trim((string) $req->post('collected_time')) ?: date('H:i');
            $seq = (int) $req->post('round_seq') ?: null;
            $svc->addEntry($roundId, (int) $req->post('department_id'), $date . ' ' . $time,
                $req->post('soiled_kg'), $req->post('infectious_kg'),
                Yii::$app->user->id ? (int) Yii::$app->user->id : null, $seq);
            Yii::$app->session->setFlash('success', 'บันทึกรับผ้าแล้ว');
        } catch (\Throwable $e) {
            $this->fail($e, ['index', 'date' => AppHelper::convertToThai($date)]);
        }
        return $this->redirect(['index', 'date' => AppHelper::convertToThai($date)]);
    }

    /** ตรวจรับผ้า: รอบที่ยังไม่ยืนยัน (OPEN) รอตรวจสอบน้ำหนัก/ถุงแล้วยืนยันรับเข้า */
    public function actionInspect()
    {
        $provider = new \yii\data\ActiveDataProvider([
            'query' => \app\modules\laundry\models\CollectionRound::find()
                ->where(['status' => 'OPEN'])
                ->orderBy(['collection_date' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        // จำนวนหน่วยงาน/ยอดชั่งต่อรอบ เพื่อดูความพร้อมตรวจรับ
        $roundIds = array_map(static fn($r) => $r->id, $provider->getModels());
        $stats = [];
        if ($roundIds) {
            $stats = (new Query())
                ->select([
                    'round_id' => 's.round_id',
                    'stops' => new \yii\db\Expression('COUNT(DISTINCT s.id)'),
                    'weighed' => new \yii\db\Expression('COUNT(DISTINCT w.stop_id)'),
                    'kg' => new \yii\db\Expression('COALESCE(SUM(w.net_kg),0)'),
                ])
                ->from(['s' => 'laundry_collection_stop'])
                ->leftJoin(['w' => 'laundry_collection_weight'], 'w.stop_id = s.id')
                ->where(['s.round_id' => $roundIds])
                ->groupBy('s.round_id')->indexBy('round_id')->all();
        }
        return $this->render('inspect', compact('provider', 'stats'));
    }

    public function actionView(int $id)
    {
        $round = $this->round($id);
        $stops = (new Query())->select(['s.*', 'department_name' => 'd.name'])
            ->from(['s' => 'laundry_collection_stop'])
            ->leftJoin(['d' => Organization::tableName()], 'd.id = s.department_id')
            ->where(['s.round_id' => $id])->orderBy(['s.id' => SORT_ASC])->all();
        $weights = (new Query())->from('laundry_collection_weight')->where(['stop_id' => array_column($stops, 'id')])->all();
        $byStop = [];
        foreach ($weights as $weight) {
            $byStop[$weight['stop_id']][$weight['linen_class']] = $weight;
        }
        $departments = Organization::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
        return $this->render('view', compact('round', 'stops', 'byStop', 'departments'));
    }

    public function actionCreate()
    {
        try {
            $dateInput = trim((string) Yii::$app->request->post('collection_date'));
            $collectionDate = $dateInput !== '' ? AppHelper::convertToGregorian($dateInput) : date('Y-m-d');
            $id = (new CollectionService())->createRound(
                (string) $collectionDate,
                Yii::$app->user->id ? (int) Yii::$app->user->id : null,
                trim((string) Yii::$app->request->post('note')),
                Yii::$app->user->id ? (int) Yii::$app->user->id : null
            );
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['index']);
        }
    }

    public function actionAddStop(int $id)
    {
        try {
            $req = Yii::$app->request;
            $dateG = AppHelper::convertToGregorian(trim((string) $req->post('collected_date')));
            $time = trim((string) $req->post('collected_time')) ?: date('H:i');
            $collectedAt = ($dateG ?: date('Y-m-d')) . ' ' . $time;
            (new CollectionService())->addEntry($id, (int) $req->post('department_id'),
                $collectedAt,
                $req->post('soiled_kg'),
                $req->post('infectious_kg'),
                Yii::$app->user->id ? (int) Yii::$app->user->id : null);
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['view', 'id' => $id]);
        }
    }

    public function actionWeigh(int $id, int $stopId)
    {
        try {
            (new CollectionService())->weigh($id, $stopId, (string) Yii::$app->request->post('linen_class'),
                Yii::$app->request->post('gross_kg'), Yii::$app->request->post('tare_kg'),
                trim((string) Yii::$app->request->post('scale_ref')),
                Yii::$app->user->id ? (int) Yii::$app->user->id : null);
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['view', 'id' => $id]);
        }
    }

    public function actionConfirm(int $id)
    {
        try {
            (new CollectionService())->confirm($id, Yii::$app->user->id ? (int) Yii::$app->user->id : null);
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['view', 'id' => $id]);
        }
    }

    public function actionReport()
    {
        $year = (int) Yii::$app->request->get('year', date('Y'));
        if ($year < 2000 || $year > 2200) {
            throw new InvalidArgumentException('ปีไม่ถูกต้อง');
        }
        $departmentId = (int) Yii::$app->request->get('department_id');
        $rows = (new CollectionReport())->monthly($year . '-01-01', $year . '-12-31', $departmentId ?: null);
        $departments = Organization::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
        return $this->render('report', compact('rows', 'year', 'departmentId', 'departments'));
    }

    private function round(int $id): array
    {
        $round = (new Query())->from('laundry_collection_round')->where(['id' => $id])->one();
        if (!$round) {
            throw new NotFoundHttpException('ไม่พบรอบเก็บผ้า');
        }
        return $round;
    }

    private function fail(\Throwable $e, array $target)
    {
        Yii::$app->session->setFlash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'บันทึกไม่สำเร็จ กรุณาตรวจสอบข้อมูล');
        Yii::error($e, __METHOD__);
        return $this->redirect($target);
    }
}
