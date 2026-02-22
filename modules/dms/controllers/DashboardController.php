<?php

namespace app\modules\dms\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;
use app\modules\dms\models\DocumentSearch;

class DashboardController extends \yii\web\Controller
{
    /** Cache duration สำหรับข้อมูล dashboard (วินาที) */
    const DASHBOARD_CACHE_DURATION = 120;

    public function actionIndex()
    {
        $searchModel = new DocumentSearch([
            'thai_year' => (Date('Y') + 543),
        ]);
        $searchModel->load($this->request->queryParams);

        // ไม่โหลดรายการเอกสาร (dashboard ไม่ใช้) ลด query
        $dataProvider = new ArrayDataProvider([
            'allModels' => [],
            'totalCount' => 0,
        ]);

        // โหลดข้อมูล dashboard ชุดเดียว แคช 2 นาที
        $cacheKey = 'dms_dashboard_' . $searchModel->thai_year;
        $cache = Yii::$app->cache;
        $dashboardData = $cache ? $cache->get($cacheKey) : false;

        if ($dashboardData === false) {
            $dashboardData = [
                'counts' => $searchModel->getCountsByGroup(),
                'chartReceive' => $searchModel->getChartSummary('receive'),
                'chartSend' => $searchModel->getChartSummary('send'),
                'summaryOrg' => $searchModel->summaryOrg(),
                'summaryDocSpeed' => $searchModel->summaryDocSpeed(),
                'summaryDocType' => $searchModel->summaryDocType(),
            ];
            if ($cache) {
                $cache->set($cacheKey, $dashboardData, self::DASHBOARD_CACHE_DURATION);
            }
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'counts' => $dashboardData['counts'],
            'chartReceive' => $dashboardData['chartReceive'],
            'chartSend' => $dashboardData['chartSend'],
            'summaryOrg' => $dashboardData['summaryOrg'],
            'summaryDocSpeed' => $dashboardData['summaryDocSpeed'],
            'summaryDocType' => $dashboardData['summaryDocType'],
        ]);
    }
}
