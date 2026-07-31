<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Controller;
use app\components\AppHelper;
use app\modules\plan\models\PlanOrder;

/**
 * Default controller for the `plan` module
 */
class OverviewController extends Controller
{
    /**
     * หน้า "ติดตามแผนรายจ่าย" — รวมยอดตามประเภท/หมวดผ่านสาย item ที่สะอาด
     * @return string
     */
    public function actionIndex()
    {
        $thaiYear = (int) $this->request->get('thai_year', AppHelper::YearBudget());
        $status   = (string) $this->request->get('status', 'all'); // ค่าเริ่มต้น = ทั้งหมด

        $summary = PlanOrder::overviewByType($thaiYear, $status);
        $years   = (new PlanOrder())->ListThaiYear();

        return $this->render('index', [
            'thaiYear' => $thaiYear,
            'years'    => $years,
            'status'   => $status,
            'summary'  => $summary,
        ]);
    }
}
