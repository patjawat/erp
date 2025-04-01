<?php

namespace app\modules\approve\controllers;

use Yii;
use yii\web\Controller;
use app\modules\hr\models\Leave;
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
        // คำนวณวันแรกและวันสุดท้ายของเดือน
        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        // ดึงอีเวนต์ที่อยู่ในช่วงของเดือนนี้
        // 1. อีเวนต์ที่เริ่มต้นในเดือนนี้
        // 2. อีเวนต์ที่สิ้นสุดในเดือนนี้
        // 3. อีเวนต์ที่เริ่มต้นก่อนเดือนนี้แต่สิ้นสุดหลังเดือนนี้ (ครอบคลุมเดือนนี้)
        $query = Leave::find()
            ->where(['or',
                ['between', 'date_start', $startDate, $endDate],
                ['between', 'date_end', $startDate, $endDate],
                ['and',
                    ['<', 'date_start', $startDate],
                    ['>', 'date_end', $endDate]
                ]
            ]);
            
        $eventsData = [];
        $events = $query->all();
        
        // แปลงข้อมูลอีเวนต์เพื่อให้ EventCalendar Widget สามารถนำไปใช้งานได้
        foreach ($events as $event) {
            $eventsData[] = [
                'id' => $event->id,
                'title' => $event->data_json['reason'],
                'description' => $event->data_json['reason'] ?? '-',
                'date_start' => $event->date_start,
                'date_end' => $event->date_end,
                'color' => $event->color ?? '#673ab79c',
            ];
        }
            
        return $eventsData;
    }
}