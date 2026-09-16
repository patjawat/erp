<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinanceCashAccount;
use app\modules\finance\models\FinanceCashAccountBalance;
use app\modules\finance\models\FinanceCashTransfer;
use app\modules\finance\models\FinanceCashTxn;
use app\modules\finance\services\CashReportService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Master Data บัญชีเงิน (เฟส 4) — บัญชีธนาคาร / เงินฝากคลัง / เงินคงเหลือสะสม
 * ยอดคงเหลือกรอกเองต่อปีงบ (ปิดงบปีแล้วตั้งยอดใหม่ ยกไปปีถัดไป)
 */
class AccountController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['financeOperate']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['save' => ['post'], 'delete' => ['post'], 'reorder' => ['post'], 'transfer-save' => ['post']],
            ],
        ]);
    }

    public function actionBank($bank = null, $year = null)
    {
        return $this->accountList(FinanceCashAccount::TYPE_BANK, 'บัญชีธนาคาร', 'bank', $bank, $year);
    }

    public function actionTreasury($year = null)
    {
        return $this->accountList(FinanceCashAccount::TYPE_TREASURY, 'บัญชีเงินฝากคลัง', 'treasury', null, $year);
    }

    private function accountList(string $type, string $title, string $active, ?string $bank, ?string $year)
    {
        $year = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        $query = FinanceCashAccount::find()->where(['account_type' => $type])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
        if ($type === FinanceCashAccount::TYPE_BANK && $bank) {
            $query->andWhere(['bank_name' => $bank]);
        }
        return $this->render('account_list', [
            'type' => $type,
            'title' => $title,
            'active' => $active,
            'accounts' => $query->all(),
            'year' => $year,
            'bank' => $bank,
        ]);
    }

    public function actionSave()
    {
        $post = Yii::$app->request->post();
        $id = (int) ($post['id'] ?? 0);
        $model = $id ? FinanceCashAccount::findOne($id) : new FinanceCashAccount();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบบัญชี');
        }
        $type = in_array($post['account_type'] ?? '', [FinanceCashAccount::TYPE_BANK, FinanceCashAccount::TYPE_TREASURY, FinanceCashAccount::TYPE_CASH], true)
            ? $post['account_type'] : FinanceCashAccount::TYPE_BANK;
        $model->account_type = $type;
        $model->bank_name = trim((string) ($post['bank_name'] ?? '')) ?: null;
        $model->branch = trim((string) ($post['branch'] ?? '')) ?: null;
        $model->deposit_type = trim((string) ($post['deposit_type'] ?? '')) ?: null;
        $model->code = trim((string) ($post['code'] ?? '')) ?: null;
        $model->name = trim((string) ($post['name'] ?? ''));
        $model->is_promptpay = !empty($post['is_promptpay']) ? 1 : 0;
        $model->is_credit = !empty($post['is_credit']) ? 1 : 0;

        if ($model->save()) {
            // ยอดคงเหลือรายปี (ถ้ากรอกมา)
            $by = (int) ($post['balance_year'] ?? 0);
            $ba = $post['balance_amount'] ?? '';
            if ($by && $ba !== '' && $ba !== null) {
                $bal = FinanceCashAccountBalance::findOne(['account_id' => $model->id, 'fiscal_year' => $by])
                    ?: new FinanceCashAccountBalance(['account_id' => $model->id, 'fiscal_year' => $by]);
                $bal->amount = $ba;
                $bal->save();
            }
            Yii::$app->session->setFlash('success', 'บันทึกบัญชีเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect([$type === FinanceCashAccount::TYPE_TREASURY ? 'treasury' : 'bank']);
    }

    public function actionDelete()
    {
        $model = FinanceCashAccount::findOne((int) Yii::$app->request->post('id'));
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบบัญชี');
        }
        $type = $model->account_type;
        $used = FinanceCashTxn::find()->where(['money_account_id' => $model->id])->exists();
        if ($used) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะมีรายการรับ-จ่ายใช้บัญชีนี้อยู่ (ปิดใช้งานแทนได้)');
        } else {
            $model->delete(); // balance cascade
            Yii::$app->session->setFlash('success', 'ลบบัญชีเรียบร้อย');
        }
        return $this->redirect([$type === FinanceCashAccount::TYPE_TREASURY ? 'treasury' : 'bank']);
    }

    public function actionReorder()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $ids = (array) Yii::$app->request->post('ids', []);
        foreach ($ids as $i => $id) {
            FinanceCashAccount::updateAll(['sort_order' => (int) $i], ['id' => (int) $id]);
        }
        return ['ok' => true];
    }

    /** เงินคงเหลือสะสม — รวมทุกบัญชี (เงินสด/เงินฝากคลัง/ธนาคาร) พร้อมยอดของปีงบ */
    public function actionSummary($year = null)
    {
        $year = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        $groups = [];
        foreach ([
            FinanceCashAccount::TYPE_CASH => 'เงินสด',
            FinanceCashAccount::TYPE_TREASURY => 'เงินฝากคลัง',
            FinanceCashAccount::TYPE_BANK => 'บัญชีเงินฝากธนาคาร',
        ] as $type => $label) {
            $accounts = FinanceCashAccount::find()->where(['account_type' => $type])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
            $rows = [];
            $sum = 0.0;
            foreach ($accounts as $a) {
                $bal = $a->balanceFor($year);
                $sum += $bal;
                $rows[] = ['account' => $a, 'balance' => $bal];
            }
            $groups[] = ['label' => $label, 'rows' => $rows, 'sum' => $sum];
        }
        return $this->render('account_summary', ['year' => $year, 'groups' => $groups]);
    }

    // ---- โอนเงินข้ามบัญชี + ทะเบียนคุม ------------------------------------

    public function actionTransfer($year = null)
    {
        $year = (int) ($year ?: FinanceCashTxn::currentFiscalYear());
        $accounts = FinanceCashAccount::find()->where(['is_active' => 1])
            ->orderBy(['account_type' => SORT_ASC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        return $this->render('transfer_list', [
            'year' => $year,
            'accounts' => $accounts,
            'accountList' => FinanceCashAccount::activeList(),
        ]);
    }

    public function actionTransferSave()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $t = new FinanceCashTransfer();
        $t->from_account_id = (int) ($post['from_account_id'] ?? 0);
        $t->to_account_id = (int) ($post['to_account_id'] ?? 0);
        $t->amount = $post['amount'] ?? 0;
        $t->doc_ref = trim((string) ($post['doc_ref'] ?? '')) ?: null;
        $t->transfer_date = AppHelper::normalizeDateToDb($post['transfer_date'] ?? null);
        $t->fiscal_year = FinanceCashTxn::currentFiscalYear();
        $t->note = trim((string) ($post['note'] ?? '')) ?: null;
        if ($t->save()) {
            return ['ok' => true, 'message' => 'บันทึกการโอนเงินเรียบร้อย'];
        }
        return ['ok' => false, 'errors' => $t->getErrors()];
    }

    /** ทะเบียนคุมบัญชีเงินฝาก (statement รายเดือน) → Excel */
    public function actionRegister($id, $y = null, $m = null)
    {
        $account = FinanceCashAccount::findOne((int) $id);
        if (!$account) {
            throw new NotFoundHttpException('ไม่พบบัญชี');
        }
        $y = (int) ($y ?: (date('n') >= 10 ? date('Y') + 544 : date('Y') + 543));
        $m = (int) ($m ?: date('n'));
        $book = CashReportService::accountRegisterSpreadsheet($account, $y, $m);
        $fileName = 'ทะเบียนคุม_' . ($account->code ?: $account->name) . '_' . $m . '-' . $y . '.xlsx';
        $path = Yii::getAlias('@runtime') . '/cash_register_' . date('YmdHis') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($path);
        return Yii::$app->response->sendFile($path, $fileName, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->on(\yii\web\Response::EVENT_AFTER_SEND, function () use ($path) {
            @unlink($path);
        });
    }
}
