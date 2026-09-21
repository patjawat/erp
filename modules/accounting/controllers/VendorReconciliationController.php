<?php

namespace app\modules\accounting\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\modules\accounting\services\PurchaseVendorReconciliationService;
use app\modules\finance\services\FinancePayableDraftService;

class VendorReconciliationController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index'], 'roles' => ['accountingView']],
            ]],
        ]);
    }

    public function actionIndex()
    {
        $year = (int) Yii::$app->request->get('year', FinancePayableDraftService::fiscalYearForDate(date('Y-m-d')));
        if ($year < 2500 || $year > 2700) $year = FinancePayableDraftService::fiscalYearForDate(date('Y-m-d'));
        $status = (string) Yii::$app->request->get('status', '');
        if (!in_array($status, ['', PurchaseVendorReconciliationService::MATCHED, PurchaseVendorReconciliationService::REVIEW, PurchaseVendorReconciliationService::MISSING], true)) $status = '';
        $report = (new PurchaseVendorReconciliationService())->report($year);
        $rows = $status === '' ? $report['rows'] : array_values(array_filter($report['rows'], static fn(array $row) => $row['status'] === $status));
        return $this->render('index', [
            'year' => $year, 'status' => $status, 'report' => $report,
            'dataProvider' => new ArrayDataProvider(['allModels' => $rows, 'pagination' => ['pageSize' => 30]]),
        ]);
    }
}
