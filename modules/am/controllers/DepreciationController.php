<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\modules\am\services\MonthlyDepreciationService;

/**
 * Depreciation processing: monthly run and related actions.
 */
class DepreciationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    /**
     * Monthly depreciation processing: select month/year, run or regenerate.
     */
    public function actionMonthlyProcessing()
    {
        $fiscalYear = (int) ($this->request->post('fiscal_year') ?: $this->request->get('fiscal_year') ?: date('Y'));
        $month = (int) ($this->request->post('month') ?: $this->request->get('month') ?: date('n'));
        $month = max(1, min(12, $month));
        $force = (bool) $this->request->post('force', false);

        $message = null;
        $result = null;

        if ($this->request->isPost) {
            $action = $this->request->post('action', 'run');
            if ($action === 'regenerate' && $force) {
                $result = MonthlyDepreciationService::runForMonth($fiscalYear, $month, true);
            } elseif ($action === 'run') {
                $result = MonthlyDepreciationService::runForMonth($fiscalYear, $month, false);
            }
            if ($result !== null) {
                $message = $result['message'];
                if ($result['success']) {
                    if (isset($result['created']) && $result['created'] === 0) {
                        $message .= ' — ถ้าไม่ตรงกับที่คาดหวัง กรุณาตรวจสอบว่าครุภัณฑ์มี ราคา, วันที่รับเข้า, อายุการใช้งาน (ปี) ระบุแล้ว และอยู่ในช่วงอายุของเดือนที่เลือก';
                    }
                    Yii::$app->session->setFlash('success', $message);
                } else {
                    Yii::$app->session->setFlash('error', $message);
                }
                return $this->redirect(['monthly-processing', 'fiscal_year' => $fiscalYear, 'month' => $month]);
            }
        }

        $schema = Yii::$app->db->getSchema()->getTableSchema('{{%am_asset_depreciation_monthly}}', true);
        $tableExists = $schema !== null;

        return $this->render('monthly-processing', [
            'fiscalYear' => $fiscalYear,
            'month' => $month,
            'message' => $message,
            'tableExists' => $tableExists,
        ]);
    }
}
