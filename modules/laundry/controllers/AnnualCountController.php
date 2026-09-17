<?php

namespace app\modules\laundry\controllers;

use app\modules\hr\models\Organization;
use app\modules\laundry\services\AnnualCountService;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AnnualCountController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['laundry.approve']],
                    ['allow' => true, 'actions' => ['open', 'record', 'cancel'], 'roles' => ['laundry.manage']],
                    ['allow' => true, 'actions' => ['approve'], 'roles' => ['laundry.approve']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['open' => ['POST'], 'record' => ['POST'], 'cancel' => ['POST'], 'approve' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $counts = (new Query())->select(['c.*', 'department_name' => 'd.name'])
            ->from(['c' => 'laundry_annual_count'])->leftJoin(['d' => 'tree'], 'd.id = c.department_id')
            ->orderBy(['c.count_year' => SORT_DESC, 'c.id' => SORT_DESC])->limit(100)->all();
        $departments = Organization::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
        return $this->render('index', compact('counts', 'departments'));
    }

    public function actionView(int $id)
    {
        $count = (new Query())->select(['c.*', 'department_name' => 'd.name'])
            ->from(['c' => 'laundry_annual_count'])->leftJoin(['d' => 'tree'], 'd.id = c.department_id')
            ->where(['c.id' => $id])->one();
        if (!$count) {
            throw new NotFoundHttpException('ไม่พบรอบสอบยอด');
        }
        $lines = (new Query())->select(['l.*', 'item_name' => 'i.item_name'])
            ->from(['l' => 'laundry_annual_count_line'])->innerJoin(['i' => 'laundry_item'], 'i.id = l.item_id')
            ->where(['l.count_id' => $id])->orderBy(['i.item_name' => SORT_ASC])->all();
        return $this->render('view', compact('count', 'lines'));
    }

    public function actionOpen()
    {
        try {
            $id = (new AnnualCountService())->open((int) Yii::$app->request->post('department_id'),
                (int) Yii::$app->request->post('count_year'), $this->userId());
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['index']);
        }
    }

    public function actionRecord(int $id)
    {
        try {
            (new AnnualCountService())->record($id, (int) Yii::$app->request->post('item_id'),
                (int) Yii::$app->request->post('on_hand_qty'),
                (int) Yii::$app->request->post('verified_in_transit_qty'),
                (string) Yii::$app->request->post('transit_evidence'),
                (string) Yii::$app->request->post('variance_reason'));
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['view', 'id' => $id]);
        }
    }

    public function actionApprove(int $id)
    {
        try {
            (new AnnualCountService())->approve($id, (int) $this->userId());
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['view', 'id' => $id]);
        }
    }

    public function actionCancel(int $id)
    {
        try {
            (new AnnualCountService())->cancel($id, (int) $this->userId(), (string) Yii::$app->request->post('reason'));
            return $this->redirect(['view', 'id' => $id]);
        } catch (\Throwable $e) {
            return $this->fail($e, ['view', 'id' => $id]);
        }
    }

    private function fail(\Throwable $e, array $target)
    {
        Yii::$app->session->setFlash('error', $e instanceof InvalidArgumentException ? $e->getMessage() : 'บันทึกไม่สำเร็จ กรุณาตรวจสอบข้อมูล');
        Yii::error($e, __METHOD__);
        return $this->redirect($target);
    }

    private function userId(): ?int
    {
        return Yii::$app->user->id ? (int) Yii::$app->user->id : null;
    }
}
