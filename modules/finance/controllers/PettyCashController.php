<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinancePettyCash;
use app\modules\finance\models\FinancePettyCashTxn;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * เงินสดย่อย / เงินทดรองจ่าย (imprest) — จัดการกอง + บันทึกการเคลื่อนไหว
 * ทะเบียนคุมรูปแบบทางการ (running balance) ดูที่ /finance/register/view?key=petty_cash
 */
class PettyCashController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['financeView']],
                    ['allow' => true, 'actions' => ['create', 'update', 'add-txn', 'delete-txn', 'delete'], 'roles' => ['financeOperate']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['add-txn' => ['post'], 'delete-txn' => ['post'], 'delete' => ['post']],
            ],
        ]);
    }

    public function actionIndex()
    {
        $funds = FinancePettyCash::find()->orderBy(['is_active' => SORT_DESC, 'name' => SORT_ASC])->all();
        return $this->render('index', ['funds' => $funds]);
    }

    public function actionCreate()
    {
        $model = new FinancePettyCash(['is_active' => 1, 'fiscal_year' => self::currentFiscalYear()]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'สร้างกองเงินสดย่อยเรียบร้อย');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findFund($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกการแก้ไขเรียบร้อย');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionView(int $id)
    {
        $fund = $this->findFund($id);
        $txns = $fund->getTxns()->all();
        return $this->render('view', [
            'fund' => $fund,
            'txns' => $txns,
            'newTxn' => new FinancePettyCashTxn(['petty_cash_id' => $id, 'doc_date' => date('Y-m-d')]),
        ]);
    }

    public function actionAddTxn(int $id)
    {
        $fund = $this->findFund($id);
        $txn = new FinancePettyCashTxn(['petty_cash_id' => $fund->id]);
        $post = Yii::$app->request->post();
        if ($txn->load($post)) {
            $txn->doc_date = AppHelper::normalizeDateToDb($post['FinancePettyCashTxn']['doc_date'] ?? null);
            if ($txn->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกรายการเรียบร้อย');
            } else {
                Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $txn->getFirstErrors()));
            }
        }
        return $this->redirect(['view', 'id' => $fund->id]);
    }

    public function actionDeleteTxn(int $id)
    {
        $txn = FinancePettyCashTxn::findOne($id);
        if ($txn === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $fundId = $txn->petty_cash_id;
        $txn->delete();
        Yii::$app->session->setFlash('success', 'ลบรายการเรียบร้อย');
        return $this->redirect(['view', 'id' => $fundId]);
    }

    public function actionDelete(int $id)
    {
        $fund = $this->findFund($id);
        if ($fund->getTxns()->count() > 0) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ — กองนี้มีรายการเคลื่อนไหวแล้ว');
            return $this->redirect(['view', 'id' => $id]);
        }
        $fund->delete();
        Yii::$app->session->setFlash('success', 'ลบกองเงินสดย่อยเรียบร้อย');
        return $this->redirect(['index']);
    }

    private function findFund(int $id): FinancePettyCash
    {
        $model = FinancePettyCash::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบกองเงินสดย่อย');
        }
        return $model;
    }

    private static function currentFiscalYear(): int
    {
        $year = (int) date('Y') + 543;
        return (int) date('n') >= 10 ? $year + 1 : $year;
    }
}
