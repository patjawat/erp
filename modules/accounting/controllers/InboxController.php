<?php

namespace app\modules\accounting\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/** Compatibility routes. งานกล่องรับเจ้าหนี้ย้ายไปโดเมนการเงินแล้ว (/finance/inbox). */
class InboxController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['receive-purchase'], 'roles' => ['accountingInboxReceive']],
                ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['financeView']],
                ['allow' => true, 'actions' => ['review'], 'roles' => ['financeOperate']],
            ]],
        ]);
    }

    public function actionIndex() { return $this->forwardTo('/finance/inbox/index'); }
    public function actionView($id) { return $this->forwardTo('/finance/inbox/view', ['id' => $id]); }
    public function actionReceivePurchase($id) { return $this->forwardTo('/finance/inbox/receive-purchase', ['id' => $id]); }
    public function actionReview($id) { return $this->forwardTo('/finance/inbox/review', ['id' => $id]); }

    private function forwardTo(string $route, array $params = [])
    {
        $params = array_merge([$route], Yii::$app->request->queryParams, $params);
        return $this->redirect($params, Yii::$app->request->isPost ? 307 : 302);
    }
}
