<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/** Compatibility routes. Creditor accounting now lives in the accounting module. */
class PayableController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['accountingView']],
                ['allow' => true, 'actions' => ['create', 'update', 'submit'], 'roles' => ['accountingPrepare']],
                ['allow' => true, 'actions' => ['review'], 'roles' => ['accountingReview', 'accountingApprove']],
            ]],
        ]);
    }

    public function actionIndex() { return $this->forwardTo('/accounting/payable/index'); }
    public function actionView($id) { return $this->forwardTo('/accounting/payable/view', ['id' => $id]); }
    public function actionCreate($inbox_id) { return $this->forwardTo('/accounting/payable/create', ['inbox_id' => $inbox_id]); }
    public function actionUpdate($id) { return $this->forwardTo('/accounting/payable/update', ['id' => $id]); }
    public function actionSubmit($id) { return $this->forwardTo('/accounting/payable/submit', ['id' => $id]); }
    public function actionReview($id) { return $this->forwardTo('/accounting/payable/review', ['id' => $id]); }

    private function forwardTo(string $route, array $params = [])
    {
        $params = array_merge([$route], Yii::$app->request->queryParams, $params);
        return $this->redirect($params, Yii::$app->request->isPost ? 307 : 302);
    }
}
