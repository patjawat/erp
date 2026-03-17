<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Controller;
use yii\db\Expression;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\helpdesk2\helpers\HelpdeskSlaHelper;

class DashboardController extends Controller
{
    public function actionIndex()
    {
        $query = Helpdesk::find()->where(['name' => 'repair']);

        // KPI
        $totalTickets = (clone $query)->count();
        $openTickets = (clone $query)->andWhere(['status' => ['pending', 'receive']])->count();
        $inProgressTickets = (clone $query)->andWhere(['status' => 'in_progress'])->count();
        $resolvedToday = (clone $query)
            ->andWhere(['status' => 'success'])
            ->andWhere(new Expression('DATE(updated_at) = CURDATE()'))
            ->count();

        // Status summary for chart
        $statusSummary = (clone $query)
            ->select(['status', 'cnt' => 'COUNT(*)'])
            ->groupBy(['status'])
            ->asArray()
            ->all();

        // Recent tickets
        $recentTickets = (clone $query)
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();

        // Top categories by device type
        $topCategories = (clone $query)
            ->select(['device_type_id', 'cnt' => 'COUNT(*)'])
            ->groupBy(['device_type_id'])
            ->orderBy(['cnt' => SORT_DESC])
            ->limit(5)
            ->asArray()
            ->all();

        // Staff workload (summary per technician)
        $staffWorkload = Helpdesk::find()
            ->alias('h')
            ->select([
                'e.id AS emp_id',
                "CONCAT(e.fname, ' ', e.lname) AS fullname",
                'COUNT(h.id) AS total',
                "SUM(CASE WHEN h.status IN ('pending','receive') THEN 1 ELSE 0 END) AS open_total",
                "SUM(CASE WHEN h.status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_total",
                "SUM(CASE WHEN h.status = 'success' THEN 1 ELSE 0 END) AS success_total",
            ])
            ->innerJoin('{{%helpdesk_detail}} d', 'd.helpdesk_id = h.id AND d.name = :name', [':name' => 'service_record'])
            ->innerJoin('{{%employees}} e', 'e.id = d.emp_id')
            ->where(['h.name' => 'repair'])
            ->groupBy(['e.id', 'e.fname', 'e.lname'])
            ->orderBy(['total' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // SLA overview
        $slaTickets = (clone $query)
            ->andWhere(['status' => ['pending', 'receive', 'in_progress']])
            ->orderBy(['created_at' => SORT_ASC])
            ->limit(100)
            ->all();

        $slaNear = 0;
        $slaBreached = 0;
        foreach ($slaTickets as $ticket) {
            $info = HelpdeskSlaHelper::calculate($ticket);
            if ($info['status'] === 'near') {
                $slaNear++;
            } elseif ($info['status'] === 'breached') {
                $slaBreached++;
            }
        }

        return $this->render('index', [
            'totalTickets' => $totalTickets,
            'openTickets' => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'resolvedToday' => $resolvedToday,
            'statusSummary' => $statusSummary,
            'recentTickets' => $recentTickets,
            'topCategories' => $topCategories,
            'staffWorkload' => $staffWorkload,
            'slaNear' => $slaNear,
            'slaBreached' => $slaBreached,
        ]);
    }
}

