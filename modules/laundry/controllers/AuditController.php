<?php

namespace app\modules\laundry\controllers;

use app\modules\laundry\services\InventoryAuditReport;
use yii\filters\AccessControl;
use yii\web\Controller;

class AuditController extends Controller
{
    public function behaviors(): array
    {
        return ['access' => ['class' => AccessControl::class, 'rules' => [
            ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.view']],
            ['allow' => true, 'actions' => ['index'], 'roles' => ['laundry.approve']],
        ]]];
    }

    public function actionIndex()
    {
        $report = (new InventoryAuditReport())->build();
        return $this->render('index', ['report' => $report]);
    }
}
