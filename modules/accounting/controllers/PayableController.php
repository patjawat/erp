<?php

namespace app\modules\accounting\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/** Compatibility routes. ทะเบียนคุมเจ้าหนี้ย้ายไปโดเมนการเงินแล้ว (/finance/payable). */
class PayableController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view', 'aging', 'letter'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['create', 'update', 'submit', 'pay'], 'roles' => ['financeOperate']],
                ['allow' => true, 'actions' => ['review'], 'roles' => ['financeApprove', 'financeOperate']],
            ]],
        ]);
    }

    public function actionIndex() { return $this->forwardTo('/finance/payable/index'); }
    public function actionView($id) { return $this->forwardTo('/finance/payable/view', ['id' => $id]); }
    public function actionAging() { return $this->forwardTo('/finance/payable/aging'); }
    public function actionPay() { return $this->forwardTo('/finance/payable/pay'); }
    public function actionLetter($id) { return $this->forwardTo('/finance/payable/letter', ['id' => $id]); }
    public function actionCreate($inbox_id) { return $this->forwardTo('/finance/payable/create', ['inbox_id' => $inbox_id]); }
    public function actionUpdate($id) { return $this->forwardTo('/finance/payable/update', ['id' => $id]); }
    public function actionSubmit($id) { return $this->forwardTo('/finance/payable/submit', ['id' => $id]); }
    public function actionReview($id) { return $this->forwardTo('/finance/payable/review', ['id' => $id]); }

    private function forwardTo(string $route, array $params = [])
    {
        $params = array_merge([$route], Yii::$app->request->queryParams, $params);
        return $this->redirect($params, Yii::$app->request->isPost ? 307 : 302);
    }
}
