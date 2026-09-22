<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinanceBankReconcile;
use app\modules\finance\models\FinanceBankReconcileItem;
use app\modules\finance\models\FinanceCashAccount;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * งบพิสูจน์ยอดเงินฝากธนาคาร (Bank Reconciliation) — ทะเบียนคุม 2.3 ส่วนที่ขาด
 */
class BankReconcileController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view', 'print'], 'roles' => ['financeView']],
                    ['allow' => true, 'actions' => ['create', 'update', 'add-item', 'delete-item', 'delete', 'toggle-status'], 'roles' => ['financeOperate']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['add-item' => ['post'], 'delete-item' => ['post'], 'delete' => ['post'], 'toggle-status' => ['post']],
            ],
        ]);
    }

    private static function currentFiscalYear(): int
    {
        $year = (int) date('Y') + 543;
        return (int) date('n') >= 10 ? $year + 1 : $year;
    }

    public function actionIndex()
    {
        $fy = (int) Yii::$app->request->get('fiscal_year', self::currentFiscalYear());
        $list = FinanceBankReconcile::find()->with('account')
            ->where(['fiscal_year' => $fy])
            ->orderBy(['period_month' => SORT_DESC, 'id' => SORT_DESC])->all();
        return $this->render('index', [
            'list' => $list,
            'fy' => $fy,
            'fiscalYears' => $this->fiscalYearOptions(),
        ]);
    }

    public function actionCreate()
    {
        $model = new FinanceBankReconcile([
            'fiscal_year' => self::currentFiscalYear(),
            'period_month' => (int) date('n'),
            'statement_date' => date('Y-m-d'),
        ]);
        if ($this->saveHeader($model)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($this->saveHeader($model)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('form', ['model' => $model]);
    }

    private function saveHeader(FinanceBankReconcile $model): bool
    {
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            $model->statement_date = AppHelper::normalizeDateToDb($post['FinanceBankReconcile']['statement_date'] ?? null) ?: null;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกงบพิสูจน์ยอดเรียบร้อย');
                return true;
            }
        }
        return false;
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'items' => $model->getItems()->all(),
            'newItem' => new FinanceBankReconcileItem(['reconcile_id' => $id]),
        ]);
    }

    public function actionAddItem(int $id)
    {
        $model = $this->findModel($id);
        $item = new FinanceBankReconcileItem(['reconcile_id' => $model->id]);
        if ($item->load(Yii::$app->request->post()) && $item->save()) {
            Yii::$app->session->setFlash('success', 'เพิ่มรายการกระทบยอดแล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'เพิ่มไม่สำเร็จ: ' . implode(' ', $item->getFirstErrors()));
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionDeleteItem(int $id)
    {
        $item = FinanceBankReconcileItem::findOne($id);
        if ($item === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        $recId = $item->reconcile_id;
        $item->delete();
        return $this->redirect(['view', 'id' => $recId]);
    }

    public function actionToggleStatus(int $id)
    {
        $model = $this->findModel($id);
        $model->status = $model->status === FinanceBankReconcile::STATUS_DONE
            ? FinanceBankReconcile::STATUS_DRAFT : FinanceBankReconcile::STATUS_DONE;
        $model->save(false);
        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);
        $fy = $model->fiscal_year;
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบงบพิสูจน์ยอดเรียบร้อย');
        return $this->redirect(['index', 'fiscal_year' => $fy]);
    }

    public function actionPrint(int $id)
    {
        $model = $this->findModel($id);
        return $this->renderPartial('print', [
            'model' => $model,
            'items' => $model->getItems()->all(),
        ]);
    }

    private function findModel(int $id): FinanceBankReconcile
    {
        $model = FinanceBankReconcile::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบงบพิสูจน์ยอด');
        }
        return $model;
    }

    private function fiscalYearOptions(): array
    {
        $cur = self::currentFiscalYear();
        $years = [];
        for ($i = 0; $i <= 3; $i++) {
            $years[] = $cur - $i;
        }
        return $years;
    }
}
