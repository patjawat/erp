<?php

namespace app\modules\accounting\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePayable;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['accountingView']]],
            ],
        ]);
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'inboxPending' => (int) FinanceInbox::find()->where(['status' => FinanceInbox::STATUS_PENDING_REVIEW])->count(),
            'payableDraft' => (int) FinancePayable::find()->where(['status' => [FinancePayable::STATUS_DRAFT, FinancePayable::STATUS_NEEDS_REVISION]])->count(),
            'payablePending' => (int) FinancePayable::find()->where(['status' => FinancePayable::STATUS_PENDING_APPROVAL])->count(),
            'payableApproved' => (int) FinancePayable::find()->where(['status' => FinancePayable::STATUS_APPROVED])->count(),
        ]);
    }
}
