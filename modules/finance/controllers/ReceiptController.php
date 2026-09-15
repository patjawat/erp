<?php

namespace app\modules\finance\controllers;

use app\components\AppHelper;
use app\modules\finance\models\FinanceCashTxn;
use app\modules\finance\models\FinanceReceiptBook;
use app\modules\hr\models\Employees;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ทะเบียนคุมใบเสร็จรับเงิน (เฟส A) — /finance/receipt
 * รับเล่มเข้า → เบิกจ่ายให้ จนท. → ใช้บันทึกรายรับ (คุมใช้แล้ว/คงเหลือ/ซ้ำ-ข้าม)
 */
class ReceiptController extends Controller
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
                'actions' => ['save' => ['post'], 'issue' => ['post'], 'delete' => ['post']],
            ],
        ]);
    }

    public function actionIndex($status = null, $emp = null)
    {
        $query = FinanceReceiptBook::find()->with('issuedTo')->orderBy(['book_no' => SORT_ASC]);
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        if ($emp) {
            $query->andWhere(['issued_to_emp_id' => (int) $emp]);
        }
        return $this->render('index', [
            'books' => $query->all(),
            'status' => $status,
            'emp' => $emp,
            'employees' => $this->employeeList(),
        ]);
    }

    public function actionView($id)
    {
        $book = FinanceReceiptBook::findOne((int) $id);
        if (!$book) {
            throw new NotFoundHttpException('ไม่พบเล่มใบเสร็จ');
        }
        // เลขที่ใช้แล้วในเล่ม (จากรายการรับ)
        $docs = FinanceCashTxn::find()->select(['doc_no', 'id', 'doc_date', 'amount'])
            ->where(['txn_type' => 'IN'])->andWhere(['like', 'doc_no', $book->book_no . '/%', false])
            ->orderBy(['doc_no' => SORT_ASC])->all();
        $usedNums = [];
        foreach ($docs as $t) {
            $parts = explode('/', (string) $t->doc_no, 2);
            if (count($parts) === 2 && $parts[0] === $book->book_no) {
                $usedNums[(int) $parts[1]][] = $t;
            }
        }
        return $this->render('view', ['book' => $book, 'usedNums' => $usedNums]);
    }

    /** ฟอร์มที่ 1: รับเล่มใบเสร็จเข้า (สร้าง/แก้ข้อมูลเล่ม) — ไม่ยุ่งกับการเบิก */
    public function actionSave()
    {
        $post = Yii::$app->request->post();
        $id = (int) ($post['id'] ?? 0);
        $model = $id ? FinanceReceiptBook::findOne($id) : new FinanceReceiptBook();
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเล่มใบเสร็จ');
        }
        $model->book_no = trim((string) ($post['book_no'] ?? ''));
        $model->number_from = (int) ($post['number_from'] ?? 0);
        $model->number_to = (int) ($post['number_to'] ?? 0);
        $model->receipt_type = trim((string) ($post['receipt_type'] ?? '')) ?: null;
        $model->received_date = AppHelper::normalizeDateToDb($post['received_date'] ?? null);
        $model->note = trim((string) ($post['note'] ?? '')) ?: null;
        if ($model->isNewRecord) {
            $model->status = FinanceReceiptBook::STATUS_RECEIVED;
        }
        // ไม่แตะ issued_to_emp_id / issued_date / status เดิม — ทำผ่านฟอร์ม "เบิกจ่าย" แยก

        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกเล่มใบเสร็จเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['index']);
    }

    /** ฟอร์มที่ 2: เบิกจ่ายเล่มให้เจ้าหน้าที่ (แยกจากการรับเข้า) */
    public function actionIssue()
    {
        $post = Yii::$app->request->post();
        $model = FinanceReceiptBook::findOne((int) ($post['id'] ?? 0));
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเล่มใบเสร็จ');
        }
        $empId = (int) ($post['issued_to_emp_id'] ?? 0);
        $status = $post['status'] ?? '';
        $model->issued_to_emp_id = $empId ?: null;
        $model->issued_date = AppHelper::normalizeDateToDb($post['issued_date'] ?? null);
        if (in_array($status, [FinanceReceiptBook::STATUS_ISSUED, FinanceReceiptBook::STATUS_COMPLETED, FinanceReceiptBook::STATUS_CANCELLED], true)) {
            $model->status = $status;
        } elseif ($empId) {
            $model->status = FinanceReceiptBook::STATUS_ISSUED;
        }
        if (!$empId && $model->status === FinanceReceiptBook::STATUS_ISSUED) {
            $model->status = FinanceReceiptBook::STATUS_RECEIVED;
        }
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกการเบิกจ่ายเรียบร้อย');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['index']);
    }

    public function actionDelete()
    {
        $model = FinanceReceiptBook::findOne((int) Yii::$app->request->post('id'));
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบเล่มใบเสร็จ');
        }
        $used = $model->usage()['used'];
        if ($used > 0) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะมีการใช้เลขในเล่มนี้บันทึกรายรับแล้ว (ใช้สถานะ “ยกเลิก” แทน)');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบเล่มใบเสร็จเรียบร้อย');
        }
        return $this->redirect(['index']);
    }

    private function employeeList(): array
    {
        $rows = Employees::find()->where(['status' => '1'])->all();
        $list = [];
        foreach ($rows as $e) {
            $list[$e->id] = $e->fullname();
        }
        asort($list);
        return $list;
    }
}
