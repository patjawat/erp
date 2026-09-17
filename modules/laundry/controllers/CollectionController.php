<?php

namespace app\modules\laundry\controllers;

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
                    ['allow' => true, 'actions' => ['index', 'view', 'report'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['create', 'add-stop', 'weigh', 'confirm'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['POST'], 'add-stop' => ['POST'], 'weigh' => ['POST'], 'confirm' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $provider = new \yii\data\ActiveDataProvider([
            'query' => \app\modules\laundry\models\CollectionRound::find()->orderBy(['collection_date' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);
        return $this->render('index', ['provider' => $provider]);
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
            $id = (new CollectionService())->createRound(
                (string) Yii::$app->request->post('collection_date', date('Y-m-d')),
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
            (new CollectionService())->addStop($id, (int) Yii::$app->request->post('department_id'),
                (int) Yii::$app->request->post('soiled_bag_count'),
                (int) Yii::$app->request->post('infectious_bag_count'),
                (string) Yii::$app->request->post('collected_at'));
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
