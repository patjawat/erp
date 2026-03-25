<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Controller;
use app\modules\helpdesk2\helpers\RepairDashboardV2Helper;

class DashboardController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index', array_merge(
            RepairDashboardV2Helper::prepareViewParams(null),
            [
                'pageTitle' => 'แดชบอร์ดงานซ่อม (ทุกศูนย์)',
            ]
        ));
    }
}

