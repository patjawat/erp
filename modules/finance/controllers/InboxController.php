<?php

namespace app\modules\finance\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/** Compatibility routes. Accounting work now lives in the accounting module. */
class InboxController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['receive-purchase'], 'roles' => ['accountingInboxReceive']],
                ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['accountingView']],
                ['allow' => true, 'actions' => ['review'], 'roles' => ['accountingPrepare']],
            ]],
        ]);
    }

    public function actionIndex()
    {
        return $this->forwardTo('/accounting/inbox/index');
    }

    public function actionView($id)
    {
        return $this->forwardTo('/accounting/inbox/view', ['id' => $id]);
    }

    public function actionReceivePurchase($id)
    {
        return $this->forwardTo('/accounting/inbox/receive-purchase', ['id' => $id]);
    }

    public function actionReview($id)
    {
        return $this->forwardTo('/accounting/inbox/review', ['id' => $id]);
    }

    private function forwardTo(string $route, array $params = [])
    {
        $params = array_merge([$route], Yii::$app->request->queryParams, $params);
        return $this->redirect($params, Yii::$app->request->isPost ? 307 : 302);
    }
}
