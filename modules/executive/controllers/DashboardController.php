<?php

namespace app\modules\executive\controllers;

use app\modules\executive\services\ExecutiveDashboardService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => ['executiveDashboardView', 'financeView', 'admin'],
                ]],
            ],
        ]);
    }

    public function actionIndex()
    {
        $service = new ExecutiveDashboardService(Yii::$app->db);
        $fiscalYear = (int) $this->request->get('year', 0);

        return $this->render('index', [
            'summary' => $service->getSummary($fiscalYear ?: null),
        ]);
    }

    public function actionInventory()
    {
        $service = new ExecutiveDashboardService(Yii::$app->db);
        $fiscalYear = (int) $this->request->get('year', 0);

        return $this->render('inventory', [
            'dashboard' => $service->getInventoryDashboard($fiscalYear ?: null),
        ]);
    }

    public function actionSubWarehouse(int $id)
    {
        $service = new ExecutiveDashboardService(Yii::$app->db);
        $fiscalYear = (int) $this->request->get('year', 0);

        return $this->render('sub-warehouse', [
            'detail' => $service->getSubWarehouseDetail($id, $fiscalYear ?: null),
        ]);
    }

    public function actionInventoryAlerts(string $type)
    {
        $service = new ExecutiveDashboardService(Yii::$app->db);

        return $this->render('inventory-alerts', [
            'detail' => $service->getInventoryAlertDetails($type),
        ]);
    }
}
