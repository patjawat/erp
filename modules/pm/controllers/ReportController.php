<?php

namespace app\modules\pm\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * รายงานโครงการ (จะพัฒนาในเฟส 4 — แบบรายงานผลการดำเนินงาน)
 */
class ReportController extends Controller
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

    public function actionIndex()
    {
        return $this->render('index');
    }
}
