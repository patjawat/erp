<?php

namespace app\modules\approve\controllers;

use Yii;
use app\models\Event;
use yii\web\Controller;
use app\components\EventCalendar;

class CalendarController extends Controller
{
    public function actionIndex()
    {
        $year = Yii::$app->request->get('year', date('Y'));
        $month = Yii::$app->request->get('month', date('m'));
        
        $events = $this->getEvents($year, $month);
        
        return $this->render('index', [
            'year' => $year,
            'month' => $month,
            'events' => $events
        ]);
    }
    
    public function actionLoad()
    {
        $year = Yii::$app->request->get('year', date('Y'));
        $month = Yii::$app->request->get('month', date('m'));
        
        $events = $this->getEvents($year, $month);
        
        $calendar = EventCalendar::widget([
            'year' => $year,
            'month' => $month,
            'events' => $events
        ]);
        
        return $calendar;
    }
    
    private function getEvents($year, $month)
    {
        // ดึงข้อมูลอีเวนต์จากฐานข้อมูลสำหรับเดือนและปีที่กำหนด
        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        $events = Event::find()
            ->where(['between', 'date', $startDate, $endDate])
            ->asArray()
            ->all();
            
        return $events;
    }
}