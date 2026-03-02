<?php

namespace app\modules\health\controllers;

use app\components\AppHelper;
use app\modules\health\models\HealthScreen;
use app\modules\health\models\HealthScreenSearch;
use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Default controller for the `health` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
     public function actionIndex()
    {
        $searchModel = new HealthScreenSearch(['thai_year' => AppHelper::YearBudget(date('Y-m-d'))]);
        $dataProvider = $searchModel->search($this->request->queryParams);

        $bmiData = $searchModel->getBmiChartData();
        $stats = $searchModel->getDeptExamStats();
        $kpiStats = $searchModel->getKpiStats();
        $diseaseStats = $searchModel->getDiseaseHistoryStats();
        $riskTrend = $searchModel->getRiskTrendByYear(5);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'bmiData' => $bmiData,
            'stats' => $stats,
            'kpiStats' => $kpiStats,
            'diseaseStats' => $diseaseStats,
            'riskTrend' => $riskTrend,
        ]);
    }

    public function actionList()
    {
        $searchModel = new HealthScreenSearch(['thai_year' => AppHelper::YearBudget(date('Y-m-d'))]);
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->joinWith('employee');

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * คืนค่า events สำหรับปฏิทินการนัดหมาย (FullCalendar)
     * GET start, end (ISO), thai_year (optional)
     */
    public function actionCalendarEvents()
    {
        $this->request->setQueryParams($this->request->get());
        $start = $this->request->get('start');
        $end = $this->request->get('end');
        $thaiYear = $this->request->get('thai_year') ?: AppHelper::YearBudget(date('Y-m-d'));

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($start) || empty($end)) {
            return [];
        }

        $query = HealthScreen::find()
            ->innerJoinWith('employee')
            ->where(['not', ['health_screen.appointment_date' => null]])
            ->andWhere(['health_screen.thai_year' => $thaiYear])
            ->andWhere(['>=', 'health_screen.appointment_date', date('Y-m-d', strtotime($start))])
            ->andWhere(['<=', 'health_screen.appointment_date', date('Y-m-d', strtotime($end))])
            ->orderBy(['health_screen.appointment_date' => SORT_ASC]);

        $models = $query->all();
        $events = [];
        foreach ($models as $m) {
            $empName = $m->employee ? $m->employee->fullname() : 'ไม่ทราบ';
            $statusLabel = HealthScreen::getHealthStatusDisplay($m->health_status, 'label');
            $events[] = [
                'id' => $m->id,
                'title' => $empName . ' (' . $statusLabel . ')',
                'start' => $m->appointment_date . 'T09:00:00',
                'allDay' => true,
                'url' => \yii\helpers\Url::to(['/health/health-screen/lab-confirm', 'id' => $m->id]),
                'extendedProps' => [
                    'empName' => $empName,
                    'status' => $m->health_status,
                    'statusLabel' => $statusLabel,
                ],
            ];
        }
        return $events;
    }
}
