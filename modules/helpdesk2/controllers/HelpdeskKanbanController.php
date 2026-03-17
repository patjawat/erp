<?php

namespace app\modules\helpdesk2\controllers;

use Yii;
use yii\web\Controller;
use app\modules\helpdesk2\models\Helpdesk;

class HelpdeskKanbanController extends Controller
{
    public function actionIndex()
    {
        $tickets = Helpdesk::find()
            ->where(['name' => 'repair'])
            ->andWhere(['status' => ['pending', 'receive', 'in_progress', 'success', 'cancel']])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();

        $columns = [
            'open' => [],
            'in_progress' => [],
            'pending' => [],
            'resolved' => [],
            'closed' => [],
        ];

        foreach ($tickets as $ticket) {
            switch ($ticket->status) {
                case 'pending':
                case 'receive':
                    $columns['open'][] = $ticket;
                    break;
                case 'in_progress':
                    $columns['in_progress'][] = $ticket;
                    break;
                case 'success':
                    $columns['resolved'][] = $ticket;
                    break;
                case 'cancel':
                    $columns['closed'][] = $ticket;
                    break;
                default:
                    $columns['pending'][] = $ticket;
                    break;
            }
        }

        return $this->render('index', [
            'columns' => $columns,
        ]);
    }
}

